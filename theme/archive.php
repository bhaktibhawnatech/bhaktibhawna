<?php
/**
 * Archive — category, tag, author, date
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>

<section class="bb-section">
    <div class="bb-container">
        <header class="bb-section-head">
            <span class="bb-eyebrow"><?php
                if ( is_category() ) bb_t( array( 'en' => 'Category', 'hi' => 'श्रेणी', 'mr' => 'श्रेणी', 'gu' => 'શ્રેણી' ) );
                elseif ( is_tag() ) bb_t( array( 'en' => 'Tag', 'hi' => 'टैग', 'mr' => 'टॅग', 'gu' => 'ટૅગ' ) );
                elseif ( is_author() ) bb_t( array( 'en' => 'Author', 'hi' => 'लेखक', 'mr' => 'लेखक', 'gu' => 'લેખક' ) );
                else esc_html_e( 'Archive', 'bhaktibhawna' );
            ?></span>
            <h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
            <span class="bb-ornament"></span>
            <?php if ( get_the_archive_description() ) : ?>
                <div class="bb-lede bb-mx-auto"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : ?>
            <div class="bb-posts__grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/card', 'post' ); ?>
                <?php endwhile; ?>
            </div>
            <div style="margin-top: var(--bb-sp-5); text-align:center;">
                <?php the_posts_pagination( array(
                    'mid_size'  => 1,
                    'prev_text' => '←',
                    'next_text' => '→',
                ) ); ?>
            </div>
        <?php else : ?>
            <p style="text-align:center; color: var(--bb-muted);"><?php esc_html_e( 'No posts found.', 'bhaktibhawna' ); ?></p>
        <?php endif; ?>
    </div>
</section>

<style>
.nav-links { display:inline-flex; gap: 0.5rem; flex-wrap:wrap; }
.page-numbers {
    display: inline-flex; align-items:center; justify-content:center;
    min-width: 40px; height: 40px; padding: 0 0.75rem;
    border: 1px solid var(--bb-line); border-radius: var(--bb-r-sm);
    background: #fff; color: var(--bb-charcoal);
}
.page-numbers:hover { border-color: var(--bb-saffron); color: var(--bb-saffron-dark); }
.page-numbers.current { background: var(--bb-saffron); color: #fff; border-color: var(--bb-saffron); }
</style>

<?php get_footer(); ?>
