<?php
$apiClient = new eBossApiClient();
$params  = array();
$candidate = $apiClient->getCandidates(['id' => $_SESSION['eboss']['uid'] ]);
if ($_POST) {
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
}

?>

<script type="text/javascript">
		jQuery(document).ready(function() {
				jQuery("#region").click(function() {
						if (jQuery('#country').val() === "") {
								alert("You must select the country first");
						}
				});
				jQuery('#btnSubmit').click(function() {
						jQuery('#candidate_register').validate({
								rules: {
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
										fname: "First name is required",
										surname: "Surname is required",
										email: {
												required: "Email is required",
												email: "Email must be in a correct format"
										},
										telephone: "Telephone is required",
										country: "Country is required",
										region: "Region is required"
								},
								errorLabelContainer: "div#errorMessage"
						});
				});
		});
</script>


<form method="post" enctype="multipart/form-data" id="candidate_register" class="eboss-v3-form">

		<div class="row">
				<p><strong>Personal Details</strong></p>

				<div class="large-6 columns">
						<label for="fname">First Name*</label>
						<input type="text" name="fname" id="fname" placeholder="required" value="<?php echo $candidate['first_name']; ?>">
				</div>
				<div class="large-6 columns">
						<label for="surname">Surname*</label>
						<input type="text" name="surname" id="surname" placeholder="required" value="<?php echo $candidate['surname'];?>">
				</div>
		</div>

		<div class="row">
				<div class="large-6 columns">
						<label for="email">Email*</label>
						<input type="text" name="email" disabled="disabled" id="email" placeholder="required" value="<?php echo $candidate['email']; ?>">
				</div>
				<div class="large-6 columns">
						<label for="telephone">Telephone*</label>
						<input type="text" name="telephone" id="telephone" placeholder="required" value="<?php echo $candidate['phone']; ?>">
				</div>
		</div>

		<div class="row">
				<div class="large-6 columns">
						<label>Mobile</label>
						<input type="text" name="mobile" placeholder="optional" value="<?php echo $candidate['mobile_phone']; ?>">
				</div>
				<div class="large-6 columns">
						<label>Work Phone</label>
						<input type="text" name="work_phone" placeholder="optional" value="<?php echo $candidate['work_phone']; ?>">
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
