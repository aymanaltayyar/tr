<?php

$active = $admin->getUserActive();

?>

<style>

.del {width:12px; height:12px; background-image: url(img/Admin/icon/del.gif);}

</style>



<table class="table" style="background-color:white;">

  <thead>

	<tr>

		<th colspan="6">الاعبين المتواجدين (<?php echo count($active);?>)</th>

	</tr>

  </thead>

	<tr>

		<td>الاسم [الصلاحية]</td>

		<td>الوقت</td>

		<td>القبيلة</td>

		<td>السكان</td>

		<td>القري</td>

		<td>الذهب</td>

	</tr>

<?php



if($active){

for ($i = 0; $i <= count($active)-1; $i++) {

$uid = $database->getUserField($active[$i]['username'],'id',1);

$varray = $database->getProfileVillages($uid);

$totalpop = 0;

foreach($varray as $vil) {

	$totalpop += $vil['pop'];

}

		if($active[$i]['tribe'] == 1){

		$tribe = "الرومان";

		} else if($active[$i]['tribe'] == 2){

		$tribe = "الجرمان";

		} else if($active[$i]['tribe'] == 3){

		$tribe = "الإغريق";

		}else if($active[$i]['tribe'] == 6){

		$tribe = "جديد";

		}

echo '

	<tr>

		<td><a href="?p=player&uid='.$uid.'">'.$active[$i]['username'].' ['.$active[$i]['access'].']</a></td>

		<td>'.date("d.m.y H:i:s",$active[$i]['timestamp']).'</td>

		<td>'.$tribe.'</td>

		<td>'.$totalpop.'</td>

		<td>'.count($varray).'</td>

		<td><img src="../img/Admin/gold.gif" class="gold" alt="Gold" title="يملك الاعب: '.$active[$i]['gold'].' ذهب"/> '.$active[$i]['gold'].'</td>

	</tr>

';

}

}else{

echo '<tr><td  colspan="6" class="hab">لا يوجد لاعبين متواجدين</td></tr>';



}



?>



</table>