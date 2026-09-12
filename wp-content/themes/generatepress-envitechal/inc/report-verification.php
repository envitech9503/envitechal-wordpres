<?php
/**
 * Instant report verification.
 *
 * A private registry of issued report numbers, a rate-limited lookup endpoint,
 * and a CSV importer so the registry can be fed from a spreadsheet or a LIMS
 * export without either being wired in directly.
 *
 * Privacy and abuse posture:
 * - A lookup requires the report number AND its issue date. Knowing a number
 *   alone confirms nothing, so the endpoint cannot be walked to harvest a
 *   client list.
 * - A successful lookup returns the issuing details already printed on the
 *   document (number, type, addressee, issue date). Nothing beyond that.
 * - Attempts are rate limited per IP.
 * - The instant path only activates once the registry holds records; until
 *   then the page keeps the existing manual request form.
 */

if (!defined('ABSPATH')) {
    exit;
}

const ETA_VERIFY_DB_VERSION = 2;
const ETA_VERIFY_TABLE      = 'eta_report_registry';
// 30 attempts in ten minutes lets a client check a batch of reports in one
// sitting while still making enumeration of the number space impractical.
const ETA_VERIFY_RATE_MAX   = 30;    // attempts
const ETA_VERIFY_RATE_WINDOW = 600;  // seconds

/**
 * Dates on the reports themselves are day-first (27-07-2026), so the card
 * shows the same form the reader is checking against.
 */
function eta_verify_display_date($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    $timestamp = strtotime($value);
    return $timestamp === false ? $value : gmdate('d-m-Y', $timestamp);
}

function eta_verify_table_name()
{
    global $wpdb;
    return $wpdb->prefix . ETA_VERIFY_TABLE;
}

/**
 * Create the registry table. Runs on admin load when the stored version lags.
 */
function eta_verify_install()
{
    if ((int) get_option('eta_verify_db_version') >= ETA_VERIFY_DB_VERSION) {
        return;
    }

    global $wpdb;
    $table   = eta_verify_table_name();
    $collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        report_number VARCHAR(64) NOT NULL,
        report_date DATE NOT NULL,
        client_hash CHAR(64) NOT NULL DEFAULT '',
        client_label VARCHAR(160) NOT NULL DEFAULT '',
        client_address VARCHAR(255) NOT NULL DEFAULT '',
        report_type VARCHAR(120) NOT NULL DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'valid',
        laboratory VARCHAR(40) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY report_number (report_number),
        KEY report_date (report_date)
    ) {$collate};");

    update_option('eta_verify_db_version', ETA_VERIFY_DB_VERSION);
}
add_action('admin_init', 'eta_verify_install');

function eta_verify_normalise_number($value)
{
    // Report numbers are compared case-insensitively with separators ignored,
    // so "ETA/2026/0148" and "eta-2026-0148" resolve to the same record.
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value));
}

function eta_verify_record_count()
{
    global $wpdb;
    $table = eta_verify_table_name();
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return 0;
    }
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
}

/**
 * The reporting system on report.envitechal.com is the source of truth when
 * it is wired up. The local registry stays as a fallback for reports that
 * predate it, and for the minutes when that host is unreachable.
 */
function eta_verify_remote_is_configured()
{
    return defined('ETA_VERIFY_ENDPOINT') && ETA_VERIFY_ENDPOINT
        && defined('ETA_VERIFY_SECRET') && ETA_VERIFY_SECRET;
}

function eta_verify_is_active()
{
    return eta_verify_remote_is_configured() || eta_verify_record_count() > 0;
}

/**
 * Asks the reporting system about one report.
 *
 * The browser never speaks to that host: this is a server-to-server call
 * signed with a shared secret that stays in wp-config.php. The timestamp is
 * inside the signed material so a captured request cannot be replayed later.
 *
 * Returns an array on a definite answer, or null when the reporting system
 * could not be reached at all -- the caller then falls back to the registry
 * rather than telling the visitor a report does not exist.
 */
function eta_verify_remote_lookup($number, $date_sql)
{
    if (!eta_verify_remote_is_configured()) {
        return null;
    }

    $body = wp_json_encode([
        'report_number' => $number,
        'issue_date'    => $date_sql,
    ]);
    $timestamp = (string) time();
    $signature = hash_hmac('sha256', $timestamp . '.' . $body, ETA_VERIFY_SECRET);

    $response = wp_remote_post(ETA_VERIFY_ENDPOINT, [
        'timeout'     => 8,
        'redirection' => 0,
        'headers'     => [
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
            'X-ETA-Timestamp' => $timestamp,
            'X-ETA-Signature' => $signature,
        ],
        'body'        => $body,
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || !isset($data['status'])) {
        return null;
    }

    return $data;
}

/* ------------------------------------------------------------------ *
 * Lookup endpoint
 * ------------------------------------------------------------------ */

add_action('rest_api_init', function () {
    register_rest_route('eta/v1', '/verify-report', [
        'methods'             => 'POST',
        'callback'            => 'eta_verify_handle_request',
        'permission_callback' => '__return_true',
        'args'                => [
            'report_number' => ['required' => true, 'type' => 'string'],
            'report_date'   => ['required' => true, 'type' => 'string'],
        ],
    ]);
});

function eta_verify_client_key()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'unknown';
    return 'eta_verify_rl_' . md5($ip);
}

function eta_verify_handle_request(WP_REST_Request $request)
{
    if (!eta_verify_is_active()) {
        return new WP_REST_Response([
            'status'  => 'unavailable',
            'message' => 'Instant verification is not available yet. Please use the request form below.',
        ], 503);
    }

    // Rate limit before touching the database.
    $key   = eta_verify_client_key();
    $hits  = (int) get_transient($key);
    if ($hits >= ETA_VERIFY_RATE_MAX) {
        return new WP_REST_Response([
            'status'  => 'rate_limited',
            'message' => 'Too many verification attempts. Please try again shortly, or use the request form below.',
        ], 429);
    }
    set_transient($key, $hits + 1, ETA_VERIFY_RATE_WINDOW);

    $number = eta_verify_normalise_number($request->get_param('report_number'));
    $date   = trim((string) $request->get_param('report_date'));

    if ($number === '' || $date === '') {
        return new WP_REST_Response([
            'status'  => 'invalid',
            'message' => 'Enter the report number and the date printed on the report.',
        ], 400);
    }

    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return new WP_REST_Response([
            'status'  => 'invalid',
            'message' => 'That date could not be read. Use the date printed on the report.',
        ], 400);
    }
    $date_sql = gmdate('Y-m-d', $timestamp);

    // Ask the reporting system first; it holds the live record. A null reply
    // means that host could not be reached, so the registry answers instead.
    $remote = eta_verify_remote_lookup($number, $date_sql);
    if (is_array($remote)) {
        if (($remote['status'] ?? '') !== 'valid') {
            return new WP_REST_Response([
                'status'  => 'no_match',
                'message' => 'No Envi Tech AL report matches those details. Check the number and date against the printed report, or send a manual request below.',
            ], 200);
        }

        return new WP_REST_Response([
            'status'     => 'verified',
            'message'    => 'Verified. This report was issued by Envi Tech AL.',
            'details'    => array_filter([
                'Report number' => (string) ($remote['report_number'] ?? $number),
                'Report type'   => (string) ($remote['report_type'] ?? ''),
                'Issued to'     => (string) ($remote['client_name'] ?? ''),
                'Address'       => (string) ($remote['client_address'] ?? ''),
                'Issue date'    => eta_verify_display_date($remote['issue_date'] ?? $date_sql),
                'Issued by'     => (string) ($remote['issued_by'] ?? 'Envi Tech AL'),
            ], 'strlen'),
        ], 200);
    }

    global $wpdb;
    $table = eta_verify_table_name();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT report_number, report_date, status, laboratory, client_label, client_address, report_type FROM {$table}
         WHERE REPLACE(REPLACE(REPLACE(UPPER(report_number),'/',''),'-',''),' ','') = %s
         LIMIT 1",
        $number
    ));

    // A number that exists but whose date does not match returns the same
    // answer as a number that does not exist, so the endpoint cannot be used
    // to confirm which numbers are real.
    if (!$row || $row->report_date !== $date_sql) {
        return new WP_REST_Response([
            'status'  => 'no_match',
            'message' => 'No Envi Tech AL report matches those details. Check the number and date against the printed report, or send a manual request below.',
        ], 200);
    }

    if ($row->status !== 'valid') {
        return new WP_REST_Response([
            'status'  => 'superseded',
            'message' => 'A report with these details was issued by Envi Tech AL but is no longer current. Contact the laboratory for the replacement.',
        ], 200);
    }

    return new WP_REST_Response([
        'status'  => 'verified',
        'message' => 'Verified. This report was issued by Envi Tech AL.',
        'details' => array_filter([
            'Report number' => (string) $row->report_number,
            'Report type'   => (string) $row->report_type,
            'Issued to'     => (string) $row->client_label,
            'Address'       => (string) $row->client_address,
            'Issue date'    => eta_verify_display_date($row->report_date),
            'Issued by'     => 'Envi Tech AL' . ($row->laboratory !== '' ? ' (' . $row->laboratory . ')' : ''),
        ], 'strlen'),
    ], 200);
}

/* ------------------------------------------------------------------ *
 * CSV import
 * ------------------------------------------------------------------ */

add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        __('Report Registry', 'envi-tech-al-modern'),
        __('Report Registry', 'envi-tech-al-modern'),
        'manage_options',
        'eta-report-registry',
        'eta_verify_render_admin'
    );
});

function eta_verify_render_admin()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice  = '';
    $reasons = [];

    if (
        isset($_POST['eta_verify_nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eta_verify_nonce'])), 'eta_verify_import')
        && !empty($_FILES['eta_verify_csv']['tmp_name'])
        && is_uploaded_file($_FILES['eta_verify_csv']['tmp_name'])
    ) {
        $result  = eta_verify_import_csv($_FILES['eta_verify_csv']['tmp_name']);
        $reasons = isset($result['reasons']) ? $result['reasons'] : [];
        $notice  = sprintf(
            /* translators: 1: imported rows, 2: skipped rows */
            __('Imported %1$d reports. Skipped %2$d rows.', 'envi-tech-al-modern'),
            $result['imported'],
            $result['skipped']
        );
    }

    $count = eta_verify_record_count();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Report Registry', 'envi-tech-al-modern'); ?></h1>

        <?php if ($notice) : ?>
            <div class="notice notice-<?php echo empty($reasons) ? 'success' : 'warning'; ?>">
                <p><?php echo esc_html($notice); ?></p>
                <?php if (!empty($reasons)) : ?>
                    <ul style="list-style:disc;margin-left:20px;">
                        <?php foreach (array_slice($reasons, 0, 25) as $reason) : ?>
                            <li><?php echo esc_html($reason); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($reasons) > 25) : ?>
                        <p><?php
                            printf(
                                /* translators: %d: number of further skipped rows */
                                esc_html__('and %d more.', 'envi-tech-al-modern'),
                                count($reasons) - 25
                            );
                        ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <p>
            <?php
            printf(
                /* translators: %d: number of reports currently in the registry */
                esc_html__('The registry currently holds %d reports.', 'envi-tech-al-modern'),
                (int) $count
            );
            ?>
            <?php if ($count === 0) : ?>
                <strong><?php esc_html_e('Instant verification stays switched off until at least one report is imported; the portal keeps its manual request form in the meantime.', 'envi-tech-al-modern'); ?></strong>
            <?php endif; ?>
        </p>

        <h2><?php esc_html_e('Import a CSV', 'envi-tech-al-modern'); ?></h2>
        <p><?php esc_html_e('Columns, with a header row: report_number and report_date are required; client_name, status and laboratory are optional. Dates may be YYYY-MM-DD or day-first (14/03/2026 is 14 March), and month names are accepted. Status is valid or superseded, defaulting to valid. Existing report numbers are updated rather than duplicated, so a corrected file can simply be imported again.', 'envi-tech-al-modern'); ?></p>
        <p><code>report_number,report_date,client_name,status,laboratory<br>ETA/2026/0148,2026-03-14,Artistic Milliners,valid,LAB-285</code></p>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('eta_verify_import', 'eta_verify_nonce'); ?>
            <input type="file" name="eta_verify_csv" accept=".csv,text/csv" required>
            <?php submit_button(__('Import reports', 'envi-tech-al-modern')); ?>
        </form>
    </div>
    <?php
}

/**
 * Read a date from a spreadsheet cell.
 *
 * Day-first is assumed for slash and dot separated dates, matching the
 * convention on the reports themselves. That choice matters: PHP's own
 * strtotime() reads 03/04/2026 as 4 March, so a report issued on 3 April
 * would import under the wrong date and then fail every verification with
 * no error shown anywhere.
 *
 * Returns Y-m-d, or null when the value cannot be read unambiguously.
 */
function eta_verify_parse_date($raw)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return null;
    }

    $formats = [
        'Y-m-d',  // ISO, and what the date field on the portal submits
        'Y/m/d',
        'd/m/Y',  // day-first
        'd-m-Y',
        'd.m.Y',
        'j/n/Y',
        'j-n-Y',
        'j.n.Y',
        'j M Y',  // textual months are unambiguous
        'j F Y',
        'd M Y',
        'd F Y',
    ];

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat('!' . $format, $raw);
        if (!$date) {
            continue;
        }
        $errors = DateTime::getLastErrors();
        if (is_array($errors) && (!empty($errors['warning_count']) || !empty($errors['error_count']))) {
            continue;
        }
        return $date->format('Y-m-d');
    }

    return null;
}

/**
 * Import a CSV into the registry.
 *
 * Returns the counts plus a per-row reason for anything skipped, so a bad
 * export can be corrected rather than quietly losing rows.
 */
function eta_verify_import_csv($path)
{
    global $wpdb;
    $table    = eta_verify_table_name();
    $imported = 0;
    $skipped  = [];

    $handle = fopen($path, 'r');
    if (!$handle) {
        return ['imported' => 0, 'skipped' => 0, 'reasons' => ['The file could not be opened.']];
    }

    // Excel writes a UTF-8 BOM, which would otherwise become part of the first
    // column name and leave report_number unmapped for the whole file.
    $first = fgets($handle);
    if ($first === false) {
        fclose($handle);
        return ['imported' => 0, 'skipped' => 0, 'reasons' => ['The file is empty.']];
    }
    $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
    if (strncmp($first, $bom, 3) === 0) {
        $first = substr($first, 3);
    }

    // Some locales export semicolon or tab separated. Pick whichever separator
    // appears most often in the header row.
    $delimiter = ',';
    $best = substr_count($first, ',');
    $tab = chr(9);
    foreach ([';' => substr_count($first, ';'), $tab => substr_count($first, $tab)] as $candidate => $count) {
        if ($count > $best) {
            $best = $count;
            $delimiter = $candidate;
        }
    }

    $header = str_getcsv(rtrim($first, chr(13) . chr(10)), $delimiter, '"', '');
    if (!is_array($header)) {
        fclose($handle);
        return ['imported' => 0, 'skipped' => 0, 'reasons' => ['The header row could not be read.']];
    }
    $map = array_flip(array_map(static function ($h) {
        return strtolower(trim((string) $h));
    }, $header));

    if (!isset($map['report_number'], $map['report_date'])) {
        fclose($handle);
        return [
            'imported' => 0,
            'skipped'  => 0,
            'reasons'  => ['The header row needs both report_number and report_date. Found: ' . implode(', ', array_keys($map)) . '.'],
        ];
    }

    $col = static function ($row, $map, $name) {
        return isset($map[$name], $row[$map[$name]]) ? trim((string) $row[$map[$name]]) : '';
    };

    $line = 1;
    while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
        $line++;
        if (!is_array($row) || (count($row) === 1 && trim((string) $row[0]) === '')) {
            continue; // blank line
        }

        $number   = $col($row, $map, 'report_number');
        $raw_date = $col($row, $map, 'report_date');
        $client   = $col($row, $map, 'client_name');
        $status   = strtolower($col($row, $map, 'status'));
        $lab      = $col($row, $map, 'laboratory');

        if ($number === '') {
            $skipped[] = sprintf('Row %d: no report number.', $line);
            continue;
        }
        $date = eta_verify_parse_date($raw_date);
        if ($date === null) {
            $skipped[] = sprintf('Row %d (%s): could not read the date "%s".', $line, $number, $raw_date);
            continue;
        }

        $wpdb->replace(
            $table,
            [
                'report_number' => $number,
                'report_date'   => $date,
                'client_hash'   => $client !== '' ? hash('sha256', strtolower($client)) : '',
                'client_label'  => $client,
                'status'        => in_array($status, ['valid', 'superseded'], true) ? $status : 'valid',
                'laboratory'    => $lab,
                'created_at'    => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        $imported++;
    }

    fclose($handle);
    return ['imported' => $imported, 'skipped' => count($skipped), 'reasons' => $skipped];
}

/* ------------------------------------------------------------------ *
 * Front-end panel
 * ------------------------------------------------------------------ */

/**
 * Instant lookup panel for the verification portal.
 *
 * Prints nothing while the registry is empty, so the page falls back to the
 * manual request form rather than telling every visitor their real report
 * cannot be found.
 */
function eta_verify_render_panel()
{
    if (!eta_verify_is_active()) {
        return;
    }
    ?>
    <section class="eta-band eta-iv" aria-labelledby="eta-iv-title">
        <div class="eta-shell eta-iv-grid">
            <div class="eta-iv-copy">
                <p class="eta-eyebrow"><?php esc_html_e('Instant check', 'envi-tech-al-modern'); ?></p>
                <h2 id="eta-iv-title"><?php esc_html_e('Confirm a report in seconds.', 'envi-tech-al-modern'); ?></h2>
                <p><?php esc_html_e('Enter the report number and the issue date printed on the document. Both must match the record held by the laboratory. A match confirms that Envi Tech AL issued the report and shows the issuing details already printed on it; no analytical results are disclosed.', 'envi-tech-al-modern'); ?></p>
                <p class="eta-iv-note"><?php esc_html_e('If the details do not match, or the report predates the online registry, send a manual request further down this page and the laboratory team will confirm it directly.', 'envi-tech-al-modern'); ?></p>
                <ul class="eta-iv-steps" aria-label="<?php esc_attr_e('How the check works', 'envi-tech-al-modern'); ?>">
                    <li><?php esc_html_e('Checked against the laboratory reporting system, not a copy.', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Number and date must both match; a partial match is not confirmed.', 'envi-tech-al-modern'); ?></li>
                    <li><?php esc_html_e('Your entries are used for this check only.', 'envi-tech-al-modern'); ?></li>
                </ul>

                <figure class="eta-iv-sample" aria-label="<?php esc_attr_e('Where the two details appear on a report', 'envi-tech-al-modern'); ?>">
                    <figcaption><?php esc_html_e('Where to find them on the report', 'envi-tech-al-modern'); ?></figcaption>
                    <div class="eta-iv-sample-doc" aria-hidden="true">
                        <div class="eta-iv-sample-head">
                            <span class="eta-iv-sample-brand">Envi Tech AL</span>
                            <span class="eta-iv-sample-qr"></span>
                        </div>
                        <div class="eta-iv-sample-row"><span>Report No.</span><mark>RO-2026XXXXX-KHI-XX</mark></div>
                        <div class="eta-iv-sample-row"><span>Reporting date</span><mark>DD-MM-YYYY</mark></div>
                        <div class="eta-iv-sample-row eta-iv-sample-dim"><span>Issued to</span><i></i></div>
                        <div class="eta-iv-sample-row eta-iv-sample-dim"><span>Sample type</span><i></i></div>
                    </div>
                    <p class="eta-iv-sample-note"><?php esc_html_e('Both details sit in the header block on page one, beside the QR code. Enter them exactly as printed.', 'envi-tech-al-modern'); ?></p>
                </figure>
            </div>

            <form class="eta-iv-panel" id="eta-iv-form" novalidate method="post" action=""
                  onsubmit="return false;">
                <div class="eta-iv-field">
                    <label for="eta-iv-number"><?php esc_html_e('Report number', 'envi-tech-al-modern'); ?></label>
                    <input type="text" id="eta-iv-number" name="report_number" autocomplete="off" autocapitalize="characters" spellcheck="false" required
                           aria-describedby="eta-iv-number-hint"
                           placeholder="<?php esc_attr_e('As printed on the report', 'envi-tech-al-modern'); ?>">
                    <p class="eta-iv-hint" id="eta-iv-number-hint"><?php esc_html_e('Top of the report, beside the QR code. Case and separators do not matter.', 'envi-tech-al-modern'); ?></p>
                </div>
                <div class="eta-iv-field">
                    <label for="eta-iv-date"><?php esc_html_e('Issue date', 'envi-tech-al-modern'); ?></label>
                    <input type="date" id="eta-iv-date" name="report_date" required aria-describedby="eta-iv-date-hint">
                    <p class="eta-iv-hint" id="eta-iv-date-hint"><?php esc_html_e('The reporting date printed on the document, in day, month, year order.', 'envi-tech-al-modern'); ?></p>
                </div>
                <button type="submit" class="eta-button eta-iv-submit"><?php esc_html_e('Verify report', 'envi-tech-al-modern'); ?></button>
                <div class="eta-iv-result" id="eta-iv-result" role="status" aria-live="polite" data-state="idle"></div>
            </form>
        </div>
    </section>

    <?php // data-no-optimize keeps LiteSpeed from delaying this; without it a
          // click before the script runs submits the form natively and puts the
          // report number and date into the URL. ?>
    <script data-no-optimize="1" data-cfasync="false">
    (function () {
        var form = document.getElementById('eta-iv-form');
        if (!form) { return; }
        var out = document.getElementById('eta-iv-result');
        var btn = form.querySelector('.eta-iv-submit');
        var endpoint = <?php echo wp_json_encode(esc_url_raw(rest_url('eta/v1/verify-report'))); ?>;
        var busy = false;

        var numberField = form.querySelector('#eta-iv-number');
        var dateField = form.querySelector('#eta-iv-date');
        var labels = {
            verified:     <?php echo wp_json_encode(__('Verified', 'envi-tech-al-modern')); ?>,
            superseded:   <?php echo wp_json_encode(__('Superseded', 'envi-tech-al-modern')); ?>,
            no_match:     <?php echo wp_json_encode(__('No match', 'envi-tech-al-modern')); ?>,
            invalid:      <?php echo wp_json_encode(__('Check the details', 'envi-tech-al-modern')); ?>,
            rate_limited: <?php echo wp_json_encode(__('Please wait', 'envi-tech-al-modern')); ?>,
            unavailable:  <?php echo wp_json_encode(__('Unavailable', 'envi-tech-al-modern')); ?>,
            error:        <?php echo wp_json_encode(__('Could not complete', 'envi-tech-al-modern')); ?>,
            pending:      <?php echo wp_json_encode(__('Checking', 'envi-tech-al-modern')); ?>
        };
        var genericError = <?php echo wp_json_encode(__('The check could not be completed. Please try again in a moment, or use the request form below.', 'envi-tech-al-modern')); ?>;

        function say(state, text, details) {
            out.setAttribute('data-state', state);
            out.setAttribute('aria-busy', state === 'pending' ? 'true' : 'false');
            out.textContent = '';

            var head = document.createElement('p');
            head.className = 'eta-iv-status';
            var badge = document.createElement('span');
            badge.className = 'eta-iv-badge';
            badge.textContent = labels[state] || labels.error;
            head.appendChild(badge);
            out.appendChild(head);

            var p = document.createElement('p');
            p.className = 'eta-iv-message';
            p.textContent = text;
            out.appendChild(p);

            if (details) {
                var dl = document.createElement('dl');
                dl.className = 'eta-iv-detail';
                Object.keys(details).forEach(function (label) {
                    if (!details[label]) { return; }
                    var dt = document.createElement('dt');
                    dt.textContent = label;
                    var dd = document.createElement('dd');
                    dd.textContent = details[label];
                    dl.appendChild(dt);
                    dl.appendChild(dd);
                });
                out.appendChild(dl);

                var foot = document.createElement('p');
                foot.className = 'eta-iv-foot';
                foot.textContent = <?php echo wp_json_encode(__('Compare each line with the printed document. If anything differs, treat the copy in your hand as unconfirmed and contact the laboratory.', 'envi-tech-al-modern')); ?>;
                out.appendChild(foot);
            }

            if (state !== 'pending') {
                var again = document.createElement('button');
                again.type = 'button';
                again.className = 'eta-iv-again';
                again.textContent = <?php echo wp_json_encode(__('Check another report', 'envi-tech-al-modern')); ?>;
                again.addEventListener('click', function () {
                    form.reset();
                    out.setAttribute('data-state', 'idle');
                    out.textContent = '';
                    numberField.focus();
                });
                out.appendChild(again);
            }
        }

        function fail() {
            say('error', genericError);
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (busy) { return; }

            var number = numberField.value.trim();
            var date = dateField.value.trim();
            if (!number || !date) {
                say('invalid', <?php echo wp_json_encode(__('Enter both the report number and the issue date.', 'envi-tech-al-modern')); ?>);
                (number ? dateField : numberField).focus();
                return;
            }

            busy = true;
            btn.disabled = true;
            say('pending', <?php echo wp_json_encode(__('Checking with the laboratory reporting system…', 'envi-tech-al-modern')); ?>);

            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ report_number: number, report_date: date })
            })
                .then(function (response) {
                    return response.json().then(function (data) { return data; }, function () { return null; });
                })
                .then(function (data) {
                    if (!data || !data.status) { fail(); return; }
                    var details = data.status === 'verified' ? data.details : null;
                    say(data.status, data.message || genericError, details);
                })
                .catch(fail)
                .then(function () {
                    busy = false;
                    btn.disabled = false;
                });
        });
    }());
    </script>
    <?php
}
