<?php
require_once __DIR__ . '/freemius/Freemius.php';
define('FS__API_SCOPE', 'developer');
define('FS__API_ENTITY_ID', 21113); // Replace with your developer ID
define('FS__API_PUBLIC_KEY', 'pk_b868d1fe04fdee48337bbd5a08f02'); // Replace with your public key
define('FS__API_SECRET_KEY', 'sk_uHpkBOnqEE@Y_QC==FAyWn115e?ZX'); // Replace with your secret key

$api = new Freemius_Api(FS__API_SCOPE, FS__API_ENTITY_ID, FS__API_PUBLIC_KEY, FS__API_SECRET_KEY);

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$paypal = trim($_POST['paypal_email'] ?? '');
$domain = trim($_POST['domain'] ?? '');
$additional = array_filter(array_map('trim', explode(',', $_POST['additional_domains'] ?? '')));
$methods = isset($_POST['promotional_methods']) ? implode(',', $_POST['promotional_methods']) : '';
$stats = trim($_POST['stats_description'] ?? '');
$promo_desc = trim($_POST['promotion_method_description'] ?? '');

try {
  $productID = 19858; // Replace with your product ID
  $affiliateProgramTermsID = 2236; // Replace with your terms ID

  $result = $api->Api(
    "/plugins/{$productID}/aff/{$affiliateProgramTermsID}/affiliates.json",
    'POST',
    [
      'name'                         => $name,
      'email'                        => $email,
      'paypal_email'                 => $paypal,
      'domain'                       => $domain,
      'additional_domains'           => $additional,
      'promotional_methods'          => $methods,
      'stats_description'            => $stats,
      'promotion_method_description' => $promo_desc,
      'state'                        => 'pending',
    ]
  );
  echo "<h3>Thanks, application submitted successfully!</h3>";
} catch (Exception $e) {
  echo "<h3>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
