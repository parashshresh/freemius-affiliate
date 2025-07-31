<?php
// custom-plugin/affiliate-form.php

// Ensure this file is not directly accessed
if (! defined('ABSPATH')) {
  exit;
}

/**
 * Renders the HTML for the affiliate application form.
 * This function is intended to be called via a shortcode.
 *
 * @return string The HTML content of the form.
 */
function wp_travel_pro_affiliate_render_affiliate_form()
{
  ob_start(); // Start output buffering to capture the HTML

  // --- START: Message Display Logic & Data Retention ---
  // Retrieve submission status and message from URL query parameters
  $submission_status = $_GET['submission_status'] ?? '';
  $message_code      = $_GET['message'] ?? '';
  $display_message   = '';
  $class             = ''; // CSS class for the notice div

  // Determine the message to display based on the status and message code
  if ($submission_status === 'success') {
      $display_message = 'Thanks, your application has been submitted successfully! We will review it shortly.';
      $class = 'notice wp-travel-affiliate notice-success is-dismissible';
  } elseif ($submission_status === 'error') {
      switch ($message_code) {
          case 'nonce_failed':
              $display_message = 'Security check failed. Please try again.';
              break;
          case 'name_empty':
              $display_message = 'Please provide your name.';
              break;
          case 'invalid_email':
              $display_message = 'Please enter a valid email address.';
              break;
          case 'domain_empty':
              $display_message = 'Please provide your primary website domain.';
              break;
          case 'api_config_error':
              $display_message = 'There was a configuration error with the API. Please contact website support.';
              break;
          case 'duplicate_application':
              $display_message = 'An application with this email or domain already exists. Please check your details or contact us if you believe this is an error.';
              break;
          case 'api_error': // Generic API error
              $display_message = 'We could not process your request at the moment. Please try again.';
              break;
          default: // Fallback for any other unexpected error
              $display_message = 'An unexpected error occurred. Please try again.';
              break;
      }
      $class = 'notice wp-travel-affiliate notice-error is-dismissible';
  }

  // Output the message to the user, right before the form
  if (!empty($display_message)) {
      // The CSS classes will style this div based on our previous discussions.
      echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($display_message) . '</p></div>';
  }
  // --- END: Message Display Logic & Data Retention ---
?>
  <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="affiliate-application-form">
    <!-- Hidden field to identify the action for admin-post.php -->
    <input type="hidden" name="action" value="my_affiliate_form_submission">

    <!-- WordPress Nonce Field for Security. Crucial to prevent CSRF. -->
    <?php wp_nonce_field('my_affiliate_form_nonce_action', 'my_affiliate_form_nonce_field'); ?>

    <!-- IMPORTANT: Hidden field to redirect back to the current page after submission -->
    <!-- This ensures the URL parameters (for messages) are passed back to the correct page. -->
    <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">

    <p>
      <label for="affiliate_name">Name *</label>
      <!-- Value retained from previous submission -->
      <input name="name" id="affiliate_name" type="text" required value="<?php echo esc_attr($_POST['name'] ?? ''); ?>" />
    </p>
    <p>
      <label for="affiliate_email">Email *</label>
      <!-- Value retained from previous submission -->
      <input name="email" id="affiliate_email" type="email" required value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" />
    </p>
    <p>
      <label for="paypal_email">PayPal Email</label>
      <!-- Value retained from previous submission -->
      <input name="paypal_email" id="paypal_email" type="email" value="<?php echo esc_attr($_POST['paypal_email'] ?? ''); ?>" />
    </p>
    <p>
      <label for="domain">Primary Website/Blog Domain *</label>
      <!-- Value retained from previous submission -->
      <input name="domain" id="domain" type="text" required value="<?php echo esc_attr($_POST['domain'] ?? ''); ?>" />
    </p>
    <p>
      <label for="additional_domains">Additional Domains (comma-separated, if any)</label>
      <!-- Value retained from previous submission -->
      <input name="additional_domains" id="additional_domains" type="text" value="<?php echo esc_attr($_POST['additional_domains'] ?? ''); ?>" />
    </p>
    <fieldset>
      <legend>Promotional Methods</legend>
      <!-- Expanded and retaining values for checkboxes -->
      <label><input type="checkbox" name="promotional_methods[]" value="social_media" <?php echo (isset($_POST['promotional_methods']) && in_array('social_media', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Social Media</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="mobile_apps" <?php echo (isset($_POST['promotional_methods']) && in_array('mobile_apps', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Mobile Apps</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="blog_website" <?php echo (isset($_POST['promotional_methods']) && in_array('blog_website', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Blog / Website Content</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="email_marketing" <?php echo (isset($_POST['promotional_methods']) && in_array('email_marketing', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Email Marketing</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="youtube_video" <?php echo (isset($_POST['promotional_methods']) && in_array('youtube_video', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> YouTube / Video Content</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="paid_ads" <?php echo (isset($_POST['promotional_methods']) && in_array('paid_ads', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Paid Advertising (e.g., Google Ads, Facebook Ads)</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="webinars_events" <?php echo (isset($_POST['promotional_methods']) && in_array('webinars_events', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Webinars / Online Events</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="courses_education" <?php echo (isset($_POST['promotional_methods']) && in_array('courses_education', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Online Courses / Educational Platforms</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="podcasts" <?php echo (isset($_POST['promotional_methods']) && in_array('podcasts', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Podcasts</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="forums_communities" <?php echo (isset($_POST['promotional_methods']) && in_array('forums_communities', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Forums / Online Communities</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="direct_referral" <?php echo (isset($_POST['promotional_methods']) && in_array('direct_referral', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Direct Referrals / Word of Mouth</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="offline_events" <?php echo (isset($_POST['promotional_methods']) && in_array('offline_events', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Offline Events / Workshops</label><br>
      <label><input type="checkbox" name="promotional_methods[]" value="other" <?php echo (isset($_POST['promotional_methods']) && in_array('other', (array)$_POST['promotional_methods'])) ? 'checked' : ''; ?>> Other (please specify in description)</label>
    </fieldset>
    <p>
      <label for="stats_description">Reach / Stats Description</label>
      <!-- Value retained from previous submission -->
      <textarea name="stats_description" id="stats_description"><?php echo esc_textarea($_POST['stats_description'] ?? ''); ?></textarea>
    </p>
    <p>
      <label for="promotion_method_description">Promotion Plan Description</label>
      <!-- Value retained from previous submission -->
      <textarea name="promotion_method_description" id="promotion_method_description"><?php echo esc_textarea($_POST['promotion_method_description'] ?? ''); ?></textarea>
    </p>
    <p>
      <button type="submit">Apply as Affiliate</button>
    </p>
  </form>
<?php
  return ob_get_clean(); // Return the captured HTML for the shortcode
}