<?php
/**
 * Single template for articles CPT.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();
        $views = (int) get_post_meta(get_the_ID(), 'cpp_article_views', true);
        ?>
        <main class="main main--article">
            <section class="section section--page-intro">
                <div class="container">
                    <div class="page-intro">
                        <nav class="breadcrumbs" aria-label="Хлебные крошки">
                            <ol class="breadcrumbs_list">
                                <li class="breadcrumbs_item">
                                    <a class="breadcrumbs_link" href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
                                </li>
                                <li class="breadcrumbs_item">
                                    <span class="breadcrumbs_sep" aria-hidden="true">/</span>
                                    <a class="breadcrumbs_link" href="<?php echo esc_url(get_post_type_archive_link('articles')); ?>">Статьи</a>
                                </li>
                                <li class="breadcrumbs_item">
                                    <span class="breadcrumbs_sep" aria-hidden="true">/</span>
                                    <span class="breadcrumbs_current" aria-current="page"><?php the_title(); ?></span>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="page-intro_title"><?php the_title(); ?></h1>
                        <div class="page-intro_info">
                            <div class="page-intro_info-item">
                                <span class="page-intro_info-item-text"><?php echo esc_html(get_the_date('j F Y')); ?></span>
                            </div>
                            <div class="page-intro_info-item">
                                <span class="page-intro_info-item-icon"></span>
                                <span class="page-intro_info-item-text"><?php echo esc_html(number_format_i18n($views)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section section--page-content">
                <div class="container">
                    <div class="page-content page-content--article">
                        <div class="page-content_main page-content_main--blocks">
                            <?php the_content(); ?>
                        </div>
                        <aside class="page-content_sidebar" aria-label="<?php echo esc_attr__('Содержание страницы', 'cpp-courses-theme'); ?>">
                            <div class="widget">
                                <div class="widget_title"><?php esc_html_e('Содержание:', 'cpp-courses-theme'); ?></div>
                                <ul class="toc-list" id="toc-list"></ul>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </main>
        <?php
    endwhile;
endif;

get_footer();

