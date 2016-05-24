<?php
$apiClient = new eBossApiClient();
if (isset($_GET['id'])) {
		$params = array(
			'id' => $_GET['id']
		);

		$job = $apiClient->getJobs($params);

		$jobTitles = array();
		foreach ($job['job-title'] as $title) {
				$jobTitles[] = $title['type'];
		}

		$country   = isset($job['country']) ? $job['country'] : '';
		$region    = isset($job['region']) ? $job['region'][0] : '';
		$companyId = isset($job['company']['id']) ? $job['company']['id'] : 0;

}

if ($_GET['action'] == 'apply' && isset($_SESSION['eboss']['uid']))
{
		$applicationParams = array (
			'candidate_id' => $_SESSION['eboss']['uid'] ,
			'job_id' => $_GET['id'],
			'is_current' => 1,
			'name' => 'application',
			'created_datetime' => date("Y-m-d H:i:s"),
			'client_id' => $companyId
		);

		$applicationResponse = $apiClient->createJobApplication($applicationParams);

		if ($applicationResponse) {
				$message = 'Successfully applied for this position';
				wp_redirect('/job-detail/?success=1&id=' . $_GET['id']);
		} else {
				$message = 'Problem in applying. Please try later';
		}
}
?>

<?php if ($_GET['success'] == 1):  ?>
<h4>Your application has been sent</h4>
<p><a href = "<?php echo site_url(); ?>/jobs/">View All Jobs</a></p>

<br>
<br>
<?php endif; ?>

<h3><?php echo isset($jobTitles[0]) ? $jobTitles[0] : $job['title'];?></h3>
<p><span>Job ID</span> : <span><?php echo $job['id']; ?></span></p>
<?php
$job_detail = $job['detail'];
$job_detail = str_replace(array('<div>', '</div>'), array('<p>', '</p>'), $job_detail);
$job_detail = strip_tags($job_detail, '<p><strong><b><br><br /><br/>');
$job_detail = preg_replace("/<p[^>]*>[\s|&nbsp;]*<\/p>/", '', $job_detail);
?>

<p><?php echo $job_detail; ?></p>
<p>
		<span>Salary</span> : <span><?php echo $job['salary'] ?></span><br/>
		<span>Country</span> : <span><?php echo $country['name'] ?></span><br/>
		<span>Region</span> : <span><?php echo $region['name'] ?></span>
</p>

<p>
		<?php
		$getQuery    = array(
			'id'         => $_GET['id'],
			'company-id' => $companyId
		);
		$queryString = http_build_query($getQuery);
		?>

		<?php if ($applied && $_SESSION['eboss']['uid']) : ?>
				You already applied for this job
		<?php elseif (!$_SESSION['eboss']['uid']): ?>
				<a href="/registration?<?php echo $queryString; ?>">Apply for this job</a>
		<?php else: ?>
				<a href="/job-detail?action=apply&<?php echo $queryString; ?>">Apply for this job</a>
		<?php endif; ?>
</p>

<?php if (!$_SESSION['eboss']['authenticated']): ?>
		<p><a href="/account-login/">Already registered. Login</a></p>
<?php endif; ?>

