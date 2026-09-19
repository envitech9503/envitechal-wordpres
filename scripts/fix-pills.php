<?php
/**
 * QA-11: remove pasted chat-assistant citation pills (including the ecspak.com
 * link whose certificate fails) from post 22706 on staging. Run with:
 *   wp --path=/home/envitechal/staging.envitechal.com eval-file ~/fix-pills.php
 */
$id = 22706;
$post = get_post($id);
if (!$post) { echo "post $id not found\n"; return; }
$c = $post->post_content;
$before = substr_count($c, 'webpage-citation-pill');
$ecs = substr_count($c, 'ecspak.com');
// Remove each pill together with its outer wrapper span by balanced-tag scanning.
function eta_qa_strip_pills($c, &$count) {
    $count = 0;
    while (($p = strpos($c, 'data-testid="webpage-citation-pill"')) !== false) {
        // Start at the wrapper <span ... data-state="closed"> immediately before, else at the pill span itself.
        $start = strrpos(substr($c, 0, $p), '<span');
        $wrap = strrpos(substr($c, 0, $start), '<span class="" data-state="closed">');
        if ($wrap !== false && trim(substr($c, $wrap + strlen('<span class="" data-state="closed">'), $start - $wrap - strlen('<span class="" data-state="closed">'))) === '') {
            $start = $wrap;
        }
        $depth = 0; $pos = $start; $end = false;
        while (preg_match('#<(/?)span\b[^>]*>#i', $c, $m, PREG_OFFSET_CAPTURE, $pos)) {
            $pos = $m[0][1] + strlen($m[0][0]);
            $depth += $m[1][0] === '/' ? -1 : 1;
            if ($depth === 0) { $end = $pos; break; }
        }
        if ($end === false) { break; }
        $c = substr($c, 0, $start) . substr($c, $end);
        $count++;
    }
    return $c;
}
$c = eta_qa_strip_pills($c, $n1);
$n2 = 0;
$after = substr_count($c, 'webpage-citation-pill');
$ecs2 = substr_count($c, 'ecspak.com');
echo "pills before=$before after=$after (removed wrappers=$n1 fallback=$n2); ecspak before=$ecs after=$ecs2\n";
if ($after === 0 && $ecs2 === 0 && $c !== $post->post_content) {
    $r = wp_update_post(['ID' => $id, 'post_content' => $c], true);
    echo is_wp_error($r) ? 'ERROR ' . $r->get_error_message() : "updated $id\n";
    if (function_exists('rank_math')) { do_action('rank_math/cache/purge'); }
} else {
    echo "NOT updated (pattern did not fully match); no change made\n";
}
