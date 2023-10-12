<div class="card mb-5">
	<div class="card-header">البحث</div>
	<div class="card-body">
	<form action="" method="post">
		<table class="table">
			<tr>
				<td>
					<select name="p" size="1" class="form-control">
						<option value="player" <?php if($_POST['p']=='player'){echo "selected";}?>>بحث عن لاعب</option>
						<option value="alliances" <?php if($_POST['p']=='alliances'){echo "selected";}?>>بحث عن تحالف</option>
						<option value="villages" <?php if($_POST['p']=='villages'){echo "selected";}?>>بحث عن قرية</option>
						<option value="email" <?php if($_POST['p']=='email'){echo "selected";}?>>بريد عن بريد</option>
						<option value="ip" <?php if($_POST['p']=='ip'){echo "selected";}?>>بحث أي بي IPs</option>
						<option value="deleted_players" <?php if($_POST['p']=='deleted_players'){echo "selected";}?>>بحث لاعب محذوف</option>
					</select>
				</td>
				<td>
					<input name="s" class="form-control" value="<?php echo $_POST['s'];?>">
				</td>
				<td>
					<button type="submit" class="btn btn-primary">ابحث</button>
				</td>
			</tr>
		</table>
	</form>

	</div>
</div>



<?php

	if($_GET['msg'])

	{

		echo '<div style="margin-top: 50px;" class="b"><center>';

		if($_GET['msg'] == 'ursdel')

		{

			echo "الاعب تم حذفه.";



		}

		

		echo '</center></div>';

	}

?>