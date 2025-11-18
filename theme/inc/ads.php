<?php
/**
 * Simple Ads tracking and revenue share scaffold
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function kora_record_ad_impression( $ad_post_id ) {
    if ( ! $ad_post_id ) return false;
    $count = intval( get_post_meta( $ad_post_id, 'kora_impressions', true ) );
    $count++;
    update_post_meta( $ad_post_id, 'kora_impressions', $count );
    return $count;
}

function kora_record_ad_click( $ad_post_id ) {
    if ( ! $ad_post_id ) return false;
    $count = intval( get_post_meta( $ad_post_id, 'kora_clicks', true ) );
    $count++;
    update_post_meta( $ad_post_id, 'kora_clicks', $count );
    return $count;
}

/**
 * Calculate revenue share for a given ad post based on linked publishers
 * Each ad post can have meta 'kora_publishers' => array( publisher_id => percentage )
 */
function kora_calculate_ad_revenue_split( $ad_post_id, $gross_amount ) {
    $publishers = get_post_meta( $ad_post_id, 'kora_publishers', true );
    if ( ! is_array( $publishers ) ) return array();
    $distributions = array();
    $platform_fee = floatval( get_option( 'kora_platform_fee_percent', 10 ) );

    $net = $gross_amount * ( 1 - ( $platform_fee / 100 ) );
    foreach ( $publishers as $pub_id => $pct ) {
        $share = $net * ( floatval( $pct ) / 100 );
        $distributions[ $pub_id ] = round( $share, 2 );
    }
    return $distributions;
}

?>