<form id="settings" action="options.php" method="post">
<input type="hidden" name="ft" value="p5">
<input type="hidden" name="e" value="1">
<!--
<h4 class="round">فلتر التقارير</h4>
<table class="transparent set" cellpadding="1" cellspacing="1" id="report_filter">
		<tbody>
		<tr>
			<td class="sel">
				<input class="check" type="checkbox" name="v4" value="1" id="report_filter_option_4">
			</td>
			<td>
				<label for="report_filter_option_4">لا تقارير في تبادل الموارد بين قراكم الخاصة</label>
			</td>
		</tr>
		<tr>
			<td class="sel">
				<input class="check" type="checkbox" name="v5" value="1" id="report_filter_option_5">
			</td>
			<td>
				<label for="report_filter_option_5">لا تقارير في إرسال الموارد الى القرى الأخرى</label>
			</td>
		</tr>
		<tr>
			<td class="sel">
				<input class="check" type="checkbox" name="v6" value="1" id="report_filter_option_6">
			</td>
			<td>
				<label for="report_filter_option_6">لا تقارير في إستقبال الموارد من القرى الأخرى</label>
			</td>
		</tr>
		</tbody>
	</table>
	<h4 class="round spacer">إعدادات التحالف</h4>
	<table class="transparent set" cellpadding="1" cellspacing="1" id="alliance">
		<tbody>
		<tr>
			<td class="sel">
				<input class="check" checked="checked" type="checkbox" name="v12" value="1">
			</td>
			<td>تلقّي إشعارات في حال دُعيت لتحالف ما.</td>
		</tr>
		</tbody>
	</table>
	<table class="transparent set" cellpadding="1" cellspacing="1" id="alliance">
			<tbody>
			<tr>
				<td colspan="2">عرض أخبار التحالف					:
				</td>
			</tr>
			<tr>
				<td class="sel">
					<input class="check" type="checkbox" name="v7" value="1">
				</td>
				<td>أسس أعضاء التحالف قرية جديدة</td>
			</tr>
			<tr>
				<td class="sel">
					<input class="check" type="checkbox" name="v8" value="1">
				</td>
				<td>انضم عضو تحالف جديد</td>
			</tr>
			<tr>
				<td class="sel">
					<input class="check" type="checkbox" name="v9" value="1">
				</td>
				<td>تمت دعوة لاعب إلى التحالف</td>
			</tr>
			<tr>
				<td class="sel">
					<input class="check" type="checkbox" name="v10" value="1">
				</td>
				<td>اللاعب ترك التحالف</td>
			</tr>
			<tr>
				<td class="sel">
					<input class="check" type="checkbox" name="v11" value="1">
				</td>
				<td>اللاعب طُرد من التحالف</td>
			</tr>
			</tbody>
		</table>
		<h4 class="round spacer">الإكمال التلقائي</h4>
		<table class="transparent set" cellpadding="1" cellspacing="1" id="completion">
		<tbody>
		<tr>
			<td colspan="2">تستخدم لنقطة التجمع والسوق:				:
			</td>
		</tr>
		<tr>
			<td class="sel">
				<input class="check" checked="checked" type="checkbox" name="v1" value="1">
			</td>
			<td>
				القرى الخاصة                     </td>
		</tr>
		<tr>
			<td class="sel">
				<input class="check" type="checkbox" name="v2" value="1">
			</td>
			<td>
				قرى الجوار</td>
		</tr>
		<tr>
			<td class="sel">
				<input class="check" type="checkbox" name="v3" value="1">
			</td>
			<td>
				قرى الأعضاء في التحالف                    </td>
		</tr>
		</tbody>
	</table>
	<h4 class="round spacer">اعرض</h4>
	<table class="transparent set" cellpadding="1" cellspacing="1" id="entriesPerPage">
		<tbody>
		<tr>
			<td>
				<label for="epp">رسائل وتقارير لكل صفحة:					:</label>
			</td>
			<td>
				<input type="text" maxlength="2" value="10" id="epp" name="epp" class="text messageReport">
			</td>
		</tr>
		<tr>
			<td>
				<label for="troopMovementsPerPage">عدد تحركّات القوّات لكل صفحة في نقطة التجمّع					:</label>
			</td>
			<td>
				<input type="text" maxlength="3" value="10" id="troopMovementsPerPage" name="troopMovementsPerPage" class="text troopMovementsPerPage">
			</td>
		</tr>
		</tbody>
	</table>
<style>
div.options #timeSettings label, div.options #advertisement label {
    margin: 6px 0;
    display: block;
}

div.options table.set input.radio {
    position: relative;
    top: 1px;
}</style>
<h4 class="round spacer">Time zone preferences</h4>
<table class="transparent set" cellpadding="1" cellspacing="1" id="timeSettings">
		<tbody>
		<tr>
			<td colspan="2">You can change your time zone here.</td>
		</tr>
		<tr>
			<th>
				Time zone:
			</th>
			<td>
				<select name="timezone">
					<optgroup label="local time zones">
						<option value="441">Canada/Newfoundland</option>
						<option value="99">Europe/Paris</option>
						<option value="495">Europe/Berlin</option>
						<option value="496" selected="selected">Europe/London</option>
						<option value="497">Asia/Amman</option>
						<option value="570">Saudi Arabia</option>
						<option value="328">Asia/Calcutta</option>
						<option value="562">Australia/ACT</option>
					</optgroup>
					<optgroup label="general time zones">
						<option value="0">UTC +1</option>
						<option value="1">UTC +2</option>
						<option value="2">UTC +3</option>
						<option value="3">UTC +4</option>
						<option value="4">UTC +5</option>
						<option value="5">UTC +6</option>
						<option value="6">UTC +7</option>
						<option value="7">UTC +8</option>
						<option value="8">UTC +9</option>
						<option value="9">UTC +10</option>
						<option value="10">UTC +11</option>
						<option value="11">UTC +12</option>
						<option value="12">UTC -11</option>
						<option value="13">UTC -10</option>
						<option value="14">UTC -9</option>
						<option value="15">UTC -8</option>
						<option value="16">UTC -7</option>
						<option value="17">UTC -6</option>
						<option value="18">UTC -5</option>
						<option value="19">UTC -4</option>
						<option value="20">UTC -3</option>
						<option value="21">UTC -2</option>
						<option value="22">UTC -1</option>
						<option value="23">UTC</option>
					</optgroup>
				</select>
			</td>
		</tr>
		<tr>
			<th class="timeFormat">
				Date format:
			</th>
			<td>
				<label>
					<input class="radio" checked="checked" type="radio" name="tformat" value="0"> EU (dd.mm.yy 24h) </label>
				<label>
					<input class="radio" type="radio" name="tformat" value="1"> US (mm/dd/yy 12h) </label>
				<label>
					<input class="radio" type="radio" name="tformat" value="2"> UK (dd/mm/yy 12h) </label>
				<label>
					<input class="radio" type="radio" name="tformat" value="3"> ISO (yy/mm/dd 24h) </label>
			</td>
		</tr>
		</tbody>
	</table>
-->
<h4 class="round spacer"><?php echo $lang['main']['options'][2]; ?></h4>
<table class="transparent set" cellpadding="1" cellspacing="1" id="languageSettings">
		<tbody>
		<tr>
			<th>
			<?php echo $lang['main']['options'][3]; ?>:</th>
			<td>
				<select name="languageNew">
					<option value="en" <?php if($session->lang == 'en'){ echo 'selected="selected"'; } ?>><?php echo $lang['main']['options'][4]; ?></option>
                    <option value="ar" <?php if($session->lang == 'ar'){ echo 'selected="selected"'; } ?>><?php echo $lang['main']['options'][5]; ?></option>
                </select>
			</td>
		</tr>
		</tbody>
	</table>
    <div class="submitButtonContainer">
		<button type="submit" value="save" name="s1" id="btn_ok" class="green disabled" disabled="">
			<div class="button-container addHoverClick">
				<div class="button-background">
					<div class="buttonStart">
						<div class="buttonEnd">
							<div class="buttonMiddle"></div>
						</div>
					</div>
				</div>
				<div class="button-content"><?php echo $lang['main']['options'][6]; ?></div>
			</div>
		</button>
		<script type="text/javascript">
			window.addEvent('domready', function () {
				if ($('btn_ok')) {
					$('btn_ok').addEvent('click', function () {
						window.fireEvent('buttonClicked', [this, {
							"type": "submit",
							"value": "save",
							"name": "s1",
							"id": "btn_ok",
							"class": "green ",
							"title": "",
							"confirm": "",
							"onclick": ""
						}]);
					});
				}
			});
		</script>
	</div>
</form>
    <script type="text/javascript">
	window.addEvent('domready', function () {
		Travian.Form.UnloadHelper.watchHtmlForm($('settings'));

	});
</script>