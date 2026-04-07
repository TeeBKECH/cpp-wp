<?php
/**
 * Front page template.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
echo cpp_courses_render_static_page('index-static.html');
get_footer();
