<?php
include("application/Account.php");

?>
<!DOCTYPE html>
<html>
<?php include("application/views/html.php");?>

<body class="v35 webkit <?=$database->bodyClass($_SERVER['HTTP_USER_AGENT']); ?> ar-AE logout <?php if($dorf1==''){echo 'perspectiveBuildings';}else{ echo 'perspectiveResources';} ?> <?php echo DIRECTION; ?> buildingsV1">
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
                    <div id="content" class="logout">





                        <h1 class="titleInHeader"><?php echo LOGOUT_TITLE; ?></h1>
                        <h4><?php echo LOGOUT_H4; ?></h4>
                        <p><?php echo LOGOUT_DESC; ?></p>
                        <p><a class="arrow" href="login.php?del_cookie"><?php echo LOGOUT_LINK;?></a></p>
                    </div>
                    <div class="clear">&nbsp;</div>
                </div>
                <div class="clear"></div>

                <div class="contentFooter">&nbsp;</div>
            </div>
        </div>
<?php include('application/views/footer.php');?>

    </div>

    <div id="ce"></div>

</body>
</html>