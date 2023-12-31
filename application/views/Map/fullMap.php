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
				'a.ad1': 'صعبة',
		'a.atm2': 'مغامرة 2',
				'a.ad2': 'صعبة',
		'a.atm3': 'مغامرة 3',
				'a.ad3': 'صعبة',
		'a.atm4': 'مغامرة 4',
				'a.ad4': 'عادية',
		'a.atm5': 'مغامرة 5',
				'a.ad5': 'عادية',

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
			width: 543,
			height: 401
		};
		var fnDelayMe = function () {
						if ($('betaBox')) {
				$('betaBox').dispose();
			}

			var fullScreenSize = $(window.document).getCoordinates();
			var body = $(document.body).addClass('fullScreen');
			var mapContainer = $('mapContainer').dispose();

			containerViewSize.width = fullScreenSize.width - 25; // rulers Y left || right
			containerViewSize.height = fullScreenSize.height - 15; // rulers X
			mapContainer.inject(body).setStyles(
				{
					position: 'absolute',
					left: fullScreenSize.left + 0, // rulers Y left || right
					top: fullScreenSize.top,
					width: containerViewSize.width,
					height: containerViewSize.height
				});
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
				fnDelayMe.delay(100);
			}
		};
		fnDelayMe();
	});
</script>                        <div class="clear"></div>
