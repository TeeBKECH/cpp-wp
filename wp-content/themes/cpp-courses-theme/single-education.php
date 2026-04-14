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
    $cta_label = function_exists('get_field') ? (string) get_field('cpp_edu_cta_label') : '';
    if ($cta_label === '') {
        $cta_label = __('Оставить заявку', 'cpp-courses-theme');
    }
    $cta_link = function_exists('get_field') ? get_field('cpp_edu_cta_url') : null;
    $cta_external = '';
    $cta_target = '_self';
    if (is_array($cta_link) && !empty($cta_link['url'])) {
        $cta_external = trim((string) $cta_link['url']);
        if (!empty($cta_link['target'])) {
            $cta_target = (string) $cta_link['target'];
        }
    }

    $cf7_form_post = cpp_courses_get_option('cpp_cf7_form_post', null);
    $fancy_id = 'cpp-edu-cta-form-' . get_the_ID();
    $cf7_html = '';
    if (!empty($cf7_form_post)) {
        $cf7_html = do_shortcode('[contact-form-7 id="' . (int) $cf7_form_post . '"]');
    }
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
                            <?php if ($cta_external !== '') : ?>
                                <a class="button button--filled button--sm" href="<?php echo esc_url($cta_external); ?>" target="<?php echo esc_attr($cta_target); ?>">
                                    <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                                </a>
                            <?php elseif ($cf7_html !== '') : ?>
                                <div id="<?php echo esc_attr($fancy_id); ?>" class="cpp-svc-cta-fancybox-inline" style="display:none;width:100%;max-width:520px;">
                                    <?php echo $cf7_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                                <button
                                    type="button"
                                    class="button button--filled button--sm"
                                    data-fancybox="education-cta-<?php echo (int) get_the_ID(); ?>"
                                    data-src="#<?php echo esc_attr($fancy_id); ?>"
                                    data-type="inline"
                                >
                                    <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                                </button>
                            <?php else : ?>
                                <a class="button button--filled button--sm" href="<?php echo esc_url(home_url('/contacts/#contacts')); ?>">
                                    <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--page-content">
            <div class="container">
                <div class="page-content">
                    <div class="page-content_main page-content_main--blocks">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </section>

        <?php get_template_part('template-parts/section', 'cta-block'); ?>

        <?php get_template_part('template-parts/section-education-faq'); ?>

        <?php get_template_part('template-parts/section-related-education-single', null, array('exclude' => (int) get_the_ID())); ?>

        <?php get_template_part('template-parts/section-about-from-front'); ?>

        <?php get_template_part('template-parts/section-teachers-static'); ?>

        <?php get_template_part('template-parts/section-education-from-front'); ?>
    </main>
    <?php
endwhile;

get_footer();
