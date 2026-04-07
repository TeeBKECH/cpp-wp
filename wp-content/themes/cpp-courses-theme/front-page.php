<?php
/**
 * Front page template.
 *
 * @package CppCoursesTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main class="page page--index">
    <section class="section section--content">
        <div class="container">
            <h1>Тема подключена</h1>
            <p>Каркас WordPress темы создан. Следующим шагом переносим блоки из Vite/Pug шаблонов.</p>
        </div>
    </section>
</main>
<?php
get_footer();
