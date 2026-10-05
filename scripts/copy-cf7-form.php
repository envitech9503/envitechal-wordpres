<?php
/** Copy one CF7 form template from a JSON file written by the staging export (see command sheet). Usage via WP-CLI eval-file with ETA_CF7_ID and ETA_CF7_FILE env vars. */
$id = (int) getenv('ETA_CF7_ID'); $file = (string) getenv('ETA_CF7_FILE');
$mode = (string) getenv('ETA_CF7_MODE');
$f = WPCF7_ContactForm::get_instance($id); if (!$f) { echo "form $id not found\n"; return; }
if ($mode === 'export') { file_put_contents($file, $f->prop('form')); echo "exported form $id: " . strlen($f->prop('form')) . " bytes\n"; return; }
$new = (string) file_get_contents($file); if ($new === '') { echo "empty template; no change\n"; return; }
file_put_contents($file . '.live-before', $f->prop('form'));
$f->set_properties(['form' => $new]); $f->save(); echo "imported form $id: " . strlen($new) . " bytes (previous saved to $file.live-before)\n";
