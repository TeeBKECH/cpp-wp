<?php
/**
 * Disable front-end search (?s=, ?search=, /search/ …).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return void
 */
function cpp_courses_disable_frontend_search() {
    if (is_admin()) {
        return;
    }
    if (isset($_GET['s']) || isset($_GET['search'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
    if (is_search()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'cpp_courses_disable_frontend_search', 1);
