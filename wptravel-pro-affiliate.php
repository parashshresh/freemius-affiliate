<?php
/**
 * Plugin Name: WP Travel Pro Affiliate Form
 * Description: Manages affiliate applications via Freemius API.
 * Version: 1.0.1
 * Author: WP Travel Pro
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

// Include the file that generates the form HTML for the shortcode.
require_once plugin_dir_path(__FILE__) . 'affiliate-form.php';

// Include the file that handles the form submission logic.
require_once plugin_dir_path(__FILE__) . 'affiliate-submit.php';

/**
 * Registers the shortcode to display the affiliate application form.
 */
add_shortcode('wp_travel_pro_affiliator_form', 'wp_travel_pro_affiliate_render_affiliate_form');

/**
 * Hooks the form submission handler function to admin-post actions.
 * This ensures the function runs when the form is submitted to admin-post.php.
 * The 'my_affiliate_form_submission' part must match the hidden 'action' field in the form.
 */
add_action('admin_post_my_affiliate_form_submission', 'wp_travel_pro_affiliate_handle_affiliate_submission');
add_action('admin_post_nopriv_my_affiliate_form_submission', 'wp_travel_pro_affiliate_handle_affiliate_submission'); // For non-logged-in users

// REMOVED: The wp_travel_pro_affiliate_display_submission_messages() function and its wp_body_open hook
// are now handled directly within affiliate-form.php for better shortcode integration.

/**
 * Enqueues the stylesheet for the affiliate form.
 */
function wp_travel_pro_affiliate_enqueue_styles()
{
    wp_enqueue_style(
        'wp-travel-pro-affiliate-form-style',
        plugin_dir_url(__FILE__) . 'assets/form-style.css', // Ensure this path is correct for your CSS file
        array(),
        '1.0.0', // Version number
        'all'    // Media type
    );
}
add_action('wp_enqueue_scripts', 'wp_travel_pro_affiliate_enqueue_styles');