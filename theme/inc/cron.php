<?php
/**
 * Cron scheduling and background sync
 */
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'kora_activate_cron' );
register_deactivation_hook( __FILE__, 'kora_deactivate_cron' );

function kora_activate_cron() {
    if ( ! wp_next_scheduled( 'kora_hourly_sync' ) ) {
        wp_schedule_event( time(), 'hourly', 'kora_hourly_sync' );
    }
}

function kora_deactivate_cron() {
    wp_clear_scheduled_hook( 'kora_hourly_sync' );
}

add_action( 'kora_hourly_sync', 'kora_cron_sync_matches' );
function kora_cron_sync_matches() {
    // Sync today and next day to keep schedule fresh
    $today = date( 'Y-m-d' );
    $tomorrow = date( 'Y-m-d', strtotime('+1 day') );
    kora_get_football_data_matches( $today, $tomorrow );
    // attempt to sync into CPTs
    kora_sync_matches_today();
}

// Process finished matches every hour
add_action( 'kora_hourly_sync', 'kora_process_finished_matches' );
function kora_process_finished_matches() {
    $args = array(
        'post_type' => 'match',
        'meta_query' => array(
            array('key' => 'kora_status','value' => 'FINISHED'),
            array('key' => 'kora_processed','compare' => 'NOT EXISTS'),
        ),
        'posts_per_page' => 50,
    );
    $matches = get_posts( $args );
    if ( empty( $matches ) ) return;
    foreach ( $matches as $m ) {
        kora_process_match_results( $m->ID );
        update_post_meta( $m->ID, 'kora_processed', 1 );
    }
}

?>