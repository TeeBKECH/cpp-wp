<?php
/**
 * Register front page + teacher + vacancy field groups from JSON.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return void
 */
function cpp_courses_acf_register_home_field_groups() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    $dir = trailingslashit(get_template_directory()) . 'acf-json/';
    foreach (array('group_cpp_home.json', 'group_cpp_teacher.json', 'group_cpp_vacancy.json') as $file) {
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
add_action('acf/init', 'cpp_courses_acf_register_home_field_groups', 5);
