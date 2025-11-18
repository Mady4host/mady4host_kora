<?php
/**
 * Register Custom Post Types for Kora Theme
 */

add_action( 'init', 'kora_register_cpts' );
function kora_register_cpts() {
    $labels = array(
        'name' => __( 'Matches', 'kora-theme' ),
        'singular_name' => __( 'Match', 'kora-theme' ),
    );
    register_post_type( 'match', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => false,
        'show_in_rest' => true,
        'supports' => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
        'rewrite' => array( 'slug' => 'match' ),
    ) );

    register_post_type( 'team', array(
        'labels' => array('name' => __('Teams','kora-theme')),
        'public' => true,
        'show_in_rest' => true,
        'supports' => array('title','thumbnail'),
        'rewrite' => array('slug'=>'team'),
    ));

    register_post_type( 'league', array(
        'labels' => array('name' => __('Leagues','kora-theme')),
        'public' => true,
        'show_in_rest' => true,
        'supports' => array('title','editor','thumbnail'),
        'rewrite' => array('slug'=>'league'),
    ));

    register_post_type( 'stream', array(
        'labels' => array('name' => __('Streams','kora-theme')),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title','custom-fields'),
    ));

    register_post_type( 'contest', array(
        'labels' => array('name' => __('Contests','kora-theme')),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title','editor','custom-fields'),
    ));

    register_post_type( 'kora_ad', array(
        'labels' => array('name' => __('Ads','kora-theme')),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title','custom-fields'),
    ));

    register_post_type( 'publisher', array(
        'labels' => array('name' => __('Publishers','kora-theme')),
        'public' => false,
        'show_ui' => true,
        'supports' => array('title','custom-fields'),
    ));
}

?>