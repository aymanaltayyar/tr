<?php
include("application/views/Plus/pmenu.php");
$extragoud="0";
$_SESSION['email']=$session->email;
if(isset($_POST) && !empty($_POST)){
    $_POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);
    //if(is_numeric($_POST['code'])){
        $Q = $database->query("SELECT * FROM codes WHERE codeNum = '".$_POST['code']."'");

        if(count($Q) > 0){
            if($Q[0]['isUsed']){
                $isError++;
                $Error = 'الكود مستعمل';
            }else{
                $database->query("UPDATE codes SET isUsed = 1 WHERE id = ".$Q[0]['id']."");
                $database->modifyGold($session->uid,$Q[0]['goldAmount'],1);
                $isError++;
                $Error = 'تم شحن الكود بنجاح وإضافة '.$Q[0]['goldAmount'].' ذهبة إلي رصيدك.';
            }
        }else{
            $isError++;
            $Error = 'الكود خاطيء';
        }
    /*}else{
        $isError++;
        $Error = 'الكود خاطيء';
    }*/
}
?>
<h4 class="round">شحن كود ذهب</h4>
<p>إذا كنت تمتلك أحد أكواد الذهب، قم بإدخاله للحصول على رصيد الكود من الذهب.</p>
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
                    
                        <span class="">كود الذهب</span>
                        <input name="code" type="text" autocomplete="off">
                        <span class="clear"></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br>
    <button type="submit" value="search" name="submit" class="gold">
        <div class="button-container addHoverClick">
            <div class="button-background">
                <div class="buttonStart">
                    <div class="buttonEnd">
                        <div class="buttonMiddle"></div>
                    </div>
                </div>
            </div>
            <div class="button-content">شحن</div>
        </div>
    </button>    

</form>