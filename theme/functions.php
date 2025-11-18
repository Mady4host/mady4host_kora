<?php
/**
 * Kora Theme functions and definitions (scaffold)
 */

if ( ! function_exists( 'kora_setup' ) ) :
    function kora_setup() {
        load_theme_textdomain( 'kora-theme', get_template_directory() . '/languages' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    }
endif;
add_action( 'after_setup_theme', 'kora_setup' );

// Load includes
$inc_files = array(
    '/inc/cpt.php',
    '/inc/api-clients.php',
    '/inc/admin.php',
    '/inc/player.php',
    '/inc/predictions.php',
    '/inc/scorecalc.php',
    '/inc/cron.php',
    '/inc/ads.php',
);
foreach ( $inc_files as $f ) {
    $path = get_template_directory() . $f;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

function kora_scripts() {
    wp_enqueue_style( 'kora-style', get_stylesheet_uri(), array(), '0.1.0' );
}
add_action( 'wp_enqueue_scripts', 'kora_scripts' );

?>