<?php
$apiClient = new eBossApiClient();
$params = array();
$candidateId = $_SESSION['eboss']['uid'];

$candidate = $apiClient->getUserById($candidateId);
if ($_POST && $candidateId) {
		$_SESSION['message'] = "Unable to update password";
		$candidate = $apiClient->getUserById($candidateId);

		if ($candidate['password'] && isset($_POST['cur_pass'])) {
				if (eBossApiClient::checkUserPassword($candidate['password'], $_POST['cur_pass'])) {

						if ($_POST['password2']) {
								$params['password'] = $_POST['password2'];
								$isUpdated = $apiClient->updateUser($candidateId, $params);

								if ($isUpdated) {
										$_SESSION['message'] = "Your password has been updated successfully";
								}
						}

				} else {
						$_SESSION['message'] = "Current Password is incorrect.";
				}
		}
}

?>

<script type="text/javascript">
		jQuery(document).ready(function()
		{
				jQuery('#btnSubmit').click(function()
				{
						jQuery('#frmChangePass').validate({
								rules: {
										cur_pass: "required",
										password1: "required",
										password2: {
												equalTo: '#password1'
										}

								},
								messages: {
										cur_pass: "Username is required",
										password1: "Password is required",
										password2: "Confirm Password must match"
								},
								errorLabelContainer: "div#errorMessage"
						});
				});
		});
</script>

<form id="frmChangePass" method="post">

		<?php
		if ($_SESSION['message']): ?>
				<?php
				$message = $_SESSION['message'];
				unset($_SESSION['message']);
				?>
				<div class="row">
						<div class="large-12 columns"><?php echo $message ?></div>
				</div>
		<?php endif; ?>
		<br/>
		<br/>
		<div class="row">
				<div class="large-12 columns">
						<label>Current Password*</label>
						<input type="password" name="cur_pass" id="cur_pass" placeholder="required">
				</div>
		</div>
		<div class="row">
				<div class="large-12 columns">
						<label>New Password*</label>
						<input type="password" name="password1" id="password1" placeholder="required">
				</div>
		</div>
		<div class="row">
				<div class="large-12 columns">
						<label>Repeat Password*</label>
						<input type="password" name="password2" id="password2" placeholder="required">
				</div>
		</div>

		<div class="row">
				<div class="large-12 columns">
						&nbsp;
				</div>
		</div>
		<input type="submit" name="btnSubmit" id="btnSubmit" value="Update" class="button small radius expand">

		<div class="row">
				<div class="large-12 columns">
						<div class="row">
								<div id="errorMessage"></div>
						</div>
				</div>
		</div>
</form>
