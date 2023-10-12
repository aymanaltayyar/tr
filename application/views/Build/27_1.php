<?php

        $artefact = $database->getOwnArtefactInfo2($village->wid);
        $result = count($artefact);


?>
<h4 class="round">التحف المستولى عليها</h4>
<div class="gid27">
<body>
    <table id="own" cellpadding="1" cellspacing="1">
        <thead>
            <tr>
                <td></td>
                <td><?=sokr11?></td>
                <td><?=sokr1?></td>
                <td><?=sokr9?></td>
            </tr>
        </thead>

        <tbody>
            <tr>
            <?php

        if($result == 0) {
        	echo '<td colspan="4" class="none">'.sokr12.'</td>';
        } else {
        foreach($artefact as $artefac){
          $te = $artefac['type'];
                   $se = $artefac['size'];
                    if($te== 1 AND $se == 1){
                    $name = ART1;
                    $desc = ART16;
                    $bonus= "(4x) " ;
                    $image =  '<img class="artefact_icon_2" src="img/x.gif">';
                    }
                     if($te== 1 AND $se == 2){
                    $name = ART2;
                    $desc =ART16;
                    $bonus= "(3x) " ;
                    $image =  '<img class="artefact_icon_2" src="img/x.gif">';
                    }
                     if($te== 1 AND $se == 3){
                    $name = ART3;
                    $desc =  ART16;
                    $bonus= "(5x) " ;
                         $image =  '<img class="artefact_icon_2" src="img/x.gif">';
                    }
                     if($te== 2 AND $se == 1){
                    $name = ART4;
                    $desc = ART17;
                    $bonus= "(2x) " ;
                         $image =  '<img class="artefact_icon_4" src="img/x.gif">';
                    }
                     if($te== 2 AND $se == 2){
                    $name = ART5;
                    $desc =  ART17;
                    $bonus= "(1.5x) " ;
                         $image =  '<img class="artefact_icon_4" src="img/x.gif">';
                    }
                     if($te== 2 AND $se == 3){
                    $name = ART6;
                    $desc =  ART17;
                    $bonus= "(3x) " ;
                         $image =  '<img class="artefact_icon_4" src="img/x.gif">';
                    }
                     if($te== 3 AND $se == 1){
                    $name = ART7;
                     $desc = ART18;
                    $bonus= "(5x) " ;
                         $image =  '<img class="artefact_icon_5" src="img/x.gif">';
                    }
                     if($te== 3 AND $se == 2){
                    $name = ART8;
                     $desc = ART18  ;
                    $bonus= "(3x) " ;
                         $image =  '<img class="artefact_icon_5" src="img/x.gif">';
                    }
                     if($te== 3 AND $se == 3){
                    $name = ART9;
                     $desc =  ART18   ;
                    $bonus= "(10x) " ;
                         $image =  '<img class="artefact_icon_5" src="img/x.gif">';
                   }
                     if($te== 4 AND $se == 1){
                    $name = "Волшебная мельничка";
                     $desc =    "Этот артефакт сокращает потребление зерна войсками."   ;
                    $bonus= "(50%) " ;
                         $image =  '<img class="artefact_icon_6" src="img/x.gif">';
                   }
                     if($te== 4 AND $se == 2){
                    $name = "Стол Лукулла";
                     $desc =   "Этот артефакт сокращает потребление зерна войсками."      ;
                    $bonus= "(25%) " ;
                         $image =  '<img class="artefact_icon_6" src="img/x.gif">';
                   }
                     if($te== 4 AND $se == 3){
                    $name = "Чаша короля Артура";
                     $desc =      "Этот артефакт сокращает потребление зерна войсками."   ;
                    $bonus= "(50%) " ;
                         $image =  '<img class="artefact_icon_6" src="img/x.gif">';
                   }
                     if($te== 5 AND $se == 1){
                    $name = ART10;
                     $desc =       ART19   ;
                    $bonus= "(50%) " ;
                         $image =  '<img class="artefact_icon_8" src="img/x.gif">';
                   }
                     if($te== 5 AND $se == 2){
                    $name = ART11;
                     $desc =       ART19     ;
                    $bonus= "(25%) " ;
                         $image =  '<img class="artefact_icon_8" src="img/x.gif">';
                   }
                     if($te== 5 AND $se == 3){
                    $name = ART12;
                     $desc =        ART19   ;
                    $bonus= "(50%) " ;
                         $image =  '<img class="artefact_icon_8" src="img/x.gif">';
                   }
                     if($te== 6){
                    $name = ART13;
                    $desc = ART20;
                    $bonus= ART16 ;
                         $image =  '<img class="artefact_icon_9" src="img/x.gif">';
                     }
                    if($te== 7 AND $se == 1){
                    $name = "Бездонный мешок";
                     $desc = "Этот артефакт увеличивает вместимость тайника. Кроме этого, вражеские катапульты могут вести прицельный огонь только по сокровищнице и Чуду света либо по случайным целям. Уникальный артефакт предотвращает возможность вести прицельный огонь по сокровищнице.";
                    $bonus= "(200) " ;
                        $image =  '<img class="artefact_icon_" src="img/x.gif">';
                   }
                     if($te== 7 AND $se == 2){
                    $name = "Подземный храм";
                     $desc =          "Этот артефакт увеличивает вместимость тайника. Кроме этого, вражеские катапульты могут вести прицельный огонь только по сокровищнице и Чуду света либо по случайным целям. Уникальный артефакт предотвращает возможность вести прицельный огонь по сокровищнице."  ;
                    $bonus= "(100) " ;
                         $image =  '<img class="artefact_icon_" src="img/x.gif">';
                   }
                     if($te== 7 AND $se == 3){
                    $name = "Троянский конь";
                     $desc =           "Этот артефакт увеличивает вместимость тайника. Кроме этого, вражеские катапульты могут вести прицельный огонь только по сокровищнице и Чуду света либо по случайным целям. Уникальный артефакт предотвращает возможность вести прицельный огонь по сокровищнице." ;
                    $bonus= "(500) " ;
                         $image =  '<img class="artefact_icon_" src="img/x.gif">';
                   }
                     if($te== 8 AND $se == 1){
                    $name = "Книга тёмных тайн";
                     $desc =            "Эффект этого артефакта меняется как при захвате, так и каждые 24 часа. Тем самым, эффект артефакта может быть как позитивным, так и негативным, то есть бонусы каких-либо других артефактов могут иметь отрицательный показатель. Например, войска могут строиться медленнее или потреблять больше зерна.";
                    $bonus= "Случайно" ;
                         $image =  '<img class="artefact_icon_" src="img/x.gif">';
                   }
                     if($te== 8 AND $se == 3){
                    $name = "Обгоревший манускрипт";
                     $desc =           "Эффект этого артефакта меняется как при захвате, так и каждые 24 часа. Тем самым, эффект артефакта может быть как позитивным, так и негативным, то есть бонусы каких-либо других артефактов могут иметь отрицательный показатель. Например, войска могут строиться медленнее или потреблять больше зерна.";
                    $bonus= "Случайно" ;
                         $image =  '<img class="artefact_icon_" src="img/x.gif">';
                   }
                     if($te== 11 ){
                    $name = ART14;
                     $desc =    ART21;
                    $bonus= ART21  ;
                         $image =  '<img class="artefact_icon_1" src="img/x.gif">';
                   }
        	if($artefac['size'] == 1) {
        		$reqlvl = 10;
        		$effect = sokr1;
        	} elseif($artefac['size'] == 2 or 3) {
        		$reqlvl = 20;
        		$effect = pluss11;
        	}
        	echo '<td class="icon">'.$image.'</td>';

        	echo '<td class="nam">
                            <a href="build.php?id=' . $id . '&show='.$artefac['id'].'">' . $name . '</a> <span class="bon">' . $bonus . '</span>
                            <div class="info">
                                '.sokr17.' <b>' . $reqlvl . '</b>, '.sokr3.' <b>' . $effect . '</b>
                            </div>
                        </td>';
        	echo '<td class="pla"><a href="karte.php?d=' . $artefac['vref'] . '&c=' . $generator->getMapCheck($artefac['vref']) . '">' . $database->getVillageField($artefac['vref'], "name") . '</a></td>';
        	echo '<td class="dist">' . date("d/m/Y H:i", $artefac['conquered']) . '</td>';
       echo' </tr>';

        }
              }
?>

        </tbody>
    </table>

</div>
