<?php
include('application/Account.php');

if(!empty($_GET['ref'])){$inviter=$database->filterstringvalue($_GET['ref']);}

?>
<!DOCTYPE html>
<html>
<?php include("application/views/html.php");?>

<body class="v35 webkit <?=$database->bodyClass($_SERVER['HTTP_USER_AGENT']); ?> ar-AE login perspectiveBuildings <?php echo DIRECTION; ?> season- buildingsV1">
<div id="background">
    <img id="staticElements" src="img/x.gif" alt=""/>
    <div id="bodyWrapper">
        <img style="filter:chroma();" src="img/x.gif" id="msfilter" alt=""/>
        <div id="header">
            <div id="mtop">
                <a id="logo" href="<?php echo HOMEPAGE; ?>" target="_blank" title="<?php echo SERVER_NAME; ?>"></a>
                <div class="clear"></div>
            </div>
        </div>
        <div id="center">
            <?php include('application/views/menu.php');?>
            <div id="contentOuterContainer" class="size1">
                <div class="contentTitle">&nbsp;</div>
                <div class="contentContainer">
                    <div id="content" class="signup"><h1 class="titleInHeader">تفعيل الحساب</h1>
<?php


	include("application/views/activate/activate.php");


?> <div class="clear"></div>
                </div>
                <div class="contentFooter">&nbsp;</div>
            </div>

        </div>


    </div>
    <div id="ce"></div></div></div>

</body>
</html>
