<?php
/**
 * Single post — bhakti article template
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header(); ?>

<article class="bb-section bb-single">
    <div class="bb-container" style="max-width: 820px;">
        <?php while ( have_posts() ) : the_post();
            $cats = get_the_category();
            $cat  = $cats ? $cats[0] : null;
        ?>

            <header class="bb-single__head">
                <?php if ( $cat ) : ?>
                    <a class="bb-eyebrow" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
                <?php endif; ?>
                <h1 class="bb-single__title"><?php the_title(); ?></h1>
                <span class="bb-ornament"></span>
                <div class="bb-single__meta">
                    <span>🕉 <?php echo bb_reading_time(); ?> <?php bb_t( array( 'en' => 'min read', 'hi' => 'मिनट पढ़ें', 'mr' => 'मिनिट वाचा', 'gu' => 'મિનિટ વાંચો' ) ); ?></span>
                    <span>·</span>
                    <span><?php echo esc_html( get_the_date() ); ?></span>
                </div>

                <?php /* Article schema for rich results */ ?>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Article",
                    "headline": <?php echo wp_json_encode( get_the_title() ); ?>,
                    "datePublished": "<?php echo esc_attr( get_the_date( 'c' ) ); ?>",
                    "dateModified": "<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>",
                    "author": { "@type": "Person", "name": <?php echo wp_json_encode( get_the_author() ); ?> },
                    "publisher": {
                        "@type": "Organization",
                        "name": <?php echo wp_json_encode( get_bloginfo( 'name' ) ); ?>,
                        "logo": { "@type": "ImageObject", "url": "<?php echo esc_url( BB_URI . '/assets/img/logo.png' ); ?>" }
                    },
                    "mainEntityOfPage": "<?php the_permalink(); ?>"<?php if ( has_post_thumbnail() ) : ?>,
                    "image": "<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>"<?php endif; ?>
                }
                </script>
            </header>

            <?php if ( has_post_thumbnail() ) : ?>
                <figure class="bb-single__hero">
                    <?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
                </figure>
            <?php endif; ?>

            <?php /* Ad slot 1 — after hero, before content. Reader is engaged. */ ?>
            <?php bb_ad_slot( BB_AD_SLOT_TOP ); ?>

            <div class="bb-entry-content">
                <?php /* Middle ad auto-injected via the_content filter after 3rd paragraph */ ?>
                <?php the_content(); ?>
            </div>

            <?php /* Ad slot 3 — end of content, before related posts. */ ?>
            <?php bb_ad_slot( BB_AD_SLOT_BOTTOM ); ?>

            <?php wp_link_pages(); ?>

            <?php if ( has_tag() ) : ?>
                <div class="bb-single__tags">
                    <?php the_tags( '<span>🏷</span> ', ' ', '' ); ?>
                </div>
            <?php endif; ?>

        <?php endwhile; ?>
    </div>
</article>

<section class="bb-section bb-section--tight" style="background: var(--bb-ivory);">
    <div class="bb-container">
        <div class="bb-section-head">
            <h2 style="font-size: var(--bb-fs-xl);"><?php bb_t( array( 'en' => 'You may also like', 'hi' => 'आप इन्हें भी पसंद कर सकते हैं', 'mr' => 'तुम्हाला हे देखील आवडू शकते', 'gu' => 'તમને આ પણ ગમી શકે' ) ); ?></h2>
            <span class="bb-ornament"></span>
        </div>
        <?php
        $related = new WP_Query( array(
            'post_type'      => 'post',
            'posts_per_page' => 3,
            'post__not_in'   => array( get_the_ID() ),
            'category__in'   => wp_list_pluck( get_the_category(), 'term_id' ),
            'orderby'        => 'rand',
        ) );
        if ( $related->have_posts() ) : ?>
            <div class="bb-posts__grid">
                <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                    <?php get_template_part( 'template-parts/card', 'post' ); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.bb-single__head { text-align: center; margin-bottom: var(--bb-sp-4); }
.bb-single__title {
    font-size: clamp(1.75rem, 1.4rem + 1.75vw, 2.75rem);
    line-height: 1.2;
    margin: var(--bb-sp-2) 0;
}
.bb-single__meta {
    display: flex; gap: 0.5rem; align-items: center; justify-content: center;
    font-size: var(--bb-fs-sm); color: var(--bb-muted);
    margin-top: var(--bb-sp-2);
}
.bb-single__hero {
    margin: 0 0 var(--bb-sp-4);
    border-radius: var(--bb-r-lg);
    overflow: hidden;
    box-shadow: var(--bb-shadow);
}
.bb-single__hero img { width: 100%; height: auto; }
.bb-entry-content { font-size: var(--bb-fs-lg); line-height: 1.85; color: var(--bb-charcoal-soft); }
.bb-entry-content p { margin-bottom: var(--bb-sp-3); }
.bb-entry-content h2 { margin-top: var(--bb-sp-5); color: var(--bb-charcoal); }
.bb-entry-content h3 { margin-top: var(--bb-sp-4); color: var(--bb-charcoal); }
.bb-entry-content img, .bb-entry-content figure { border-radius: var(--bb-r); margin: var(--bb-sp-3) 0; box-shadow: var(--bb-shadow-sm); }
.bb-entry-content blockquote {
    margin: var(--bb-sp-3) 0;
    padding: var(--bb-sp-3);
    border-left: 4px solid var(--bb-saffron);
    background: var(--bb-cream-warm);
    border-radius: 0 var(--bb-r) var(--bb-r) 0;
    font-family: var(--bb-font-devanagari);
    font-size: var(--bb-fs-lg);
    color: var(--bb-maroon);
}
.bb-single__tags {
    margin-top: var(--bb-sp-4);
    padding-top: var(--bb-sp-3);
    border-top: 1px dashed var(--bb-line);
    font-size: var(--bb-fs-sm);
    color: var(--bb-muted);
}
.bb-single__tags a {
    display: inline-block;
    padding: 0.2rem 0.7rem;
    margin: 0.2rem;
    background: var(--bb-cream-warm);
    border-radius: var(--bb-r-pill);
}
/* Ad slots — neutral, labeled, never overlap content */
.bb-ad-slot {
    margin: var(--bb-sp-4) 0;
    padding: var(--bb-sp-2);
    background: rgba(0,0,0,0.02);
    border: 1px dashed var(--bb-line);
    border-radius: var(--bb-r-sm);
    text-align: center;
    min-height: 100px;
}
.bb-ad-slot__label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--bb-muted);
    margin-bottom: 0.5rem;
}
</style>

<?php get_footer(); ?>
