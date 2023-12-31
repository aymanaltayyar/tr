<?php include("application/views/Plus/pmenu.php"); ?>
<h4 class="round">توليد أكواد</h4>
<?php
if($session->access != 9){
    header('Location: dorf1.php'); exit();
}else{

    if(isset($_GET['del'])){
        $database->query("DELETE FROM codes WHERE id = ".$_GET['del']."");
        echo 'تم حذف الكود بنجاح <br><br>';
    }
    if(isset($_POST) && !empty($_POST)){
        $_POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);
        if(is_numeric($_POST['goldAmount']) && is_numeric($_POST['codeNum'])){
            
            if($_POST['goldAmount'] > 0){
                echo 'قائمة الأكواد:<br><br>';
                for($i=1;$i<=$_POST['codeNum'];$i++){
                    $code = $generator->generateRandStr(10);
                    $database->query("INSERT into codes (codeNum,goldAmount) VALUES('".$code."', ".$_POST['goldAmount'].")");
                    echo $code;
                    echo '<br>';
                }
                echo '<br>';
            }else{
                $isError++;
                $Error = 'مدخلات خاطئة';
            }
        }else{
            $isError++;
            $Error = 'مدخلات خاطئة';
        }
    }
?>

<?php if($isError){ ?>
    <b style="color:red;"><?php echo $Error; ?></b> <br>
<?php } ?>
<form action="" method="post">
<div class="boxes boxesColor gray">
        <div class="boxes-tl"></div>
        <div class="boxes-tr"></div>
        <div class="boxes-tc"></div>
        <div class="boxes-ml"></div>
        <div class="boxes-mr"></div>
        <div class="boxes-mc"></div>
        <div class="boxes-bl"></div>
        <div class="boxes-br"></div>
        <div class="boxes-bc"></div>
        <div class="boxes-contents cf">
            <table class="transparent">
                <tbody>
                <tr>
                    <td>
                        <span class="">كمية الذهب</span>
                        <input name="goldAmount" type="number" autocomplete="off">
                        <span class="clear"></span>
                    </td>
                    <td>
                        <span class="">عدد الأكواد</span>
                        <input name="codeNum" type="number" value="1" autocomplete="off">
                        <span class="clear"></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br>
    <button type="submit" value="submit" name="submit" class="gold">
        <div class="button-container addHoverClick">
            <div class="button-background">
                <div class="buttonStart">
                    <div class="buttonEnd">
                        <div class="buttonMiddle"></div>
                    </div>
                </div>
            </div>
            <div class="button-content">توليد</div>
        </div>
    </button>    

</form>
<br><br>
<h4 class="round">قائمة الأكواد</h4>
<table cellpadding="1" cellspacing="1" id="overview" class="inbox">
    <thead>
        <tr>
            <th>#</th>
            <th>الكود</th>
            <th>الذهب</th>
            <th>الحالة</th>
            <th>عمليات</th>
        </tr>
    </thead>
    <tbody>
    <?php $codes = $database->query("SELECT * FROM codes ORDER BY id DESC"); 
    if(count($codes) > 0){
        foreach($codes as $code){
         ?>
        <tr>
        <td><?php echo $code['id']; ?></td>
        <td><?php echo $code['codeNum']; ?></td>
        <td><?php echo $code['goldAmount']; ?></td>
        <td><?php echo $code['isUsed'] ? 'مستعمل' : 'غير مستعمل'; ?></td>
        <td><a href="plus.php?id=6&del=<?php echo $code['id']; ?>">حذف</a></td>
        </tr>
    <?php 
        }
        }else{
            echo '<tr><td colspan="5">لا يوجد أكواد بعد.</td></tr>';
        } ?>
    </tbody>
</table>

<?php } ?>