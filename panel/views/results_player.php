<?php

$result = $admin->search_player($_POST['s']);

?>

<table class="table">

  <thead>

	<tr>

		<th>الاعبين (<?php echo count($result);?>)</th>

	</tr>

  </thead>



</table>

<table class="table">

	<tr>

		<td class="b">UID</td>

		<td class="b">الاعب</td>

		<td class="b">القري</td>

		<td class="b">السكان</td>

	</tr>

<?php

if($result){

for ($i = 0; $i <= count($result)-1; $i++) {

$varray = $database->getProfileVillages($result[$i]["id"]);

$totalpop = 0;

foreach($varray as $vil) {

	$totalpop += $vil['pop'];

}

echo '

	<tr>

		<td>'.$result[$i]["id"].'</td>

		<td><a href="?p=player&uid='.$result[$i]["id"].'">'.$result[$i]["username"].'</a></td>

		<td>'.count($varray).'</td>

		<td>'.$totalpop.'</td>

	</tr>

';

}}

else{

echo '

	<tr>

		<td colspan="4">No results</td>

	</tr>

';

}

?>



</table>

