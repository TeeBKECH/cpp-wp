<?php
/**
 * Default page template: intro + Gutenberg content (same structure as service single).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (!have_posts()) {
    get_footer();
    return;
}

while (have_posts()) :
    the_post();
    get_template_part('template-parts/page', 'shell');
endwhile;

get_footer();
