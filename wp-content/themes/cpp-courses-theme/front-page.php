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
echo '<!-- deploy-check: github-actions-ftp -->';
get_footer();
