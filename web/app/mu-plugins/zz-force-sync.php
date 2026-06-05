<?php
add_action('init', function() {
    if (isset($_GET['zz_force_sync']) && current_user_can('manage_options')) {
        set_time_limit(600);
        delete_option('fasad-sync-lock');
        do_action('sync_all_listings');
        wp_die('Sync triggered klart ' . date('H:i:s'));
    }
}, 999);
