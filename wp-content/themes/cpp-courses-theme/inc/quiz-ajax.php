<?php
/**
 * AJAX: fetch one quiz question for page template QUIZ.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ordered quiz question post IDs for a QUIZ page.
 *
 * @param int $page_id
 * @return array<int, int>
 */
function cpp_quiz_get_ordered_question_ids($page_id) {
    $page_id = (int) $page_id;
    if ($page_id < 1 || !function_exists('get_field')) {
        return array();
    }
    $raw = get_field('quiz_items', $page_id);
    if (!is_array($raw)) {
        return array();
    }
    $ids = array();
    foreach ($raw as $item) {
        $id = is_numeric($item) ? (int) $item : 0;
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    return array_values(array_unique($ids));
}

/**
 * @return bool
 */
function cpp_quiz_page_has_template($page_id) {
    $page_id = (int) $page_id;
    if ($page_id < 1) {
        return false;
    }
    $tpl = (string) get_page_template_slug($page_id);
    return $tpl === 'page-quiz.php';
}

/**
 * @return void
 */
function cpp_quiz_ajax_fetch_question() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'cpp_quiz')) {
        wp_send_json_error(array('message' => 'bad_nonce'), 403);
    }

    $page_id = isset($_POST['page_id']) ? (int) $_POST['page_id'] : 0;
    $index = isset($_POST['index']) ? (int) $_POST['index'] : 0;

    if ($page_id < 1 || !cpp_quiz_page_has_template($page_id)) {
        wp_send_json_error(array('message' => 'invalid_page'), 400);
    }

    $ids = cpp_quiz_get_ordered_question_ids($page_id);
    $total = count($ids);
    if ($total < 1 || $index < 0 || $index >= $total) {
        wp_send_json_error(array('message' => 'out_of_range'), 400);
    }

    $qid = $ids[ $index ];
    $post = get_post($qid);
    if (!$post instanceof WP_Post || $post->post_type !== 'quiz' || $post->post_status !== 'publish') {
        wp_send_json_error(array('message' => 'invalid_question'), 400);
    }

    $multiple = function_exists('get_field') && (bool) get_field('cpp_quiz_multiple', $qid);
    $rows = function_exists('get_field') ? get_field('quiz_answers', $qid) : null;
    if (!is_array($rows)) {
        $rows = array();
    }

    $answers = array();
    $correct_indices = array();
    $i = 0;
    foreach ($rows as $row) {
        $text = isset($row['answer_text']) ? (string) $row['answer_text'] : '';
        if ($text === '') {
            continue;
        }
        $correct = !empty($row['is_correct']);
        $answers[] = array(
            'i' => $i,
            'text' => $text,
        );
        if ($correct) {
            $correct_indices[] = $i;
        }
        $i++;
    }

    if (empty($answers)) {
        wp_send_json_error(array('message' => 'no_answers'), 400);
    }

    $question_html = apply_filters('the_content', $post->post_content);
    if (!is_string($question_html)) {
        $question_html = '';
    }

    wp_send_json_success(
        array(
            'index' => $index,
            'total' => $total,
            'question_html' => $question_html,
            'input_type' => $multiple ? 'checkbox' : 'radio',
            'answers' => $answers,
            'correct_indices' => $correct_indices,
        )
    );
}
add_action('wp_ajax_cpp_quiz_fetch_question', 'cpp_quiz_ajax_fetch_question');
add_action('wp_ajax_nopriv_cpp_quiz_fetch_question', 'cpp_quiz_ajax_fetch_question');
