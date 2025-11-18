<?php
/**
 * Scoring and prediction processing
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Calculate points for a single prediction
 * Rules default: outcome correct = 3, exact score = +2, top scorer optional (not implemented here)
 */
function kora_calculate_prediction_points( $actual_home, $actual_away, $pred_home, $pred_away ) {
    $points = 0;
    // outcome
    $actual_diff = $actual_home - $actual_away;
    $pred_diff = $pred_home - $pred_away;
    $actual_outcome = ( $actual_diff > 0 ) ? 'home' : ( $actual_diff < 0 ? 'away' : 'draw' );
    $pred_outcome = ( $pred_diff > 0 ) ? 'home' : ( $pred_diff < 0 ? 'away' : 'draw' );
    if ( $actual_outcome === $pred_outcome ) {
        $points += 3;
    }
    // exact score
    if ( intval( $actual_home ) === intval( $pred_home ) && intval( $actual_away ) === intval( $pred_away ) ) {
        $points += 2;
    }
    return $points;
}

/**
 * Process a finished match and award points to users who predicted
 */
function kora_process_match_results( $match_post_id ) {
    $home = intval( get_post_meta( $match_post_id, 'kora_home_score', true ) );
    $away = intval( get_post_meta( $match_post_id, 'kora_away_score', true ) );
    // iterate users with predictions (simple approach: scan all users)
    $users = get_users( array( 'fields' => 'ID' ) );
    foreach ( $users as $uid ) {
        $preds = get_user_meta( $uid, 'kora_predictions', true );
        if ( ! is_array( $preds ) || empty( $preds[ $match_post_id ] ) ) continue;
        $p = $preds[ $match_post_id ];
        $pts = kora_calculate_prediction_points( $home, $away, $p['home'], $p['away'] );
        // store in user meta ledger
        $ledger = get_user_meta( $uid, 'kora_points_ledger', true );
        if ( ! is_array( $ledger ) ) $ledger = array();
        $ledger[] = array( 'match_id' => $match_post_id, 'points' => $pts, 'ts' => time() );
        update_user_meta( $uid, 'kora_points_ledger', $ledger );
        // update total points
        $total = intval( get_user_meta( $uid, 'kora_total_points', true ) );
        $total += $pts;
        update_user_meta( $uid, 'kora_total_points', $total );
    }
}

?>