<?php include "templates/default/partials/header.php" ?>

<div id="main" class="col">
	<div id="page-title"><?php echo pageTitle(); ?></div>
	<div class="content">

		<div class="entry row">
			<form method="post" action="" class="col row" style="flex: 3">
				<input name="action" value="add" type="hidden">
				<input type="text" name="name" placeholder="New setting name" style="margin-right: 20px;">
				<input type="text" name="value" placeholder="New setting value" style="margin-right: 20px;">
				<button class="col">Add</button>
			</form>
		</div>

		<hr>

		<?php foreach ($self['settings']->list as $setting) { ?>
			
			<div class="entry row">
				<label class="col" style="flex: 2"><?php echo $setting[0] ?></label>
				<form method="post" action="" class="col row" style="flex: 3">
					<input name="action" value="update" type="hidden">
					<input name="name" value="<?php echo $setting[1] ?>" type="hidden">
					<input name="value" value="<?php echo $setting[2] ?>" type="hidden">
					<input name="new_value" type="text" value="<?php echo $setting[2] ?>" class="col" style="flex: 2;margin-right: 30px">
					<button class="col">Update</button>
				</form>
				<form method="post" action="" class="col row" style="flex: 1">
					<input name="action" value="delete" type="hidden">
					<input name="name" value="<?php echo $setting[1] ?>" type="hidden">
					<input name="value" value="<?php echo $setting[2] ?>" type="hidden">
					<button class="col">Delete</button>
				</form>
			</div>

		<?php } ?>
	</div>
</div>

<div id="sidebar" class="col"></div>

<?php include "templates/default/partials/footer.php" ?>