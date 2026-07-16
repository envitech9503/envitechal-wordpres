<?php

/**
 * Build the reviewed .htaccess candidate used by the cPanel remediation.
 *
 * Usage:
 * php discovery-cache-htaccess.php INPUT BLOCK OUTPUT staging|production
 */

if (PHP_SAPI !== 'cli' || $argc !== 5) {
    fwrite(STDERR, "Usage: php discovery-cache-htaccess.php INPUT BLOCK OUTPUT staging|production\n");
    exit(64);
}

[$script, $inputPath, $blockPath, $outputPath, $environment] = $argv;
unset($script);

if (!in_array($environment, ['staging', 'production'], true)) {
    fwrite(STDERR, "Environment must be staging or production.\n");
    exit(64);
}

$source = file_get_contents($inputPath);
$block = file_get_contents($blockPath);
if (!is_string($source) || !is_string($block) || $block === '') {
    fwrite(STDERR, "Could not read the .htaccess source and managed header block.\n");
    exit(65);
}

$block = rtrim($block, "\r\n") . "\n";
$begin = '# BEGIN Envi Tech AL discovery cache policy';
$end = '# END Envi Tech AL discovery cache policy';
$lines = preg_split('/(?<=\n)/', $source);
if (!is_array($lines)) {
    fwrite(STDERR, "Could not split the .htaccess source safely.\n");
    exit(65);
}

$withoutBlock = [];
$insideBlock = false;
$beginCount = 0;
$endCount = 0;

foreach ($lines as $line) {
    $logical = rtrim($line, "\r\n");
    if ($logical === $begin) {
        if ($insideBlock || ++$beginCount > 1) {
            fwrite(STDERR, "The managed .htaccess begin marker is duplicated or nested.\n");
            exit(65);
        }
        $insideBlock = true;
        continue;
    }

    if ($logical === $end) {
        if (!$insideBlock || ++$endCount > 1) {
            fwrite(STDERR, "The managed .htaccess end marker is unmatched or duplicated.\n");
            exit(65);
        }
        $insideBlock = false;
        continue;
    }

    if ($insideBlock) {
        continue;
    }

    $withoutBlock[] = $line;
}

if ($insideBlock || $beginCount !== $endCount) {
    fwrite(STDERR, "The managed .htaccess markers are incomplete.\n");
    exit(65);
}

$source = implode('', $withoutBlock);
$removedSources = [];

if ($environment === 'production') {
    $reviewedRules = [
        'water-testing-in-pakistan' => '/services/water-testing-lab-services/?',
        'water-testing-lab-near-me' => '/services/water-testing-lab-services/?',
        'water-quality-testing-mastering-your-ultimate-guide-to-excellence' => '/services/water-testing-lab-services/?',
        'get-accurate-results-from-our-water-testing-lab-in-lahore' => '/lahore-environmental-lab/?',
        'reliable-water-testing-services-environmental-lab-karachi' => '/karachi-environmental-lab/?',
        'discover-the-best-testing-laboratory-near-you-for-reliable-and-accurate-results' => '/how-to-choose-the-suitable-environmental-lab/?',
        'https-envitechal-com-services-environmental-consultancy' => '/services/environmental-consultancy/?',
        'https-envitechal-com-calibration-of-equipment-in-karachi' => '/services/equipment-calibration-services/?',
        '22653-2' => '/services/water-testing-lab-services/?',
    ];

    $outputLines = [];
    $rewriteConditionsPending = false;
    $lines = preg_split('/(?<=\n)/', $source);
    foreach ($lines as $line) {
        $logical = rtrim($line, "\r\n");
        $trimmed = trim($logical);
        if (preg_match('/^RewriteCond[\t ]+/i', $trimmed)) {
            $rewriteConditionsPending = true;
            $outputLines[] = $line;
            continue;
        }
        if ($trimmed === '' || $trimmed[0] === '#') {
            $outputLines[] = $line;
            continue;
        }
        $isRewriteRule = preg_match('/^[\t ]*RewriteRule[\t ]+/i', $logical) === 1;
        $mentionsReviewedSource = false;
        foreach (array_keys($reviewedRules) as $reviewedSource) {
            if ($isRewriteRule && strpos($logical, $reviewedSource) !== false) {
                $mentionsReviewedSource = true;
                break;
            }
        }

        if (!$mentionsReviewedSource) {
            $outputLines[] = $line;
            $rewriteConditionsPending = false;
            continue;
        }

        if ($rewriteConditionsPending) {
            fwrite(STDERR, "A reviewed legacy RewriteRule is governed by one or more RewriteCond directives; refusing to detach them.\n");
            exit(65);
        }

        if (!preg_match(
            '/^[\t ]*RewriteRule[\t ]+\^([^\s]+)\/\?\$[\t ]+(\/[^\s]+)[\t ]+\[R=301,L,NE\][\t ]*$/i',
            $logical,
            $matches
        )) {
            fwrite(STDERR, "A reviewed legacy source appears in an unrecognized RewriteRule: {$logical}\n");
            exit(65);
        }

        $ruleSource = $matches[1];
        $ruleTarget = $matches[2];
        if (!isset($reviewedRules[$ruleSource]) || $reviewedRules[$ruleSource] !== $ruleTarget) {
            fwrite(STDERR, "A reviewed legacy RewriteRule has an unexpected source or target: {$logical}\n");
            exit(65);
        }
        if (isset($removedSources[$ruleSource])) {
            fwrite(STDERR, "A reviewed legacy RewriteRule is duplicated: {$ruleSource}\n");
            exit(65);
        }

        $removedSources[$ruleSource] = true;
        $rewriteConditionsPending = false;
    }

    $removedCount = count($removedSources);
    if ($removedCount !== 0 && $removedCount !== count($reviewedRules)) {
        fwrite(
            STDERR,
            "Found {$removedCount} of the 9 reviewed legacy RewriteRules; refusing a partial removal.\n"
        );
        exit(65);
    }
    $source = implode('', $outputLines);
}

// Append after hosting-wide Expires/header rules so this exact-path policy is
// evaluated last. Normalize only a missing final line ending; every existing
// directive and byte otherwise remains in its original order.
$candidate = $source;
if ($candidate !== '' && substr($candidate, -1) !== "\n") {
    $candidate .= "\n";
}
$candidate .= $block;
if (substr_count($candidate, $begin) !== 1 || substr_count($candidate, $end) !== 1) {
    fwrite(STDERR, "The candidate does not contain exactly one managed header block.\n");
    exit(65);
}

$bytes = file_put_contents($outputPath, $candidate, LOCK_EX);
if (!is_int($bytes) || $bytes !== strlen($candidate)) {
    fwrite(STDERR, "Could not write the complete .htaccess candidate.\n");
    exit(74);
}

printf("removed_rules=%d\n", count($removedSources));
