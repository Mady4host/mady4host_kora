<?php
/**
 * Predictions REST endpoints and handling
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function() {
    register_rest_route( 'kora/v1', '/predict', array(
        'methods' => 'POST',
        'callback' => 'kora_handle_prediction',
        'permission_callback' => function() { return is_user_logged_in(); },
    ) );
} );

function kora_handle_prediction( $request ) {
    $params = $request->get_json_params();
    $user_id = get_current_user_id();
    $match_id = isset( $params['match_id'] ) ? intval( $params['match_id'] ) : 0;
    $home = isset( $params['home_score'] ) ? intval( $params['home_score'] ) : null;
    $away = isset( $params['away_score'] ) ? intval( $params['away_score'] ) : null;

    if ( ! $match_id || $home === null || $away === null ) {
        return new WP_Error( 'invalid_data', 'Missing parameters', array( 'status' => 400 ) );
    }

    $lock_minutes = intval( get_option( 'kora_match_lock_minutes', 15 ) );
    $match_time = get_post_meta( $match_id, 'kora_match_time', true );
    if ( $match_time ) {
        $match_ts = strtotime( $match_time );
        if ( time() >= ( $match_ts - ( $lock_minutes * 60 ) ) ) {
            return new WP_Error( 'locked', 'Predictions for this match are closed', array( 'status' => 403 ) );
        }
    }

    // store prediction in user meta as associative array by match id
    $preds = get_user_meta( $user_id, 'kora_predictions', true );
    if ( ! is_array( $preds ) ) $preds = array();
    $preds[ $match_id ] = array(
        'home' => $home,
        'away' => $away,
        'ts' => time(),
    );
    update_user_meta( $user_id, 'kora_predictions', $preds );

    return rest_ensure_response( array( 'success' => true, 'prediction' => $preds[ $match_id ] ) );
}

?>