<?php
/**
 * Theme header.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $favicon = cpp_courses_image_field_url(cpp_courses_get_option('cpp_favicon', ''));
    if ($favicon) {
        printf('<link rel="icon" href="%s" sizes="32x32" />', esc_url($favicon));
    }
    ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class('body'); ?>>
<?php wp_body_open(); ?>

<?php get_template_part('template-parts/modals'); ?>
<?php get_template_part('template-parts/bottom-nav'); ?>
<?php get_template_part('template-parts/scroll-to-top'); ?>

<?php
$page_class = 'default';
if (is_front_page()) {
    $page_class = 'index';
} elseif (is_page_template('page-quiz.php')) {
    $page_class = 'test-intro';
} elseif (is_page()) {
    $page_class = get_post_field('post_name', get_post());
} elseif (is_post_type_archive('services')) {
    $page_class = 'services';
} elseif (is_singular('services')) {
    $page_class = 'service';
} elseif (is_post_type_archive('articles')) {
    $page_class = 'articles';
} elseif (is_singular('articles')) {
    $page_class = 'article';
} elseif (is_post_type_archive('education')) {
    $page_class = 'edu-info';
} elseif (is_singular('education')) {
    $page_class = 'edu-info';
}
?>
<div class="page <?php echo esc_attr('page--' . $page_class); ?>">
  <header class="header">
    <div class="header_top">
      <div class="container container--header">
        <div class="header_inner">
          <?php
          $logo_icon = cpp_courses_image_field_url(cpp_courses_get_option('cpp_logo_icon', ''));
          $title_short = cpp_courses_get_option('cpp_site_title_short', 'ЦПП');
          $title_full = cpp_courses_get_option('cpp_site_title_full', 'Центр Профессиональной Подготовки');
          ?>
          <a class="header_logo" href="<?php echo esc_url(home_url('/')); ?>">
            <?php if ($logo_icon) : ?>
              <img class="header_logo-icon" src="<?php echo esc_url($logo_icon); ?>" alt="<?php echo esc_attr($title_short); ?>" width="121" height="44" />
            <?php endif; ?>
            <span class="header_logo-text"><?php echo wp_kses($title_full, array('br' => array(), 'strong' => array(), 'em' => array(), 'span' => array('class' => array()))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
          </a>

          <div class="header_contacts">
            <div class="header_contact">
              <div class="header_contact-main"><?php echo esc_html(cpp_courses_get_option('cpp_phone_display', '+7 (495) 129-50-41')); ?></div>
              <div class="header_contact-sub"><?php echo esc_html(cpp_courses_get_option('cpp_work_hours', '')); ?></div>
            </div>
            <div class="header_contact">
              <?php $email = cpp_courses_get_option('cpp_email', 'info@cpp-globez.ru'); ?>
              <a class="header_contact-main" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
              <a class="header_contact-sub wave-link" href="mailto:<?php echo esc_attr($email); ?>"><?php esc_html_e('Написать нам', 'cpp-courses-theme'); ?></a>
            </div>
            <div class="header_contact">
              <div class="header_contact-main"><?php echo esc_html(cpp_courses_get_option('cpp_address', '')); ?></div>
              <a class="header_contact-sub wave-link" href="<?php echo esc_url(home_url('/contacts/#contacts')); ?>">Как добраться?</a>
            </div>
          </div>

          <div class="header_mobile">
            <a class="header_mobile-phone" href="tel:<?php echo esc_attr(cpp_courses_phone_to_tel(cpp_courses_get_option('cpp_phone_display', '+7 (495) 129-50-41'))); ?>">
              <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/phone.svg'); ?>" alt="Телефон" />
            </a>
            <div class="header_mobile-menu">
              <button class="burger" type="button" aria-label="Открыть меню" aria-expanded="false" data-modal="mobile-menu">
                <span class="burger_line"></span>
                <span class="burger_line"></span>
                <span class="burger_line"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="header_bottom">
      <div class="container">
        <button class="burger" type="button" aria-label="Открыть меню" aria-expanded="false" data-modal="big-menu">
          <span class="burger_line"></span>
          <span class="burger_line"></span>
          <span class="burger_line"></span>
        </button>
        <?php
        wp_nav_menu(
          array(
            'theme_location' => 'primary',
            'container' => false,
            'menu_class' => 'header_nav',
            'fallback_cb' => false,
            'walker' => class_exists('Cpp_Courses_Header_Nav_Walker') ? new Cpp_Courses_Header_Nav_Walker() : null,
          )
        );
        ?>
      </div>
    </div>
  </header>
