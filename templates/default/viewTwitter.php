
<?php $Twitter = $self['twitter']; ?>
<?php $Settings = $self['settings']; ?>

<?php include "templates/default/partials/header.php" ?>

<div id="main" class="col">
	<div id="page-title"><?php echo pageTitle(); ?></div>
	<div class="content">
		<?php 

		foreach ($Twitter->getFriendsTweets() as $tweet) {

			$id = $tweet['id'];
			$tweet = array_key_exists('retweeted_status', $tweet) ? $tweet['retweeted_status'] : $tweet; ?>
			
			<div class="entry">
				<img src="<?php echo $tweet['user']['profile_image_url'] ?>" alt="">
				<div class="user"><?php echo $tweet['user']['name'] ?></div>
				<div class="text"><?php echo preg_replace('/https:\/\/t.co.*/i', '', $tweet['text']) ?></div>

				<?php 
					if ($tweet['entities']['urls']) {
						foreach($tweet['entities']['urls'] as $url) {
							$url = $url['expanded_url'];
							echo "<div class=\"link\"><a href=\"${url}\" target=\"_blank\">${url}</a></div>";
						}
					}
				 ?>
				<div class="url"></div>
			</div>
			<hr>

		<?php }

		 ?>
	</div>
</div>

<?php 

foreach (array_slice($Twitter->getFriendsTweets(),0,1) as $tweet) {

	$id = $tweet['id'];
	$user_id = $tweet['user']['id'];
	$tweet = array_key_exists('retweeted_status', $tweet) ? $tweet['retweeted_status'] : $tweet; 

	$date = $tweet['created_at'];
	$date = explode(' ', $date);
	$date = array_splice($date, 1,2);
	$date = join($date, ' ');
	$date = preg_replace('/0([0-9])$/i', '${1}', $date);
?>

<div class="modal">
	<div class="inner">
		<div class="tweet row">
			<div class="col info">
				<img src="<?php echo $tweet['user']['profile_image_url'] ?>" alt="">
				<div class="title">
					<?php echo $tweet['user']['name'] ?>
					<span style="opacity: .5;font-weight: 400"><?php echo $date; ?></span>
				</div>
				<div class="text"><?php echo preg_replace('/https:\/\/t.co.*/i', '', $tweet['text']) ?></div>
			</div>
			<div class="col">
				<form method="post" action="">
					<input type="hidden" name="user_id" value="<?php echo $user_id ?>">
					<input type="hidden" name="tweet_id" value="<?php echo $id ?>">
					<input type="hidden" name="action" value="mark_as_read">
					<button>Next</button>
				</form>
			</div>
		</div>
		<div class="row frames">
			<?php 
				if ($tweet['entities']['urls']) {
					foreach($tweet['entities']['urls'] as $url) {
						$url = $url['expanded_url'];
						echo "<div class=\"col\"><iframe src=\"${url}\" frameborder=\"0\"></iframe></div>";
					}
				}
			 ?>
		</div>
	</div>
</div>

<?php } ?>

<div id="sidebar" class="col">
	<div id="sidebar-title">Channels</div>
	<div id="sidebar-title">Requirements</div>
</div>

<?php include "templates/default/partials/footer.php" ?>