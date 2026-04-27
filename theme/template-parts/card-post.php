<?php
/**
 * Post card — reusable in homepage and archives
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$cats = get_the_category();
$cat_name = $cats ? $cats[0]->name : '';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'bb-post-card' ); ?>>
    <a class="bb-post-card__img" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
        <?php if ( has_post_thumbnail() ) {
            the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => get_the_title() ) );
        } ?>
    </a>
    <div class="bb-post-card__body">
        <?php if ( $cat_name ) : ?>
            <span class="bb-post-card__cat"><?php echo esc_html( $cat_name ); ?></span>
        <?php endif; ?>
        <h3 class="bb-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <span class="bb-post-card__meta">
            🕉 <?php echo bb_reading_time(); ?> <?php bb_t( array( 'en' => 'min read', 'hi' => 'मिनट पढ़ें', 'mr' => 'मिनिट वाचा' ) ); ?>
        </span>
    </div>
</article>
