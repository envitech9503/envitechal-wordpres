<?php

$root = dirname(__DIR__);
$transformer = $root . '/scripts/lib/discovery-cache-htaccess.php';
$validator = $root . '/scripts/lib/validate-discovery-cache-headers.php';
$work = sys_get_temp_dir() . '/eta-discovery-cache-test-' . bin2hex(random_bytes(8));
if (!mkdir($work, 0700, true)) {
    fwrite(STDERR, "FAILED: could not create test directory.\n");
    exit(1);
}

function eta_cache_test_cleanup($path)
{
    if (!is_dir($path)) {
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            unlink($path . '/' . $entry);
        }
    }
    rmdir($path);
}

function eta_cache_test_run(array $arguments, &$output = null)
{
    $command = implode(' ', array_map('escapeshellarg', $arguments)) . ' 2>&1';
    $lines = [];
    $status = 0;
    exec($command, $lines, $status);
    $output = implode("\n", $lines);
    return $status;
}

function eta_cache_test_assert($condition, $label, $detail = '')
{
    if ($condition) {
        return;
    }
    fwrite(STDERR, "FAILED: {$label}" . ($detail === '' ? '' : "\n{$detail}") . "\n");
    exit(1);
}

register_shutdown_function('eta_cache_test_cleanup', $work);

$block = <<<'BLOCK'
# BEGIN Envi Tech AL discovery cache policy
<IfModule mod_setenvif.c>
    SetEnvIf Request_URI "^/(robots\.txt|llms\.txt|llms-full\.txt|\.well-known/agent-skills/index\.json)(\?.*)?$" ETA_DISCOVERY_SHORT_CACHE=1
</IfModule>
<IfModule mod_headers.c>
    Header onsuccess unset Expires env=ETA_DISCOVERY_SHORT_CACHE
    Header always unset Expires env=ETA_DISCOVERY_SHORT_CACHE
    # PHP/LSAPI response headers live in Apache's always table. Remove any
    # normal-table value, then replace the always-table value exactly once.
    Header onsuccess unset Cache-Control env=ETA_DISCOVERY_SHORT_CACHE
    Header always set Cache-Control "public, max-age=300, s-maxage=3600, must-revalidate" env=ETA_DISCOVERY_SHORT_CACHE
    # Production serves these resources as physical files. A hosting/plugin
    # FilesMatch context can run after the outer per-directory header rules,
    # so replace both header tables again in an equally specific, URI-gated
    # context. Staging's virtual WordPress responses resolve to index.php and
    # continue to use the outer PHP/LSAPI handling above.
    <FilesMatch "^(robots\.txt|llms\.txt|llms-full\.txt|index\.json)$">
        Header unset Expires "expr=%{REQUEST_URI} =~ m#^/(robots\.txt|llms\.txt|llms-full\.txt|\.well-known/agent-skills/index\.json)(\?.*)?$#"
        Header always unset Expires "expr=%{REQUEST_URI} =~ m#^/(robots\.txt|llms\.txt|llms-full\.txt|\.well-known/agent-skills/index\.json)(\?.*)?$#"
        Header always unset Cache-Control "expr=%{REQUEST_URI} =~ m#^/(robots\.txt|llms\.txt|llms-full\.txt|\.well-known/agent-skills/index\.json)(\?.*)?$#"
        Header set Cache-Control "public, max-age=300, s-maxage=3600, must-revalidate" "expr=%{REQUEST_URI} =~ m#^/(robots\.txt|llms\.txt|llms-full\.txt|\.well-known/agent-skills/index\.json)(\?.*)?$#"
    </FilesMatch>
</IfModule>
# END Envi Tech AL discovery cache policy
BLOCK;
$block .= "\n";
file_put_contents($work . '/block', $block);

$rules = [
    'RewriteRule ^water-testing-in-pakistan/?$ /services/water-testing-lab-services/? [R=301,L,NE]',
    'RewriteRule ^water-testing-lab-near-me/?$ /services/water-testing-lab-services/? [R=301,L,NE]',
    'RewriteRule ^water-quality-testing-mastering-your-ultimate-guide-to-excellence/?$ /services/water-testing-lab-services/? [R=301,L,NE]',
    'RewriteRule ^get-accurate-results-from-our-water-testing-lab-in-lahore/?$ /lahore-environmental-lab/? [R=301,L,NE]',
    'RewriteRule ^reliable-water-testing-services-environmental-lab-karachi/?$ /karachi-environmental-lab/? [R=301,L,NE]',
    'RewriteRule ^discover-the-best-testing-laboratory-near-you-for-reliable-and-accurate-results/?$ /how-to-choose-the-suitable-environmental-lab/? [R=301,L,NE]',
    'RewriteRule ^https-envitechal-com-services-environmental-consultancy/?$ /services/environmental-consultancy/? [R=301,L,NE]',
    'RewriteRule ^https-envitechal-com-calibration-of-equipment-in-karachi/?$ /services/equipment-calibration-services/? [R=301,L,NE]',
    'RewriteRule ^22653-2/?$ /services/water-testing-lab-services/? [R=301,L,NE]',
];

$prefix = "# Unrelated prefix remains byte-for-byte\r\nRewriteEngine On\r\n";
$suffix = "RewriteRule ^unrelated/?$ /still-here/? [R=301,L,NE]\r\n# Unrelated suffix remains\r\n";
$source = $prefix . implode("\r\n", $rules) . "\r\n" . $suffix;
file_put_contents($work . '/production.before', $source);

$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/production.before', $work . '/block', $work . '/production.after', 'production'],
    $output
);
eta_cache_test_assert($status === 0, 'complete production transform succeeds', $output);
eta_cache_test_assert($output === 'removed_rules=9', 'complete production transform removes all nine exact rules', $output);
$expected = $prefix . $suffix . $block;
$actual = file_get_contents($work . '/production.after');
eta_cache_test_assert($actual === $expected, 'production transform preserves every unrelated byte');
eta_cache_test_assert(
    strpos($actual, '# Unrelated suffix remains') < strpos($actual, '# BEGIN Envi Tech AL discovery cache policy'),
    'managed block is appended after unrelated and hosting-wide directives'
);

$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/production.after', $work . '/block', $work . '/production.idempotent', 'production'],
    $output
);
eta_cache_test_assert($status === 0 && $output === 'removed_rules=0', 'second production transform is accepted as already remediated', $output);
eta_cache_test_assert(
    file_get_contents($work . '/production.idempotent') === $actual,
    'second production transform is byte-idempotent'
);

file_put_contents($work . '/partial', $prefix . $rules[0] . "\n" . $suffix);
$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/partial', $work . '/block', $work . '/partial.out', 'production'],
    $output
);
eta_cache_test_assert($status !== 0, 'partial reviewed RewriteRule set fails closed');

$altered = str_replace('/services/water-testing-lab-services/?', '/unexpected-target/?', $source);
file_put_contents($work . '/altered', $altered);
$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/altered', $work . '/block', $work . '/altered.out', 'production'],
    $output
);
eta_cache_test_assert($status !== 0, 'reviewed source with an altered target fails closed');

$lowercaseAltered = preg_replace('/^RewriteRule /m', 'rewriterule ', $altered, 1);
file_put_contents($work . '/lowercase-altered', $lowercaseAltered);
$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/lowercase-altered', $work . '/block', $work . '/lowercase-altered.out', 'production'],
    $output
);
eta_cache_test_assert($status !== 0, 'lowercase reviewed directive with altered target cannot escape fail-closed detection');

$conditioned = str_replace(
    $rules[0],
    "RewriteCond %{QUERY_STRING} ^legacy$\n" . $rules[0],
    $source
);
file_put_contents($work . '/conditioned', $conditioned);
$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/conditioned', $work . '/block', $work . '/conditioned.out', 'production'],
    $output
);
eta_cache_test_assert($status !== 0, 'reviewed RewriteRule governed by RewriteCond fails closed');

$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/production.before', $work . '/block', $work . '/staging.after', 'staging'],
    $output
);
eta_cache_test_assert($status === 0 && $output === 'removed_rules=0', 'staging never removes production RewriteRules', $output);
foreach ($rules as $rule) {
    eta_cache_test_assert(strpos(file_get_contents($work . '/staging.after'), $rule) !== false, 'staging retains exact RewriteRule: ' . $rule);
}

file_put_contents($work . '/broken-markers', $block . "\n" . $block . "\n" . $suffix);
$status = eta_cache_test_run(
    [PHP_BINARY, $transformer, $work . '/broken-markers', $work . '/block', $work . '/broken.out', 'staging'],
    $output
);
eta_cache_test_assert($status !== 0, 'duplicate managed markers fail closed');

$validHeaders = "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\n" .
    "Cache-Control: public, max-age=300, s-maxage=3600, must-revalidate\r\n\r\n";
file_put_contents($work . '/headers-valid', $validHeaders);
$status = eta_cache_test_run([PHP_BINARY, $validator, $work . '/headers-valid'], $output);
eta_cache_test_assert($status === 0, 'one exact short cache policy passes', $output);
$status = eta_cache_test_run([PHP_BINARY, $validator, '--assert-absent', $work . '/headers-valid'], $output);
eta_cache_test_assert($status !== 0, 'absence mode detects the managed policy');

$ordinaryHeaders = "HTTP/1.1 200 OK\r\nCache-Control: public, max-age=86400\r\n" .
    "Expires: tomorrow\r\n\r\n";
file_put_contents($work . '/headers-ordinary', $ordinaryHeaders);
$status = eta_cache_test_run([PHP_BINARY, $validator, '--assert-absent', $work . '/headers-ordinary'], $output);
eta_cache_test_assert($status === 0, 'absence mode permits an unrelated ordinary cache policy', $output);

$proxyThenMissing = $validHeaders . "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\n\r\n";
file_put_contents($work . '/headers-proxy-then-missing', $proxyThenMissing);
$status = eta_cache_test_run([PHP_BINARY, $validator, $work . '/headers-proxy-then-missing'], $output);
eta_cache_test_assert($status !== 0, 'an earlier policy cannot hide a missing final response policy');

$proxyThenValid = "HTTP/1.1 200 Connection established\r\n" .
    "Cache-Control: public, max-age=31536000\r\nExpires: tomorrow\r\n\r\n" .
    $validHeaders;
file_put_contents($work . '/headers-proxy-then-valid', $proxyThenValid);
$status = eta_cache_test_run([PHP_BINARY, $validator, $work . '/headers-proxy-then-valid'], $output);
eta_cache_test_assert($status === 0, 'only the final response block controls exact validation', $output);

$informationalThenValid = "HTTP/1.1 100 Continue\nInterim: yes\n\n" .
    str_replace("\r\n", "\n", $validHeaders);
file_put_contents($work . '/headers-informational-then-valid', $informationalThenValid);
$status = eta_cache_test_run([PHP_BINARY, $validator, $work . '/headers-informational-then-valid'], $output);
eta_cache_test_assert($status === 0, 'informational blocks and LF-only final headers are handled', $output);

$leakedWithNoise = $validHeaders . "Cache-Control: immutable\r\nExpires: tomorrow\r\n";
file_put_contents($work . '/headers-leaked-with-noise', $leakedWithNoise);
$status = eta_cache_test_run([PHP_BINARY, $validator, '--assert-absent', $work . '/headers-leaked-with-noise'], $output);
eta_cache_test_assert($status !== 0, 'absence mode detects managed policy even with duplicate/noisy headers');

$splitLeak = "HTTP/1.1 200 OK\r\n" .
    "Cache-Control: public, max-age=300\r\n" .
    "Cache-Control: s-maxage=3600, must-revalidate\r\n\r\n";
file_put_contents($work . '/headers-split-leak', $splitLeak);
$status = eta_cache_test_run([PHP_BINARY, $validator, '--assert-absent', $work . '/headers-split-leak'], $output);
eta_cache_test_assert($status !== 0, 'absence mode combines list-valued Cache-Control fields before leak detection');

$invalidHeaders = [
    'long age' => str_replace('max-age=300', 'max-age=31536000', $validHeaders),
    'duplicate field' => str_replace("\r\n\r\n", "\r\nCache-Control: public, max-age=31536000\r\n\r\n", $validHeaders),
    'expires field' => str_replace("\r\n\r\n", "\r\nExpires: Thu, 31 Dec 2037 23:55:55 GMT\r\n\r\n", $validHeaders),
    'missing shared age' => str_replace(', s-maxage=3600', '', $validHeaders),
    'unexpected directive' => str_replace('must-revalidate', 'immutable', $validHeaders),
];
foreach ($invalidHeaders as $label => $headers) {
    $path = $work . '/headers-' . str_replace(' ', '-', $label);
    file_put_contents($path, $headers);
    $status = eta_cache_test_run([PHP_BINARY, $validator, $path], $output);
    eta_cache_test_assert($status !== 0, "{$label} header policy fails closed");
}

echo "Discovery cache remediation tests passed.\n";
