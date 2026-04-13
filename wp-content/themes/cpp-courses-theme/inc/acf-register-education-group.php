<?php
/**
 * Load education field group from Local JSON via acf_add_local_field_group.
 *
 * Ensures fields exist on the site even when SCF does not list JSON for sync.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return void
 */
function cpp_courses_acf_register_education_field_group() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $path = trailingslashit(get_template_directory()) . 'acf-json/group_cpp_education.json';
    if (!is_readable($path)) {
        return;
    }

    $json = file_get_contents($path);
    if ($json === false) {
        return;
    }

    $group = json_decode($json, true);
    if (!is_array($group) || empty($group['key']) || empty($group['fields'])) {
        return;
    }

    acf_add_local_field_group($group);
}
add_action('acf/init', 'cpp_courses_acf_register_education_field_group', 5);
