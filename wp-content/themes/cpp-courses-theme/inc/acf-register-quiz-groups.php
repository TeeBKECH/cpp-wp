<?php
/**
 * Register quiz SCF field groups from Local JSON (no sync dependency).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return void
 */
function cpp_courses_acf_register_quiz_field_groups() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $dir = trailingslashit(get_template_directory()) . 'acf-json/';
    $files = array(
        'group_cpp_quiz_question.json',
        'group_cpp_quiz_page.json',
    );

    foreach ($files as $file) {
        $path = $dir . $file;
        if (!is_readable($path)) {
            continue;
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            continue;
        }
        $group = json_decode($raw, true);
        if (!is_array($group) || empty($group['key']) || empty($group['fields'])) {
            continue;
        }
        acf_add_local_field_group($group);
    }
}
add_action('acf/init', 'cpp_courses_acf_register_quiz_field_groups', 5);
