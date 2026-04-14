<?php
/**
 * Register SCF field group for services archive (options).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return void
 */
function cpp_courses_acf_register_services_archive_group() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    $path = trailingslashit(get_template_directory()) . 'acf-json/group_cpp_services_archive.json';
    if (!is_readable($path)) {
        return;
    }
    $raw = file_get_contents($path);
    if ($raw === false) {
        return;
    }
    $group = json_decode($raw, true);
    if (!is_array($group) || empty($group['key']) || empty($group['fields'])) {
        return;
    }
    acf_add_local_field_group($group);
}
add_action('acf/init', 'cpp_courses_acf_register_services_archive_group', 5);
