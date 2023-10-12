<?php 
    if(isset($_POST['gFarms'])){
        if(is_numeric($_POST['fNum'])){
            for($i=1;$i<=$_POST['fNum'];$i++){
                $database->generateFarm(0,3,'Natars');
                
            }
            echo "تم إضافة المزارع بنجاح";
        }
    }
?>
<form action="" method="post">
	<br>
	<table class="table">
		<thead>
			<tr>
				<th colspan="3">توليد مزارع التتار</th>
			</tr>
		</thead>
		<tbody><tr class="slr3">
			<td>عدد المزارع</td>
			<td>
				<input name="fNum" class="text" type="text" value="">
			</td>
			<td>
				<input type="submit" name="gFarms" value="توليد">
			</td>
		</tr>
	</tbody></table>
</form>
<br>

الإضافة مازالت تجريبية ولم يتم الإنتهاء منها بعد.