<?php
/**
 * Player scaffold and tokenized stream URL generator
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function kora_generate_stream_token( $stream_id, $expiry_seconds = 300 ) {
    $secret = get_option( 'kora_stream_secret', 'change-this-secret' );
    $expires = time() + intval( $expiry_seconds );
    $hash = wp_hash( $stream_id . '|' . $expires . '|' . $secret );
    return base64_encode( $hash . '|' . $expires );
}

function kora_get_stream_url( $stream_post_id ) {
    $url_template = get_post_meta( $stream_post_id, 'kora_stream_url', true );
    if ( ! $url_template ) return '';
    $token = kora_generate_stream_token( $stream_post_id );
    // append token param
    $sep = ( strpos( $url_template, '?' ) === false ) ? '?' : '&';
    return $url_template . $sep . 'kora_token=' . rawurlencode( $token );
}

add_action( 'wp_enqueue_scripts', 'kora_enqueue_player_assets' );
function kora_enqueue_player_assets() {
    wp_enqueue_style( 'videojs', 'https://vjs.zencdn.net/7.24.1/video-js.css', array(), '7.24.1' );
    wp_enqueue_script( 'videojs', 'https://vjs.zencdn.net/7.24.1/video.min.js', array(), '7.24.1', true );
}

function kora_player_shortcode( $atts ) {
    $atts = shortcode_atts( array( 'stream_id' => 0 ), $atts, 'kora_player' );
    $stream_id = intval( $atts['stream_id'] );
    if ( ! $stream_id ) return '';
    $stream_url = kora_get_stream_url( $stream_id );
    if ( ! $stream_url ) return '<p>Stream not available.</p>';
    ob_start();
    ?>
    <video id="kora-player-<?php echo esc_attr( $stream_id ); ?>" class="video-js vjs-default-skin" controls preload="auto" width="640" height="360">
        <source src="<?php echo esc_url( $stream_url ); ?>" type="application/x-mpegURL">
    </video>
    <script>if(window.videojs){videojs(document.getElementById('kora-player-<?php echo esc_js( $stream_id ); ?>'));}</script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'kora_player', 'kora_player_shortcode' );

?>