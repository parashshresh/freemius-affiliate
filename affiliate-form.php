<!DOCTYPE html>
<html>
<head><title>Affiliate Application</title></head>
<body>
<form method="POST" action="<?php echo get_template_directory_uri(); ?>/affiliate-form/affiliate-submit.php">
  <label>Name *</label>
  <input name="name" required />
  <label>Email *</label>
  <input name="email" type="email" required />
  <label>PayPal Email</label>
  <input name="paypal_email" type="email" />
  <label>Primary Domain *</label>
  <input name="domain" required />
  <label>Additional Domains (comma-separated)</label>
  <input name="additional_domains" />
  <fieldset>
    <legend>Promotional Methods</legend>
    <label><input type="checkbox" name="promotional_methods[]" value="social_media"> Social Media</label>
    <label><input type="checkbox" name="promotional_methods[]" value="mobile_apps"> Mobile Apps</label>
  </fieldset>
  <label>Reach / Stats Description</label>
  <textarea name="stats_description"></textarea>
  <label>Promotion Plan Description</label>
  <textarea name="promotion_method_description"></textarea>
  <button type="submit">Apply as Affiliate</button>
</form>
</body>
</html>
