<h5>Latest News</h5>
<?php
$apiClient = new eBossApiClient();
$news      = $apiClient->getNews();
?>

<div>
		<?php if (is_array($news) && $news): ?>
				<?php foreach ($news as $item ) : ?>
				<p>
						<h6><?php echo $item['title']; ?></h6>
						<p>
							<?php echo $item['text']; ?>

						</p>
				</p>
				<?php endforeach; ?>
		<?php endif; ?>
</div>
