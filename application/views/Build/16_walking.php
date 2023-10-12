<?php
$units = $database->getMovement(99,$village->wid,0);
$total_for = count($units);
if(!isset($timer)){$timer=1;}
for($y=0;$y<$total_for;$y++){

switch($units[$y]['attack_type']){
	case 1: $attack_type = "التجسس على"; break;
	case 2: $attack_type = "تعزيز"; break;
	case 3: $attack_type = "هجوم كامل"; break;
	case 4: $attack_type = "هجوم للنهب"; break;
	case 5: $attack_type = "بناء قرية جديدة"; break;
}

    if ($units[$y]['oasistype']){
        $units[$y]['name']="واحة (".$units[$y]['x']."|".$units[$y]['y'].")";}
    if($units[$y]['sort_type']==9){$attack_type = "مغامرة (".$units[$y]['x']."|".$units[$y]['y'].")";}

?>
<table class="troop_details" cellpadding="1" cellspacing="1">
	<thead>
		<tr>
			<td class="role"><a href="karte.php?d=<?php echo $village->wid."&c=".$generator->getMapCheck($village->wid); ?>"><?php echo $village->vname; ?></a></td>
			<td colspan="<?php if($units[$y]['t11'] == 0) {echo"10";}else{echo"11";}?>"><a href="karte.php?d=<?php echo $units[$y]['wref']."&c=".$generator->getMapCheck($units[$y]['wref']); ?>"><?php echo $attack_type." ".$units[$y]['name']; ?></a></td>
		</tr>
	</thead>
	<tbody class="units">
			<?php
                  echo "<tr><th>&nbsp;</th>";
                  for($i=($session->tribe-1)*10+1;$i<=$session->tribe*10;$i++) {
                  	echo "<td><img src=\"img/x.gif\" class=\"unit u$i\" title=\"".$technology->getUnitName($i)."\" alt=\"".$technology->getUnitName($i)."\" /></td>";
                  }
                  if($units[$y]['t11'] != 0) {
                   echo "<td><img src=\"img/x.gif\" class=\"unit uhero\" title=\"Hero\" alt=\"Hero\" /></td>";
                  }
			?>
			</tr>
 <tr><th>القوات</th>
            <?php
			if($units[$y]['t11'] != 0) {
				$end = 12;
			}else{
				$end = 11;
			}
            for($i=1;$i<$end;$i++) {
            	if($units[$y]['t'.$i] == 0) {
                	echo "<td class=\"none\">";
                }
                else {
                echo "<td>";
                }
                echo $units[$y]['t'.$i]."</td>";
            }
            ?>
           </tr></tbody>
		<tbody class="infos">
			<tr>
				<th>الوصول</th>
				<td colspan="<?php if($units[$y]['t11'] == 0) {echo"10";}else{echo"11";}?>">
				<?php
				    echo "<div class=\"in small\"><span class='timer' counting='down' value='".($units[$y]['endtime']-time())."'>".$generator->getTimeFormat($units[$y]['endtime']-time())."</span></div>";
				    $datetime = $generator->procMtime($units[$y]['endtime']);
				    echo "<div class=\"at small\">";
				    if($datetime[0] != "today") {
				    echo $datetime[0]." ";
				    }
				    echo $datetime[1]."</div>";
					if (($units[$y]['starttime']+90)>time()){
				?>
                    <div class="abort"><a href="build.php?id=<?php echo $_GET['id']."&mode=troops&cancel=1&moveid=".$units[$y]['moveid']; ?>"><img src="img/x.gif" class="del" /></a></div>
					<?php } ?>
					</div>
				</td>
			</tr>
		</tbody>
</table>
		<?php
    $timer++;
		}
		?>

