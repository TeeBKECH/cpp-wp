<?php
/**
 * Single template for education CPT (registered in SCF).
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

    $intro_raw = function_exists('get_field') ? get_field('cpp_edu_intro_text') : '';
    $intro_raw = is_string($intro_raw) ? trim($intro_raw) : '';
    if ($intro_raw === '') {
        $intro_raw = get_the_excerpt();
    }

    $show_cta = function_exists('get_field') && (bool) get_field('cpp_edu_show_cta');
    $cta_label = function_exists('get_field') ? (string) get_field('cpp_edu_cta_label') : 'Оставить заявку';
    if ($cta_label === '') {
        $cta_label = 'Оставить заявку';
    }
    $cta_url = function_exists('get_field') ? trim((string) get_field('cpp_edu_cta_url')) : '';

    $faq_title = function_exists('get_field') ? (string) get_field('cpp_edu_faq_title') : 'Часто задаваемые вопросы';
    $faq_subtitle = function_exists('get_field') ? (string) get_field('cpp_edu_faq_subtitle') : '';
    $faq_items = function_exists('get_field') ? get_field('cpp_edu_faq_items') : null;
    $rel_title = function_exists('get_field') ? (string) get_field('cpp_edu_related_title') : 'Другие материалы';
    $rel_subtitle = function_exists('get_field') ? (string) get_field('cpp_edu_related_subtitle') : '';
    $rel_count = function_exists('get_field') ? (int) get_field('cpp_edu_related_count') : 3;
    if ($rel_count < 1) {
        $rel_count = 3;
    }
    if ($rel_count > 12) {
        $rel_count = 12;
    }

    $cf7_form_post = cpp_courses_get_option('cpp_cf7_form_post', null);
    $fancy_id = 'cpp-edu-cta-form-' . get_the_ID();
    $cf7_html = '';
    if (!empty($cf7_form_post)) {
        $cf7_html = do_shortcode('[contact-form-7 id="' . (int) $cf7_form_post . '"]');
    }

    $related = new WP_Query(
        array(
            'post_type' => 'education',
            'post_status' => 'publish',
            'posts_per_page' => $rel_count,
            'post__not_in' => array(get_the_ID()),
            'orderby' => 'date',
            'order' => 'DESC',
        )
    );
    ?>
    <main class="main main--service">
        <section class="section section--page-intro">
            <div class="container">
                <div class="page-intro">
                    <?php get_template_part('template-parts/breadcrumbs'); ?>
                    <h1 class="page-intro_title"><?php the_title(); ?></h1>
                    <?php if ($intro_raw !== '') : ?>
                        <p class="page-intro_desc"><?php echo nl2br(esc_html($intro_raw)); ?></p>
                    <?php endif; ?>
                    <?php if ($show_cta) : ?>
                        <div class="page-intro_actions">
                            <?php if ($cta_url !== '' && filter_var($cta_url, FILTER_VALIDATE_URL)) : ?>
                                <a class="button button--filled button--sm" href="<?php echo esc_url($cta_url); ?>">
                                    <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                                </a>
                            <?php else : ?>
                                <?php if ($cf7_html !== '') : ?>
                                    <div id="<?php echo esc_attr($fancy_id); ?>" class="cpp-svc-cta-fancybox-inline" style="display:none;width:100%;max-width:520px;">
                                        <?php echo $cf7_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </div>
                                    <a
                                        class="button button--filled button--sm"
                                        href="#"
                                        data-fancybox="education-cta"
                                        data-src="#<?php echo esc_attr($fancy_id); ?>"
                                        data-type="inline"
                                    >
                                        <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                                    </a>
                                <?php else : ?>
                                    <a class="button button--filled button--sm" href="<?php echo esc_url(home_url('/contacts/#contacts')); ?>">
                                        <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--page-content">
            <div class="container">
                <div class="page-content">
                    <div class="page-content_main">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($faq_items) && is_array($faq_items)) : ?>
            <section class="section section--faq">
                <div class="container">
                    <div class="faq section_content">
                        <div class="section_header">
                            <h2 class="section_title"><?php echo esc_html($faq_title); ?></h2>
                            <?php if ($faq_subtitle !== '') : ?>
                                <p class="section_subtitle"><?php echo esc_html($faq_subtitle); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="faq_list">
                            <?php
                            $fi = 0;
                            foreach ($faq_items as $fitem) :
                                $q = isset($fitem['question']) ? (string) $fitem['question'] : '';
                                $a = isset($fitem['answer']) ? $fitem['answer'] : '';
                                if ($q === '') {
                                    continue;
                                }
                                $open = (0 === $fi) ? ' open' : '';
                                $fi++;
                                ?>
                                <div class="accordion_item<?php echo esc_attr($open); ?>" data-accordion="data-accordion">
                                    <div class="accordion_item_head" data-accordion-trigger="data-accordion-trigger">
                                        <div class="accordion_item_title"><?php echo esc_html($q); ?></div>
                                        <div class="accordion_item_icon"></div>
                                    </div>
                                    <div class="accordion_item_body">
                                        <div class="accordion_item_content">
                                            <?php echo wp_kses_post($a); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($related->have_posts()) : ?>
            <section class="section section--services" id="other-education">
                <div class="container container--other-services">
                    <div class="services section_content">
                        <div class="section_header section_header--other-services">
                            <h2 class="section_title"><?php echo esc_html($rel_title); ?></h2>
                            <?php if ($rel_subtitle !== '') : ?>
                                <p class="section_subtitle"><?php echo esc_html($rel_subtitle); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="services_grid services_grid--swiper">
                            <?php
                            while ($related->have_posts()) :
                                $related->the_post();
                                get_template_part('template-parts/post-card', 'education');
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <?php
endwhile;

get_footer();
