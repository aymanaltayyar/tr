
<style>
.del {width:12px; height:12px; background-image: url(img/Admin/icon/del.gif);}
</style>
<table class="table">
  <thead>
	<tr>
		<th>القري</th>
	</tr>
  </thead>
</table>

<table class="table">
	<tr>
		<td>الاسم</td>
		<td>السكان</td>
		<td>الاحداثيات</td>
		<td>التحكم</td>
		<td></td>
	</tr>
<?php

for ($i = 0; $i <= count($varray)-1; $i++) {

$coorproc = $database->getCoor($varray[$i]['wref']);

if($varray[$i]['capital']){

$capital = '<span class="c">(العاصمة)</span>';

$delLink = '<a href="#"><img src="../img/Admin/del_g.gif" class="del"></a>';

}else{

$capital = '';

	if($_SESSION['access'] == ADMIN){

	$delLink = '<a href="?action=delVil&did='.$varray[$i]['wref'].'" onClick="return del(\'did\','.$varray[$i]['wref'].');"><img src="../img/Admin/del.gif" class="del"></a>';

  }else if($_SESSION['access'] == MULTIHUNTER){

  $delLink = '<a href="#"><img src="../img/Admin/del_g.gif" class="del"></a>';

	}

}

$addTroops = '<a href="?p=addTroops&did='.$varray[$i]['wref'].'"> تعديل القوات</a>';

echo '

	<tr>

		<td><a href="?p=village&did='.$varray[$i]['wref'].'">'.$varray[$i]['name'].'</a> '.$capital.'</td>

		<td>'.$varray[$i]['pop'].' <a href="?action=recountPop&did='.$varray[$i]['wref'].'">تحديث<a/></td>

		<td>('.$coorproc['x'].'|'.$coorproc['y'].')</td>

		<td>'.$addTroops.' </td>

		<td>'.$delLink.' </td>

	</tr>

';

}



?>



</table>