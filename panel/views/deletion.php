<?php


if($_GET['uid'])
{

	$user = $database->getUserArray($_GET['uid'],1);
	$varray = $database->getProfileVillages($_GET['uid']);
	if($user){
		$totalpop = 0;
		foreach($varray as $vil){
			$totalpop += $vil['pop'];
		} ?>


		<style>
			.del {width:12px; height:12px; background-image: url(img/Admin/icon/del.gif);}
		</style>

		<form action="" method="post">
			<input type="hidden" name="action" value="DelPlayer">
			<input type="hidden" name="uid" value="<?php echo $user['id'];?>">
			<input type="hidden" name="admid" id="admid" value="<?php echo $_SESSION['id']; ?>">

			<table class="table">
				<thead>
					<tr>
						<th colspan="4">حذف لاعب</th>
					</tr>
				</thead>

				<tbody>

					<tr>

						<td>الاسم:</td>
						<td><a href="?p=player&uid=<?php echo $user['id'];?>"><?php echo $user['username'];?></a></td>
						<td>الذهب:</td>
						<td><?php echo $user['gold'];?></td>

					</tr>

					<tr>
						<td>الترتيب:</td>
						<td>???.</td>
						<td>السكان:</td>
						<td><?php echo $totalpop;?></td>

					</tr>

					<tr>

						<td>القري:</td>
						<td>
							<?php
								echo $database->queryNumRow("SELECT SQL_CACHE * FROM vdata WHERE owner = ".$user['id']."");
							?>
						</td>

						<td><b><font color='#71D000'>P</font><font color='#FF6F0F'>l</font><font color='#71D000'>u</font><font color='#FF6F0F'>s</font></b>:</td>

						<td>

							<?php
							if($user['plus']){
								$plus = date('d.m.Y H:i',$user['plus']);
								echo $plus;

							}else{
								echo '-';
							}
							?>

						</td>

					</tr>
					<tr>
						<td>التحالف:</td>
						<td><?php echo $database->getAllianceName($user['alliance']);?></td>
						<td>الحالة:</td>
						<td>-</td>
					</tr>
					<tr>
						<td colspan="4" class="empty"></td>
					</tr>
					<tr>
						<td>كلمة المرور:</td>
						<td><input type="text" name="pass"></td>
						<td colspan="2"><input type="submit" class="c5" value="حذف الاعب"></td>

					</tr>

				</tbody>

			</table>

			<br /><br /><font color="Red"><b>هام: البيانات غير قابلة للاستعادة بعد الحذف!</font></b><br /><br />




		</form>
		
		<?php

	if($_GET['msg'])

	{

		echo '<div style="margin-top: 50px;" class="b"><center>';

		if($_GET['msg'] == 'ursdel')

		{

			echo "كلمة المرور خاطئة.";



		}

		

		echo '</center></div>';

	}

?>
		<?php

	}

}

else

{

	include("404.php");

}

?>