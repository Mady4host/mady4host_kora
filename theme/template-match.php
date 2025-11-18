<?php
/*
Template Name: Match Template
*/
get_header();
if ( have_posts() ) : while ( have_posts() ) : the_post();
    $match_id = get_the_ID();
    $home_id = get_post_meta( $match_id, 'kora_home_team', true );
    $away_id = get_post_meta( $match_id, 'kora_away_team', true );
    $match_time = get_post_meta( $match_id, 'kora_match_time', true );
    ?>
    <main class="kora-match">
        <h1><?php the_title(); ?></h1>
        <p>Time: <?php echo esc_html( $match_time ); ?></p>

        <section class="kora-player">
            <?php
            // find a stream post linked to this match (simple approach)
            $streams = get_posts( array( 'post_type' => 'stream', 'meta_key' => 'kora_match', 'meta_value' => $match_id, 'posts_per_page' => 1 ) );
            if ( ! empty( $streams ) ) {
                echo do_shortcode( '[kora_player stream_id="' . $streams[0]->ID . '"]' );
            } else {
                echo '<p>No stream available.</p>';
            }
            ?>
        </section>

        <section class="kora-predictions">
            <?php if ( is_user_logged_in() ) : ?>
                <h3>Make a prediction</h3>
                <form id="kora-predict-form">
                    <input type="number" name="home_score" id="home_score" min="0" />
                    <input type="number" name="away_score" id="away_score" min="0" />
                    <button type="submit">Submit</button>
                </form>
                <script>
                (function(){
                    var form = document.getElementById('kora-predict-form');
                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        var home = document.getElementById('home_score').value;
                        var away = document.getElementById('away_score').value;
                        fetch('<?php echo esc_url( rest_url( 'kora/v1/predict' ) ); ?>', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>' },
                            body: JSON.stringify({ match_id: <?php echo intval( $match_id ); ?>, home_score: parseInt(home,10), away_score: parseInt(away,10) })
                        }).then(function(r){return r.json();}).then(function(j){ alert(JSON.stringify(j)); }).catch(function(e){ alert('Error'); });
                    });
                })();
                </script>
            <?php else: ?>
                <p>Please <a href="<?php echo wp_login_url( get_permalink() ); ?>">login</a> to predict.</p>
            <?php endif; ?>
        </section>
    </main>
    <?php
endwhile; endif;
get_footer();
?>