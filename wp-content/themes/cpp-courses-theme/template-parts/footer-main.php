<?php
/**
 * Main footer content.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

$logo_icon = cpp_courses_image_field_url(cpp_courses_get_option('cpp_logo_icon', ''));
$title_full = cpp_courses_get_option('cpp_site_title_full', 'Центр Профессиональной Подготовки');
$email = cpp_courses_get_option('cpp_email', '');
$phone_display = cpp_courses_get_option('cpp_phone_display', '');
$phone_tel = cpp_courses_get_option('cpp_phone_tel', '');
$address = cpp_courses_get_option('cpp_address', '');
$work_hours = cpp_courses_get_option('cpp_work_hours', '');
?>
<footer class="footer">
    <div class="container">
        <div class="footer_inner">
            <div class="footer_col footer_col--logo">
                <div class="footer_logo">
                    <?php if ($logo_icon) : ?>
                        <img class="footer_logo-icon" src="<?php echo esc_url($logo_icon); ?>" alt="<?php echo esc_attr($title_full); ?>" width="121" height="44" />
                    <?php endif; ?>
                    <span class="footer_logo-text"><?php echo esc_html($title_full); ?></span>
                </div>
                <p class="footer_copy">©<?php echo esc_html(date_i18n('Y')); ?> - <?php echo esc_html($title_full); ?></p>
                <ul class="footer_legal">
                    <li><a class="footer_link" href="<?php echo esc_url(home_url('/edu-info/')); ?>">Сведения об образовательной организации</a></li>
                    <li><a class="footer_link" href="#">Политика конфиденциальности</a></li>
                </ul>
            </div>

            <div class="footer_col footer_col--nav">
                <h3 class="footer_title">Курсы</h3>
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'footer',
                        'container'      => false,
                        'menu_class'     => 'footer_links',
                        'fallback_cb'    => 'cpp_courses_footer_menu_fallback',
                    )
                );
                ?>
            </div>

            <div class="footer_col footer_col--contacts">
                <h3 class="footer_title">Контакты</h3>
                <div class="footer_contacts">
                    <div class="footer_contacts-wrap">
                        <p class="footer_contacts-label"><?php echo esc_html($address); ?></p>
                        <a class="footer_contacts-value wave-link" href="<?php echo esc_url(home_url('/contacts/#contacts')); ?>">Как добраться?</a>
                    </div>
                    <div class="footer_contacts-wrap">
                        <a class="footer_contacts-label" href="tel:<?php echo esc_attr($phone_tel); ?>"><?php echo esc_html($phone_display); ?></a>
                        <p class="footer_contacts-value"><?php echo esc_html($work_hours); ?></p>
                    </div>
                    <div class="footer_contacts-wrap">
                        <a class="footer_contacts-label" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                        <a class="footer_contacts-value wave-link" href="mailto:<?php echo esc_attr($email); ?>">Написать письмо</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
