<?php

/**
 * Fail unless a response has one exact, short-lived discovery cache policy.
 */

if (PHP_SAPI !== 'cli' || !in_array($argc, [2, 3], true)) {
    fwrite(STDERR, "Usage: php validate-discovery-cache-headers.php [--assert-absent] HEADERS\n");
    exit(64);
}

$absenceMode = $argc === 3 && $argv[1] === '--assert-absent';
if ($argc === 3 && !$absenceMode) {
    fwrite(STDERR, "Unknown validation mode.\n");
    exit(64);
}
$headers = file_get_contents($argv[$absenceMode ? 2 : 1]);
if (!is_string($headers)) {
    fwrite(STDERR, "Could not read response headers.\n");
    exit(65);
}

preg_match_all('/^cache-control\s*:\s*([^\r\n]*)/mi', $headers, $cacheMatches);
$cacheValues = $cacheMatches[1] ?? [];
if ($absenceMode) {
    foreach ($cacheValues as $cacheValue) {
        $normalizedDirectives = [];
        foreach (explode(',', strtolower($cacheValue)) as $directive) {
            $normalized = preg_replace('/\s*=\s*/', '=', trim($directive));
            if (is_string($normalized)) {
                $normalizedDirectives[$normalized] = true;
            }
        }
        if (isset(
            $normalizedDirectives['public'],
            $normalizedDirectives['max-age=300'],
            $normalizedDirectives['s-maxage=3600'],
            $normalizedDirectives['must-revalidate']
        )) {
            fwrite(STDERR, "The managed discovery cache policy leaked onto an out-of-scope response.\n");
            exit(1);
        }
    }
    echo "Managed discovery cache policy is absent.\n";
    exit(0);
}

if (count($cacheValues) !== 1) {
    fwrite(STDERR, 'Expected one Cache-Control field; found ' . count($cacheValues) . ".\n");
    exit(1);
}

$directives = array_map('trim', explode(',', strtolower($cacheValues[0])));
$expected = [
    'public' => false,
    'max-age=300' => false,
    's-maxage=3600' => false,
    'must-revalidate' => false,
];

foreach ($directives as $directive) {
    $normalized = preg_replace('/\s*=\s*/', '=', $directive);
    if (!is_string($normalized) || !array_key_exists($normalized, $expected) || $expected[$normalized]) {
        fwrite(STDERR, "Unexpected or duplicate Cache-Control directive: {$directive}\n");
        exit(1);
    }
    $expected[$normalized] = true;

    if (preg_match('/^(?:s-)?max-age=([0-9]+)$/', $normalized, $ageMatch) &&
        (int) $ageMatch[1] > 3600) {
        fwrite(STDERR, "Cache-Control contains an age above 3600 seconds.\n");
        exit(1);
    }
}

if (in_array(false, $expected, true)) {
    fwrite(STDERR, "Cache-Control is missing part of the reviewed short cache policy.\n");
    exit(1);
}

if (preg_match('/^expires\s*:/mi', $headers)) {
    fwrite(STDERR, "Expires must be absent from managed discovery responses.\n");
    exit(1);
}

echo "Discovery cache response headers passed.\n";
