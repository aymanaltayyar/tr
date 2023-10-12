<table cellpadding="1" cellspacing="1" class="table">

	<thead>

		<tr>

			<th colspan="10">لاعبين غير نشطين (لم يكملوا  عملية التسجيل)</th>

		</tr>

		<tr>

			<td class="on">#</td>

			<td class="on">ID</td>

			<td class="on">الاعب</td>

			<td class="on">البريد</td>

			<td class="on">القبيلة</td>

			<td class="on">كود التفعيل</td>

			<td class="on">Act 2</td>

			<td class="on">الوقت</td>

		</tr>

	</thead>

	<tbody>

		<?php

			$sql = "SELECT * FROM activate";

			$result = $database->query($sql);

			foreach($result as $row)

			{

				$i++;

				if($row['tribe'] == 1) {$tribe = "الرومان"; }

				elseif($row['tribe'] == 2) {$tribe = "الجرمان"; }

				elseif($row['tribe'] == 3) {$tribe = "الإغريق"; }

				echo "

				<tr>

					<td>".$i."</td>

					<td>".$row['id']."</td>

					<td>".$row['username']."</td>

					<td><a href=\"mailto:".$row['email']."\">".$row['email']."</a></td>

					<td>".$tribe."</td>

					<td>".$row['act']."</td>

					<td>".$row['act2']."</td>

					<td class=\"hab\">".date('d:m:Y H:i', $row['timestamp'])."</td>

				</tr>";

			}

		?>

	</tbody>

</table>