<?php
// wp eval-file scripts/fix-mail.php  (ETA_APPLY=1 to write; otherwise dry run)
// 1) Careers form 23: "Gander" -> "Gender" in form and mail.
// 2) Contact form 21: Cc kazmi.imran@envitechal.com on the main mail.
$apply = getenv('ETA_APPLY') === '1';
$cc = 'kazmi.imran@envitechal.com';
foreach ([23 => 'careers', 21 => 'contact', 22994 => 'verification'] as $id => $label) {
    $form = get_post_meta($id, '_form', true);
    $mail = get_post_meta($id, '_mail', true);
    if (!is_array($mail)) { echo "$id $label: no mail meta\n"; continue; }
    $before = md5(serialize([$form, $mail]));
    $form = preg_replace('/\bGander\b/', 'Gender', (string) $form); // labels only; lowercase field names untouched
    foreach (['subject', 'body'] as $k) {
        $mail[$k] = preg_replace('/\bGander\b/', 'Gender', (string) $mail[$k]);
    }
    if ($id === 21 && stripos((string) $mail['additional_headers'], $cc) === false) {
        $mail['additional_headers'] = trim((string) $mail['additional_headers'] . "\nCc: " . $cc);
    }
    $changed = md5(serialize([$form, $mail])) !== $before;
    echo "$id $label: " . ($changed ? 'CHANGE' : 'no change') . " | Gander left: " . substr_count($form . $mail['body'], 'Gander') . " | headers: " . str_replace("\n", ' / ', $mail['additional_headers']) . "\n";
    if ($changed && $apply) {
        update_post_meta($id, '_form', $form);
        update_post_meta($id, '_mail', $mail);
    }
}
echo $apply ? "APPLIED\n" : "DRY RUN (set ETA_APPLY=1)\n";
