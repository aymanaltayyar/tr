<div class="card">
	<div class="card-header">توزيع ذهب لجميع الأعضاء</div>
	<div class="card-body">
	<?php 
		if(isset($_POST['addGold'])){
			if(is_numeric($_POST['gold'])){
				$database->query('UPDATE users SET gold = gold + '.$_POST['gold'].'');
				echo '<div class="alert alert-success">تم إضافة '.$_POST['gold'].' ذهبة للكل.</div>';

			}
		}
	?>
	
		<form action="" method="post">
		<div class="form-group">
				<label>كمية الذهب</label>
				<input class="form-control" name="gold" type="number" required>
			</div>
		<div class="form-group">
			<button class="btn btn-primary" name="addGold">إضافة</button>
		</div>
		</form>
	</div>
</div>
