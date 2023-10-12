<?php
ini_set('memory_limit', '-1');

if(isset($_GET['x']) && isset($_GET['y'])) {
	$x = $_GET['x'];
	$y = $_GET['y'];
}else {
	$vCoor = $database->getCoor($session->vid);
	$y = $vCoor['y'];
	$x = $vCoor['x'];
}

if (time() - filemtime("minimap2.gif") >= 3600) {
$ww=WORLD_MAX*2;
$img = imagecreatetruecolor($ww,$ww);
//imagecopyresized($img, imagecreatefromgif('minimap.gif'), 0, 0, 0, 0, $ww, $ww, 100, 100); //обводка
$wdata=$database->query("SELECT x,y,occupied,oasistype FROM wdata WHERE oasistype>0 or occupied>0");
    //$wdata=$database->query("SELECT x,y FROM wdata WHERE occupied>0 or type_of='lake'");
$lgreen = imagecolorallocate($img, 112,186,28);
imagefill($img, 0, 0, $lgreen);
foreach($wdata as $w){
    if($w['occupied']!=0 && $w['oasistype']==0){
   $color = imagecolorallocate($img, 255,0,0);
  //  }elseif($w['type_of']=='lake'){
   //     $color = imagecolorallocate($img, 0,0,255);
    }else
    if($w['oasistype']==3 || $w['oasistype']==6 || $w['oasistype']==9){
        $color = imagecolorallocate($img, 200,224,13);
    }elseif($w['oasistype']==0 && $w['occupied']==0){
        $color = imagecolorallocate($img, 112,186,28);
    }elseif($w['oasistype']>0 && $w['oasistype']<10){
        $color = imagecolorallocate($img, 170,186,20);
    }
    imagestring($img, 1, WORLD_MAX+$w['x'], WORLD_MAX+$w['y'],'.',$color);
}
imagejpeg($img,'minimap2.gif',IMGQUALITY);
imagedestroy($img);
}

?>
<?php


$xy_sort=$xy_coor=$map_symb='';
$movements=$map_info=$sel_vil=array();
if($session->plus){

    $move=$database->getmovvill2($village->wid);
    if(count($move)>0) {
        foreach($move as $mo){
            if(!in_array($mo['vref'],$sel_vil)){
                array_push($sel_vil,$mo['vref']);
                $xy_coor.='\''.$mo['vref'].'\',';
                switch($mo['sort_type']){
                    case 3:
                        if($mo['attack_type']==1){$movements[]="spy";}elseif($mo['attack_type']==2){ $movements[]="support";}else{$movements[]="attack";}
                        break;
                    case 4:
                        $movements[]="back";
                        break;
                }
            }
        }


    $xy_coor = (substr($xy_coor, 0, -1));
    $xy= $database->query("SELECT x,y  FROM wdata WHERE `id` IN (".$xy_coor.")");

foreach($movements as $key=>$m){
    $map_info[$key]=array('x'=>$xy[$key]['x'],'y'=>$xy[$key]['y'],'type'=>$m);
}
    foreach($map_info as $mi){
		if($mi['x']){ // check this later
			$map_symb.= '{"x": '.$mi['x'].', "y": '.$mi['y'].', "s": [{"dataId": "", "x": "'.$mi['x'].'", "y": "'.$mi['y'].'", "type": "attack", "parameters": {"attackType":"'.$mi['type'].'"}, "title": "'.$mi['type'].'", "text": "{a.atm1}\u003Cbr \/\u003E{a.ad} {a.ad3}"}]},';
		}
    }
    $map_symb = (substr($map_symb, 0, -1));
    }
}

?>
<div id="content" class="map">
    <h1 class="titleInHeader">الخارطة</h1><div class="map2">
	<div id="mapContainer" style="cursor: auto;">
		<div class="innerShadow">
			<div class="innerShadow-tl">
				<div class="innerShadow-tr">
					<div class="innerShadow-tc"></div>
				</div>
			</div>
			<div class="innerShadow-ml">
				<div class="innerShadow-mr"></div>
			</div>
			<div class="innerShadow-bl">
				<div class="innerShadow-br">
					<div class="innerShadow-bc"></div>
				</div>
			</div>
		</div>
		<div id="toolbar" class="toolbar">
			<div class="ml">
				<div class="mr">
					<div class="mc">
						<div class="contents">
							<div class="iconButton zoomIn"></div>
							<div class="iconButton zoomOut"></div>

							<div class="dropdown">
								<div class="dataContainer">
									<div class="entry display">100%
									</div>
									<div class="entry hide">50%
									</div>
																	</div>
								<div class="iconButton dropDownImage"></div>
								<div class="clear"></div>
							</div>
							<?php // this need plus ?>
                            <div class="iconButton <?php if(isset($_GET['fullscreen'])){ echo 'viewNormal checked'; }else{ echo 'viewFull'; } ?>"></div>
                            <?php if(!$session->goldclub){ ?>
                                <div class="iconButton iconRequireGold" id="iconCropfinder">
                            <?php } ?>
                            <div class="iconButton linkCropfinder"></div>
							<?php if(!$session->goldclub){ ?> </div> <?php } ?>
														<div class="text">فلتر</div>
							<div class="iconButton filterMy checked"></div>
							<div class="iconButton filterAlliance "></div>

							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="bl">
				<div class="br">
					<div class="bc"></div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			Travian.Game.Map.Options.Toolbar.filterPlayer.checked = true;
			Travian.Game.Map.Options.Toolbar.filterAlliance.checked = false;
		</script>
        <?php if(!$session->goldclub){ ?>
        <script type="text/javascript">window.addEvent('domready', function () {
					$('iconCropfinder').addEvent('click', function () {
						window.fireEvent("buttonClicked", [this, {
							"goldclubDialog": {
								"featureKey": "cropFinder",
								"infoIcon": "http:\/\/t4.answers.travian.com\/index.php?aid=Travian Answers#go2answer"
							}
						}]);
					})
				});</script>
		<?php } ?>
		<form id="mapCoordEnter" action="karte.php" method="get" class="toolbar">
			<div class="ml">
				<div class="mr">
					<div class="mc">
						<div class="contents">
							<div class="coordinatesInput">
								<div class="xCoord">
									<label for="xCoordInputMap">X:</label>
									<input value="<?php echo $x; ?>" name="x" id="xCoordInputMap" class="text coordinates x ">
								</div>
								<div class="yCoord">
									<label for="yCoordInputMap">Y:</label>
									<input value="<?php echo $y; ?>" name="y" id="yCoordInputMap" class="text coordinates y ">
								</div>
								<div class="clear"></div>
							</div>
							<button type="submit" class="green small" id="buttonSfiBQehEAoQ1M"><div class="button-container addHoverClick">
		<div class="button-background">
			<div class="buttonStart">
				<div class="buttonEnd">
					<div class="buttonMiddle"></div>
				</div>
			</div>
		</div>
		<div class="button-content">حسناً</div></div></button>
    <script type="text/javascript" id="buttonSfiBQehEAoQ1M_script">
    window.addEvent('domready', function()
        {
        if($('buttonSfiBQehEAoQ1M'))
        {
          $('buttonSfiBQehEAoQ1M').addEvent('click', function ()
          {
            window.fireEvent('buttonClicked', [this, {"value":"OK","class":"green small","id":"buttonSfiBQehEAoQ1M"}]);
          });
        }
        });
    </script>							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<div id="minimapContainer" >
			<div class="background"></div>
			<div class="headline">
				<div class="title"><?=MINIMAP?></div>
				<div class="iconButton small expand"></div>
				<div class="clear"></div>
			</div>
			<div id="miniMap" unselectable="on" style="overflow: hidden;">
				<img class="map" style="width: 185px; height: 109px;" src="minimap.php" alt="<?=MINIMAP?>">
                </div>

			<div class="bl">
				<div class="br">
					<div class="bc"></div>
				</div>
			</div>
		</div>
		<div id="outline">
			<div class="tl">
				<div class="tr">
					<div class="tc"></div>
				</div>
			</div>
			<div class="background"></div>
			<div id="mapMarks">
				<div class="headline">
					<div class="title">الإحداثيات</div>
					<div class="iconButton small expand"></div>
					<div class="clear"></div>
				</div>
				<div class="tabContainer" style="display: none;">
					<div class="tab">
						<div class="entry selected">
							<div class="tab-container">
								<div class="tab-position">
									<div class="tab-tl">
										<div class="tab-tr">
											<div class="tab-tc"></div>
										</div>
									</div>
									<div class="tab-ml">
										<div class="tab-mr">
											<div class="tab-mc"></div>
										</div>
									</div>
								</div>
								<div class="tab-contents">
									<a href="#" onclick="
								if (!$(this).up('.entry').hasClass('selected'))
								{
									$('tabPlayer').show();
									$('tabAlliance').hide();
									$(this).up('.entry').toggleClass('selected').next('.entry').toggleClass('selected');
								}
								$('mapContainer')._map.mapMarks.player.update(false);
								return false;
							">اللاعب:     </a>
								</div>
							</div>
						</div>
												<div class="clear"></div>
					</div>

					<div id="tabPlayer" class="dataTab"><div class="collapseContainer"><div class="title">التحالف <a href="#" class="add addButton">+</a></div><div class="iconButton expandButton small expand"></div><div class="clear"></div><div class="expandContainer" style="height: 0px;"><div class="jScroll"><div class="jScroll-content" style="height: auto; margin-top: 0px;"><div class="dataContainer"></div></div><div class="jScroll-scroll jScroll-element-scroll" style="display: none;"><div class="jScroll-scroll-up"><div class="jScroll-element-up"></div></div><div class="jScroll-scroll-between jScroll-element-between"></div><div class="jScroll-scroll-slider jScroll-element-slider"><div class="jScroll-element-slider top"></div><div class="jScroll-element-slider center"></div><div class="jScroll-element-slider bottom"></div></div><div class="jScroll-scroll-between jScroll-element-between"></div><div class="jScroll-scroll-down"><div class="jScroll-element-down"></div></div></div></div></div></div><div class="collapseContainer"><div class="title">اللاعب:  <a href="#" class="add addButton">+</a></div><div class="iconButton expandButton small expand"></div><div class="clear"></div><div class="expandContainer" style="height: 0px;"><div class="jScroll"><div class="jScroll-content" style="height: auto; margin-top: 0px;"><div class="dataContainer"></div></div><div class="jScroll-scroll jScroll-element-scroll" style="display: none;"><div class="jScroll-scroll-up"><div class="jScroll-element-up"></div></div><div class="jScroll-scroll-between jScroll-element-between"></div><div class="jScroll-scroll-slider jScroll-element-slider"><div class="jScroll-element-slider top"></div><div class="jScroll-element-slider center"></div><div class="jScroll-element-slider bottom"></div></div><div class="jScroll-scroll-between jScroll-element-between"></div><div class="jScroll-scroll-down"><div class="jScroll-element-down"></div></div></div></div></div></div><div class="collapseContainer"><div class="title">الأعلام <a href="#" class="add addButton">+</a></div><div class="iconButton expandButton small collapse"></div><div class="clear"></div><div class="expandContainer" style="height: 0px;"><div class="jScroll"><div class="jScroll-content" style="height: auto; margin-top: 0px;"><div class="dataContainer"></div></div><div class="jScroll-scroll jScroll-element-scroll" style="display: none;"><div class="jScroll-scroll-up"><div class="jScroll-element-up"></div></div><div class="jScroll-scroll-between jScroll-element-between"></div><div class="jScroll-scroll-slider jScroll-element-slider"><div class="jScroll-element-slider top"></div><div class="jScroll-element-slider center"></div><div class="jScroll-element-slider bottom"></div></div><div class="jScroll-scroll-between jScroll-element-between"></div><div class="jScroll-scroll-down"><div class="jScroll-element-down"></div></div></div></div></div></div></div>
					<div id="tabAlliance" class="dataTab"></div>
				</div>
			</div>
		</div>
    </div>
	
</div>

<?php
if(isset($_GET['fullscreen'])){
	include "application/views/Map/fullMap.php";
    ?>
    
    <?php
}else{
?>

<script type="text/javascript">
	Travian.Translation.add(
		{
			'k.spieler': 'اللاعب: ',
			'k.einwohner': 'السكان',
			'k.allianz': 'التحالف',
			'k.volk': 'القبيلة',
			'k.dt': 'قرية',
			'k.bt': 'واحة غير محتلة',
			'k.fo': 'واحة محتلة',
			'k.vt': 'الإحداثيات',
			'k.loadingData': 'إنتظر ..',

			'a.v1': 'الرومان',
			'a.v2': 'الجرمان',
			'a.v3': 'الإغريق',
			'a.v4': 'نتار',
			'a.v5': 'النتار',
			'a.v6': 'الفراعنة',
			'a.v7': 'المغول',

			'k.f1': '3-3-3-9',
			'k.f2': '3-4-5-6',
			'k.f3': '4-4-4-6',
			'k.f4': '4-5-3-6',
			'k.f5': '5-3-4-6',
			'k.f6': '1-1-1-15',
			'k.f7': '4-4-3-7',
			'k.f8': '3-4-4-7',
			'k.f9': '4-3-4-7',
			'k.f10': '3-5-4-6',
			'k.f11': '4-3-5-6',
			'k.f12': '5-4-3-6',
			'k.f99': 'قرية نتار',

			'b.ri1': 'لقد فزت بالهجوم دون خسائر.',
			'b.ri2': 'لقد فزت بالهجوم مع خسائر.',
			'b.ri3': 'لم ينج أحد من جنودك.',
			'b.ri4': 'لقد فزت بالدفاع دون خسائر.',
			'b.ri5': 'لقد فزت بالدفاع مع خسائر.',
			'b.ri6': 'تم إختراق القرية.',
			'b.ri7': 'لقد فزت بالدفاع مع خسائر.',

			'b:ri1': '&lt;img src="img/x.gif" class="iReport iReport1"/&gt;'.unescapeHtml(),
			'b:ri2': '&lt;img src="img/x.gif" class="iReport iReport2"/&gt;'.unescapeHtml(),
			'b:ri3': '&lt;img src="img/x.gif" class="iReport iReport3"/&gt;'.unescapeHtml(),
			'b:ri4': '&lt;img src="img/x.gif" class="iReport iReport4"/&gt;'.unescapeHtml(),
			'b:ri5': '&lt;img src="img/x.gif" class="iReport iReport5"/&gt;'.unescapeHtml(),
			'b:ri6': '&lt;img src="img/x.gif" class="iReport iReport6"/&gt;'.unescapeHtml(),
			'b:ri7': '&lt;img src="img/x.gif" class="iReport iReport7"/&gt;'.unescapeHtml(),

			'b:bi0': '&lt;img class="carry empty" src="img/x.gif" alt="مكافأة" /&gt;'.unescapeHtml(),
			'b:bi1': '&lt;img class="carry half" src="img/x.gif" alt="مكافأة" /&gt;'.unescapeHtml(),
			'b:bi2': '&lt;img class="carry" src="img/x.gif" alt="مكافأة" /&gt;'.unescapeHtml(),

			'a.r1': 'الخشب',
			'a.r2': 'الطين',
			'a.r3': 'الحديد',
			'a.r4': 'القمح',

			'a.ad': 'الصعوبة:',
					'a.atm1': 'مغامرة 1',
				'a.ad1': 'عادية',
		'a.atm2': 'مغامرة 2',
				'a.ad2': 'عادية',
		'a.atm3': 'مغامرة 3',
				'a.ad3': 'صعبة',
		'a.atm4': 'مغامرة 4',
				'a.ad4': 'صعبة',
		'a.atm5': 'مغامرة 5',
				'a.ad5': 'عادية',
		'a.atm6': 'مغامرة 6',
				'a.ad6': 'صعبة',
		'a.atm7': 'مغامرة 7',
				'a.ad7': 'عادية',

			'a:r1': '&lt;img alt="الخشب" src="img/x.gif" class="r1"&gt;'.unescapeHtml(),
			'a:r2': '&lt;img alt="الطين" src="img/x.gif" class="r2"&gt;'.unescapeHtml(),
			'a:r3': '&lt;img alt="الحديد" src="img/x.gif" class="r3"&gt;'.unescapeHtml(),
			'a:r4': '&lt;img alt="القمح" src="img/x.gif" class="r4"&gt;'.unescapeHtml(),

			'k.arrival': 'الوصول في',
			'k.ssupport': 'تعزيز',
			'k.sspy': 'تعزيز',
			'k.sreturn': 'إرجاع',
			'k.sraid': 'هجوم للنهب',
			'k.sattack': 'هجوم كامل'
		});
</script>

<script type="text/javascript">
	window.addEvent('domready', function () {
		var containerViewSize = {
			width: 540,
			height: 401
		};
		var fnDelayMe = function () {
			var fnInit = function () {
				Travian.Game.Map.Options.Rulers.steps = Object.merge({}, Travian.Game.Map.Options.Rulers.steps, {
					"x": {
						"1": 1,
						"2": 1,
						"3": 10,
						"4": 20
					}, "y": {"1": 1, "2": 1, "3": 10, "4": 20}
				});
				Travian.Game.Map.Options.Default.dataStore = Object.merge({}, Travian.Game.Map.Options.Default.dataStore, {
					"cachingTimeForType": {
						"blocks": 1800000,
						"symbol": 600000,
						"tile": 600000,
						"tooltip": 300000
					},
					"persistentStorage": false,
					"useStorageForType": {
						"blocks": false,
						"symbol": false,
						"tile": false,
						"tooltip": false
					},
					"clearStorageForType": {
						"blocks": false,
						"symbol": false,
						"tile": false,
						"tooltip": false
					}
				});
				Travian.Game.Map.Options.Default.updater = Object.merge({}, Travian.Game.Map.Options.Default.updater, {
					"maxRequestCount": 5,
					"requestDelayTime": {"multiple": 100, "position": 300},
					"url": "map_ajax.php",
					"positionOptions": {
						"areaAroundPosition": {
							"1": {
								"left": -5,
								"top": -4,
								"right": 5,
								"bottom": 4
							},
							"2": {
								"left": -10,
								"top": -8,
								"right": 10,
								"bottom": 8
							},
							"3": {
								"left": -15,
								"top": -15,
								"right": 15,
								"bottom": 15
							},
							"4": {
								"left": -15,
								"top": -15,
								"right": 15,
								"bottom": 15
							}
						}
					}
				});
				Travian.Game.Map.Options.Default.tileDisplayInformation.type = 'dialog';

				Travian.Game.Map.Options.MapMark.Flag.dialog.html = '<div class=\"mapMarkField\">\n	<div class=\"flag {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\">\n		<input id=\"coordinateDialogText\" class=\"text {inputText}\" type=\"text\" />\n	<\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';
				Travian.Game.Map.Options.MapMark.Mark.dialog.html = '<div class=\"mapMarkMark\">\n	<div class=\"color {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\"><\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';

				Travian.Game.Map.Options.Default.mapMarks.player.layers.alliance.dialog.html = '<div class=\"mapMarkMark\">\n	<div class=\"color {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\"><\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';
				Travian.Game.Map.Options.Default.mapMarks.player.layers.player.dialog.html = '<div class=\"mapMarkMark\">\n	<div class=\"color {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\"><\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';
				Travian.Game.Map.Options.Default.mapMarks.alliance.layers.alliance.dialog.html = '<div class=\"mapMarkMark\">\n	<div class=\"color {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\"><\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';
				Travian.Game.Map.Options.Default.mapMarks.alliance.layers.player.dialog.html = '<div class=\"mapMarkMark\">\n	<div class=\"color {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\"><\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';

				Travian.Game.Map.Options.Default.mapMarks.player.layers.flag.dialog.html = '<div class=\"mapMarkField\">\n	<div class=\"flag {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\">\n		<input id=\"coordinateDialogText\" class=\"text {inputText}\" type=\"text\" />\n	<\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';
				Travian.Game.Map.Options.Default.mapMarks.alliance.layers.flag.dialog.html = '<div class=\"mapMarkField\">\n	<div class=\"flag {select}\"><\/div>\n	<div class=\"{coord}\">\n		\n			<div class=\"coordinatesInput\">\n				<div class=\"xCoord\">\n					<label for=\"coordinateDialogX\">X:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"x\" id=\"coordinateDialogX\" class=\"text coordinates x {inputX}\" />\n				<\/div>\n				<div class=\"yCoord\">\n					<label for=\"coordinateDialogY\">Y:<\/label>\n					<input maxlength=\"4\" value=\"\" name=\"y\" id=\"coordinateDialogY\" class=\"text coordinates y {inputY}\" />\n				<\/div>\n				<div class=\"clear\"><\/div>\n			<\/div>\n			<\/div>\n	<div class=\"{textDisplay}\">\n		<input id=\"coordinateDialogText\" class=\"text {inputText}\" type=\"text\" />\n	<\/div>\n	<p class=\"error errorMessage\"><\/p>\n<\/div>';

				Travian.Game.Map.Tips.tooltipHtml = '<span class=\"coordinates coordinatesWrapper\"><span class=\"coordinateX\">({x}<\/span><span class=\"coordinatePipe\">|<\/span><span class=\"coordinateY\">{y})<\/span><\/span>';
				Travian.Game.Map.Options.Default.block.tooltipHtml = '<span class=\"coordinates coordinatesWrapper\"><span class=\"coordinateX\">({x}<\/span><span class=\"coordinatePipe\">|<\/span><span class=\"coordinateY\">{y})<\/span><\/span><br />{k.loadingData}';
				Travian.Game.Map.Options.MiniMap.tooltipHtml = '<span class=\"coordinates coordinatesWrapper\"><span class=\"coordinateX\">({x}<\/span><span class=\"coordinatePipe\">|<\/span><span class=\"coordinateY\">{y})<\/span><\/span>';

				new Travian.Game.Map.Container(Object.merge({}, Travian.Game.Map.Options.Default,
					{
						blockOverflow: 1,
						blockSize: {
							width: 600,
							height: 600
						},
						containerViewSize: containerViewSize,
						mapInitialPosition: {
							x: <?=$x?>,
							y: <?=$y?>},
						transition: {
							zoomOptions: {
								level: 1,
								sizes: [{"x": 10, "y": 10}, {
									"x": 20,
									"y": 20
								} ]
							}
						},
						data: {"elements":[<?=$map_symb?>]
                            ,"blocks":{}},
						mapMarks: {
							player: {
								data: [],
								layers: {
									alliance: {
										title: 'التحالف',
										dialog: {
											title: 'علامات التحالف الخاصة',
											textOkay: 'حفظ',
											textCancel: 'إلغاء'
										},
										optionsData: {
											urlLink: 'allianz.php?aid={markId}'
										}
									},
									player: {
										title: 'اللاعب: ',
										dialog: {
											title: 'العلامات الخاصة',
											textOkay: 'حفظ',
											textCancel: 'إلغاء'
										},
										optionsData: {
											urlLink: 'spieler.php?uid={markId}'
										}
									},
									flag: {
										title: 'الأعلام',
										dialog: {
											title: 'الأعللام الخاصة.',
											textOkay: 'حفظ',
											textCancel: 'إلغاء'
										}
									}
								}
							},
							alliance: {
								enabled: false,
								data: [],
								layers: {
									alliance: {
										editable: false,										title: 'التحالف',
										dialog: {
											title: 'علامات التحالف الخاصه',
											textOkay: 'حفظ',
											textCancel: 'إلغاء'
										},
										optionsData: {
											urlLink: 'allianz.php?aid={markId}'
										}
									},
									player: {
										editable: false,										title: 'اللاعب: ',
										dialog: {
											title: 'علامات لاعب تحالفي',
											textOkay: 'حفظ',
											textCancel: 'إلغاء'
										},
										optionsData: {
											urlLink: 'spieler.php?uid={markId}'
										}
									},
									flag: {
										editable: false,										title: 'الأعلام',
										dialog: {
											title: 'علامات تحالفي',
											textOkay: 'حفظ',
											textCancel: 'إلغاء'
										}
									}
								}
							}
						}
					}));
			};

			if ((!Browser.safari && !Browser.chrome) || $('mapContainer').getSize().y == containerViewSize.height) {
				fnInit();
			}
			else {
				fnInit();
			}
		};
		fnDelayMe();
	});
</script>                        <div class="clear"></div>
                    </div>
<?php }  ?>