<style>

	.del {width:12px; height:12px; background-image: url(img/Admin/icon/del.gif);}

</style>


<div class="card">
	<div class="card-header">حظر لاعب</div>
	<div class="card-body">
		<form action="" method="get">
		<input name="action" type="hidden" value="addBan">
		<div class="form-group">
		<label>الاعب</label>
		<select name="uid" class="form-control">
		<?php foreach($database->query('SELECT username,id FROM users WHERE id > 6') as $user): ?>
			<option value="<?php echo $user['id']; ?>" <?php if($_GET['uid'] == $user['id']){ echo 'selected'; } ?>><?php echo $user['username']; ?></option>
		<?php endforeach; ?>
		</select>
		</div>
		<div class="form-group">
		<label>السبب</label>
		<select name="reason" class="form-control">
			<?php
				$arr = array('عقاب','الغش','هاك','ثغرة','إسم مخالف','تعدد الحسابات','السب');
				foreach($arr as $r){
					echo '<option value="'.$r.'">'.$r.'</option>';
				}
			?>
		</select>
		</div>
		<div class="form-group">
		<label>المدة</label>
		<select name="time" class="form-control">
			<?php
				$arr = array(1,2,5,10,12);
				foreach($arr as $r){
					echo '<option value="'.($r*3600).'">'.$r.' ساعة</option>';
				}

				$arr2 = array(1,2,5,10,30,50,90);
				foreach($arr2 as $r){
					echo '<option value="'.($r*3600*24).'">'.$r.' يوم</option>';
				}
				echo '<option value="">للأبد</option>';
			?>
			</select>
		</div>
			<button  class="btn btn-primary">تأكيد</button>
		</form>
	</div>
</div>




<?php

$bannedUsers = $admin->search_banned();

?>



<table class="table mt-5 mb-5" cellpadding="1" cellspacing="1">

	<thead>

		<tr>

			<th colspan="6">الاعبين المحظورين (<?php echo count($bannedUsers); ?>)</th>

		</tr>

		<tr>

			<td><b>الاعب</b></td>

			<td><b>المدة (من/إلي)</b></td>

			<td><b>السبب</b></td>

			<td></td>

		</tr>

		</thead>

		<tbody>

		<?php

			if($bannedUsers)

			{

				for ($i = 0; $i <= count($bannedUsers)-1; $i++)

				{

					if($database->getUserField($bannedUsers[$i]['uid'],'username',0)=='')

					{

						$name = $bannedUsers[$i]['name'];

						$link = "<span class=\"c b\">[".$name."]</span>";

					}

					else

					{

						$name = $database->getUserField($bannedUsers[$i]['uid'],'username',0);

						$link = '<a href="?p=player&uid='.$bannedUsers[$i]['uid'].'">'.$name.'<a/>';

					}

					if($bannedUsers[$i]['end'])

					{

						$end = date("d.m.y H:i",$bannedUsers[$i]['end']);

					}

					else

					{

						$end = '*';

					}

					echo '

					<tr>

						<td>'.$link.'</td>

						<td ><span class="f7">'.date("d.m.y H:i",$bannedUsers[$i]['time']).' - '.$end.'</td>

						<td>'.$bannedUsers[$i]['reason'].'</td>

						<td class="on"><a href="?action=delBan&uid='.$bannedUsers[$i]['uid'].'&id='.$bannedUsers[$i]['id'].'" onClick="return del(\'unban\',\''.$name.'\')"><img src="../img/Admin/del.gif" class="del" title="cancel" alt="cancel"></img></a></td>

					</tr>';

				}

			}

			else

			{

				echo '<tr><td colspan="6" class="on">لا يوجد لاعبين محظورين</td></tr>';

			}

		?>

	</tbody>

</table>