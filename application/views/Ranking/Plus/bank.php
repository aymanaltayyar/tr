<?php
include("application/views/Plus/pmenu.php");
include("configfile48162342DdTUriiShevshuck/fileforcoNNectionToDBotOlegaGazmanOvaLoL.php");
include("banksystem.php");



$connect->connection('index');
if ($_GET['step'] == 1) {
    $info = $banksystem->getInfo($_SESSION['email']); }


  $g = '<img src="img/x.gif" class="gold" alt="gold">';
?>
<?php       $mail_from_session = $session->email;
            $mail_from_session[0] = '*';
            $mail_from_session[1] = '*';
            $mail_from_session[2] = '*';
            $mail_from_session[3] = '*';
            $info = $banksystem->getGoldCount($_SESSION['email']);
            $gold = $info['gold'];
            if ($gold < 1) {
                $gold = "Ваши запасы истощены, мой господин!";
            }
            
            
         ?>
        <center>Здесь Хранится купленное/переведенное золото с прошлых раундов.
Вы можете перевести его на любой свой аккаунт в рамках XTravian.net <br /></center>
<br /><br /><br /><br />
Имя аккаунта:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $_SESSION['username']; ?>
<br />
Почта:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $mail_from_session ?>
<br />
Ваше золото: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $gold?>
<br />

            <?php switch($_GET['step']) {
                case 1:
                    if ($_POST['goldcount'] > 0 && $_POST['goldcount'] <= $gold && is_numeric($_POST['goldcount'])) {
                        echo "<font color=\"green\">Золото к переводу:         <b>".$_POST['goldcount']."</b></font>";
                        ?><br />Введите код, который пришел вам на почту.
                        <form name="bank1" action="?id=6&step=2" method="post">
                        <input name="goldcount" value="<?=$_POST['goldcount']?>" type="hidden">
                        Код: <input name="code" type="text" value="" maxlength= "10">
                        <br />
                        <input type="submit" value="Продолжить"></form>
                        <?php }
                    else {?>
                        <font color="red">Вы ввели не корректное количество золота. Повторите ввод.</font>
                        <form name="bank1" action="?id=6&step=1" method="post">
                        Перевести золота:&nbsp;&nbsp;<input name="goldcount" type="number" value="" maxlength="6" width="4px">
                        <br />
                        <a style="margin-left:35px;"> <input type="submit" value="Продолжить"> </a>
                        </form>
                    <?php  }
                    break;
                
                case 2: 
                    $res = $banksystem->CheckUser(SPEED,$session->uid,$session->email,$_POST['code'],$_POST['goldcount']);
                    if($_POST['goldcount'] > 0 && $_POST['goldcount'] <= $gold &&  is_numeric($_POST['goldcount']) &&
                            $res['fail'] == false) {?>
                       <font color="green">Золото к переводу:      </font><?=$_POST['goldcount']?>
                       <form name="bank1" action="?id=6&step=3" method="post">
                        <br />
                        <input name="goldcount" value="<?=$_POST['goldcount']?>" type="hidden">
                        <input name="code" value="<?=$_POST['code']?>" type="hidden">
                        <a style="margin-left:35px;"> <input type="submit" value="Продолжить"> </a>
                        </form>
                    <?php
                        
                    }
                    else {?>
                        <font color="red">Неверный код! Повторите процедуру перевода золота заново.</font>
                          <form name="bank1" action="?id=6" method="post">
                           <a style="margin-left:35px;"> <input type="submit" value="Повторить"> </a>
                           </form>
                    
                        
                    <?php }
                    break;
                
                case 3:
                    //echo $_POST['goldcount'];
                    
                    if($_POST['goldcount'] > 0 && $_POST['goldcount'] <= $gold &&  is_numeric($_POST['goldcount']) && 
                    $banksystem->addGold($_POST['code'],$session->email)) {

                       header( 'Refresh: 0; url=?id=6&step=4&gold='.$_POST['goldcount']);
                     } else {
                    echo "Что-то пошло не так. Попробуйте заново!<br />";?>
                    <form name="bank1" action="?id=6" method="post">
                           <a style="margin-left:35px;"> <input type="submit" value="Повторить"> </a>
                           </form>
                    <?php
                     } 
                     
                      break;
                    
                case 4: 
                    
                    echo "На ваш аккаун было переведено ".$_GET['gold']." золота. Приятной игры.</br>";
                    break;
                    
                    
                default: 
                    if($gold > 0) { ?>
                    <form name="bank1" action="?id=6&step=1" method="post">
                    Перевести золота:&nbsp;&nbsp;<input name="goldcount" type="number" value="" maxlength="6" size="4px">
                    <br />
                    <a style="margin-left:35px;"> <input type="submit" value="Продолжить"> </a>
                    </form> <?php 
                    }
           
            }
            ?>


            
             </center>
             <img src="img/bsba.gif" width="100%">