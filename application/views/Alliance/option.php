<?php
if(!isset($aid)) {
$aid = $session->alliance;
}
 if($session->alliance==$aid){
$allianceinfo = $database->getAlliance($aid);
//echo "<h1>".$database->RemoveXSS($allianceinfo['tag'])." - ".$database->RemoveXSS($allianceinfo['name'])."</h1>";
include("alli_menu.php");

?>
<h4 class="round"><?php echo ALLIANCE1;?></h4>
<!--
<ul class="options">
                    <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=100">تغيير الأسم</a>
            </li>
            <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=3">تغيير وصف التحالف</a>
            </li>
            <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=7">صفحة المعلومات الداخلية</a>
            </li>
                            <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=1">توزيع المناصب</a>
            </li>
                            <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=5">رابط إلى المنتدى</a>
            </li>
            </ul>
            <h4 class="round">المزادات</h4>
            <ul class="options">
                        <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=4">دعوة لاعب للتحالف</a>
            </li>
                        <li>
            <a class="a arrow" href="allianz.php?s=5&amp;o=6">دبلوماسية التحالف</a>
        </li>
                            <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=2">طرد لاعب</a>
            </li>
                <li>
                <a class="a arrow" href="allianz.php?s=5&amp;o=11">ترك التحالف</a>
            
    </li>
</ul>-->
<form method="POST" action="allianz.php?ss=5">
<input type="hidden" name="s" value="5">
    <ul class="options">
<?php
if ($alliance->userPermArray['opt1']==1){
?>
        <label><li><input class="radio" type="radio" name="o" value="1"> <?php echo ALLIANCE2;?></li></label>

<?php
}
if ($alliance->userPermArray['opt3']==1){
?>
        <label><li><input class="radio" type="radio" name="o" value="100"> <?php echo ALLIANCE3;?></li></label>
<?php
}
if ($alliance->userPermArray['opt2']==1){
?>
<label><li><input class="radio" type="radio" name="o" value="2"> <?php echo ALLIANCE4;?></li></label>
<?php
}
if ($alliance->userPermArray['opt3']==1){
?>

<label><li><input class="radio" type="radio" name="o" value="3"> <?php echo ALLIANCE5;?></li></label>
<?php
}
if ($alliance->userPermArray['opt6']==1){
?>
      <label><li><input class="radio" type="radio" name="o" value="6"> <?php echo ALLIANCE6;?></li></label>
<?php
}
if ($alliance->userPermArray['opt4']==1){
?>
        <label><li><input class="radio" type="radio" name="o" value="4"> <?php echo ALLIANCE7;?></li></label>
<?php
}
?>
          <label><li><input class="radio" type="radio" name="o" value="11"> <?php echo ALLIANCE8;?></li></label>

    </ul>

     <p>
         <button type="submit" value="ok" name="s1" id="btn_ok" class="green">
             <div class="button-container addHoverClick ">
                 <div class="button-background">
                     <div class="buttonStart">
                         <div class="buttonEnd">
                             <div class="buttonMiddle"></div>
                         </div>
                     </div>
                 </div><div class="button-content"><?= ally3 ?></div>
             </div>
         </button>
     </p></form><?php
 }
?>