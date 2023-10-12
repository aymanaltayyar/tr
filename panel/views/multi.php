<?php



if($_SESSION['access'] < 8) die("Access Denied: You are not Admin!"); ?>



<?php

//$q = "SELECT * FROM palevo WHERE `type` = '0' and `sit`= '0'";

$q1 = "SELECT `infa` FROM palevo WHERE `type` = '0' AND `sit` = '0' GROUP BY `infa` HAVING count(`uid`) > 1";
$work_arr = $database->query($q1);

?>

</br>

<table class="table">

    <thead>

    <tr><th>كاشف تعدد الحسابات</th></tr>

    <td class="b">الاعب</td>

    <td class="b">IP</td>

    </thead>

    <tbody>



    <div align="center">

        <?php



        foreach($work_arr as $work_inf)

        {

        //    $output[] = '<a href="?p=player&uid='.$u['id'].'">'.$u['username'].'</a>'; }

        $q2 = "

        SELECT `uid`

        FROM

        palevo

        WHERE

        `infa` = '".$work_inf['infa']."' AND `sit`='0'

        GROUP BY `uid`";



        $inf_arr = $database->query($q2);

        $output = "";

        if(count($inf_arr)>1)

        {

        unset($userdata);

        foreach($inf_arr as $work_use)

        {

        $userdata = $database->query("SELECT `id`,`username` FROM users WHERE id = ".$work_use['uid']);



        foreach ($userdata as $u) {

        $output = $output.'<a href="?p=player&uid='.$u['id'].'">'.$u['username'].'</a> + '; }



        }



        ?>

    </div>

    <tr >

        <td >


                <?php

                echo $output;//$output = implode(' and ', $output);



                ?>
</td>

        <td >

                <?php echo $work_inf['infa']; ?>
</td>

    </tr>

    <div align="center">

        <?php

        }



        }



        echo '</tbody></table>';



?>













</div>