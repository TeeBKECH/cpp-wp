<?php
/**
 * Single template for services CPT.
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

    $intro_wysiwyg = function_exists('get_field') ? get_field('cpp_svc_intro_text') : '';
    $show_cta = function_exists('get_field') && (bool) get_field('cpp_svc_show_cta');
    $cta_label = function_exists('get_field') ? (string) get_field('cpp_svc_cta_label') : 'Оставить заявку';
    $cta_anchor = function_exists('get_field') ? (string) get_field('cpp_svc_cta_anchor') : '#cta';
    if ($cta_anchor === '') {
        $cta_anchor = '#cta';
    }

    $gallery = function_exists('get_field') ? get_field('cpp_svc_gallery') : null;
    $certificates = function_exists('get_field') ? get_field('cpp_svc_certificates') : null;
    $schedule = function_exists('get_field') ? get_field('cpp_svc_schedule') : null;
    $faq_title = function_exists('get_field') ? (string) get_field('cpp_svc_faq_title') : 'Часто задаваемые вопросы';
    $faq_subtitle = function_exists('get_field') ? (string) get_field('cpp_svc_faq_subtitle') : '';
    $faq_items = function_exists('get_field') ? get_field('cpp_svc_faq_items') : null;
    $toc_items = function_exists('get_field') ? get_field('cpp_svc_toc_items') : null;
    $rel_title = function_exists('get_field') ? (string) get_field('cpp_svc_related_title') : 'Другие услуги';
    $rel_subtitle = function_exists('get_field') ? (string) get_field('cpp_svc_related_subtitle') : '';
    $rel_count = function_exists('get_field') ? (int) get_field('cpp_svc_related_count') : 3;
    if ($rel_count < 1) {
        $rel_count = 3;
    }
    if ($rel_count > 12) {
        $rel_count = 12;
    }

    $related = new WP_Query(
        array(
            'post_type' => 'services',
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
                    <?php if (!empty($intro_wysiwyg)) : ?>
                        <div class="page-intro_desc"><?php echo wp_kses_post($intro_wysiwyg); ?></div>
                    <?php endif; ?>
                    <?php if ($show_cta) : ?>
                        <div class="page-intro_actions">
                            <a class="button button--filled button--sm" href="<?php echo esc_url($cta_anchor); ?>">
                                <span class="button_text"><?php echo esc_html($cta_label); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section section--page-content">
            <div class="container">
                <div class="page-content">
                    <div class="page-content_main">
                        <?php if (!empty($gallery) && is_array($gallery)) : ?>
                            <div class="page-content_gallery">
                                <div class="gallery gallery--service">
                                    <div class="gallery_items">
                                        <?php foreach ($gallery as $row) : ?>
                                            <?php
                                            $img = isset($row['image']) ? $row['image'] : null;
                                            $img_url = cpp_courses_image_field_url($img);
                                            if ($img_url === '') {
                                                continue;
                                            }
                                            ?>
                                            <div class="gallery_item" data-fancybox="gallery-service-<?php the_ID(); ?>" data-src="<?php echo esc_url($img_url); ?>">
                                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" />
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="gallery_nav">
                                        <nav class="swiper_navigation swiper_navigation--gallery">
                                            <button class="swiper_navigation-btn swiper_navigation-btn--prev" type="button" aria-label="Назад"></button>
                                            <div class="swiper_navigation-pages"></div>
                                            <button class="swiper_navigation-btn swiper_navigation-btn--next" type="button" aria-label="Вперёд"></button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php the_content(); ?>

                        <?php if (!empty($certificates) && is_array($certificates)) : ?>
                            <?php foreach ($certificates as $cert) : ?>
                                <?php
                                $c_title = isset($cert['title']) ? (string) $cert['title'] : '';
                                $c_content = isset($cert['content']) ? $cert['content'] : '';
                                $c_files = isset($cert['files']) && is_array($cert['files']) ? $cert['files'] : array();
                                $c_preview = isset($cert['preview']) ? $cert['preview'] : null;
                                $preview_url = cpp_courses_image_field_url($c_preview);
                                ?>
                                <div class="page-content_section page-content_section--certificate">
                                    <?php if ($c_title !== '') : ?>
                                        <div class="section_header">
                                            <h2 class="section_title"><?php echo esc_html($c_title); ?></h2>
                                        </div>
                                    <?php endif; ?>
                                    <div class="page-content_certificate">
                                        <?php if (!empty($c_content)) : ?>
                                            <div class="page-content_certificate-text">
                                                <?php echo wp_kses_post($c_content); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($c_files)) : ?>
                                            <div class="page-content_certificate-options">
                                                <?php foreach ($c_files as $frow) : ?>
                                                    <?php
                                                    $flabel = isset($frow['label']) ? (string) $frow['label'] : '';
                                                    $furl = isset($frow['file']) ? (string) $frow['file'] : '';
                                                    if ($flabel === '' || $furl === '') {
                                                        continue;
                                                    }
                                                    ?>
                                                    <a class="button button--icon button--sm" href="<?php echo esc_url($furl); ?>" download>
                                                        <div class="button_icon button_icon--download" aria-hidden="true"></div>
                                                        <span class="button_text"><?php echo esc_html($flabel); ?></span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($preview_url !== '') : ?>
                                        <div class="page-content_certificate-img" data-fancybox="preview-service-<?php the_ID(); ?>" data-src="<?php echo esc_url($preview_url); ?>">
                                            <img src="<?php echo esc_url($preview_url); ?>" alt="<?php echo esc_attr($c_title ?: get_the_title()); ?>" loading="lazy" />
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($schedule) && is_array($schedule)) : ?>
                            <div class="page-content_section">
                                <div class="section_header">
                                    <h2 class="section_title">Расписание занятий</h2>
                                </div>
                                <div class="page-content_schedule">
                                    <?php foreach ($schedule as $srow) : ?>
                                        <?php
                                        $slabel = isset($srow['label']) ? (string) $srow['label'] : '';
                                        $sfile = isset($srow['file']) ? (string) $srow['file'] : '';
                                        if ($slabel === '' || $sfile === '') {
                                            continue;
                                        }
                                        ?>
                                        <a class="button button--icon button--sm" href="<?php echo esc_url($sfile); ?>" download>
                                            <div class="button_icon button_icon--download" aria-hidden="true"></div>
                                            <span class="button_text"><?php echo esc_html($slabel); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($toc_items) && is_array($toc_items)) : ?>
                        <div class="page-content_sidebar">
                            <div class="widget">
                                <div class="widget_title">Содержание:</div>
                                <ul class="toc-list" id="toc-list-service">
                                    <?php foreach ($toc_items as $trow) : ?>
                                        <?php
                                        $tl = isset($trow['label']) ? (string) $trow['label'] : '';
                                        $ta = isset($trow['anchor']) ? (string) $trow['anchor'] : '';
                                        if ($tl === '' || $ta === '') {
                                            continue;
                                        }
                                        $href = '#' . ltrim($ta, '#');
                                        ?>
                                        <li><a href="<?php echo esc_url($href); ?>"><?php echo esc_html($tl); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
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
            <section class="section section--services" id="other-services">
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
                                get_template_part('template-parts/post-card', 'service');
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
