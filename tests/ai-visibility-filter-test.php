<?php

define('ABSPATH', __DIR__);

function add_action()
{
    // WordPress hook registration is intentionally inert in this pure helper test.
}

function home_url($path = '')
{
    return 'https://envitechal.com' . $path;
}

require dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/ai-visibility.php';

function eta_test_same($expected, $actual, $label)
{
    if ($expected === $actual) {
        return;
    }

    file_put_contents('php://stderr', "FAILED: {$label}\nExpected: {$expected}\nActual:   {$actual}\n", FILE_APPEND);
    exit(1);
}

function eta_test_true($condition, $label)
{
    if ($condition) {
        return;
    }

    file_put_contents('php://stderr', "FAILED: {$label}\n", FILE_APPEND);
    exit(1);
}

$ordinary_script = '<script src="/assets/site.js">window.site = true;</script>';
$ordinary_style = '<style>.site-button { color: green; }</style>';
eta_test_same(
    $ordinary_style . $ordinary_script,
    eta_ai_visibility_remove_legacy_chatbot_html($ordinary_style . $ordinary_script),
    'unrelated script and style remain unchanged'
);

$agent_script = '<script defer data-agent-id="public-id" src="https://example.invalid/loader.js"></script>';
eta_test_same(
    '<main>safe</main>',
    eta_ai_visibility_remove_legacy_chatbot_html('<main>safe</main>' . $agent_script),
    'agent identifier removes the complete script only'
);

$chatbot_script = '<script data-chatbot-id="public-id">window.chatbot = true;</script>';
eta_test_same(
    '<p>before</p><p>after</p>',
    eta_ai_visibility_remove_legacy_chatbot_html('<p>before</p>' . $chatbot_script . '<p>after</p>'),
    'chatbot identifier removes the complete script only'
);

$widget_script = '<script async src="https://example.invalid/static/chatbot/widget.js"></script>';
$chatbot_style = '<style>.chatbot-button:hover { opacity: .9; }</style>';
eta_test_same(
    "<div>content</div>\n\n",
    eta_ai_visibility_remove_legacy_chatbot_html('<div>content</div>' . $chatbot_style . "\n" . $widget_script . "\n"),
    'widget source and immediately preceding distinctive style are removed'
);

eta_test_same(
    "\n<div>content</div>",
    eta_ai_visibility_remove_legacy_chatbot_html($widget_script . "\n" . $chatbot_style . '<div>content</div>'),
    'immediately following distinctive style is removed'
);

eta_test_same(
    $chatbot_style,
    eta_ai_visibility_remove_legacy_chatbot_html($chatbot_style),
    'distinctive style without a target script remains'
);

eta_test_same(
    $chatbot_style . '<div>separate</div>',
    eta_ai_visibility_remove_legacy_chatbot_html($chatbot_style . '<div>separate</div>' . $widget_script),
    'distinctive style separated by HTML remains while target script is removed'
);

$similar_style = '<style>.chatbot-buttonish { display: block; }</style>';
eta_test_same(
    $similar_style,
    eta_ai_visibility_remove_legacy_chatbot_html($similar_style . $widget_script),
    'similar but non-distinctive style remains'
);

$delayed_widget_script = '<script type="litespeed/javascript" src="https://example.invalid/static/chatbot/widget.js?ver=1&amp;delay=1#fragment"></script>';
eta_test_same(
    '<main>safe</main>',
    eta_ai_visibility_remove_legacy_chatbot_html($delayed_widget_script . '<main>safe</main>'),
    'widget source with optimizer type, query, entity encoding and fragment is removed'
);

$similar_widget_path = '<script src="https://example.invalid/static/chatbot/widget.js-extra?ver=1"></script>';
eta_test_same(
    $similar_widget_path,
    eta_ai_visibility_remove_legacy_chatbot_html($similar_widget_path),
    'similar widget filename is not removed'
);

$attribute_value_only = '<script data-note="data-agent-id=example">window.safe = true;</script>';
eta_test_same(
    $attribute_value_only,
    eta_ai_visibility_remove_legacy_chatbot_html($attribute_value_only),
    'identifier text inside another attribute value does not trigger removal'
);

$incomplete_script = '<script data-agent-id="public-id">';
eta_test_same(
    $incomplete_script,
    eta_ai_visibility_remove_legacy_chatbot_html($incomplete_script),
    'incomplete script tag remains unchanged'
);

eta_test_same(
    $agent_script,
    eta_ai_visibility_filter_legacy_chatbot_response($agent_script),
    'non-HTML response without an HTML content type remains unchanged'
);

$html_document = '<!doctype html><html><body>' . $agent_script . '<main>safe</main></body></html>';
eta_test_same(
    '<!doctype html><html><body><main>safe</main></body></html>',
    eta_ai_visibility_filter_legacy_chatbot_response($html_document),
    'headerless full HTML response is filtered'
);

$_SERVER['HTTP_ACCEPT'] = 'text/html, text/markdown;q=1.0';
eta_test_true(eta_ai_visibility_wants_markdown(), 'Markdown Accept media range is recognised');
$_SERVER['HTTP_ACCEPT'] = 'text/markdown;q=0, text/html';
eta_test_true(!eta_ai_visibility_wants_markdown(), 'Markdown q=0 media range is refused');

$rendered_html = <<<'HTML'
<!doctype html><html><body>
<header>Header navigation</header>
<main id="primary">
  <h1>Parameters &amp; Panels</h1>
  <p>Testing &amp; analysis with <a href="/services/">service details</a>.</p>
  <ul><li>Water testing</li><li>Air monitoring</li></ul>
  <table><tr><th>Parameter</th><th>Matrix</th></tr><tr><td>COD</td><td>Effluent</td></tr></table>
  <aside class="sidebar">Sidebar text</aside>
  <div class="eta-chatbot-root">Chatbot text</div>
  <script>window.secret = true;</script>
</main>
<footer>Footer navigation</footer>
</body></html>
HTML;
$rendered_markdown = eta_ai_visibility_extract_main_markdown($rendered_html, 'https://envitechal.com/example/');
eta_test_same('Parameters & Panels', $rendered_markdown['title'], 'rendered H1 entities are decoded before use');
eta_test_true(strpos($rendered_markdown['content'], '# Parameters & Panels') !== false, 'rendered heading is preserved');
eta_test_true(strpos($rendered_markdown['content'], '[service details](https://envitechal.com/services/)') !== false, 'relative links become absolute');
eta_test_true(strpos($rendered_markdown['content'], '- Water testing') !== false, 'unordered lists are preserved');
eta_test_true(strpos($rendered_markdown['content'], '| Parameter | Matrix |') !== false, 'tables are preserved');
eta_test_true(strpos($rendered_markdown['content'], 'Sidebar text') === false, 'sidebar is excluded');
eta_test_true(strpos($rendered_markdown['content'], 'Chatbot text') === false, 'chatbot root is excluded');
eta_test_true(strpos($rendered_markdown['content'], 'window.secret') === false, 'scripts are excluded');

$visibility_source = file_get_contents(dirname(__DIR__) . '/wp-content/themes/generatepress-envitechal/inc/ai-visibility.php');
foreach (['/llms-full.txt', '/services/', '/report-verification-portal/', '/.well-known/security.txt'] as $discovery_path) {
    eta_test_true(strpos($visibility_source, "home_url('{$discovery_path}')") !== false, "Markdown discovery metadata includes {$discovery_path}");
}

eta_test_same(
    str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__) . '/deploy/public_html/llms.txt')),
    str_replace("\r\n", "\n", eta_ai_visibility_llms_text(false)),
    'static and virtual llms.txt stay identical'
);

eta_test_same(
    str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__) . '/deploy/public_html/llms-full.txt')),
    str_replace("\r\n", "\n", eta_ai_visibility_llms_text(true)),
    'static and virtual llms-full.txt stay identical'
);

eta_test_same(
    "Contact: mailto:info@envitechal.com\nPreferred-Languages: en, ur\nCanonical: https://envitechal.com/.well-known/security.txt\nExpires: 2027-07-21T23:59:59Z\n",
    eta_ai_visibility_security_text('2027-07-21T23:59:59Z'),
    'security.txt fields remain exact'
);

echo "Legacy chatbot response filter and LLMS parity tests passed.\n";
