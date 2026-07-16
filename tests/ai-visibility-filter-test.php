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

eta_test_same(
    file_get_contents(dirname(__DIR__) . '/deploy/public_html/llms.txt'),
    eta_ai_visibility_llms_text(false),
    'static and virtual llms.txt stay identical'
);

eta_test_same(
    file_get_contents(dirname(__DIR__) . '/deploy/public_html/llms-full.txt'),
    eta_ai_visibility_llms_text(true),
    'static and virtual llms-full.txt stay identical'
);

echo "Legacy chatbot response filter and LLMS parity tests passed.\n";
