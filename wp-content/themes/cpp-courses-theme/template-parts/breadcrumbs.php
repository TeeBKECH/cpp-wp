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
} elseif (is_404()) {
    ?>
    <nav class="breadcrumbs" aria-label="<?php echo esc_attr__('Хлебные крошки', 'cpp-courses-theme'); ?>">
        <ol class="breadcrumbs_list">
            <li class="breadcrumbs_item">
                <a class="breadcrumbs_link" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Главная', 'cpp-courses-theme'); ?></a>
            </li>
            <li class="breadcrumbs_item">
                <span class="breadcrumbs_sep" aria-hidden="true">/</span>
                <span class="breadcrumbs_current" aria-current="page"><?php esc_html_e('Страница не найдена', 'cpp-courses-theme'); ?></span>
            </li>
        </ol>
    </nav>
    <?php
} else {
    ?>
    <nav class="breadcrumbs" aria-label="<?php echo esc_attr__('Хлебные крошки', 'cpp-courses-theme'); ?>">
        <ol class="breadcrumbs_list">
            <li class="breadcrumbs_item">
                <a class="breadcrumbs_link" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Главная', 'cpp-courses-theme'); ?></a>
            </li>
            <li class="breadcrumbs_item">
                <span class="breadcrumbs_sep" aria-hidden="true">/</span>
                <span class="breadcrumbs_current" aria-current="page"><?php echo esc_html(get_the_title()); ?></span>
            </li>
        </ol>
    </nav>
    <?php
}

