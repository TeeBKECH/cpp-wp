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
    do_action('cpp_courses_breadcrumbs');
} else {
    ?>
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <ol class="breadcrumbs_list">
            <li class="breadcrumbs_item">
                <a class="breadcrumbs_link" href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            </li>
            <li class="breadcrumbs_item">
                <span class="breadcrumbs_sep" aria-hidden="true">/</span>
                <span class="breadcrumbs_current" aria-current="page"><?php echo esc_html(get_the_title()); ?></span>
            </li>
        </ol>
    </nav>
    <?php
}

