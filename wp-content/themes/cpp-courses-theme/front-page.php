<?php
/**
 * Front page: full home layout from SCF (group_cpp_home on static front page).
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$front_id = cpp_home_front_page_id();
if ($front_id < 1) {
    ?>
    <main class="main main--index">
        <section class="section section--page-intro">
            <div class="container">
                <div class="page-intro">
                    <h1 class="page-intro_title"><?php bloginfo('name'); ?></h1>
                    <p class="page-intro_desc"><?php esc_html_e('Назначьте статическую главную в Настройки → Чтение.', 'cpp-courses-theme'); ?></p>
                </div>
            </div>
        </section>
    </main>
    <?php
    get_footer();
    return;
}

$lead_modal_cf7 = cpp_courses_get_modal_cf7_form_id();

// --- Fields (option '' with post id for front page).
$h1 = function_exists('get_field') ? (string) get_field('main_intro_title', $front_id) : '';
if ($h1 === '') {
    $h1 = get_the_title($front_id);
}
$intro_label = function_exists('get_field') ? trim((string) get_field('main_intro_label', $front_id)) : '';
$intro_text = function_exists('get_field') ? (string) get_field('main_intro_text', $front_id) : '';
$intro_btn = function_exists('get_field') ? get_field('main_intro_button', $front_id) : null;
$intro_video = function_exists('get_field') ? get_field('main_intro_bg_video', $front_id) : null;
$intro_img = function_exists('get_field') ? get_field('main_intro_bg_image', $front_id) : null;
$video_url = is_array($intro_video) && !empty($intro_video['url']) ? (string) $intro_video['url'] : '';
$img_url = cpp_courses_image_field_url($intro_img);

$courses_title = function_exists('get_field') ? (string) get_field('main_courses_title', $front_id) : 'Курсы';
$courses_sub = function_exists('get_field') ? (string) get_field('main_courses_subtitle', $front_id) : '';
$courses_ids = function_exists('get_field') ? get_field('main_courses_items', $front_id) : null;
$courses_posts = cpp_home_get_posts_for_section('services', is_array($courses_ids) ? $courses_ids : null, 9);

$about_title = function_exists('get_field') ? (string) get_field('main_about_title', $front_id) : 'О нас';
$about_html = function_exists('get_field') ? (string) get_field('main_about_content', $front_id) : '';
$about_img = cpp_courses_image_field_url(function_exists('get_field') ? get_field('main_about_image', $front_id) : null);
$about_more = function_exists('get_field') ? get_field('main_about_more', $front_id) : null;

$teach_title = function_exists('get_field') ? (string) get_field('main_teachers_title', $front_id) : '';
$teach_sub = function_exists('get_field') ? (string) get_field('main_teachers_subtitle', $front_id) : '';
$teach_ids = function_exists('get_field') ? get_field('main_teachers_items', $front_id) : null;
$teach_posts = cpp_home_get_posts_for_section('teachers', is_array($teach_ids) ? $teach_ids : null, 4);

$edu_title = function_exists('get_field') ? (string) get_field('main_edu_seo_title', $front_id) : '';
$edu_sub = function_exists('get_field') ? (string) get_field('main_edu_seo_subtitle', $front_id) : '';
$edu_img = cpp_courses_image_field_url(function_exists('get_field') ? get_field('main_edu_seo_image', $front_id) : null);
$edu_content = function_exists('get_field') ? (string) get_field('main_edu_seo_content', $front_id) : '';

$orders_title = function_exists('get_field') ? (string) get_field('main_orders_title', $front_id) : '';
$orders_ids = function_exists('get_field') ? get_field('main_orders_items', $front_id) : null;
$orders_posts = array();
if (is_array($orders_ids) && !empty($orders_ids)) {
    $orders_posts = cpp_home_get_posts_for_section('education', $orders_ids, count($orders_ids));
}

$blog_title = function_exists('get_field') ? (string) get_field('main_blog_title', $front_id) : '';
$blog_sub = function_exists('get_field') ? (string) get_field('main_blog_subtitle', $front_id) : '';
$blog_ids = function_exists('get_field') ? get_field('main_blog_items', $front_id) : null;
$blog_posts = cpp_home_get_posts_for_section('articles', is_array($blog_ids) ? $blog_ids : null, 6);
$blog_more = function_exists('get_field') ? get_field('main_blog_more', $front_id) : null;
$blog_more_url = is_array($blog_more) && !empty($blog_more['url']) ? (string) $blog_more['url'] : '';
if ($blog_more_url === '') {
    $blog_more_url = get_post_type_archive_link('articles') ?: home_url('/articles/');
}
$blog_more_text = is_array($blog_more) && !empty($blog_more['title']) ? (string) $blog_more['title'] : __('Все публикации', 'cpp-courses-theme');
$blog_more_target = is_array($blog_more) && !empty($blog_more['target']) ? (string) $blog_more['target'] : '_self';

$vac_title = function_exists('get_field') ? (string) get_field('main_vacancy_title', $front_id) : '';
$vac_sub = function_exists('get_field') ? (string) get_field('main_vacancy_subtitle', $front_id) : '';
$vac_ids = function_exists('get_field') ? get_field('main_vacancy_items', $front_id) : null;
$vac_posts = cpp_home_get_posts_for_section('vacancy', is_array($vac_ids) ? $vac_ids : null, 12);

$seo_title = function_exists('get_field') ? (string) get_field('main_cpp_seo_title', $front_id) : '';
$seo_content = function_exists('get_field') ? (string) get_field('main_cpp_seo_content', $front_id) : '';

$part_title = function_exists('get_field') ? (string) get_field('main_partners_title', $front_id) : '';
$part_sub = function_exists('get_field') ? (string) get_field('main_partners_subtitle', $front_id) : '';
$partners = function_exists('get_field') ? get_field('partners_items', $front_id) : null;
?>
<main class="main main--index">
    <section class="section section--intro">
        <div class="intro">
            <div class="intro_bg">
                <?php if ($video_url !== '') : ?>
                    <video class="intro_bg_video" autoplay muted loop playsinline>
                        <source src="<?php echo esc_url($video_url); ?>" />
                    </video>
                <?php else : ?>
                    <div class="intro_bg_image"<?php echo $img_url !== '' ? ' style="' . esc_attr('background-image:url(' . esc_url($img_url) . ')') . '"' : ''; ?>></div>
                <?php endif; ?>
                <div class="intro_overlay"></div>
            </div>
            <div class="container">
                <div class="intro_content">
                    <?php if ($intro_label !== '') : ?>
                        <p class="intro_label"><?php echo esc_html($intro_label); ?></p>
                    <?php endif; ?>
                    <h1 class="intro_title"><?php echo esc_html($h1); ?></h1>
                    <?php if ($intro_text !== '') : ?>
                        <div class="intro_text"><?php echo wp_kses_post(wpautop($intro_text)); ?></div>
                    <?php endif; ?>
                    <div class="intro_actions">
                        <?php
                        $btn_url = is_array($intro_btn) && !empty($intro_btn['url']) ? trim((string) $intro_btn['url']) : '';
                        $btn_title = is_array($intro_btn) && !empty($intro_btn['title']) ? (string) $intro_btn['title'] : __('Наши курсы', 'cpp-courses-theme');
                        $btn_target = is_array($intro_btn) && !empty($intro_btn['target']) ? (string) $intro_btn['target'] : '_self';
                        if ($btn_url !== '') :
                            ?>
                            <a class="button button--primary button--lg" href="<?php echo esc_url($btn_url); ?>" target="<?php echo esc_attr($btn_target); ?>">
                                <span class="button_text"><?php echo esc_html($btn_title); ?></span>
                            </a>
                        <?php elseif ($lead_modal_cf7 > 0) : ?>
                            <a
                                class="button button--primary button--lg"
                                href="#"
                                data-fancybox="cpp-lead"
                                data-src="#cpp-lead-fancy-inline"
                                data-type="inline"
                            >
                                <span class="button_text"><?php echo esc_html($btn_title); ?></span>
                            </a>
                        <?php else : ?>
                            <a class="button button--primary button--lg" href="<?php echo esc_url(home_url('/#courses')); ?>">
                                <span class="button_text"><?php echo esc_html($btn_title); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($courses_posts)) : ?>
        <section class="section section--courses" id="courses">
            <div class="container container--courses">
                <div class="courses section_content">
                    <div class="section_header">
                        <h2 class="section_title"><?php echo esc_html($courses_title); ?></h2>
                        <?php if ($courses_sub !== '') : ?>
                            <p class="section_subtitle"><?php echo esc_html($courses_sub); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="courses_grid courses_grid--courses">
                        <?php
                        foreach ($courses_posts as $p) {
                            $GLOBALS['post'] = $p;
                            setup_postdata($p);
                            get_template_part('template-parts/post-card', 'service');
                        }
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($about_html !== '' || $about_img !== '') : ?>
        <section class="section section--about" id="about">
            <div class="container">
                <div class="about section_content">
                    <div class="section_header">
                        <h2 class="section_title"><?php echo esc_html($about_title); ?></h2>
                    </div>
                    <div class="about_inner">
                        <?php if ($about_html !== '') : ?>
                            <div class="about_text entry-content"><?php echo apply_filters('the_content', $about_html); ?></div>
                        <?php endif; ?>
                        <?php if ($about_img !== '') : ?>
                            <div class="about_img-wrap">
                                <img src="<?php echo esc_url($about_img); ?>" alt="<?php echo esc_attr($about_title); ?>" width="760" height="480" loading="lazy" />
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                    $more_url = is_array($about_more) && !empty($about_more['url']) ? (string) $about_more['url'] : '';
                    $more_text = is_array($about_more) && !empty($about_more['title']) ? (string) $about_more['title'] : __('Подробнее', 'cpp-courses-theme');
                    $more_target = is_array($about_more) && !empty($about_more['target']) ? (string) $about_more['target'] : '_self';
                    if ($more_url !== '') :
                        ?>
                        <div class="section_footer">
                            <a class="button button--filled button--md" href="<?php echo esc_url($more_url); ?>" target="<?php echo esc_attr($more_target); ?>">
                                <span class="button_text"><?php echo esc_html($more_text); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($teach_posts)) : ?>
        <section class="section section--teachers" id="teachers">
            <div class="container container--teachers">
                <div class="teachers section_content">
                    <div class="section_header">
                        <h2 class="section_title"><?php echo esc_html($teach_title); ?></h2>
                        <?php if ($teach_sub !== '') : ?>
                            <p class="section_subtitle"><?php echo esc_html($teach_sub); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="teachers_grid teachers_grid--teachers">
                        <?php
                        foreach ($teach_posts as $p) {
                            get_template_part('template-parts/card', 'teacher', array('post' => $p));
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($edu_content !== '' || $edu_img !== '') : ?>
        <section class="section section--education" id="education">
            <div class="container">
                <div class="education section_content">
                    <div class="section_header">
                        <h2 class="section_title"><?php echo esc_html($edu_title); ?></h2>
                        <?php if ($edu_sub !== '') : ?>
                            <p class="section_subtitle"><?php echo esc_html($edu_sub); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="education_inner">
                        <?php if ($edu_img !== '') : ?>
                            <div class="education_img-wrap">
                                <img src="<?php echo esc_url($edu_img); ?>" alt="" width="360" height="320" loading="lazy" />
                            </div>
                        <?php endif; ?>
                        <?php if ($edu_content !== '') : ?>
                            <div class="education_text entry-content"><?php echo apply_filters('the_content', $edu_content); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/section', 'cta-block'); ?>

    <?php if (!empty($orders_posts) && $orders_title !== '') : ?>
        <section class="section section--orders" id="orders">
            <div class="container">
                <div class="orders section_content">
                    <div class="section_header">
                        <h2 class="section_title"><?php echo esc_html($orders_title); ?></h2>
                    </div>
                    <div class="orders_list">
                        <?php
                        foreach ($orders_posts as $p) {
                            get_template_part('template-parts/home', 'education-order', array('post' => $p));
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($blog_posts)) : ?>
        <section class="section section--blog" id="blog">
            <div class="container container--blog">
                <div class="blog section_content">
                    <div class="section_header section_header--blog">
                        <h2 class="section_title"><?php echo esc_html($blog_title); ?></h2>
                        <?php if ($blog_sub !== '') : ?>
                            <p class="section_subtitle"><?php echo esc_html($blog_sub); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="blog_grid blog_grid--swiper">
                        <?php
                        foreach ($blog_posts as $p) {
                            $GLOBALS['post'] = $p;
                            setup_postdata($p);
                            get_template_part('template-parts/post-card', 'article');
                        }
                        wp_reset_postdata();
                        ?>
                    </div>
                    <?php if ($blog_more_url) : ?>
                        <div class="section_footer section_footer--blog">
                            <a class="button button--filled button--md" href="<?php echo esc_url($blog_more_url); ?>" target="<?php echo esc_attr($blog_more_target); ?>">
                                <span class="button_text"><?php echo esc_html($blog_more_text); ?></span>
                            </a>
                            <nav class="swiper_navigation swiper_navigation--blog" aria-hidden="true">
                                <button class="swiper_navigation-btn swiper_navigation-btn--prev" type="button" aria-label="<?php esc_attr_e('Назад', 'cpp-courses-theme'); ?>"></button>
                                <div class="swiper_navigation-pages"></div>
                                <button class="swiper_navigation-btn swiper_navigation-btn--next" type="button" aria-label="<?php esc_attr_e('Вперёд', 'cpp-courses-theme'); ?>"></button>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($vac_posts)) : ?>
        <section class="section section--vacancies" id="vacancies">
            <div class="container container--vacancies">
                <div class="vacancies section_content">
                    <div class="section_header">
                        <h2 class="section_title"><?php echo esc_html($vac_title); ?></h2>
                        <?php if ($vac_sub !== '') : ?>
                            <p class="section_subtitle"><?php echo esc_html($vac_sub); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="vacancies_grid vacancies_grid--swiper">
                        <?php
                        foreach ($vac_posts as $p) {
                            get_template_part('template-parts/card', 'vacancy', array('post' => $p));
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($seo_content !== '') : ?>
        <section class="section section--seo-text">
            <div class="container">
                <div class="seo-text section_content">
                    <?php if ($seo_title !== '') : ?>
                        <div class="section_header">
                            <h2 class="section_title"><?php echo esc_html($seo_title); ?></h2>
                        </div>
                    <?php endif; ?>
                    <div class="seo-text_content entry-content"><?php echo apply_filters('the_content', $seo_content); ?></div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($partners) && !empty($partners)) : ?>
        <section class="section section--partners">
            <div class="container">
                <div class="partners section_content">
                    <?php if ($part_title !== '' || $part_sub !== '') : ?>
                        <div class="section_header">
                            <?php if ($part_title !== '') : ?>
                                <h2 class="section_title"><?php echo esc_html($part_title); ?></h2>
                            <?php endif; ?>
                            <?php if ($part_sub !== '') : ?>
                                <p class="section_subtitle"><?php echo esc_html($part_sub); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="partners_line" aria-label="<?php esc_attr_e('Логотипы партнёров', 'cpp-courses-theme'); ?>">
                        <div class="partners_line-inner">
                            <div class="partners_track">
                                <?php
                                foreach ($partners as $row) {
                                    $pname = isset($row['partner_name']) ? (string) $row['partner_name'] : '';
                                    $logo = isset($row['partner_logo']) ? $row['partner_logo'] : null;
                                    $logo_url = cpp_courses_image_field_url($logo);
                                    if ($logo_url === '') {
                                        continue;
                                    }
                                    ?>
                                    <div class="partners_card">
                                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($pname); ?>" loading="lazy" />
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php
get_footer();
