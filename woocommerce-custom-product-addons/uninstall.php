<?php
/**
 * Uninstall script for WooCommerce Custom Product Add-ons
 * 
 * This file is executed when the plugin is uninstalled via WordPress admin.
 * It cleans up any options or data created by the plugin.
 * 
 * @package WC_Custom_Product_Addons
 */

// Exit if not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Note: This plugin doesn't store any options in wp_options table
// or create any custom database tables, so cleanup is minimal.

// If you add options in future versions, clean them up here:
// delete_option( 'wc_custom_addons_option_name' );

// If you add custom post types or taxonomies, clean them up here:
// Custom post type cleanup example:
// $posts = get_posts( array( 'post_type' => 'custom_addon', 'numberposts' => -1 ) );
// foreach ( $posts as $post ) {
//     wp_delete_post( $post->ID, true );
// }

// Clear any transients created by the plugin
// delete_transient( 'wc_custom_addons_transient_name' );

// Note: Order meta data is intentionally NOT deleted
// This preserves historical order information even after plugin removal
