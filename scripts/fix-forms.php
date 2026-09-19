<?php
/**
 * QA-06/08/09: rewrite the careers form (CF7 id 23) with visible labels,
 * autocomplete, a labelled gender select with a neutral first option and a
 * CV upload with format/size guidance; add required markers to the contact
 * form (CF7 id 21). Preserves each form's existing reCAPTCHA/submit block.
 * Run: wp --path=/home/envitechal/staging.envitechal.com eval-file ~/fix-forms.php
 */
if (!class_exists('WPCF7_ContactForm')) { echo "CF7 missing\n"; return; }
$dry = getenv('ETA_DRY') === '1';

$careers = WPCF7_ContactForm::get_instance(23);
$form = $careers ? (string) $careers->prop('form') : '';
if ($form === '') { echo "form 23 not found\n"; return; }
file_put_contents(getenv('HOME') . '/cf7-23-before.txt', $form);
$k = strpos($form, '<label class="eta-recaptcha-field">');
$submitPos = strpos($form, '[submit', $k === false ? 0 : $k);
if ($k === false || $submitPos === false) { echo "careers: recaptcha/submit block not found; no change\n"; } else {
    $tail = substr($form, $k); // recaptcha label + submit + closing markup
    $tail = preg_replace('/\[submit\s+"[^"]*"\]/', '[submit "Submit application"]', $tail, 1);
    $head = <<<'TPL'
<div class="eta-cf7-form form-22">
<p>
<label>Full name*
    [text* your-name autocomplete:name placeholder "Full name"]</label>
<label>Email address*
    [email* your-email autocomplete:email placeholder "name@example.com"]</label>
<label>Phone number*
    [tel* phone-number autocomplete:tel placeholder "+92 3XX XXXXXXX"]</label>
<label>Town / City*
    [text* town-city autocomplete:address-level2 placeholder "Town or city"]</label>
<label>Country*
    [text* country autocomplete:country-name placeholder "Country"]</label>
<label>Job position*
    [text* job-position placeholder "Position you are applying for"]</label>
<label>CNIC / Passport No. (optional)
    [text passport-no autocomplete:off placeholder "Optional"]</label>
<label>Years of experience*
    [text* years-of-experiences placeholder "e.g. 5"]</label>
<label>Gender*
    [select* gender first_as_label "Select gender" "Male" "Female" "Prefer not to say"]</label>
</p>
<p>
<label class="file-input" for="file-upload">Upload your CV*</label>
[file* choose id:file-upload limit:5mb filetypes:pdf|doc|docx]
<span id="file-hint" class="eta-field-hint">One file only. PDF, DOC or DOCX, up to 5 MB. Mention certificates in your statement below or bring them to interview.</span>
</p>
<p>
<label>Tell us about yourself*
    [textarea* talk-about-yourself placeholder "Relevant experience, qualifications and certifications"]</label>
TPL;
    $new = $head . $tail;
    // make sure the recaptcha label and submit still sit inside the last <p> of the original
    echo "careers: before=" . strlen($form) . " after=" . strlen($new) . " labels=" . substr_count($new, '<label') . " recaptcha=" . (strpos($new, 'g-recaptcha') !== false || strpos($new, '[recaptcha') !== false ? 'kept' : 'MISSING') . "\n";
    if (!$dry) { $careers->set_properties(['form' => $new]); $careers->save(); echo "careers: saved\n"; }
}

$contact = WPCF7_ContactForm::get_instance(21);
$cform = $contact ? (string) $contact->prop('form') : '';
if ($cform === '') { echo "form 21 not found\n"; return; }
file_put_contents(getenv('HOME') . '/cf7-21-before.txt', $cform);
$cnew = $cform;
$cnew = preg_replace('/<label>\s*Service required\s*(?=\r?\n|\[)/', '<label>Service required*' . "\n", $cnew, 1, $n1);
$cnew = preg_replace('/<label>\s*City\s*(?=\r?\n|\[)/', '<label>City*' . "\n", $cnew, 1, $n2);
$cnew = str_replace(['"Service required" "Water Testing"', '"City" "Karachi"'], ['"Select a service" "Water Testing"', '"Select a city" "Karachi"'], $cnew);
echo "contact: service*=$n1 city*=$n2 changed=" . ($cnew !== $cform ? 'yes' : 'no') . "\n";
if (!$dry && $cnew !== $cform) { $contact->set_properties(['form' => $cnew]); $contact->save(); echo "contact: saved\n"; }
