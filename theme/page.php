<?php
/**
 * Generic page template.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>

<article class="bb-section">
    <div class="bb-container" style="max-width: 820px;">
        <?php while ( have_posts() ) : the_post(); ?>
            <header style="text-align:center; margin-bottom: var(--bb-sp-4);">
                <h1><?php the_title(); ?></h1>
                <span class="bb-ornament"></span>
            </header>

            <div class="bb-entry-content">
                <?php the_content(); ?>
            </div>

            <?php wp_link_pages( array(
                'before' => '<nav style="margin-top: var(--bb-sp-4);">',
                'after'  => '</nav>',
            ) ); ?>
        <?php endwhile; ?>
    </div>
</article>

<style>
    .bb-entry-content { font-size: var(--bb-fs-lg); line-height: 1.85; }
    .bb-entry-content h2 { margin-top: var(--bb-sp-5); }
    .bb-entry-content h3 { margin-top: var(--bb-sp-4); }
    .bb-entry-content img, .bb-entry-content figure { border-radius: var(--bb-r); margin: var(--bb-sp-3) 0; }
    .bb-entry-content ul, .bb-entry-content ol { padding-left: 1.25rem; margin-bottom: var(--bb-sp-2); }
    .bb-entry-content blockquote {
        margin: var(--bb-sp-3) 0;
        padding: var(--bb-sp-2) var(--bb-sp-3);
        border-left: 4px solid var(--bb-saffron);
        background: var(--bb-cream-warm);
        border-radius: 0 var(--bb-r-sm) var(--bb-r-sm) 0;
        font-style: italic;
    }
    .bb-entry-content a { color: var(--bb-saffron-dark); border-bottom: 1px solid currentColor; }
</style>

<?php get_footer(); ?>
