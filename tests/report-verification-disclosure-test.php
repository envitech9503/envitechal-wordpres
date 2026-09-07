<?php
/**
 * Disclosure and enumeration guards for the report verification endpoint.
 *
 * A successful lookup now returns the client name and address, which is
 * personal data. What keeps that safe is the gate in front of it: a caller must
 * present the report number AND its matching issue date, and every kind of
 * failure must look identical so the endpoint cannot be walked to discover
 * which numbers are real.
 *
 * These are source-level assertions rather than live calls, because the point
 * is that the guarantees hold by construction and stay that way when somebody
 * edits the module later.
 *
 * Run: php tests/report-verification-disclosure-test.php
 */

$module = __DIR__ . '/../wp-content/themes/generatepress-envitechal/inc/report-verification.php';
$source = file_get_contents($module);
if ($source === false) {
    fwrite(STDERR, "Could not read the verification module.\n");
    exit(1);
}

$failures = 0;
function check($label, $condition, &$failures)
{
    if ($condition) {
        printf("  pass  %s\n", $label);
        return;
    }
    printf("  FAIL  %s\n", $label);
    $failures++;
}

/* 1. Both "no match" replies must be byte-identical. One is returned when the
      reporting system denies the report, the other when the local registry
      does. A caller must not be able to tell which path answered. */
preg_match_all(
    "/'status'\s*=>\s*'no_match',\s*'message'\s*=>\s*'([^']+)'/",
    $source,
    $m
);
$messages = array_unique($m[1]);
check(
    'every no_match reply uses one identical message',
    count($m[1]) >= 2 && count($messages) === 1,
    $failures
);

/* 2. A wrong date on a real number must not be distinguishable. The registry
      branch has to test the date together with the row lookup. */
check(
    'registry branch rejects a row whose date does not match',
    strpos($source, '$row->report_date !== $date_sql') !== false,
    $failures
);

/* 3. Client data may only ever leave under a verified status. */
$verified_blocks = preg_split("/'status'\s*=>\s*'/", $source);
$leaks = 0;
foreach ($verified_blocks as $block) {
    if ($block === '' || strncmp($block, 'verified', 8) === 0) {
        continue;
    }
    // Cut the block at the end of its response array so we only inspect it.
    $slice = substr($block, 0, 600);
    foreach (["'Issued to'", "'Address'", 'client_label', 'client_address', 'client_name'] as $field) {
        if (strpos($slice, $field) !== false) {
            $leaks++;
        }
    }
}
check('client name and address appear only under a verified status', $leaks === 0, $failures);

/* 4. The remote call must be signed, and the timestamp must be inside the
      signed material or a captured request could be replayed indefinitely. */
check(
    'remote lookup signs timestamp together with the body',
    strpos($source, "hash_hmac('sha256', \$timestamp . '.' . \$body, ETA_VERIFY_SECRET)") !== false,
    $failures
);

/* 5. An unreachable reporting system must not read as "report does not exist".
      eta_verify_remote_lookup returns null in that case and the caller falls
      through to the registry rather than answering no_match. */
check(
    'unreachable reporting system falls back instead of denying',
    strpos($source, 'if (is_array($remote)) {') !== false,
    $failures
);

/* 6. The shared secret must never reach the browser. */
$panel = strstr($source, 'function eta_verify_render_panel');
check(
    'ETA_VERIFY_SECRET is never printed into the page',
    $panel !== false && strpos($panel, 'ETA_VERIFY_SECRET') === false,
    $failures
);

/* 7. Rate limiting must happen before any lookup work. */
$handler = strstr($source, 'function eta_verify_handle_request');
$rate_pos = strpos($handler, 'ETA_VERIFY_RATE_MAX');
$remote_pos = strpos($handler, 'eta_verify_remote_lookup');
check(
    'rate limit is enforced before the lookup runs',
    $rate_pos !== false && $remote_pos !== false && $rate_pos < $remote_pos,
    $failures
);

/* 8. Values rendered into the panel must go through textContent, never
      innerHTML, since they now carry free-text client data. */
$script = strstr($source, 'function say(state');
check(
    'panel renders values with textContent, not innerHTML',
    $script !== false && strpos(substr($script, 0, 1200), 'innerHTML') === false,
    $failures
);

if ($failures > 0) {
    printf("\n%d disclosure guard(s) failed.\n", $failures);
    exit(1);
}

echo "\nReport verification disclosure guards passed.\n";
