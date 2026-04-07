<?php
/**
 * Shared modals (mobile menu / big menu).
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Modal Mobile Menu-->
<div class="modal modal-top modal--mobile" id="mobile-menu">
    <div class="modal_overlay" data-close=""></div>
    <div class="modal_content">
        <nav class="nav nav--mobile">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'menu--mobile',
                    'fallback_cb'    => false,
                )
            );
            ?>
            <div class="nav_contacts">
                <div class="footer_contacts">
                    <div class="footer_contacts-wrap">
                        <p class="footer_contacts-label"><?php echo esc_html(cpp_courses_get_option('cpp_address', '')); ?></p>
                        <a class="footer_contacts-value wave-link" href="<?php echo esc_url(home_url('/contacts/#contacts')); ?>">
                            Как добраться?
                        </a>
                    </div>
                    <div class="footer_contacts-wrap">
                        <a class="footer_contacts-label" href="tel:<?php echo esc_attr(cpp_courses_get_option('cpp_phone_tel', '')); ?>">
                            <?php echo esc_html(cpp_courses_get_option('cpp_phone_display', '')); ?>
                        </a>
                        <p class="footer_contacts-value"><?php echo esc_html(cpp_courses_get_option('cpp_work_hours', '')); ?></p>
                    </div>
                    <div class="footer_contacts-wrap">
                        <a class="footer_contacts-label" href="mailto:<?php echo esc_attr(cpp_courses_get_option('cpp_email', '')); ?>">
                            <?php echo esc_html(cpp_courses_get_option('cpp_email', '')); ?>
                        </a>
                        <a class="footer_contacts-value wave-link" href="mailto:<?php echo esc_attr(cpp_courses_get_option('cpp_email', '')); ?>">
                            Написать письмо
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>

<!-- Modal Big Menu-->
<div class="modal modal-top modal--menu" id="big-menu">
    <div class="modal_overlay" data-close=""></div>
    <div class="modal_content">
        <div class="menu menu--big">
            <div class="container">
                <div class="menu_inner">
                    <!-- TODO: Вынести пункты big menu в админку/SCF, пока заглушка -->
                    <div class="menu_col">
                        <p class="menu_col-title">Меню</p>
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'primary',
                                'container'      => false,
                                'menu_class'     => 'menu_col-list',
                                'fallback_cb'    => false,
                            )
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

