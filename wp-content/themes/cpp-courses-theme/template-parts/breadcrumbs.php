<?php
/**
 * Breadcrumbs.
 *
 * Uses Yoast SEO breadcrumbs if available.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('yoast_breadcrumb')) {
    yoast_breadcrumb('<nav class="breadcrumbs" aria-label="Хлебные крошки"><div class="breadcrumbs_list">', '</div></nav>');
}

