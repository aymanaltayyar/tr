<?php

$result = $admin->search_alliance($_POST['s']);

?>

<table class="table">

  <thead>

	<tr>

		<th>التحالفات (<?php echo count($result);?>)</th>

	</tr>

  </thead>



</table>

<table class="table">

	<tr>

		<td class="b">AID</td>

		<td class="b">الاسم</td>

		<td class="b">الرمز</td>

		<td class="b">الرئيس</td>

	</tr>

<?php

if($result){

for ($i = 0; $i <= count($result)-1; $i++) {

echo '

	<tr>

		<td>'.$result[$i]["id"].'</td>

		<td><a href="?p=alliance&aid='.$result[$i]["id"].'">'.$result[$i]["name"].'</a></td>

		<td><a href="?p=alliance&aid='.$result[$i]["id"].'">'.$result[$i]["tag"].'</a></td>

		<td><a href="?p=player&uid='.$result[$i]["id"].'">'.$database->getUserField($result[$i]["leader"],'username',0).'</a></td>

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

