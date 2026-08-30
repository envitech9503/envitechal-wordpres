<?php
/**
 * Report registry CSV import, against the export formats a real spreadsheet
 * produces: Excel's UTF-8 BOM, CRLF endings, and semicolon or tab separators.
 *
 * The laboratory assertion is load bearing. When the header line is trimmed of
 * LF but not CR, the final column name keeps a stray carriage return and that
 * column silently stops mapping.
 *
 * Run: php tests/report-verification-import-test.php
 */

class FakeWpdb {
    public $prefix = 'wp_';
    public $rows = [];
    public function replace($t, $data, $fmt) { $this->rows[$data['report_number']] = $data; return 1; }
    public function get_var($q) { return null; }
    public function prepare($q, ...$a) { return $q; }
    public function get_charset_collate() { return ''; }
}
$wpdb = new FakeWpdb();
function eta_verify_table_name() { return 'wp_eta_report_registry'; }
function current_time($t) { return '2026-08-31 00:00:00'; }

$src = file_get_contents(__DIR__ . '/../wp-content/themes/generatepress-envitechal/inc/report-verification.php');
$start = strpos($src, 'function eta_verify_parse_date');
$end   = strpos($src, '/* ------------------------------------------------------------------ *', $start);
eval(substr($src, $start, $end - $start));

function build($name, $bom, $eol, $delim) {
    $head = ['report_number','report_date','client_name','status','laboratory'];
    $rows = [
        ['ETA/QA/A-1','14/03/2026','Artistic Milliners','valid','LAB-285'],
        ['ETA/QA/A-2','03/04/2026','Soorty Enterprises','','LAB-285'],
        ['ETA/QA/A-3','2026-01-09','WWF','superseded','LAB-347'],
        ['ETA/QA/A-4','14 March 2026',' Crown Textile ','VALID','LAB-347'],
        ['ETA/QA/A-1','20/05/2026','Artistic Milliners','valid','LAB-285'], // duplicate, must update
        ['','10/10/2026','Missing Number','valid','LAB-285'],               // skip
        ['ETA/QA/A-6','31/02/2026','Impossible','valid','LAB-285'],         // skip
        ['ETA/QA/A-7','banana','Bad Date','valid','LAB-285'],               // skip
    ];
    $out = ($bom ? chr(0xEF).chr(0xBB).chr(0xBF) : '') . implode($delim, $head) . $eol;
    foreach ($rows as $r) { $out .= implode($delim, $r) . $eol; }
    $out .= $eol;
    $p = sys_get_temp_dir() . '/' . $name;
    file_put_contents($p, $out);
    return $p;
}

$variants = [
    'plain comma LF'          => [false, "\n",   ','],
    'Excel BOM + CRLF comma'  => [true,  "\r\n", ','],
    'BOM + CRLF semicolon'    => [true,  "\r\n", ';'],
    'tab separated CRLF'      => [false, "\r\n", "\t"],
];

$fail = 0;
foreach ($variants as $label => $v) {
    $wpdb->rows = [];
    $path = build('eta_t_' . md5($label) . '.csv', $v[0], $v[1], $v[2]);
    $r = eta_verify_import_csv($path);
    $ok = ($r['imported'] === 5 && $r['skipped'] === 3);
    // A-1 must hold the SECOND date (duplicate updates, not duplicates)
    $a1 = isset($wpdb->rows['ETA/QA/A-1']) ? $wpdb->rows['ETA/QA/A-1']['report_date'] : 'MISSING';
    $a2 = isset($wpdb->rows['ETA/QA/A-2']) ? $wpdb->rows['ETA/QA/A-2']['report_date'] : 'MISSING';
    $a4 = isset($wpdb->rows['ETA/QA/A-4']) ? $wpdb->rows['ETA/QA/A-4'] : null;
    $lab = $a4 ? $a4['laboratory'] : 'MISSING';
    $client = $a4 ? $a4['client_label'] : 'MISSING';
    $status = $a4 ? $a4['status'] : 'MISSING';
    $checks = [
        'imported=5'        => $r['imported'] === 5,
        'skipped=3'         => $r['skipped'] === 3,
        'dup updated'       => $a1 === '2026-05-20',
        'day-first 3 Apr'   => $a2 === '2026-04-03',
        'laboratory kept'   => $lab === 'LAB-347',
        'client trimmed'    => $client === 'Crown Textile',
        'status lowercased' => $status === 'valid',
        'unique rows=4'     => count($wpdb->rows) === 4,
    ];
    $bad = array_keys(array_filter($checks, function ($x) { return !$x; }));
    if ($bad) { $fail++; printf("FAIL  %-24s -> %s\n", $label, implode(', ', $bad)); }
    else      { printf("ok    %-24s imported=%d skipped=%d\n", $label, $r['imported'], $r['skipped']); }
    @unlink($path);
}

// Header without required columns
$p = sys_get_temp_dir() . '/eta_bad_head.csv';
file_put_contents($p, "number,date\nX,2026-01-01\n");
$r = eta_verify_import_csv($p);
if ($r['imported'] !== 0 || empty($r['reasons'])) { $fail++; echo "FAIL  missing headers not reported\n"; }
else { echo "ok    missing headers rejected with a reason\n"; }
@unlink($p);

echo $fail ? "\n$fail VARIANT FAILURES\n" : "\nAll CSV import variants pass.\n";
exit($fail ? 1 : 0);
