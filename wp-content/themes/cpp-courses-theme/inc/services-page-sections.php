<?php
/**
 * Shared blocks for services archive / single (about from front page, teachers, etc.).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Static front page ID for pulling main_* home fields.
 *
 * @return int
 */
function cpp_services_sections_front_page_id() {
    if (function_exists('cpp_home_front_page_id')) {
        return (int) cpp_home_front_page_id();
    }
    return (int) get_option('page_on_front');
}

/**
 * @return string
 */
function cpp_services_archive_seo_title() {
    if (!function_exists('get_field')) {
        return '';
    }
    return trim((string) get_field('cpp_services_archive_seo_title', 'option'));
}

/**
 * @return string
 */
function cpp_services_archive_seo_content() {
    if (!function_exists('get_field')) {
        return '';
    }
    return (string) get_field('cpp_services_archive_seo_content', 'option');
}
