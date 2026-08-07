<?php
declare(strict_types=1);

$script = file_get_contents(dirname(__DIR__) . '/scripts/generate-ai-discovery.php');
if (!is_string($script)) {
    throw new RuntimeException('Unable to read discovery generator.');
}

$required = [
    "getenv('ETA_DISCOVERY_FETCH_BASE_URL')",
    "['https://envitechal.com', 'https://staging.envitechal.com']",
    "eta_discovery_fetch_url(\$url)",
    "\$canonical_url = home_url(\$parts['path']",
];
foreach ($required as $needle) {
    if (strpos($script, $needle) === false) {
        throw new RuntimeException('Missing safe discovery-origin contract: ' . $needle);
    }
}
if (strpos($script, "'http://") !== false) {
    throw new RuntimeException('Discovery generator must not allow an HTTP fetch origin.');
}

echo "Discovery generator staging-origin contract passed.\n";
