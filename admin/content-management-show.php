<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } ?>

<?php

$sqlQuery = $wpdb->prepare("SELECT *  FROM `" . EBOSS_API_V3_TABLE . "` WHERE `id` = %d", array(1));
$data = array();
$data = $wpdb->get_row($sqlQuery, ARRAY_A);

$consumerKey = isset($data['consumer_key']) ? $data['consumer_key'] : "";
$consumerSecret = isset($data['consumer_secret']) ? $data['consumer_secret'] : "";
$myOfficeUsername = isset($data['myoffice_username']) ? $data['myoffice_username'] : "";
$myOfficePassword = isset($data['myoffice_password']) ? $data['myoffice_password'] : "";
$myOfficeUrl = isset($data['myoffice_url']) ? $data['myoffice_url'] : "";

$isSuccess = 1;

if (isset($_POST) && !empty($_POST)) {
		$consumerKey = isset($_POST['eboss-consumer-key']) ? $_POST['eboss-consumer-key'] : "";
		$consumerSecret = isset($_POST['eboss-consumer-secret']) ? $_POST['eboss-consumer-secret'] : "";
		$myOfficeUsername = isset($_POST['eboss-username']) ? $_POST['eboss-username'] : "";
		$myOfficePassword = isset($_POST['eboss-password']) ? $_POST['eboss-password'] : "";
		$myOfficeUrl = isset($_POST['eboss-api-url']) ? $_POST['eboss-api-url'] : "";

		if ($data) {
				$sSql = $wpdb->prepare(
					"UPDATE `" . EBOSS_API_V3_TABLE . "`
				SET `consumer_key` = %s,
				`consumer_secret` = %s,
				`myoffice_username` = %s,
				`myoffice_password` = %s,
				`myoffice_url` = %s
				WHERE id = %d
				",
					array(
						$consumerKey,
						$consumerSecret,
						$myOfficeUsername,
						$myOfficePassword,
						$myOfficeUrl,
						1
					));

				$wpdb->query($sSql);

				if ($wpdb->last_error) {
						$isSuccess = 0;
				}


		} else {
				$sql = $wpdb->prepare(
					"INSERT INTO `" . EBOSS_API_V3_TABLE .
					"` (`consumer_key`, `consumer_secret`, `myoffice_username`, `myoffice_password`, `myoffice_url`) VALUES(%s, %s, %s, %s, %s)",
					array(
						$consumerKey,
						$consumerSecret,
						$myOfficeUsername,
						$myOfficePassword,
						$myOfficeUrl
					));
				$wpdb->query($sql);
				if ($wpdb->last_error) {
						$isSuccess = 0;
				}
		}
}

?>

<div class="container">
		<h1>eBoss API V3 Integration Setup</h1>
		<?php if (isset($_POST) && $isSuccess): ?>
				<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
						<p><strong>Settings saved.</strong></p>
								<button class="notice-dismiss" type="button">
										<span class="screen-reader-text">Dismiss this notice.</span>
								</button>
				</div>
		<?php endif; ?>
		<form method="post">
				<table class="form-table">
						<tr>
								<th scope="row">Consumer Key</th>
								<td>
										<input type="text" class="form-control" name="eboss-consumer-key" placeholder="Consumer key" style="width: 30%;" value="<?php echo $consumerKey; ?>">
								</td>
						</tr>
						<tr>
								<th scope="row">Consumer Secret</th>
								<td>
										<input type="text" class="form-control" name="eboss-consumer-secret" placeholder="Consumer Secret" style="width: 30%;" value="<?php echo $consumerSecret; ?>">
								</td>
						</tr>
						<tr>
								<th scope="row">eBoss Username</th>
								<td>
										<input type="text" class="form-control" name="eboss-username" placeholder="Username" style="width: 30%;" value="<?php echo $myOfficeUsername; ?>">
								</td>
						</tr>
						<tr>
								<th scope="row">eBoss Password</th>
								<td>
										<input type="password" class="form-control" name="eboss-password" style="width: 30%;" value="<?php echo $myOfficePassword; ?>">
								</td>
						</tr>
						<tr>
								<th scope="row">API URL</th>
								<td>
										<input type="text" class="form-control" name="eboss-api-url" placeholder="demo.api.recruits-online.com" style="width: 30%;" value="<?php echo $myOfficeUrl; ?>">
								</td>
						</tr>

				</table>
				<p class="submit">
						<input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit">
				</p>
		</form>
		<br/>
		<br/>
		<h2>Short Code List</h2>
		<label style="font-weight: 300;">Paste it on any page you want to render the corresponding component.</label>
		<br>
		<br>
		<table class="form-table">
				<?php

						$shortcodes = array(
								"eboss_v3_list_jobs" => "List all jobs. (Supports filter from job search)",
								"eboss_v3_job_search" => "Renders job search form.",
								"eboss_v3_list_news" => "List all news.",
								"eboss_v3_candidate_reg_form" => "Candidate registration form.",
								"eboss_v3_client_registration_form" => "Client registration form.",
								"eboss_v3_job_detail" => "Job detail. Renders job information and apply button",
								"eboss_v3_account_login" => "Candidate login form.",
								"eboss_v3_account_logout" => "Candidate logout",
								"eboss_v3_candidate_profile" => "Candidate profile",
								"eboss_v3_account_settings" => "Candidate account. Renders change new password form.",
						);

					?>
				<?php foreach ($shortcodes as $key => $value ): ?>
						<tr>
								<td>
										<p>[<?php echo $key; ?>] </p>
								</td>
								<td>
										<p style="font-weight: 300;"><?php echo $value; ?></p>
								</td>
						</tr>
				<?php endforeach; ?>
		</table>
</div>
