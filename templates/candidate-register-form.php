<?php $apiClient = new eBossApiClient(); ?>

<?php
/** ======START FORM PROCESS ========  **/
if ($_POST['btnClear'] == 'Clear') {
		unset($_SESSION['parsed_detail']);
		wp_redirect('/registration?' . $_SERVER['QUERY_STRING']);
}

if (!empty($_POST)) {

		if ($_POST['btnSubmit1'] == 'Upload') {
				if (is_uploaded_file($_FILES['resume']['tmp_name'])) {
						if (!function_exists('wp_handle_upload')) {
								require_once(ABSPATH . 'wp-admin/includes/file.php');
						}

						$cv_data   = $apiClient->parseCvDocument($_FILES['resume']);

						$uploadedFile     = $_FILES['resume'];
						$uploadOverrides = array('test_form' => false);
						$movedFile         = wp_handle_upload($uploadedFile, $uploadOverrides);

						if ($movedFile) {
								$cv_data['uploaded_resume'] = $movedFile['file'];
								$_SESSION['parsed_detail'] = $cv_data;


								$getQuery = array();
								if (isset($_GET['company-id'])) {
										$getQuery['company-id'] = $_GET['company-id'];
								}

								if (isset($_GET['id'])) {
										$getQuery['id'] = $_GET['id'];
								}

								$queryString = http_build_query($getQuery);
								$redirect = '/registration?' . $queryString;


								header('location:' . $redirect);
								exit(0);
						} else {
								echo 'error uploading a file';
						}
				} else {
						echo 'error uploading a file';
				}
		}

		if ($_POST['btnSubmit'] == 'Send') {

				if (is_uploaded_file($_FILES['resume']['tmp_name'])) {
						if (!function_exists('wp_handle_upload')) {
								require_once(ABSPATH . 'wp-admin/includes/file.php');
						}
						$cv_data   = $apiClient->parseCvDocument($_FILES['resume']);
						$uploadedfile     = $_FILES['resume'];
						$upload_overrides = array('test_form' => false);
						$movefile         = wp_handle_upload($uploadedfile, $upload_overrides);

						if ($movefile) {
								$cv_data['uploaded_resume'] = $movefile['file'];
								$_SESSION['parsed_detail']  = $cv_data;
						}
				}

				$app_data = array(
					'added_datetime' => date("Y-m-d H:i:s"),
					'created_from' => 'web'
				);

				$app_data['current_duties'] = isset($_SESSION['parsed_detail']['UCCurrentDuties']) ? $_SESSION['parsed_detail']['UCCurrentDuties'] : '';
				$app_data['UCNMCNotes'] = isset($_SESSION['parsed_detail']['UCNMCNotes']) ? $_SESSION['parsed_detail']['UCNMCNotes'] : '';

				$jobId = 0;
				 if (isset($_POST['job_id'])) {
						 $jobId = $_POST['job_id'];
						 unset($_POST['job_id']);
				}
				unset($_POST['btnSubmit']);

				$postData = $_POST;
				foreach ($postData as $key => $value) {
						if (!empty($value)) {
								if ($key == 'job_type') {
										$key = 'job_type_id';
								} elseif ($key == 'country') {
										$key = 'country_id';
								} elseif ($key == 'region') {
										$key = 'region_id';
								}
								$app_data[$key] = $value;
						}
				}

				$candidate = $apiClient->addCandidate($app_data);
				if ($candidate) {
						$apiClient->uploadFile(
							$_SESSION['parsed_detail']['uploaded_resume'],
							$candidate['id'],
							basename($_SESSION['parsed_detail']['uploaded_resume'])
						);

						if ($jobId) {
								$applicationParams = array (
									'candidate_id' => $candidate['id'],
									'job_id' => $jobId,
									'is_current' => 1,
									'name' => 'application',
									'created_datetime' => date("Y-m-d H:i:s"),
									'client_id' => $_GET['company-id']
								);

								$applicationResponse = $apiClient->createJobApplication($applicationParams);

						}

						unset($_SESSION['parsed_detail']);
						wp_redirect('/thank-you/');
						exit;
				} else {
						$_SESSION['message'] = 'Problem in registration';
						if ($apiClient->errors) {
								$_SESSION['message'] = $apiClient->errors[0]['message'];
						}

						$getQuery = array('step' => 2);
						if (isset($_GET['company-id'])) {
								$getQuery['company-id'] = $_GET['company-id'];
						}

						if (isset($_GET['id'])) {
								$getQuery['id'] = $_GET['id'];
						}

						$queryString = http_build_query($getQuery);

						wp_redirect('/registration?' . $queryString);
						exit;
				}

		}
}

?>

<?php if ($_SESSION['message'] != ''): ?>
		<?php $message = $_SESSION['message']; ?>
		<div class="row">
				<div class="large-12 columns"><span class="error"><?php echo $message ?></span></div>
		</div>
		<?php unset($_SESSION['message']) ?>
<?php endif; ?>

<?php /** =============START STEP 2 =====================**/ ?>
<?php if ((isset($_GET['step']) &&  $_GET['step'] == 2 )|| isset($_SESSION['parsed_detail']['uploaded_resume'])): ?>

		<script type="text/javascript">

				jQuery(document).ready(function () {
						jQuery("#region").click(function () {
								if (jQuery('#country').val() === "") {
										alert("You must select the country first");
								}
						});
						jQuery('#btnSubmit').click(function () {
								jQuery('#candidate_register').validate({
										rules: {
												<?php if (!isset($_SESSION['parsed_detail']['uploaded_resume'])) : ?>
												resume: "required",
												<?php endif; ?>
												fname: "required",
												surname: "required",
												email: {
														required: true,
														email: true
												},
												telephone: "required",
												country: "required",
												region: "required"
										},
										messages: {
												<?php if (!isset($_SESSION['parsed_detail']['uploaded_resume'])) : ?>
												resume: "Resume is required",
												<?php endif; ?>
												fname: "First name is required",
												surname: "Surname is required",
												email: {
														required: "Email is required",
														email: "Email must be in a correct format",
												},
												telephone: "Telephone is required",
												country: "Country is required",
												region: "Region is required"
										},
										errorLabelContainer: "div#errorMessage"
								});
						});
				});</script>

		<form method="post" enctype="multipart/form-data" id="candidate_register" class="eboss-v3-form">

				<input type="hidden" name="job_id" value="<?php echo $_GET['id']; ?>">

				<?php if (!isset($_SESSION['parsed_detail']['uploaded_resume'])) : ?>
						<div class="row">
								<div class="large-12 columns">
										<label for="resume">Upload your resume*</label>
										<input type="file" name="resume" />
								</div>
						</div>
				<?php endif; ?>

				<div class="row">
						<p><strong>Personal Details</strong></p>

						<div class="large-6 columns">
								<label for="fname">First Name*</label>
								<input type="text" name="fname" id="fname" placeholder="required" value="<?php if (isset($_SESSION['parsed_detail']['first_name'])) echo $_SESSION['parsed_detail']['first_name']; ?>">
						</div>
						<div class="large-6 columns">
								<label for="surname">Surname*</label>
								<input type="text" name="surname" id="surname" placeholder="required" value="<?php if (isset($_SESSION['parsed_detail']['last_name'])) echo $_SESSION['parsed_detail']['last_name']; ?>">
						</div>
				</div>

				<div class="row">
						<div class="large-6 columns">
								<label for="email">Email*</label>
								<input type="text" name="email" id="email" placeholder="required" value="<?php if (isset($_SESSION['parsed_detail']['email'])) echo $_SESSION['parsed_detail']['email']; ?>">
						</div>
						<div class="large-6 columns">
								<label for="telephone">Telephone*</label>
								<input type="text" name="telephone" id="telephone" placeholder="required">
						</div>
				</div>

				<div class="row">
						<div class="large-6 columns">
								<label>Mobile</label>
								<input type="text" name="mobile" placeholder="optional" value="<?php if (isset($_SESSION['parsed_detail']['mobile_phone'])) echo $_SESSION['parsed_detail']['mobile_phone']; ?>">
						</div>
						<div class="large-6 columns">
								<label>Work Phone</label>
								<input type="text" name="work_phone" placeholder="optional">
						</div>
				</div>

				<div class="row">
						<p><strong>Employment Details</strong></p>
				</div>

				<div class="row">
						<div class="large-6 columns">
								<label for="job_title">Job Title</label>
								<?php $jobTitles = $apiClient->getJobTitles();  ?>
								<select size="4" multiple="multiple" name="job_title[]" id="job_title" class="multiple">
										<?php foreach ($jobTitles as $jobTitle): ?>
												<option value="<?php echo $jobTitle['id']; ?>"><?php echo $jobTitle['type'];?></option>
										<?php endforeach; ?>
								</select>
						</div>
						<div class="large-6 columns">
								<label for="industry">Industry</label>
								<?php  $industries = $apiClient->getIndustries();  ?>
								<select size="4" multiple="multiple" name="industry[]" id="industry" class="multiple">
										<?php foreach ($industries as $industry):  ?>
												<option value="<?php echo $industry['id']; ?>"> <?php echo $industry['area']; ?> </option>
										<?php endforeach; ?>
								</select>
						</div>
				</div>

				<div class="row">
						<div class="large-6 columns">
								<label for="job_type">Job Type</label>
								<?php  $jobTypes = $apiClient->getJobTypes();  ?>
								<select name="job_type" id="job_type">
										<option value="">Choose Below</option>
										<?php foreach ($jobTypes as  $jobType): ?>
											<option value="<?php echo $jobType['id'];  ?>"><?php echo $jobType['type']?></option>
										<?php endforeach; ?>

								</select>
						</div>
						<div class="large-6 columns">
								<label for="country">Country*</label>
								<?php  $countries = $apiClient->getCountries();  ?>
								<select  class="eboss-v3-country" id="country" name="country">
										<option value="">Choose Below</option>
										<?php foreach ($countries as $country): ?>
												<option value="<?php echo $country['id']; ?>"  <?php echo ($country['id'] == 216) ? "selected": ""; ?>> <?php echo $country['name']; ?> </option>
										<?php endforeach; ?>
								</select>
						</div>
				</div>


				<div class="row">
						<div class="large-6 columns">
						</div>
						<div class="large-6 columns">
								<label for="region">Region*</label>
								<select  class="eboss-v3-region"  id="region" name="region">
										<option value="">Choose Below</option>
								</select>
						</div>
				</div>

				<div class="row">
						<div class="large-6 columns">
								<input type="submit" name="btnClear" id="btnClear" value="Clear" class="button small radius expand">
						</div>
						<div class="large-6 columns">
								<input type="submit" name="btnSubmit" id="btnSubmit" value="Send" class="button small radius expand">
						</div>

				</div>

				<div class="row">
						<div class="large-12 columns">
								<div class="row">
										<div id="errorMessage"></div>
								</div>
						</div>
				</div>

		</form>
		<?php /** =============END STEP 2 =====================**/ ?>

<?php else : ?>


		<?php /** =============START STEP 1 =====================**/ ?>

		<script type="text/javascript">
				jQuery(document).ready(function()
				{
						jQuery('#btnSubmit1').click(function()
						{
								jQuery('#candidate_register1').validate({
										rules: {
												resume: "required"
										},
										messages: {
												resume: "Resume is required"
										},
										errorLabelContainer: "div#errorMessage"
								});
						});
				});
		</script>

		<form method="post" enctype="multipart/form-data" id="candidate_register1">
				<input type="hidden" name="job_id" value="<?php echo $_GET['id']; ?>" />
				<div class="row">
						<div class="row">
								<div class="large-12 columns">
										<label for="resume">Upload your resume*</label>
										<input type="file" name="resume" />
								</div>
						</div>
						<input type="submit" name="btnSubmit1" id="btnSubmit1" value="Upload" class="button small radius expand">

						<div class="row">
								<div class="large-12 columns">
										<div class="row">
												<div id="errorMessage"></div>
										</div>
								</div>
						</div>

						<div class="row">
								<div class="large-12 columns">
										<div class="row collapse">
												<p> or prefer to enter your details yourself, just
														<?php
														$redirect = '/registration?step=2';
														if (isset($_GET['id']))
																$redirect .= '&id=' . $_GET['id'];
														?>
														<a href="<?php echo $redirect; ?>"><strong>skip this</strong></a>, and we'll get you to the
														registration form for you.</p>
										</div>
								</div>
						</div>

				</div>
		</form>
<?php endif; ?>

<?php /** =============END STEP 1 =====================**/ ?>


