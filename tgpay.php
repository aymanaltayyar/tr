<?php 

include('application/Account.php');
include('application/library/Paypal/paypal.class.php');

if(isset($_GET['provider']) && $_GET['provider'] == 11){ // Bank
	?>
	<head>
		<title>الشحن عبر البنك</title>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
	</head>
	<body style="direction:rtl;text-align:right;">
	<div class="containter mr-5 ml-5">
	<br>
	<center><h4>الشحن عبر البنوك</h4>
	الخدمة تعمل 24 ساعة يمكنك التحويل عبر جميع البنوك
	<div class="row mt-3">
	<div class="col-md-4">
	<div class="card">
		<div class="card-header">مصرف الراجحي</div>
		<div class="card-body">
			للإستفسار عن الإسم ورقم الحساب والايبان راسل ادارة الحسابات على واتس اب
		</div>
	</div>
	</div>
	<div class="col-md-4">
	<div class="card">
		<div class="card-header">البنك الاهلى</div>
		<div class="card-body">
			للإستفسار عن الإسم ورقم الحساب والايبان راسل ادارة الحسابات على واتس اب
		</div>
	</div>
	</div>
	<div class="col-md-4">
	<div class="card">
		<div class="card-header">بنك الرياض</div>
		<div class="card-body">
			للإستفسار عن الإسم ورقم الحساب والايبان راسل ادارة الحسابات على واتس اب
		</div>
	</div>
	</div>
	<div class="col-md-12">
	<div class="card mt-3">
		<div class="card-header">مصرف الانماء</div>
		<div class="card-body">
			للإستفسار عن الإسم ورقم الحساب والايبان راسل ادارة الحسابات على واتس اب
		</div>
	</div>
	</div>
	</div>
	</center>
	<div class="card mt-3 mb-5">
		<div class="card-header">بيانات الادارة والتحويل</div>
		<div class="card-body">
		واتس اب : <b class="alert-danger">00201090115254</b> متوفرين 24 ساعة
			<br>
			<br>
			<ul>
				<li>استلام الذهب فور التحويل , اقل مبلغ للتحويل هو 100 ريال</li>
				<li>يجب عليك ارسال صورة من وصل التحويل او لقطة شاشة جوال لاثبات التحويل</li>
				<li>واسم المحول واسم البنك المحول منه او اى تاكيد لتحويل المبلغ على واتس اب</li>
				<li>يتوفر رقم هاتف الدعم 24 ساعة : واتس اب : تواصل مع الادارة للحصول عليه</li>

			</ul>
		</div>
	</div>

	</div>
	</body>
	<?php
}else{
	// Payment success or nah
	if(isset($_GET['success'])){
		if(!isset($_POST['PAYMENT_ID']) || !isset($_POST['PAYEE_ACCOUNT']) || !isset($_POST['PAYMENT_AMOUNT']) || !isset($_POST['PAYMENT_UNITS']) || !isset($_POST['PAYMENT_BATCH_NUM']) || !isset($_POST['PAYER_ACCOUNT']) || !isset($_POST['TIMESTAMPGMT'])){			
			$response = $Paypal->request('GetExpressCheckoutDetails', array(
				'TOKEN' => $_GET['token']
			));
				
			// check if the response if TRUE of 0
			if ($response) {
				if ($response['CHECKOUTSTATUS'] == 'PaymentActionCompleted') {
					die('تم الدفع بالفعل!');
					}
			} else { // Error  !
				die('Error!');
			}
				
			// add the payment data and the new fundus to user account
			$params = array(
				'TOKEN' => $_GET['token'],
				'PAYERID' => $_GET['PayerID'],
				'PAYEMENTACTION' => 'Sale',
				'PAYMENTREQUEST_0_AMT' => $_SESSION['cost'],
				'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
				'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
				'PAYMENTREQUEST_0_ITEMAMT' => $_SESSION['cost'],
			);
			$response = $Paypal->request('DoExpressCheckoutPayment', $params);
			if($response['PAYMENTINFO_0_TRANSACTIONID'] != ''){
				$database->query("INSERT INTO payments VALUES(NULL,'Paypal','".$response['PAYMENTINFO_0_TRANSACTIONID']."',".time().",".$session->uid.",".$_SESSION['goldAmount'].",'".$_SESSION['cost']."')");
				$database->query("UPDATE users set gold = gold + ".$_SESSION['goldAmount']." where id = ".$session->uid."");
				$database->sendMessage($session->uid, 6, 'تم الشحن','تم شحن رصيدك بقيمة '.$_SESSION['goldAmount'].' ذهبة، رصيد السابق '.$session->gold.' ورصيدك الحالي '.($session->gold + $_SESSION['goldAmount']).'.', 0, 0, 0, 0,0);
				$database->sendMessage(6, 6, 'شحن جديد','قام الاعب "'.$session->username.'" بشحن '.$_SESSION['goldAmount'].' ذهبة، مقابل مبلغ '.$_SESSION['cost'].'. رصيده السابق '.$session->gold.' ورصيده الحالي '.($session->gold + $_SESSION['goldAmount']).'.', 0, 0, 0, 0,0);
				echo "تم شحن الذهب، يمكنك غلق هذه الصفحة الأن."; die();
			}else{
				echo "هناك مشكلة فى عملية الدفع، حاول مرة أخري."; die();
			}
		}
			
		unset($_SESSION['goldAmount']);
		unset($_SESSION['cost']);
	}elseif(isset($_GET['failed'])){
		
	}else{ // Paypal redirect
		if(isset($_GET) && !empty($_GET)){
			foreach($packages as $package){
				if($package['id'] == $_GET['product']){
					$_SESSION['goldAmount'] = $package['gold'];
					$_SESSION['cost'] = $package['cost'];

					$params = array(
						'RETURNURL' => HOMEPAGE.'tgpay.php?success',
						'CANCELURL' => HOMEPAGE.'tgpay.php?error',
						'PAYMENTREQUEST_0_AMT' => $package['cost'],
						'PAYMENTREQUEST_0_CURRENCYCODE' => $package['currency'],
					);
					$response = $Paypal->request('SetExpressCheckout', $params);
					if ($response) {
						header('Location: '.$Paypal->getUrl($response).'');
					} else {
						die("Cannot get response");
					}

				}
			}
		}
 } 
}?>