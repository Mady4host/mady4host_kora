<?php
/**
 * Admin settings and sync for Kora theme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'kora_admin_menu' );
function kora_admin_menu() {
    add_menu_page( 'Kora Settings', 'Kora', 'manage_options', 'kora-settings', 'kora_settings_page', 'dashicons-media-sound', 61 );
}

function kora_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    // Save settings
    if ( isset( $_POST['kora_settings_nonce'] ) && wp_verify_nonce( $_POST['kora_settings_nonce'], 'kora_save_settings' ) ) {
        update_option( 'kora_fd_api_key', sanitize_text_field( $_POST['kora_fd_api_key'] ) );
        update_option( 'kora_tsdb_api_key', sanitize_text_field( $_POST['kora_tsdb_api_key'] ) );
        update_option( 'kora_match_lock_minutes', intval( $_POST['kora_match_lock_minutes'] ) );
        update_option( 'kora_enable_ads', isset( $_POST['kora_enable_ads'] ) ? 1 : 0 );
        update_option( 'kora_primary_color', sanitize_text_field( $_POST['kora_primary_color'] ) );
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Sync action
    if ( isset( $_POST['kora_sync_now'] ) && wp_verify_nonce( $_POST['kora_settings_nonce'], 'kora_save_settings' ) ) {
        $res = kora_sync_matches_today();
        echo '<div class="updated"><p>Sync finished. Retrieved: ' . intval( $res ) . ' matches.</p></div>';
    }

    $fd_key = esc_attr( get_option( 'kora_fd_api_key', '' ) );
    $tsdb_key = esc_attr( get_option( 'kora_tsdb_api_key', '' ) );
    $lock = intval( get_option( 'kora_match_lock_minutes', 15 ) );
    $enable_ads = get_option( 'kora_enable_ads', 1 );
    $primary_color = esc_attr( get_option( 'kora_primary_color', '#0b74de' ) );
    ?>
    <div class="wrap">
        <h1>Kora Settings</h1>
        <form method="post">
            <?php wp_nonce_field( 'kora_save_settings', 'kora_settings_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="kora_fd_api_key">Football-Data.org API Key</label></th>
                    <td><input name="kora_fd_api_key" id="kora_fd_api_key" type="text" value="<?php echo $fd_key; ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kora_tsdb_api_key">TheSportsDB API Key (optional)</label></th>
                    <td><input name="kora_tsdb_api_key" id="kora_tsdb_api_key" type="text" value="<?php echo $tsdb_key; ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kora_match_lock_minutes">Match lock (minutes)</label></th>
                    <td><input name="kora_match_lock_minutes" id="kora_match_lock_minutes" type="number" value="<?php echo $lock; ?>" class="small-text" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kora_enable_ads">Enable Ads</label></th>
                    <td><input name="kora_enable_ads" id="kora_enable_ads" type="checkbox" <?php checked( $enable_ads, 1 ); ?> value="1" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="kora_primary_color">Primary Color</label></th>
                    <td><input name="kora_primary_color" id="kora_primary_color" type="color" value="<?php echo $primary_color; ?>" /></td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="Save Changes" />
                <button type="submit" name="kora_sync_now" class="button">Sync Matches Now</button>
            </p>
        </form>
    </div>
    <?php
}

function kora_sync_matches_today() {
    // simple sync - fetch today's matches from football-data
    $today = date( 'Y-m-d' );
    $data = kora_get_football_data_matches( $today, $today );
    if ( empty( $data['matches'] ) ) return 0;
    $count = 0;
    foreach ( $data['matches'] as $m ) {
        $ext_id = isset( $m['id'] ) ? $m['id'] : null;
        if ( ! $ext_id ) continue;
        // try to find existing match by external id stored in post meta
        $existing = get_posts( array(
            'post_type' => 'match',
            'meta_key' => 'kora_external_id',
            'meta_value' => $ext_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ) );
        $post_data = array(
            'post_title' => esc_html( $m['homeTeam']['name'] . ' vs ' . $m['awayTeam']['name'] ),
            'post_type' => 'match',
            'post_status' => 'publish',
            'meta_input' => array(
                'kora_home_team' => $m['homeTeam']['id'],
                'kora_away_team' => $m['awayTeam']['id'],
                'kora_match_time' => isset( $m['utcDate'] ) ? $m['utcDate'] : '',
                'kora_status' => isset( $m['status'] ) ? $m['status'] : '',
                'kora_external_id' => $ext_id,
            ),
        );
        if ( ! empty( $existing ) ) {
            $post_data['ID'] = $existing[0];
            wp_update_post( $post_data );
        } else {
            wp_insert_post( $post_data );
            $count++;
        }
    }
    return $count;
}

?>