
<?php 
$html = '<div id="smallestPackageDialog">
ليس لديك ما يكفي من الذهب لاستخدام هذه الميزة!	<div id="smallestPackageData">
    <div class="package size1 hideForLoading">
        <input type="hidden" class="goldProductId" value="1">
        <div class="goldProductTextWrapper">
            <div class="goldUnits">'.$packages[0]['gold'].'</div>
            <div class="goldUnitsTypeText">الذهب</div>
            <div class="footerLine"><span class="price">'.$packages[0]['cost'].' '.$packages[0]['currency'].'&nbsp;*</span></div>
        </div>
        <div class="goldProductImageWrapper"><img src="img/product/'.$packages[0]['img'].'" width="100" height="114" alt="Package A"></div>
    </div>
</div>
<span class="buyGoldQuestion">شراء الآن ؟</span>
<div>
    <button type="submit" value="شراء الذهب" id="buttontOphouP10TsMd" class="green " onclick="openPaymentWizard(true); return false;">
        <div class="button-container addHoverClick ">
            <div class="button-background">
                <div class="buttonStart">
                    <div class="buttonEnd">
                        <div class="buttonMiddle"></div>
                    </div>
                </div>
            </div>
            <div class="button-content">شراء الذهب</div>
        </div>
    </button>
    <script type="text/javascript">
        window.addEvent(\'domready\', function () {
            if ($(\'buttontOphouP10TsMd\')) {
                $(\'buttontOphouP10TsMd\').addEvent(\'click\', function () {
                    window.fireEvent(\'buttonClicked\', [this, {
                        "type": "submit",
                        "value": "Purchase gold",
                        "name": "",
                        "id": "buttontOphouP10TsMd",
                        "class": "green ",
                        "title": "شراء الذهب",
                        "confirm": "",
                        "onclick": "openPaymentWizard(true); return false;"
                    }]);
                });
            }
        });
    </script>
</div>
<a class="changeGoldPackage arrow" href="#" onclick="openPaymentWizard(false); return false;">إختر حزمة أخرى</a>
<script>

    function openPaymentWizard(withPackage) {
        var options = {callback: \'openPaymentWizardWithProsTab\'};
        if (withPackage) {
            options = Object.merge(options, {goldProductId: \'7\'});
        }
        Travian.Game.WayOfPaymentEventListener.WayOfPaymentObject.openPaymentWizard(options);
    }
</script>
</div>';
echo '{"response":{"error":false,"errorMsg":null,"data":{"functionToCall":"renderDialog","options":{"dialogOptions":{"infoIcon":"http:\/\/t4.answers.travian.com\/index.php?aid=368#go2answer","saveOnUnload":false,"draggable":false,"buttonOk":false,"context":"smallestPackage"},"html":'.json_encode($html).'}}}}';
