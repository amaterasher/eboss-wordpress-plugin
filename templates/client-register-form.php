<?php
$apiClient = new eBossApiClient();
$data      = array();

if (isset($_POST['btnSubmit'])) {
		if ($_POST['btnSubmit'] == 'Send') {
				$data['added_on'] = date('Y-m-d H:i:s');;
				$data['created_from'] = 'web';

				if (isset($_POST['company_name']) && $_POST['company_name']) $data['company_name'] = $_POST['company_name'];
				if (isset($_POST['post_code']) && $_POST['post_code']) $data['postcode'] = $_POST['post_code'];
				if (isset($_POST['addr1']) && $_POST['addr1']) $data['address_one'] = $_POST['addr1'];
				if (isset($_POST['addr2']) && $_POST['addr2']) $data['address_two'] = $_POST['addr2'];
				if (isset($_POST['addr3']) && $_POST['addr3']) $data['address_three'] = $_POST['addr3'];
				if (isset($_POST['main_phone']) && $_POST['main_phone']) $data['phone'] = $_POST['main_phone'];

				$client = $apiClient->addClient($data);
				if (isset($client['id'])) {

						$contactsParam = array();
						if (isset($_POST['contact_name']) && $_POST['contact_name']) $contactsParam['name'] = $_POST['contact_name'];
						if (isset($_POST['email']) && $_POST['email']) $contactsParam['email'] = $_POST['email'];
						if (isset($_POST['main_phone']) && $_POST['main_phone']) $contactsParam['phone'] = $_POST['main_phone'];

						$contactsParam['client_id'] = $client['id'];
						$clientContact              = $apiClient->addClientContact($contactsParam);

						if (isset($clientContact['id'])) {
								wp_redirect('/thank-you/');
						}
				}

				$_SESSION['message'] = 'Problem in registration. Please try after some time';

				if (isset($apiClient->errors[0]['message'])) {
						$_SESSION['message'] = $apiClient->errors[0]['message'];
				}
		}
}
?>

<script type="text/javascript">
		jQuery(document).ready(function () {
				jQuery('#btnSubmit').click(function () {
						jQuery('#frmClientReg').validate({
								rules: {
										contact_name: "required",
										company_name: "required",
										email: {
												required: true,
												email: true
										},
										chkawt: "required"
								},
								messages: {
										contact_name: "Contact name is required",
										company_name: "Company name is required",
										email: {
												required: "Email is required",
												email: "Email must be in a correct format"
										},
										chkawt: "You must accept the web terms"
								},
								errorLabelContainer: "div#errorMessage"
						});
				});
		});
</script>

<form id="frmClientReg" method="post">

		<?php if ($_SESSION['message']) : ?>
				<?php
				$message = $_SESSION['message'];
				unset($_SESSION['message']);
				?>
				<div class="row">
						<div class="large-12 columns"><?php echo $message ?></div>
				</div>
				<br>
				<br>
		<?php endif; ?>

		<div class="row">
				<div class="large-6 columns">
						<label>Contact Name*</label>
						<input type="text" name="contact_name" id="contact_name" placeholder="required">
				</div>
				<div class="large-6 columns">
						<label>Company Name*</label>
						<input type="text" name="company_name" id="company_name" placeholder="required">
				</div>
		</div>

		<div class="row">
				<div class="large-6 columns">
						<label>Phone</label>
						<input type="text" name="main_phone" id="main_phone" placeholder="optional">
				</div>
				<div class="large-6 columns">
						<label>Email*</label>
						<input type="text" name="email" id="email" placeholder="required">
				</div>
		</div>

		<p class="t-five-divider">

		<div class="row">
				<div class="large-6 columns">
						<label>Address1</label>
						<input type="text" name="addr1" id="addr1" placeholder="optional">
				</div>
				<div class="large-6 columns">
						<label>Address2</label>
						<input type="text" name="addr2" id="addr2" placeholder="optional">
				</div>
		</div>

		<div class="row">
				<div class="large-6 columns">
						<label>Address 3</label>
						<input type="text" name="addr3" id="addr3" placeholder="optional">
				</div>
				<div class="large-6 columns">
						<label>Postcode</label>
						<input type="text" name="post_code" id="post_code" placeholder="optional">
				</div>
		</div>


		<div class="row">
				<div class="large-6 columns">
						<div class="row">

						</div>
				</div>
				<div class="large-6 columns">
						<div class="row">
								<div class="small-9 columns">
										<label for="chkawt" class="right inline">Accept Web Terms</label>
								</div>
								<div class="small-3 columns">
										<input type="checkbox" name="chkawt" id="chkawt">
								</div>
						</div>

				</div>
		</div>
		<input type="submit" name="btnSubmit" id="btnSubmit" value="Send" class="button small radius expand">


		<div class="row">
				<div class="large-12 columns">
						<div class="row">
								<div id="errorMessage"></div>
						</div>
				</div>
		</div>


</form>
