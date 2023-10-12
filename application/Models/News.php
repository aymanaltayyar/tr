<?php
/*
    By iRedux - https://www.facebook.com/opito8
*/
Class News{
    function __construct(){
        $this->pCheck();
        $this->nEnd();
    }

    function pCheck(){
        global $database, $session;
        if($session->plust < (time() + 43200) && $session->plust > time()){ $this->addNotice(1);}
        if($session->bonus1 < (time() + 43200) && $session->bonus1 > time()){ $this->addNotice(2);}
        if($session->bonus2 < (time() + 43200) && $session->bonus2 > time()){ $this->addNotice(3);}
        if($session->bonus3 < (time() + 43200) && $session->bonus3 > time()){ $this->addNotice(4);}
        if($session->bonus4 < (time() + 43200) && $session->bonus4 > time()){ $this->addNotice(5);}
    }

    function nEnd(){
        global $database, $session;

        $pnData = $database->query("SELECT * FROM pnews WHERE uid = ".$session->uid."");
        foreach($pnData as $uNews){
            switch($uNews['nid']){
                case 1: if($session->plust > (time() + 43200) || time() > $session->plust){ $isDelete = TRUE; } break;
                case 2: if($session->bonus1 > (time() + 43200) || time() > $session->bonus1){ $isDelete = TRUE; } break;
                case 3: if($session->bonus2 > (time() + 43200) || time() > $session->bonus2){ $isDelete = TRUE; } break;
                case 4: if($session->bonus3 > (time() + 43200) || time() > $session->bonus3){ $isDelete = TRUE; } break;
                case 5: if($session->bonus4 > (time() + 43200) || time() > $session->bonus4){ $isDelete = TRUE; } break;
            }

            if($isDelete){
                $database->query('DELETE FROM pnews WHERE id = '.$uNews['id'].'');
            }
        }
        
    }

    function nSum(){
        global $database, $session;
        
        $pNSum = $database->queryNumRow('SELECT * FROM pnews WHERE `uid` = '.$session->uid.' AND `hidden` = 0');
        $gNSum = $database->queryNumRow('SELECT * FROM pnews WHERE `uid` = 0 AND `nid` = 0 AND `hidden` = 0');

        return $pNSum + $gNSum;
    }

    function delNew($id){
        global $database, $session;
        
        $nData = $database->queryFetch('SELECT * FROM `pnews` WHERE `id` = '.$id.' AND `uid` = '.$session->uid.'');
        if($nData['id']){
            $database->query('UPDATE pnews SET `hidden` = 1 WHERE `id` = '.$id.'');
            return TRUE;
        }else{
            return False;
        }
    }

    // 1 -> plus , 2 -> wood , 3 -> clay , 4 -> iron , 5 -> crop
    function addNotice($type){
        global $database, $session;

        $cNotice = $database->queryFetch('SELECT * FROM `pnews` WHERE `nid` = '.$type.' AND `uid` = '.$session->uid.'');
        if(!$cNotice['id']){
            $database->query('INSERT INTO pnews VALUES(NULL, '.$session->uid.', '.$type.', "", 0)');
        }
    }

    function getNews(){
        global $database, $session, $generator, $lang, $config;
        $n = '';

        $gNews = $database->query('SELECT * FROM `pnews` WHERE `uid` = 0 AND `nid` = 0 AND `hidden` = 0');
        foreach($gNews as $gnew){
            $n .= '<li><p>'.$gnew['ncontent'].'</p></li>';
        }

        $q = $database->query('SELECT * FROM `pnews` WHERE `uid` = '.$session->uid.' AND `hidden` = 0');
        foreach($q as $new){
            $uData = $database->queryFetch('SELECT * FROM `users` WHERE `id` = '.$new['uid'].'');
            switch($new['nid']){
                case 1: $t = $uData['plus'] - time(); break;
                case 2: $t = $uData['b1'] - time(); break;
                case 3: $t = $uData['b2'] - time(); break;
                case 4: $t = $uData['b3'] - time(); break;
                case 5: $t = $uData['b4'] - time(); break;
            }

            $n .= '<li>';
            $n .= '<p><a class="infoboxDeleteButton" href="#" data-id="'.$new['id'].'"><img src="img/x.gif" class="del" alt="del"></a>'.$lang['News'][$new['nid']].' <span class="timer" counting="down" value="'.$t.'">'.($generator->getTimeFormat($t)).'</span> '.$lang['News']['Hour'].'.</p>';
            $n .= $this->genButton($new['nid']);
            $n .= '</li>';
        }

        return $n;
    }

    function genButton($type){
        global $config;
        switch($type){
            case 1: $fKey = 'plus'; $gold = $config['Plus']; break;
            case 2: $tName = 'wood'; $fKey = 'productionboostWood'; $gold = $config['addonProduction']; break;
            case 3: $tName = 'clay'; $fKey = 'productionboostClay'; $gold = $config['addonProduction']; break;
            case 4: $tName = 'iron'; $fKey = 'productionboostIron'; $gold = $config['addonProduction']; break;
            case 5: $tName = 'crop'; $fKey = 'productionboostCrop'; $gold = $config['addonProduction']; break;
        }

        return '<button type="button" class="gold  productionBoostButton '.$tName.'" title="Extend Now||Bonus duration in days: 1 hour" coins="'.$gold.'" id="buttoneGUUeMJ38I7WY"><div class="button-container addHoverClick">
        <div class="button-background">
            <div class="buttonStart">
                <div class="buttonEnd">
                    <div class="buttonMiddle"></div>
                </div>
            </div>
        </div>
        <div class="button-content">تمديد<img src="img/x.gif" class="goldIcon" alt=""><span class="goldValue">'.$gold.'</span></div></div></button>
        <script type="text/javascript" id="buttoneGUUeMJ38I7WY_script">
            window.addEvent(\'domready\', function(){
                if($(\'buttoneGUUeMJ38I7WY\')){
                $(\'buttoneGUUeMJ38I7WY\').addEvent(\'click\', function (){
                    window.fireEvent(\'buttonClicked\', [this, {"type":"button","value":"Extend","confirm":"","onclick":"","wayOfPayment":{"featureKey":"'.$fKey.'","context":"infobox"},"title":"Extend now &lt;br&gt; Bonus duration in days: &lt;span class=&quot;bold&quot;&gt;1&lt;\/span&gt; hour","coins":'.$config['addonProduction'].',"id":"buttoneGUUeMJ38I7WY"}]);
                });
                }
                });
        </script>';
    }
}

$news = new News;