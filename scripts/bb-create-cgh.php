<?php
/**
 * Create 4 Choghadiya pages, set Polylang language + translation linkage.
 * Run via: wp eval-file /tmp/bb-create-cgh.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$pages = array(
    'en' => array(
        'slug'  => 'choghadiya',
        'title' => 'Choghadiya',
        'yoast_title' => 'Today\'s Choghadiya — Auspicious Muhurat Timings',
        'yoast_desc'  => 'Daily Choghadiya muhurats — 8 day + 8 night auspicious time slots. Amrut, Shubh, Labh, Char, Kaal, Rog, Udveg with city + date selection.',
    ),
    'hi' => array(
        'slug'  => 'aaj-ka-choghadiya',
        'title' => 'आज का चौघड़िया',
        'yoast_title' => 'आज का चौघड़िया — दिन और रात के शुभ मुहूर्त',
        'yoast_desc'  => 'दैनिक चौघड़िया मुहूर्त — दिन और रात के 8-8 शुभ समय। अमृत, शुभ, लाभ, चर, काल, रोग, उद्वेग के साथ शहर एवं तारीख चयन।',
    ),
    'mr' => array(
        'slug'  => 'aajcha-choghadiya',
        'title' => 'आजचा चौघडिया',
        'yoast_title' => 'आजचा चौघडिया — दिवस आणि रात्रीचे शुभ मुहूर्त',
        'yoast_desc'  => 'दैनिक चौघडिया मुहूर्त — दिवस आणि रात्रीचे 8-8 शुभ काळ. अमृत, शुभ, लाभ, चर, काळ, रोग, उद्वेग शहर आणि दिनांक निवडीसह.',
    ),
    'gu' => array(
        'slug'  => 'aaj-nu-choghadiya',
        'title' => 'આજનું ચોઘડિયું',
        'yoast_title' => 'આજનું ચોઘડિયું — દિવસ અને રાત્રિના શુભ મુહૂર્ત',
        'yoast_desc'  => 'દૈનિક ચોઘડિયું મુહૂર્ત — દિવસ અને રાત્રિના 8-8 શુભ સમય. અમૃત, શુભ, લાભ, ચર, કાળ, રોગ, ઉદ્વેગ સાથે શહેર અને તારીખ પસંદગી.',
    ),
);

$created = array();

foreach ( $pages as $lang => $p ) {
    /* Skip if a page with this slug already exists in this language */
    $existing = get_page_by_path( $p['slug'], OBJECT, 'page' );
    if ( $existing ) {
        WP_CLI::warning( "Page with slug {$p['slug']} already exists (ID {$existing->ID}). Skipping creation, will reuse." );
        $post_id = $existing->ID;
    } else {
        $post_id = wp_insert_post( array(
            'post_title'   => $p['title'],
            'post_name'    => $p['slug'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
            'post_author'  => 11,
        ), true );
        if ( is_wp_error( $post_id ) ) {
            WP_CLI::error( "Failed to create $lang page: " . $post_id->get_error_message() );
        }
        WP_CLI::success( "Created $lang page (ID $post_id, slug {$p['slug']})." );
    }

    /* Set page template */
    update_post_meta( $post_id, '_wp_page_template', 'template-choghadiya.php' );

    /* Yoast meta */
    update_post_meta( $post_id, '_yoast_wpseo_title', $p['yoast_title'] );
    update_post_meta( $post_id, '_yoast_wpseo_metadesc', $p['yoast_desc'] );

    /* Set Polylang language term */
    wp_set_object_terms( $post_id, $lang, 'language', false );

    $created[ $lang ] = $post_id;
}

/* Create or update post_translations term linking all 4 */
$translations = array(
    'en' => $created['en'],
    'hi' => $created['hi'],
    'mr' => $created['mr'],
    'gu' => $created['gu'],
);
$desc = serialize( $translations );
$term_name = 'pll_' . md5( $desc );

/* Check if a term with this name already exists */
$term = get_term_by( 'name', $term_name, 'post_translations' );
if ( $term ) {
    wp_update_term( $term->term_id, 'post_translations', array( 'description' => $desc ) );
    $term_id = $term->term_id;
    WP_CLI::log( "Reused post_translations term $term_id." );
} else {
    $res = wp_insert_term( $term_name, 'post_translations', array( 'description' => $desc ) );
    if ( is_wp_error( $res ) ) {
        WP_CLI::error( 'Failed to insert post_translations term: ' . $res->get_error_message() );
    }
    $term_id = $res['term_id'];
    WP_CLI::success( "Created post_translations term $term_id." );
}

/* Assign each page to the translation term */
foreach ( $created as $lang => $pid ) {
    wp_set_object_terms( $pid, array( (int) $term_id ), 'post_translations', false );
}

WP_CLI::success( 'Polylang linkage done. Summary:' );
foreach ( $created as $lang => $pid ) {
    $url = get_permalink( $pid );
    WP_CLI::log( "  $lang  ID=$pid  $url" );
}
