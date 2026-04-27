<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<section class="bb-section">
    <div class="bb-container">
        <header class="bb-section-head">
            <span class="bb-eyebrow"><?php esc_html_e( 'Search', 'bhaktibhawna' ); ?></span>
            <h1><?php printf( esc_html__( 'Results for: %s', 'bhaktibhawna' ), '<em>' . get_search_query() . '</em>' ); ?></h1>
            <span class="bb-ornament"></span>
        </header>

        <?php if ( have_posts() ) : ?>
            <div class="bb-posts__grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/card', 'post' ); ?>
                <?php endwhile; ?>
            </div>
            <div style="margin-top: var(--bb-sp-5); text-align:center;">
                <?php the_posts_pagination(); ?>
            </div>
        <?php else : ?>
            <p style="text-align:center;"><?php esc_html_e( 'Nothing found. Try another search.', 'bhaktibhawna' ); ?></p>
            <div style="max-width: 480px; margin: var(--bb-sp-3) auto 0;">
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
