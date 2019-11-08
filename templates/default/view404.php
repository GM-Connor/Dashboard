<?php header("HTTP/1.0 404 Not Found"); ?>
<?php include "templates/beta/partials/header.php" ?>


<div id="error404" class="container">
	<div class="row">
		<div class="twelve columns">
			<div class="panel">
				<div class="panel-body">
					<div id="notfound">
						<div class="notfound">
							<div class="notfound-404">
								<h1>404</h1>
								<h2><?php echo isJP() ? 'このページがない' : 'Page not found' ?></h2>
							</div>
							<a href="<?php echo buildLink('/') ?>"><?php echo isJP() ? 'Top' : 'Homepage' ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<?php include "templates/beta/partials/footer.php" ?>