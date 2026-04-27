<?php
/**
 * Fallback template — when no more specific template applies.
 * WP will use archive.php / home.php first; this catches everything else.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>

<section class="bb-section">
    <div class="bb-container">
        <?php if ( have_posts() ) : ?>
            <div class="bb-section-head">
                <h1><?php echo is_home() ? esc_html__( 'Blog', 'bhaktibhawna' ) : wp_get_document_title(); ?></h1>
                <span class="bb-ornament"></span>
            </div>
            <div class="bb-posts__grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/card', 'post' ); ?>
                <?php endwhile; ?>
            </div>
            <div style="margin-top: var(--bb-sp-5); text-align:center;">
                <?php the_posts_pagination( array(
                    'mid_size'  => 1,
                    'prev_text' => '← ' . __( 'Previous', 'bhaktibhawna' ),
                    'next_text' => __( 'Next', 'bhaktibhawna' ) . ' →',
                ) ); ?>
            </div>
        <?php else : ?>
            <div class="bb-section-head">
                <h1><?php esc_html_e( 'Nothing found', 'bhaktibhawna' ); ?></h1>
                <p><?php esc_html_e( 'Try searching or browse the categories.', 'bhaktibhawna' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
