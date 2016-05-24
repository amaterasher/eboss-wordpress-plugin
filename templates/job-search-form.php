<?php
$apiClient = new eBossApiClient();

?>

<form action="jobs" class="eboss-v3-form">
		<div id="side-template-five" class="panel">
				<h5>Job Search</h5>

				<label for="customDropdown1">Job Title</label>

				<?php $jobTitles = $apiClient->getJobTitles(); ?>
				<select class="small" name="title" id="job-title">
						<option value="">Choose Below</option>
						<?php foreach ($jobTitles as $jobTitle): ?>
								<option value="<?php echo $jobTitle['id']; ?>"> <?php echo $jobTitle['type']; ?> </option>
						<?php endforeach; ?>
				</select>

				<label for="customDropdown2">Industry</label>

				<?php $industries = $apiClient->getIndustries(); ?>
				<select class="medium" name="industry" id="industry">
						<option value="">Choose Below</option>
						<?php foreach ($industries as $industry): ?>
								<option value="<?php echo $industry['id']; ?>"> <?php echo $industry['area']; ?> </option>
						<?php endforeach; ?>
				</select>

				<label for="customDropdown4">Country</label>
				<?php $countries = $apiClient->getCountries(); ?>
				<select class="medium eboss-v3-country" name="country" id="country">
						<option value="">Choose Below</option>
						<?php foreach ($countries as $list): ?>
								<option
									value="<?php echo $list['id']; ?>" <?php echo ($list['id'] == 216) ? "selected" : ""; ?>> <?php echo $list['name']; ?> </option>
						<?php endforeach; ?>
				</select>
				<label for="customDropdown5">Regions</label>
				<select class="eboss-v3-region" id="region_dd" name="region" class="medium">
						<option value="">Choose Below</option>
				</select>

				<label>Keywords</label>
				<input type="text" name="keywords" placeholder="Enter keywords here" value="<?php echo $_GET['keywords'] ?>">
				<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="button small radius expand">
		</div>
</form>

