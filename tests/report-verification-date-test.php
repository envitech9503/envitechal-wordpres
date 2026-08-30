<?php
/**
 * Date parsing for the report registry importer.
 *
 * The day-first cases are the point of this file. PHP's strtotime() reads
 * 03/04/2026 as 4 March, so a report issued on 3 April would import under the
 * wrong date and then fail every verification with no error shown anywhere.
 *
 * Run: php tests/report-verification-date-test.php
 */

$module = __DIR__ . '/../wp-content/themes/generatepress-envitechal/inc/report-verification.php';
$source = file_get_contents($module);
$start  = strpos($source, 'function eta_verify_parse_date');
$end    = strpos($source, 'function eta_verify_import_csv');
if ($start === false || $end === false || $end <= $start) {
    fwrite(STDERR, "Could not isolate eta_verify_parse_date() from the module.\n");
    exit(1);
}
eval(substr($source, $start, $end - $start));

$cases = [
    ['2026-03-14', '2026-03-14', 'ISO'],
    ['2026/03/14', '2026-03-14', 'ISO with slashes'],
    ['14/03/2026', '2026-03-14', 'day-first slashes'],
    ['03/04/2026', '2026-04-03', 'ambiguous date must be read day-first'],
    ['14-03-2026', '2026-03-14', 'day-first dashes'],
    ['14.03.2026', '2026-03-14', 'day-first dots'],
    ['1/9/2026',   '2026-09-01', 'single digit day and month'],
    ['14 Mar 2026', '2026-03-14', 'abbreviated month name'],
    ['14 March 2026', '2026-03-14', 'full month name'],
    ['31/02/2026', null, 'impossible date rejected'],
    ['14/13/2026', null, 'month 13 rejected'],
    ['not a date', null, 'unparseable text rejected'],
    ['',           null, 'empty cell rejected'],
];

$failures = 0;
foreach ($cases as $case) {
    list($input, $expected, $label) = $case;
    $actual = eta_verify_parse_date($input);
    if ($actual !== $expected) {
        $failures++;
        printf(
            "FAIL  %-15s gave %-14s expected %-14s (%s)\n",
            "'" . $input . "'",
            var_export($actual, true),
            var_export($expected, true),
            $label
        );
    }
}

if ($failures > 0) {
    printf("\n%d date parsing failure(s).\n", $failures);
    exit(1);
}

echo "Report registry date parsing tests passed.\n";
