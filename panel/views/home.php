<div class="card">
	<div class="card-header"><?php echo $lang['Admin']['home01']; ?></div>
	<div class="card-body">
	<?php echo $lang['Admin']['home02']; ?> <b><?php echo $_SESSION['username']; ?></b>, <?php echo $lang['Admin']['home03']; ?>: <b><font color="Red"><?php echo $lang['Admin']['home04']; ?></font></b>
	</div>
</div>

<div class="row mt-3">
	<div class="col-md-4">
		<div class="card">
			<div class="card-header"><?php echo $lang['Admin']['home05']; ?></div>
			<div class="card-body">
				<div class="card-title"><b class="h2"><?php echo $database->queryNumRow('SELECT * FROM users'); ?></b> <?php echo $lang['Admin']['home06']; ?></div> 
				<p class="card-text"><?php echo $lang['Admin']['home07']; ?>.</p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-header"><?php echo $lang['Admin']['home08']; ?></div>
			<div class="card-body">
				<div class="card-title"><b class="h2"><?php echo $database->queryNumRow('SELECT * FROM payments'); ?></b> <?php echo $lang['Admin']['home09']; ?></div> 
				<p class="card-text"><?php echo $lang['Admin']['home10']; ?>.</p>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-header"><?php echo $lang['Admin']['home11']; ?></div>
			<div class="card-body">
				<?php 
					$fullM = 0;
					$paymentsD = $database->query('SELECT cost FROM payments');

					foreach($paymentsD as $payment){
						$fullM = $fullM + $payment['cost'];
					}
				?>
				<div class="card-title"><b class="h2"><?php echo $fullM; ?></b>$</div> 
				<p class="card-text"><?php echo $lang['Admin']['home12']; ?>.</p>
			</div>
		</div>
	</div>
</div>