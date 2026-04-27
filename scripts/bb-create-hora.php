<?php
/** Create 4 Hora pages with Polylang linkage. Run via: wp eval-file. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$pages = array(
    'en' => array(
        'slug'  => 'hora',
        'title' => 'Hora',
        'yoast_title' => 'Today\'s Hora — Planetary Hours & Auspicious Timings',
        'yoast_desc'  => 'Daily Hora timings — 12 day + 12 night planetary hours ruled by Sun, Moon, Mars, Mercury, Jupiter, Venus, Saturn. With city + date selection.',
    ),
    'hi' => array(
        'slug'  => 'aaj-ka-hora',
        'title' => 'आज का होरा',
        'yoast_title' => 'आज का होरा — ग्रह घंटे एवं शुभ मुहूर्त',
        'yoast_desc'  => 'दैनिक होरा — दिन और रात के 12-12 ग्रह-घंटे। सूर्य, चंद्र, मंगल, बुध, गुरु, शुक्र, शनि होरा के साथ शहर एवं तारीख चयन।',
    ),
    'mr' => array(
        'slug'  => 'aajcha-hora',
        'title' => 'आजची होरा',
        'yoast_title' => 'आजची होरा — ग्रह तास आणि शुभ मुहूर्त',
        'yoast_desc'  => 'दैनिक होरा — दिवस आणि रात्रीचे 12-12 ग्रह-तास. सूर्य, चंद्र, मंगळ, बुध, गुरु, शुक्र, शनी होरा शहर आणि दिनांक निवडीसह.',
    ),
    'gu' => array(
        'slug'  => 'aaj-no-hora',
        'title' => 'આજનો હોરા',
        'yoast_title' => 'આજનો હોરા — ગ્રહ કલાકો અને શુભ મુહૂર્ત',
        'yoast_desc'  => 'દૈનિક હોરા — દિવસ અને રાત્રિના 12-12 ગ્રહ-કલાકો. સૂર્ય, ચંદ્ર, મંગળ, બુધ, ગુરુ, શુક્ર, શનિ હોરા સાથે શહેર અને તારીખ પસંદગી.',
    ),
);

$created = array();
foreach ( $pages as $lang => $p ) {
    $existing = get_page_by_path( $p['slug'], OBJECT, 'page' );
    if ( $existing ) {
        WP_CLI::warning( "Page slug {$p['slug']} exists (ID {$existing->ID}). Reusing." );
        $post_id = $existing->ID;
    } else {
        $post_id = wp_insert_post( array(
            'post_title'  => $p['title'],
            'post_name'   => $p['slug'],
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 11,
        ), true );
        if ( is_wp_error( $post_id ) ) WP_CLI::error( "Failed $lang: " . $post_id->get_error_message() );
        WP_CLI::success( "Created $lang page (ID $post_id, slug {$p['slug']})." );
    }
    update_post_meta( $post_id, '_wp_page_template', 'template-hora.php' );
    update_post_meta( $post_id, '_yoast_wpseo_title', $p['yoast_title'] );
    update_post_meta( $post_id, '_yoast_wpseo_metadesc', $p['yoast_desc'] );
    wp_set_object_terms( $post_id, $lang, 'language', false );
    $created[ $lang ] = $post_id;
}

$translations = array(
    'en' => $created['en'], 'hi' => $created['hi'],
    'mr' => $created['mr'], 'gu' => $created['gu'],
);
$desc = serialize( $translations );
$term_name = 'pll_' . md5( $desc );
$term = get_term_by( 'name', $term_name, 'post_translations' );
if ( $term ) {
    wp_update_term( $term->term_id, 'post_translations', array( 'description' => $desc ) );
    $term_id = $term->term_id;
    WP_CLI::log( "Reused post_translations term $term_id." );
} else {
    $res = wp_insert_term( $term_name, 'post_translations', array( 'description' => $desc ) );
    if ( is_wp_error( $res ) ) WP_CLI::error( 'term insert: ' . $res->get_error_message() );
    $term_id = $res['term_id'];
    WP_CLI::success( "Created post_translations term $term_id." );
}
foreach ( $created as $lang => $pid ) {
    wp_set_object_terms( $pid, array( (int) $term_id ), 'post_translations', false );
}

WP_CLI::success( 'Done:' );
foreach ( $created as $lang => $pid ) {
    WP_CLI::log( "  $lang  ID=$pid  " . get_permalink( $pid ) );
}
