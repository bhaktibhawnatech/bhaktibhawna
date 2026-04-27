<?php
/** Strip Elementor postmeta from all pages + posts. Keeps post_content. */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$elem_keys = array(
    '_elementor_edit_mode', '_elementor_template_type', '_elementor_version',
    '_elementor_pro_version', '_elementor_data', '_elementor_page_settings',
    '_elementor_css', '_elementor_controls_usage', '_elementor_page_assets',
    '_elementor_conditions', '_elementor_priority',
);
$placeholders = implode( ',', array_fill( 0, count( $elem_keys ), '%s' ) );
$sql = "DELETE pm FROM {$wpdb->postmeta} pm
        JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type IN ('page','post') AND pm.meta_key IN ($placeholders)";
$result = $wpdb->query( $wpdb->prepare( $sql, $elem_keys ) );
WP_CLI::log( "Stripped $result rows of Elementor postmeta from pages+posts." );

/* Delete Elementor's own templates entirely */
$lib_count = 0;
$lib_posts = get_posts( array(
    'post_type'      => array( 'elementor_library', 'elementor-hf', 'shopengine-template' ),
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
) );
foreach ( $lib_posts as $pid ) { wp_delete_post( $pid, true ); $lib_count++; }
WP_CLI::log( "Deleted $lib_count Elementor library/HF/ShopEngine templates." );

/* Final sanity */
$remaining = $wpdb->get_var( "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
    WHERE pm.meta_key = '_elementor_edit_mode' AND pm.meta_value = 'builder'" );
WP_CLI::log( "Remaining Elementor-marked posts: $remaining" );
