<table cellpadding="1" cellspacing="1" class="table">
	<thead>
		<tr>
			<th colspan="10">سجل  الشحن</th>
		</tr>
		<tr>
			<td class="on">#</td>
			<td class="on">الاعب</td>
			<td class="on">طريقة الدفع</td>
			<td class="on">رقم العملية</td>
			<td class="on">تاريخ العملية</td>
			<td class="on">كمية الذهب</td>
			<td class="on">التكلفة</td>
			<td class="on">الحالة</td>
		</tr>
	</thead>
	<tbody>

	<?php 
        $pyList = $database->query("SELECT * FROM payments ORDER BY id DESC");
        foreach($pyList as $pyData){
            $pData = $database->getUserInfo($pyData['idUser']);
    ?>
				<tr>
					<td><?php echo $pyData['id']; ?></td>
					<td><a href="index.php?p=player&uid=<?php echo $pyData['idUser']; ?>"><?php echo $pData['username']; ?></a></td>
					<td><?php echo $pyData['pMethod']; ?></td>
					<td><?php echo $pyData['idTrans']; ?></td>
					<td><?php echo date("Y/m/d h:m", $pyData['dTrans']); ?></td>
					<td><?php echo number_format($pyData['gAmount']); ?></td>
					<td><?php echo $pyData['cost']; ?>USD</td>
					<td><center><?php echo $pyData['idTrans'] ? 'مكتملة' : 'فاشلة'; ?></center></td>
				</tr>
<?php } ?>
	</tbody>

</table>