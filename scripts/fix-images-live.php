<?php
/** Repoint the in-content image of the three live-only Knowledge Hub posts to their existing media files. */
$map = [23135 => 'food-beverage-water-environmental-testing', 23137 => 'thermal-imaging-inspection-electrical-reliability', 23139 => 'calibration-certificate-intervals-traceability'];
foreach ($map as $id => $slug) {
    $p = get_post($id); if (!$p) { echo "$id missing\n"; continue; }
    $c = str_replace("/wp-content/uploads/envi-tech-al-knowledge-hub/$slug.webp", "/wp-content/uploads/2026/09/$slug.webp", $p->post_content, $n);
    if ($n) { $r = wp_update_post(['ID' => $id, 'post_content' => $c], true); echo is_wp_error($r) ? "ERROR $id\n" : "updated $id ($n)\n"; } else { echo "no change $id\n"; }
}
