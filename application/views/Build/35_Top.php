
<div id="build" class="gid35">

<table cellpadding="1" cellspacing="1" id="build_value">
    <tr>
        <th>زيادة قوة الهجوم:</th>
        <td><b><?php echo $bid35[$village->resarray['f'.$id]]['attri']; ?></b> %</td>
    </tr>
    <tr>
    <?php
    $cur=$building->isCurrent($id);
    $loop=$building->isLoop($id);
    $master=$building->isMaster($id);
    if($cur+$loop+$master>0){
        foreach($building->buildArray as $bu){
            echo "<tr class=\"underConstruction\"><th>زيادة قوة الهجوم في المستوي ".$bu['level'].":</th> <td><span class=\"number\">".$bid35[$bu['level']]['attri']." %</span> </td></tr>";
        }

    }
    if(!$building->isMax($village->resarray['f'.$id.'t'],$id)) {

    ?>
        <th>زيادة قوة الهجوم في المستوي <?php echo $next; ?>:</th>
        <td><b><?php echo $bid35[$next]['attri']; ?></b> %</td>
        <?php } ?>
    </tr>

</table>
</div>
