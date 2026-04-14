<?php
/**
 * Template Name: Contacts
 * Template Post Type: page
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$phone_display = cpp_courses_get_option('cpp_phone_display', '+7 (495) 129-50-41');
$phone_tel = cpp_courses_phone_to_tel($phone_display);
$email = cpp_courses_get_option('cpp_email', 'info@cpp-globez.ru');
$address = cpp_courses_get_option('cpp_address', 'г. Мытищи, ул. Новослободская, вл. 1, стр. 1');
$work_hours = cpp_courses_get_option('cpp_work_hours', '');
$weapons_hours = cpp_courses_get_option('cpp_weapons_hours', '');
$map_iframe = cpp_courses_get_option('cpp_map_iframe', '');
$cf7_form_post = cpp_courses_get_option('cpp_cf7_form_post', null);

?>
<main class="main main--contacts">
    <section class="section section--page-intro section--page-intro--alt">
        <div class="container">
            <div class="page-intro">
                <?php get_template_part('template-parts/breadcrumbs'); ?>
                <h1 class="page-intro_title"><?php the_title(); ?></h1>
            </div>
        </div>
    </section>

    <section class="section section--contacts" id="contacts">
        <div class="container">
            <div class="contacts">
                <div class="contacts_inner">
                    <div class="contacts_info">
                        <div class="contacts_row">
                            <a class="contacts_link" href="tel:<?php echo esc_attr($phone_tel); ?>">
                                <?php echo esc_html($phone_display); ?>
                            </a>
                            <?php if (!empty($work_hours)) : ?>
                                <span class="contacts_label"><?php echo esc_html($work_hours); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="contacts_row">
                            <a class="contacts_link" href="mailto:<?php echo esc_attr($email); ?>">
                                <?php echo esc_html($email); ?>
                            </a>
                            <a class="contacts_label wave-link" href="mailto:<?php echo esc_attr($email); ?>">
                                Написать нам
                            </a>
                        </div>
                        <div class="contacts_row">
                            <p class="contacts_link"><?php echo esc_html($address); ?></p>
                            <a class="contacts_label wave-link" href="#contacts">Как добраться?</a>
                        </div>
                        <?php if (!empty($work_hours)) : ?>
                            <div class="contacts_row">
                                <p class="contacts_text">Режим работы:</p>
                                <p class="contacts_value"><?php echo esc_html($work_hours); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($weapons_hours)) : ?>
                            <div class="contacts_row">
                                <p class="contacts_text">Приём на гражданское оружие:</p>
                                <p class="contacts_value"><?php echo esc_html($weapons_hours); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="contacts_map-wrap">
                        <?php
                        if (!empty($map_iframe)) {
                            echo $map_iframe;
                        } else {
                            ?>
                            <iframe
                                src="https://yandex.ru/map-widget/v1/?ll=37.7380%2C55.9150&amp;z=15&amp;lang=ru_RU"
                                title="Карта"
                                width="100%"
                                height="100%"
                            ></iframe>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--cta section--light" id="cta">
        <div class="container">
            <div class="cta section_content section_content--alt">
                <div class="section_header">
                    <h2 class="section_title">Написать нам</h2>
                    <p class="section_subtitle">
                        По телефону или по электронной почте заполните пожалуйста форму обратной связи.
                        Сотрудник центра свяжется с Вами в ближайшее время.
                    </p>
                </div>
                <div class="cta_form">
                    <?php
                    $cf7_id = 0;
                    if (is_array($cf7_form_post) && !empty($cf7_form_post['ID'])) {
                        $cf7_id = (int) $cf7_form_post['ID'];
                    } elseif (is_numeric($cf7_form_post)) {
                        $cf7_id = (int) $cf7_form_post;
                    }
                    if ($cf7_id > 0) {
                        echo cpp_courses_render_cf7_form($cf7_id, 'light'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    } else {
                        ?>
                        <div class="form form--light">
                            <p><?php esc_html_e('Выберите форму Contact Form 7 в опциях сайта: поле «Форма Contact Form 7 (страница контактов)».', 'cpp-courses-theme'); ?></p>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();

