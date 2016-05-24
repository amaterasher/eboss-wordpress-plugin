<?php
if ($_POST['btnSubmit'] == 'Send') {
		$apiClient = new eBossApiClient();
		$params  = array();

		if (!empty($_POST['username'])) $params['login'] = $_POST['username'];
		if (!empty($_POST['password'])) $params['password'] = $_POST['password'];

		$candidate = $apiClient->candidateLogin($params, true);

		if (isset($candidate[0]['id'])) {
				$_SESSION['eboss']['authenticated'] = true;
				$_SESSION['eboss']['uid']           = $candidate[0]['id'];
				$_SESSION['eboss']['email']         = $candidate[0]['login'];
				$_SESSION['eboss']['user']          = 'candidate';
				wp_redirect('/jobs/');
		} else {
				$_SESSION['message'] = 'Problem in authentication';
		}

}
?>

<script type="text/javascript">
		jQuery(document).ready(function()
		{
				jQuery('#btnSubmit').click(function()
				{
						jQuery('#frmLogin').validate({
								rules: {
										username: "required",
										password: "required",
										email : {
												"required": true,
												"email" : true
										}
								},
								messages: {
										username: "Username is required",
										password: "Password is required",
										email: "Email is required",
								},
								errorLabelContainer: "div#errorMessage"
						});
				});
		});
</script>

<form id="frmLogin" method="post">
		<?php if ($_SESSION['message'] != ''): ?>
				<?php $message = $_SESSION['message']; ?>
				<div class="row">
						<div class="large-12 columns"><span class="error"><?php echo $message ?></span></div>
				</div>
				<?php unset($_SESSION['message']) ?>
		<?php endif; ?>



		<?php if (isset($_GET['action'])) : ?>
				<div class="row" style="padding:8px;">
						<div class="large-12 columns">
								<span>You will receive a new password via email.</span>
						</div>
				</div>
				<div class="row">
						<div class="large-6 columns">
								<label>Email*</label>
								<input type="text" name="email" id="email" placeholder="required">
						</div>
				</div>
				<div class="row">
						<div class="large-12 columns">
								&nbsp;
						</div>
				</div>

				<input type="submit" name="btnSubmit" id="btnSubmit" value="Get New Password" class="button small radius expand">

				<div class="row">
						<div class="large-12 columns">
								<a href="<?php echo site_url(); ?>/account-login">Login</a>
						</div>
				</div>
		<?php else: ?>
				<div class="row">
						<div class="large-6 columns">
								<label>Username*</label>
								<input type="text" name="username" id="username" placeholder="required">
						</div>
						<div class="large-6 columns">
								<label>Password*</label>
								<input type="password" name="password" id="password" placeholder="required">
						</div>
				</div>
				<div class="row">
						<div class="large-12 columns">
								<div class="pull-left columns large-6 ">
										<a href="<?php echo site_url(); ?>/registration/">Register</a>
								</div>
								<div class="pull-right columns large-6 ">
										<a  href="<?php echo site_url(); ?>/account-login?action=lostpassword">Lost your password?</a>
								</div>
						</div>
				</div>
				<div class="row">
						<div class="large-12 columns">
								&nbsp;
						</div>
				</div>

				<input type="submit" name="btnSubmit" id="btnSubmit" value="Send" class="button small radius expand">

		<?php endif; ?>

		<div class="row">
				<div class="large-12 columns">
						<div class="row">
								<div id="errorMessage"></div>
						</div>
				</div>
		</div>

</form>
