<?php
/**
 * 404 template (same shell as default pages / service single).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="main main--service main--404">
    <section class="section section--page-intro">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title"><?php esc_html_e('Страница не найдена', 'cpp-courses-theme'); ?></h1>
                <p class="page-intro_desc">
                    <?php esc_html_e('Такой страницы нет или адрес изменился. Проверьте ссылку или вернитесь на главную.', 'cpp-courses-theme'); ?>
                </p>
                <div class="cpp-404-actions">
                    <a class="button button--filled button--lg" href="<?php echo esc_url(home_url('/')); ?>">
                        <span class="button_text"><?php esc_html_e('На главную', 'cpp-courses-theme'); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();
