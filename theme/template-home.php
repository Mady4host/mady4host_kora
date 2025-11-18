<?php
/*
Template Name: Kora Home
*/
get_header();
?>
<main class="kora-home">
    <h1>Live Now</h1>
    <?php
    $live_matches = get_posts( array( 'post_type' => 'match', 'meta_key' => 'kora_status', 'meta_value' => 'LIVE', 'posts_per_page' => 10 ) );
    if ( ! empty( $live_matches ) ) {
        echo '<ul class="kora-live-list">';
        foreach ( $live_matches as $m ) {
            $time = get_post_meta( $m->ID, 'kora_match_time', true );
            echo '<li><a href="' . get_permalink( $m->ID ) . '">' . esc_html( $m->post_title ) . ' — ' . esc_html( $time ) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No live matches right now.</p>';
    }
    ?>
</main>
<?php
get_footer();
?>