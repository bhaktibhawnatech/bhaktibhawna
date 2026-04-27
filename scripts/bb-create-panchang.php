<?php
/**
 * Create 4 Panchang pages, set Polylang language + translation linkage.
 * Run via: wp eval-file /tmp/bb-create-panchang.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$pages = array(
    'en' => array(
        'slug'  => 'panchang',
        'title' => 'Panchang',
        'yoast_title' => 'Daily Panchang Today — Tithi, Nakshatra, Yoga, Karana, Muhurat',
        'yoast_desc'  => 'Today\'s Drik Panchang — Tithi, Nakshatra, Yoga, Karana, Vaara, sun & moon timings, auspicious & inauspicious periods. City + date selectable.',
    ),
    'hi' => array(
        'slug'  => 'aaj-ka-panchang',
        'title' => 'आज का पंचांग',
        'yoast_title' => 'आज का पंचांग — तिथि, नक्षत्र, योग, करण, मुहूर्त',
        'yoast_desc'  => 'आज का दैनिक दृक पंचांग — तिथि, नक्षत्र, योग, करण, वार, सूर्य व चंद्र का समय, शुभ व अशुभ काल। शहर एवं तारीख चयन।',
    ),
    'mr' => array(
        'slug'  => 'aajcha-panchang',
        'title' => 'आजचा पंचांग',
        'yoast_title' => 'आजचा पंचांग — तिथी, नक्षत्र, योग, करण, मुहूर्त',
        'yoast_desc'  => 'आजचा दैनिक दृक पंचांग — तिथी, नक्षत्र, योग, करण, वार, सूर्य व चंद्र वेळा, शुभ व अशुभ काळ. शहर आणि दिनांक निवडीसह.',
    ),
    'gu' => array(
        'slug'  => 'aaj-no-panchang',
        'title' => 'આજનો પંચાંગ',
        'yoast_title' => 'આજનો પંચાંગ — તિથિ, નક્ષત્ર, યોગ, કરણ, મુહૂર્ત',
        'yoast_desc'  => 'આજનો દૈનિક દ્રિક પંચાંગ — તિથિ, નક્ષત્ર, યોગ, કરણ, વાર, સૂર્ય અને ચંદ્રના સમય, શુભ અને અશુભ સમય. શહેર અને તારીખ પસંદગી.',
    ),
);

$created = array();

foreach ( $pages as $lang => $p ) {
    $existing = get_page_by_path( $p['slug'], OBJECT, 'page' );
    if ( $existing ) {
        WP_CLI::warning( "Page with slug {$p['slug']} already exists (ID {$existing->ID}). Reusing." );
        $post_id = $existing->ID;
    } else {
        $post_id = wp_insert_post( array(
            'post_title'   => $p['title'],
            'post_name'    => $p['slug'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ), true );
        if ( is_wp_error( $post_id ) ) {
            WP_CLI::error( "Failed to create $lang page: " . $post_id->get_error_message() );
        }
        WP_CLI::success( "Created $lang page (ID $post_id, slug {$p['slug']})." );
    }

    update_post_meta( $post_id, '_wp_page_template', 'template-panchang.php' );
    update_post_meta( $post_id, '_yoast_wpseo_title', $p['yoast_title'] );
    update_post_meta( $post_id, '_yoast_wpseo_metadesc', $p['yoast_desc'] );
    wp_set_object_terms( $post_id, $lang, 'language', false );

    $created[ $lang ] = $post_id;
}

$translations = $created;
$desc = serialize( $translations );
$term_name = 'pll_' . md5( $desc );

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

foreach ( $created as $lang => $pid ) {
    wp_set_object_terms( $pid, array( (int) $term_id ), 'post_translations', false );
}

WP_CLI::success( 'Polylang linkage done. Summary:' );
foreach ( $created as $lang => $pid ) {
    $url = get_permalink( $pid );
    WP_CLI::log( "  $lang  ID=$pid  $url" );
}
