<?php
/**
 * 404 template.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="main main--404">
    <section class="section section--page-intro section--page-intro--alt">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title">Страница не найдена</h1>
                <p class="page-intro_desc">Кажется, такой страницы нет или она была перемещена.</p>
                <div class="page-intro_info">
                    <div class="page-intro_info-item">
                        <span class="page-intro_info-item-text">Ошибка 404</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--page-content">
        <div class="container">
            <div class="page-content">
                <div class="page-content_main">
                    <div class="section_header">
                        <h2 class="section_title">Куда перейти?</h2>
                    </div>
                    <div class="page-content_section">
                        <a class="button button--filled button--md" href="<?php echo esc_url(home_url('/')); ?>">
                            <span class="button_text">На главную</span>
                        </a>
                        <a class="button button--filled button--md" href="<?php echo esc_url(home_url('/articles/')); ?>">
                            <span class="button_text">Статьи</span>
                        </a>
                        <a class="button button--filled button--md" href="<?php echo esc_url(home_url('/contacts/')); ?>">
                            <span class="button_text">Контакты</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();

