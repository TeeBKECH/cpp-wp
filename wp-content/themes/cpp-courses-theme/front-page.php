<?php
/**
 * Front page: same shell as default pages (header/footer + WP content), no static HTML dump.
 *
 * Set «Главная» (or any page) as static front page in Настройки → Чтение.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) :
        the_post();
        get_template_part('template-parts/page', 'shell');
    endwhile;
} else {
    ?>
    <main class="main main--service main--index">
        <section class="section section--page-intro">
            <div class="container">
                <div class="page-intro">
                    <h1 class="page-intro_title"><?php bloginfo('name'); ?></h1>
                    <p class="page-intro_desc"><?php esc_html_e('Назначьте статическую главную страницу в настройках чтения и добавьте контент в редактор.', 'cpp-courses-theme'); ?></p>
                </div>
            </div>
        </section>
    </main>
    <?php
}

get_footer();
