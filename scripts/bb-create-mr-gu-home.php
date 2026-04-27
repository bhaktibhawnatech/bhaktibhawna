<?php
/** Create MR + GU home pages, link them as Polylang translations of the EN home.
 *  Run via: wp eval-file. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$en_home_id = (int) get_option( 'page_on_front' );
if ( ! $en_home_id ) {
    WP_CLI::error( 'page_on_front not set.' );
}
WP_CLI::log( "EN home ID: $en_home_id" );

/* Get existing translation map */
global $wpdb;
$row = $wpdb->get_row( $wpdb->prepare(
    "SELECT t.term_id, tt.description FROM {$wpdb->terms} t
     JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
     WHERE tt.taxonomy = %s AND tt.description LIKE %s LIMIT 1",
    'post_translations', '%i:' . $en_home_id . ';%'
) );
if ( ! $row ) {
    WP_CLI::error( 'No post_translations term found for EN home.' );
}
$trans = unserialize( $row->description );
WP_CLI::log( 'Existing translations: ' . wp_json_encode( $trans ) );

$pages = array(
    'mr' => array( 'title' => 'मुख्यपृष्ठ', 'slug' => 'mukhya-prushtha' ),
    'gu' => array( 'title' => 'મુખ્યપૃષ્ઠ', 'slug' => 'mukhya-prushth' ),
);

foreach ( $pages as $lang => $p ) {
    if ( ! empty( $trans[ $lang ] ) ) {
        WP_CLI::log( "$lang home already linked (ID {$trans[ $lang ]}). Skipping." );
        continue;
    }
    $existing = get_page_by_path( $p['slug'], OBJECT, 'page' );
    if ( $existing ) {
        $post_id = $existing->ID;
        WP_CLI::warning( "Slug {$p['slug']} exists (ID $post_id). Reusing." );
    } else {
        $post_id = wp_insert_post( array(
            'post_title'  => $p['title'],
            'post_name'   => $p['slug'],
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 11,
        ), true );
        if ( is_wp_error( $post_id ) ) WP_CLI::error( "$lang failed: " . $post_id->get_error_message() );
        WP_CLI::success( "Created $lang home (ID $post_id, slug {$p['slug']})." );
    }
    wp_set_object_terms( $post_id, $lang, 'language', false );
    $trans[ $lang ] = $post_id;
}

/* Update post_translations term description */
$desc = serialize( $trans );
wp_update_term( $row->term_id, 'post_translations', array( 'description' => $desc ) );
foreach ( $trans as $pid ) {
    wp_set_object_terms( (int) $pid, array( (int) $row->term_id ), 'post_translations', false );
}

WP_CLI::success( 'Polylang home translations now: ' . wp_json_encode( $trans ) );
foreach ( $trans as $lang => $pid ) {
    WP_CLI::log( "  $lang  ID=$pid  " . get_permalink( (int) $pid ) );
}
