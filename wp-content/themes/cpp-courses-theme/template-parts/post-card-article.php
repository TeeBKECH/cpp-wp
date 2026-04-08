<?php
/**
 * Article post card.
 *
 * @package CppCoursesTheme
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<article <?php post_class('blog_card'); ?>>
    <a class="blog_card_img-wrap" href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('medium_large', array('class' => 'blog_card_img', 'loading' => 'lazy')); ?>
        <?php else : ?>
            <img
                class="blog_card_img"
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/post-1.png'); ?>"
                alt="<?php the_title_attribute(); ?>"
                loading="lazy"
            />
        <?php endif; ?>
    </a>
    <div class="blog_card_body">
        <time class="blog_card_date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
            <?php echo esc_html(get_the_date('j F Y')); ?>
        </time>
        <h3 class="blog_card_title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        <p class="blog_card_desc">
            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?>
        </p>
        <a class="blog_card_link wave-link" href="<?php the_permalink(); ?>">Читать далее</a>
    </div>
</article>
