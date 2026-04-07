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

$phone_display = function_exists('get_field') ? get_field('cpp_phone_display', 'option') : '';
$phone_tel = function_exists('get_field') ? get_field('cpp_phone_tel', 'option') : '';
$email = function_exists('get_field') ? get_field('cpp_email', 'option') : '';
$address = function_exists('get_field') ? get_field('cpp_address', 'option') : '';
$work_hours = function_exists('get_field') ? get_field('cpp_work_hours', 'option') : '';
$weapons_hours = function_exists('get_field') ? get_field('cpp_weapons_hours', 'option') : '';
$map_iframe = function_exists('get_field') ? get_field('cpp_map_iframe', 'option') : '';
$cf7_shortcode = function_exists('get_field') ? get_field('cpp_cf7_shortcode', 'option') : '';

if (!$phone_display) $phone_display = '+7 (495) 129-50-41';
if (!$phone_tel) $phone_tel = '+74951295041';
if (!$email) $email = 'info@cpp-globez.ru';
if (!$address) $address = 'г. Мытищи, ул. Новослободская, вл. 1, стр. 1';

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
                    if (!empty($cf7_shortcode)) {
                        echo do_shortcode($cf7_shortcode);
                    } else {
                        ?>
                        <div class="form form--light">
                            <p>Добавь шорткод Contact Form 7 в опции сайта: `cpp_cf7_shortcode`.</p>
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

