<?php
$adv=$database->getAdventure($session->uid,$_GET['kid']);
if($adv){
    $eigen = $database->getCoor($village->wid);
    $adventure = $database->getMInfo($_GET['kid']);
    $from = array('x'=>$eigen['x'], 'y'=>$eigen['y']);
    $to = array('x'=>$adventure['x'], 'y'=>$adventure['y']);
    
    $speed = $session->heroD['speed'];
    $time = $database->procDistanceTime($from,$to,$speed,1);
    ?>
<div id="tileDetails" class="landscape landscape-<?=$adventure['type_of']?>">
    <div class="detailImage" style="border-radius:2%"></div>
    <div class="clear"></div>
   <?php $error="";
    if($village->resarray['f39']==0){
    $error=punktxuev9;
    }elseif($session->heroD['dead']==1){
    $error=punktxuev8;
    }elseif(!$village->unitarray['u11']){
    $error=punktxuev7;
        }elseif($session->heroD['revivetime']>0){
        if(!isset($timer)){$timer=0;}
        $timer++;
        $error="Герой восстанавливается. Осталось <span class='timer' counting='down' value='".$timer."'>".$generator->getTimeFormat($session->heroD['revivetime'])."</span>.";
    }?>
            <div class="adventureStatusMessage">
                <div class="heroStatusMessage ">
                    <?php echo $heroF->printHeroSt(); ?>  
                        </div>
                </div>

                <strong>وقت المغامرة:</strong>
                <br>
                 الوصول في: <?php echo $generator->getTimeFormat($time); ?> ساعة | عودة في: <?php echo $generator->getTimeFormat($time*2); ?> ساعة 
                <br>
                <br>
                <div class="adventureSend">
                    <div class="adventureSendButton">
                    <?php if($heroF->getHeroStatus() == 100){ ?> 
                        <form class="adventureSendButton" method="post" action="build.php?t=2&id=39">
                            <div>
                            <input type="hidden" name="a" value="adventure" />
                            <input type="hidden" name="c" value="6" />
                            <input type="hidden" name="h" value="<?php echo $_GET['kid']; ?>" />
                            <input type="hidden" name="id" value="39" />
                    <?php }else{ echo '<div>'; } ?>
                    <?php if($heroF->getHeroStatus() != 100){ echo '<a href="dorf2.php">'; } ?>
                                    <button <?php if($heroF->getHeroStatus() == 100){ echo 'type="submit"'; } ?> value="إذهب للمغامرة" name="s1" id="start" class="green">
                                        <div class="button-container addHoverClick ">
                                            <div class="button-background">
                                                <div class="buttonStart">
                                                    <div class="buttonEnd">
                                                        <div class="buttonMiddle"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="button-content">إذهب للمغامرة</div>
                                        </div>
                                    </button>
                    <?php if($heroF->getHeroStatus() != 100){ echo '</a>'; } ?>
                            </div>
                    <?php if($heroF->getHeroStatus() == 100){ ?> </form><?php } ?>
                    </div>
                    <div class="adventureBackButton">
                        <a href="hero_adventure.php" class="a arrow">عودة</a>
                    </div>
                </div>
                </form>
<?php 
   }else{
       header('Location: hero_adventure.php'); exit();
   }
?>
    </div>