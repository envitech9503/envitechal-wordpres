<?php
/**
 * QA 24-09-2026: repoint three broken in-content image URLs (404 on staging
 * and live) to the existing media files, and repoint two redirecting internal
 * links in post 4642. Run:
 *   wp --path=/home/envitechal/staging.envitechal.com eval-file ~/fix-images.php
 */
$map = [
    23129 => ['/wp-content/uploads/envi-tech-al-knowledge-hub/how-to-read-water-test-report-peqs-who.webp' => '/wp-content/uploads/2026/07/how-to-read-water-test-report-peqs-who.webp'],
    23133 => ['/wp-content/uploads/envi-tech-al-knowledge-hub/pharmaceutical-environmental-testing-compliance.webp' => '/wp-content/uploads/2026/08/pharmaceutical-environmental-testing-compliance.webp'],
    4642  => [
        '/wp-content/uploads/2024/01/EnviTechal-SMO-61-Jan-24.jpg' => '/wp-content/uploads/2024/01/environmental-lab-services.jpg',
        'https://staging.envitechal.com/?p=3652' => 'https://staging.envitechal.com/how-to-choose-the-suitable-environmental-lab/',
        'https://envitechal.com/?p=3652' => 'https://envitechal.com/how-to-choose-the-suitable-environmental-lab/',
        'https://staging.envitechal.com/water-testing-lab-in-karachi/' => 'https://staging.envitechal.com/services/water-testing-lab-services/',
        'https://envitechal.com/water-testing-lab-in-karachi/' => 'https://envitechal.com/services/water-testing-lab-services/',
    ],
];
foreach ($map as $id => $pairs) {
    $post = get_post($id);
    if (!$post) { echo "$id missing\n"; continue; }
    $c = $post->post_content; $n = 0;
    foreach ($pairs as $from => $to) { $c = str_replace($from, $to, $c, $k); $n += $k; }
    if ($n && $c !== $post->post_content) {
        $r = wp_update_post(['ID' => $id, 'post_content' => $c], true);
        echo is_wp_error($r) ? "ERROR $id " . $r->get_error_message() . "\n" : "updated $id ($n replacements)\n";
    } else { echo "no change $id\n"; }
}
