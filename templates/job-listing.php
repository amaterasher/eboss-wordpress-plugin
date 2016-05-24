<h5>All Jobs</h5>
<?php

$apiClient = new eBossApiClient();

$params = array();

if (isset($_GET['title']) && (int)$_GET['title']) {
		$params['job_title_id'] = $_GET['title'];
}

if (isset($_GET['country']) && $_GET['country']) {
		$params['country_id'] = $_GET['country'];
}

if (isset($_GET['region']) && $_GET['region']) {
		$params['region_id'] = $_GET['region'];
}

if (isset($_GET['industry']) && $_GET['industry']) {
		$params['industry_id'] = $_GET['industry'];
}

if (isset($_GET['salary']) && $_GET['salary']) {
		$params['salary'] = $_GET['salary'];
}

if (isset($_GET['keywords']) && $_GET['keywords']) {
		$params['keywords'] = $_GET['keywords'];
}

$jobs = $apiClient->getJobs($params, true);
?>

<?php if ($jobs): ?>
		<?php foreach ($jobs as $item): ?>

				<?php
				$jobTitles = array();
				foreach ($item['job-title'] as $title) {
						$jobTitles[] = $title['type'];
				}

				$companyId      = isset($item['company']['id']) ? $item['company']['id'] : "";
				$country        = isset($item['country']['name']) ? $item['country']['name'] : '';
				$region         = isset($item['region'][0]['name']) ? $item['region'][0]['name'] : '';
				$slugJobTitle   = isset($jobTitles[0]) ? eBossApiClient::sluggify($jobTitles[0]) : "";
				$jobDescription = isset($item['detail']) ? $item['detail'] : "";
				?>


				<div class="panel">
						<div class="row">
								<div class="large-9 columns">
										<h6><a
													href="<?php echo site_url() ?>/job-detail?title=<?php echo $slugJobTitle; ?>&id=<?php echo $item['id'] ?>"><?php echo $item['title'] ?></a>
										</h6>
								</div>
								<div class="large-3 columns">
										<?php if (isset($_SESSION['eboss']['authenticated'])) : ?>
												<a class="button small radius expand"
												   href="<?php echo site_url() ?>/job-detail?title=<?php echo $slugJobTitle; ?>&id=<?php echo $item['id'] ?>">Apply</a>
										<?php else: ?>

												<a class="button small radius expand"
												   href="<?php echo site_url() ?>/registration?id=<?php echo $item['id'] . '&company-id=' . $companyId; ?>">Apply</a>
										<?php endif; ?>
								</div>
						</div>
						<div class="row">
								<div class="large-12 columns">
										<p>
												<span class="small">Country</span> : <span><?php echo $country; ?></span>
												<span class="small">Region</span> : <span><?php echo $region; ?></span>
										</p>

										<?php $jobdesc = strip_tags($jobDescription); ?>
										<?php if (strlen($jobdesc) > 150): ?>
												<p><?php echo substr($jobdesc, 0, 150) ?>...
														<a
															href="<?php echo site_url() ?>/job-detail?title=<?php echo $slugJobTitle; ?>&id=<?php echo $item['id'] ?>">Read
																More...</a>
												</p>
										<?php else : ?>
												<p><?php echo $jobdesc; ?></p>
										<?php endif; ?>
								</div>
						</div>
				</div>
		<?php endforeach; ?>

<?php else : ?>
		No jobs found
<?php endif; ?>
