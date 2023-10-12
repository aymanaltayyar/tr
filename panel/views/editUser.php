<?php
$id = $_GET['uid'];
if(isset($id))
{
	$user = $database->getUserArray($id,1);
	$varray = $database->getProfileVillages($id);
	$varmedal = $database->getProfileMedal($id); ?>
<div class="card">
	<div class="card-header">الاعب <a href="index.php?p=player&uid=<?php echo $user['id']; ?>"><?php echo $user['username']; ?></a></div>
	<div class="card-body">
		<form action="../application/panel/Mods/editUser.php" method="POST">

		<input type="hidden" name="admid" id="admid" value="<?php echo $_SESSION['id']; ?>">
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<div class="row">
			<div class="col-md-6">
				التفاصيل<hr>
				<div class="form-group">
					<label>القبيلة</label>
					<select name="tribe" class="form-control">
						<option value="1" <?php if($user['tribe'] == 1) { echo "selected='selected'"; } else {} ?>>الرومان</option>
						<option value="2" <?php if($user['tribe'] == 2) { echo "selected='selected'"; } else {} ?>>الجرمان</option>
						<option value="3" <?php if($user['tribe'] == 3) { echo "selected='selected'"; } else {} ?>>الإغريق</option>
						<option value="4" <?php if($user['tribe'] == 4) { echo "selected='selected'"; } else {} ?>>وحوش</option>
						<option value="5" <?php if($user['tribe'] == 5) { echo "selected='selected'"; } else {} ?>>التتار</option>
					</select>
				</div>
				<div class="form-group">
					<label>السكن</label>
					<input class="form-control" name="location" value="<?php echo $user['location']; ?>">
				</div>
				<div class="form-group">
					<label>البريد الإلكتروني</label>
					<input class="form-control" name="email" value="<?php echo $user['email']; ?>">
				</div>
				<hr>
				<a href="?p=player&uid=<?php echo $user['id']; ?>"><span class="rn2" >&raquo;</span> العودة</a>
				<hr>
				<div class="form-group">
					
					<textarea class="form-control" style="height: 150px !important;" cols="25" rows="14" tabindex="1" name="desc1"><?php echo nl2br($user['desc1']); ?></textarea>
				</div>

			</div>
			<div class="col-md-6">
		الوصف<hr>
		<div class="form-group">

		<textarea class="form-control" style="height: 492px !important;" tabindex="8" cols="30" rows="20" name="desc2"><?php echo nl2br($user['desc2']); ?></textarea>
</div>
</div>
</div>

	<table cellspacing="1" cellpadding="2" class="table">

		<thead>

			<tr>

				<th colspan="4">الأوسمة</th>

			</tr>

			<tr>

				<td>القسم</td>

				<td>التصنيف</td>

				<td>الاسبوع</td>

				<td>BB كود</td>

			</tr>

		</thead>

		<tbody>

			<?php

				foreach($varmedal as $medal)

				{

					$titel="إضافي";

					switch ($medal['categorie']){
						case"1":$titel="مهاجموا الأسبوع";break;
						case"2":$titel="مدافعوا الأسبوع";break;
						case "3":$titel="مطوروا الأسبوع";break;
						case "4":$titel="سارقوا الأسبوع";break;
					}

					echo"

					<tr>

						<td> ".$titel."</td>

						<td>".$medal['plaats']."</td>

						<td>".$medal['week']."</td>

						<td>[#".$medal['id']."]</td>

					</tr>";

				}

			?>

			<tr>

				<td>حماية المبتدئين</td>

				<td></td>

				<td></td>

				<td>[#0]</td>

			</tr>

			<tr>

				<td></td>

				<td></td>

				<td></td>

				<td></td>

			</tr>

		</tbody>

		<tfoot>

			<tr>

				<td colspan="4">

					<center>
						<button type="submit" class="btn btn-primary">حفظ</button>
					</center>

				</td>

			</tr>

		</tfoot>

	</table>

	</form>
	</div>
	</div>

	<?php

}

else

{

	echo "<br /><br />Not found. <a href=\"javascript: history.go(-1)\"> Go Back</a>";

}

?>