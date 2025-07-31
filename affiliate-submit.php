<?php
// custom-plugin/affiliate-submit.php

// Ensure this file is not directly accessed
if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Include the Freemius SDK.
require_once __DIR__ . '/freemius/Freemius.php';

// Define the Freemius API scope.
define('FS__API_SCOPE', 'developer');

/**
 * Handles the affiliate application form submission.
 * This function is hooked to 'admin_post_my_affiliate_form_submission'
 * and 'admin_post_nopriv_my_affiliate_form_submission'.
 */
function wp_travel_pro_affiliate_handle_affiliate_submission()
{
  error_log('Affiliate form submission handler triggered.');

  // Initialize redirect URL. Prioritize 'redirect_to' from the form, fallback to referer, then home_url.
  $redirect_url = home_url(); // Default fallback
  if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
    $redirect_url = esc_url_raw($_POST['redirect_to']); // Use explicit redirect_to if provided
  } elseif (wp_get_referer()) {
    $redirect_url = wp_get_referer(); // Fallback to referer if redirect_to not set
  }

  // Add error status by default; will be changed to success if needed.
  $redirect_url = add_query_arg('submission_status', 'error', $redirect_url);


  // 1. NONCE VERIFICATION (Crucial for security against CSRF attacks)
  if (! isset($_POST['my_affiliate_form_nonce_field']) || ! wp_verify_nonce($_POST['my_affiliate_form_nonce_field'], 'my_affiliate_form_nonce_action')) {
    error_log('Affiliate form: Nonce verification failed.');
    $redirect_url = add_query_arg('message', 'nonce_failed', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit; // Always exit after a redirect
  }
  error_log('Affiliate form: Nonce verified successfully.');

  // 2. RETRIEVE AND VALIDATE FREEMIUS API CONSTANTS FROM WP-CONFIG.PHP
  $api_errors = [];

  // Validate FS__API_ENTITY_ID: Must be defined and a numeric value.
  if (! defined('FS__API_ENTITY_ID')) {
    $api_errors[] = 'Freemius API ENTITY ID is not defined in wp-config.php.';
  } elseif (! is_numeric(FS__API_ENTITY_ID)) {
    $api_errors[] = 'Freemius API ENTITY ID is not a valid number in wp-config.php.';
  }

  // Validate FS__API_PUBLIC_KEY: Must be defined and not empty.
  if (! defined('FS__API_PUBLIC_KEY')) {
    $api_errors[] = 'Freemius API PUBLIC KEY is not defined in wp-config.php.';
  } elseif (empty(FS__API_PUBLIC_KEY)) {
    $api_errors[] = 'Freemius API PUBLIC KEY is empty in wp-config.php.';
  }

  // Validate FS__API_SECRET_KEY: Must be defined and not empty.
  if (! defined('FS__API_SECRET_KEY')) {
    $api_errors[] = 'Freemius API SECRET KEY is not defined in wp-config.php.';
  } elseif (empty(FS__API_SECRET_KEY)) {
    $api_errors[] = 'Freemius API SECRET KEY is empty in wp-config.php.';
  }

  // If any API configuration errors are found, log them and redirect the user.
  if (! empty($api_errors)) {
    foreach ($api_errors as $error_msg) {
      error_log('Affiliate form: Configuration Error: ' . $error_msg);
    }
    // Use a generic error message for the user to avoid exposing sensitive details.
    $redirect_url = add_query_arg('message', 'api_config_error', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
  }

  // Assign the validated constants to local variables for cleaner code.
  $fs_entity_id = FS__API_ENTITY_ID;
  $fs_public_key = FS__API_PUBLIC_KEY;
  $fs_secret_key = FS__API_SECRET_KEY;


  // 3. SANITIZE AND VALIDATE INPUT DATA FROM THE FORM
  // Use WordPress's sanitization functions to clean user input.
  $name = sanitize_text_field($_POST['name'] ?? '');
  $email = sanitize_email($_POST['email'] ?? '');
  $paypal = sanitize_email($_POST['paypal_email'] ?? '');
  $domain = sanitize_text_field($_POST['domain'] ?? '');

  // Handle additional domains: explode by comma, trim whitespace, sanitize each, and filter out empty entries.
  $additional_domains_raw = explode(',', $_POST['additional_domains'] ?? '');
  $additional_domains_sanitized = array_map('trim', $additional_domains_raw);
  $additional_domains_sanitized = array_map('sanitize_text_field', $additional_domains_sanitized);
  $additional_domains = array_filter($additional_domains_sanitized);

  // Handle promotional methods: ensure it's an array, sanitize each, then implode into a comma-separated string for Freemius.
  $promotional_methods_raw = isset($_POST['promotional_methods']) ? (array) $_POST['promotional_methods'] : [];
  $promotional_methods_sanitized = array_map('sanitize_text_field', $promotional_methods_raw);
  $promotional_methods = implode(',', $promotional_methods_sanitized);

  // Sanitize textarea fields.
  $stats_description = sanitize_textarea_field($_POST['stats_description'] ?? '');
  $promotion_method_description = sanitize_textarea_field($_POST['promotion_method_description'] ?? '');

  error_log('Affiliate form: Sanitized Data: ' . print_r([
    'name' => $name,
    'email' => $email,
    'paypal_email' => $paypal,
    'domain' => $domain,
    'additional_domains' => $additional_domains,
    'promotional_methods' => $promotional_methods,
    'stats_description' => $stats_description,
    'promotion_method_description' => $promotion_method_description
  ], true));

  // Basic Server-Side Validation of required fields.
  // Redirect with specific messages if validation fails.
  if (empty($name)) {
    error_log('Affiliate form: Validation failed - Name empty.');
    $redirect_url = add_query_arg('message', 'name_empty', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
  }
  if (! is_email($email)) {
    error_log('Affiliate form: Validation failed - Invalid email: ' . $email);
    $redirect_url = add_query_arg('message', 'invalid_email', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
  }
  if (empty($domain)) {
    error_log('Affiliate form: Validation failed - Domain empty.');
    $redirect_url = add_query_arg('message', 'domain_empty', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
  }
  error_log('Affiliate form: All basic validations passed.');

  // 4. FREEMIUS API INTERACTION
  try {
    // Instantiate the Freemius_Api class with the validated credentials.
    $api = new Freemius_Api(FS__API_SCOPE, $fs_entity_id, $fs_public_key, $fs_secret_key);

    // Define the specific product and affiliate program terms IDs.
    $productID = 19858; // Replace with your actual product ID from Freemius.
    $affiliateProgramTermsID = 2236; // Replace with your actual affiliate program terms ID from Freemius.

    // Prepare the data payload for the Freemius API request.
    $api_data_payload = [
      'name'                         => $name,
      'email'                        => $email,
      'paypal_email'                 => $paypal,
      'domain'                       => $domain,
      'additional_domains'           => $additional_domains, // Freemius expects an array for additional domains.
      'promotional_methods'          => $promotional_methods, // Freemius expects a comma-separated string.
      'stats_description'            => $stats_description,
      'promotion_method_description' => $promotion_method_description,
      'state'                        => 'pending', // Set the initial state for new applications.
    ];

    error_log('Affiliate form: Sending to Freemius API. Endpoint: /plugins/' . $productID . '/aff/' . $affiliateProgramTermsID . '/affiliates.json');
    error_log('Affiliate form: Data payload to Freemius: ' . print_r($api_data_payload, true));


    // Make the API call to Freemius to create a new affiliate.
    $result = $api->Api(
      "/plugins/{$productID}/aff/{$affiliateProgramTermsID}/affiliates.json", // API endpoint.
      'POST', // HTTP method.
      $api_data_payload // Data to send.
    );

    error_log('Affiliate form: Raw Freemius API Response: ' . print_r($result, true));

    // Check the structure of the API response to determine success or specific error.
    if (is_object($result) && isset($result->id) && ! empty($result->id)) {
      // API call was successful, and a new affiliate ID was returned.
      error_log('Affiliate form: Freemius API call successful. New Affiliate ID: ' . $result->id);
      $redirect_url = add_query_arg('submission_status', 'success', $redirect_url);
    } elseif (is_object($result) && isset($result->error) && is_object($result->error)) {
      // Freemius API returned an error object.
      $freemius_error_message = isset($result->error->message) ? $result->error->message : 'Unknown Freemius API error.';
      $freemius_error_code    = isset($result->error->code) ? $result->error->code : ''; // Retrieve error code if available.

      error_log('Affiliate form: Freemius API returned an error in response: ' . $freemius_error_message . ' (Code: ' . $freemius_error_code . ')');

      // Check for specific error messages or codes indicating a duplicate entry.
      if (
        strpos(strtolower($freemius_error_message), 'already exists') !== false || // Common message for duplicates
        strpos(strtolower($freemius_error_message), 'has already been taken') !== false || // Another common duplicate message
        $freemius_error_code === 'duplicate_entry' || // Specific code for duplicates (if provided by Freemius)
        ($freemius_error_code === 'validation_failed' && strpos(strtolower($freemius_error_message), 'email') !== false) // Validation error related to duplicate email
      ) {
        // Set a specific message for duplicate applications.
        $redirect_url = add_query_arg('message', 'duplicate_application', $redirect_url);
      } else {
        // For any other API errors, use a generic error message.
        $redirect_url = add_query_arg('message', 'api_error', $redirect_url);
      }
    } else {
      // Handle unexpected API response format (e.g., empty response, non-object).
      error_log('Affiliate form: Unexpected Freemius API response format or empty result.');
      $redirect_url = add_query_arg('message', 'api_error', $redirect_url);
    }
  } catch (Exception $e) {
    // Catch any PHP Exceptions thrown by the Freemius_Api class itself (e.g., network issues,
    // invalid API keys causing the class to fail instantiation or method calls).
    error_log('Affiliate form: Caught PHP Exception during Freemius API call: ' . $e->getMessage());
    $redirect_url = add_query_arg('message', 'api_error', $redirect_url);
  }

  // 5. REDIRECT THE USER
  wp_safe_redirect($redirect_url); // Safely redirect the user back to the referring page.
  exit; // Crucial to exit after redirect to prevent further script execution.
}
