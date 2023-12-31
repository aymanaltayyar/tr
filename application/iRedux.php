<?php 
require 'library/PHPMailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'library/PHPMailer/src/Exception.php';
require 'library/PHPMailer/src/PHPMailer.php';
require 'library/PHPMailer/src/SMTP.php';

//require 'config.php';

Class iRedux{
    function __construct(){
        global $config;
        $this->host = $config['SMTPHost'];
        $this->Username = $config['SMTPUser'];
        $this->Password = $config['SMTPPass'];
        $this->sender = SERVER_NAME;
    }

    public function sendPassword($email,$username,$npw,$code){
        $mail = new PHPMailer;

        // if have smtp
        if($this->host && $this->Username && $this->Password){
            $mail->IsSMTP();
            $mail->Mailer = "smtp";
            $mail->SMTPDebug  = 0;  
            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;
            $mail->Host       = $this->host;
            $mail->Username   = $this->Username;
            $mail->Password   = $this->Password;    
        }else{
            $mail->From = adminMail;
            $mail->FromName = SERVER_NAME;    
        }
        
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML(False);
        $mail->AddAddress($email, $username);
        $mail->SetFrom($this->Username, $this->sender);        
        $mail->Subject = 'طلب كلمة سر جديدة - '.SERVER_NAME;
        
        $content = "
        <br />
            <div style=\"direction:rtl; text-align: right\"><br />
            مرحباً ".$username."<br />
            لقد طلبت إرسال كلمة المرور الخاصة بك في<br />
            ترافيان. في الرسالة التالية ستجد كل ما<br />
            يتعلق بمعلومات دخولك على عضويتك في لعبة<br />
            ترافيان:<br />
            ---------------------------------------------------------------------------------------------------------------------------------<br />
            <br />
            بيانات الدخول:<br />
            <br />
            اسم اللاعب:  ".$username."<br />
            عنوان البريد الإلكتروني:  ".$email."<br />
            كلمة المرور: ".$npw."<br />
            رابط السيرفر: ".SERVER_NAME."<br />
            <br />
            ---------------------------------------------------------------------------------------------------------------------------------<br />
            قم رجاء بالضغط على الرابط التالي لتفعيل<br />
            كلمة السر الجديدة. بعد الضغط يتم إلغاء كلمة<br />
            السر القديمة وتصبح باطلة:<br />
            <a href='".HOMEPAGE."password.php?code=$code&npw=$npw&user=$username'>".HOMEPAGE."password.php?code=$code&npw=$npw&user=$username</a><br />
            <br />
            في حال أردت إعادة تعيين كلمة السر هذه،<br />
            يتوجب عليك الدخول لصفحة العضوية، وبعدها<br />
            اختيار القائمة \"العضوية\".<br />
            <br />
            في حال أنك تذكرت كلمة السر الخاصة بك، أو<br />
            أنك لم تقم بالاساس بطلبها، تجاهل هذه<br />
            الرسالة رجاء.<br />
            <br />
            مع تحيات فريق ترافيان<br />";

        $mail->MsgHTML($content); 
        if(!$mail->Send()) {
            echo "Error while sending Email.";
        } else {
            echo "Email sent successfully";
        }

    }
    public function sendActivate($email,$username,$pass,$act){
        $mail = new PHPMailer;
        // if have smtp
        if($this->host && $this->Username && $this->Password){
            $mail->IsSMTP();
            $mail->Mailer = "smtp";
            $mail->SMTPDebug  = 0;  
            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;
            $mail->Host       = $this->host;
            $mail->Username   = $this->Username;
            $mail->Password   = $this->Password;
        }else{
            $mail->From = adminMail;
            $mail->FromName = SERVER_NAME;    
        }
        
        $mail->CharSet = 'UTF-8';
        $mail->IsHTML(true);
        $mail->AddAddress($email, $username);
        $mail->SetFrom($this->Username, $this->sender);        
        $mail->Subject = 'شكرا لتسجيلك في سیرفر ترافیان - '.SERVER_NAME;
        
        $content = '
        <!DOCTYPE html>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
                xmlns:o="urn:schemas-microsoft-com:office:office">
            <head><title></title>  <!--[if !mso]><!-- -->
                <meta http-equiv="X-UA-Compatible" content="IE=edge">  <!--<![endif]-->
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style type="text/css">  #outlook a {
                        padding: 0;
                    }

                    .ReadMsgBody {
                        width: 100%;
                    }

                    .ExternalClass {
                        width: 100%;
                    }

                    .ExternalClass * {
                        line-height: 100%;
                    }

                    body {
                        margin: 0;
                        padding: 0;
                        -webkit-text-size-adjust: 100%;
                        -ms-text-size-adjust: 100%;
                    }

                    table, td {
                        border-collapse: collapse;
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                    }

                    img {
                        border: 0;
                        height: auto;
                        line-height: 100%;
                        outline: none;
                        text-decoration: none;
                        -ms-interpolation-mode: bicubic;
                    }

                    p {
                        display: block;
                        margin: 13px 0;
                    }</style><!--[if !mso]><!-->
                <style type="text/css">  @media only screen and (max-width: 480px) {
                        @-ms-viewport {
                            width: 320px;
                        }    @viewport {
                            width: 320px;
                        }
                    }</style><!--<![endif]--><!--[if mso]>
                <xml>
                    <o:OfficeDocumentSettings>
                        <o:AllowPNG/>
                        <o:PixelsPerInch>96</o:PixelsPerInch>
                    </o:OfficeDocumentSettings>
                </xml><![endif]--><!--[if lte mso 11]>
                <style type="text/css">  .outlook-group-fix {
                    width: 100% !important;
                }</style><![endif]--><!--[if !mso]><!-->
                <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
                <link href="https://fonts.googleapis.com/css?family=Merriweather" rel="stylesheet" type="text/css">
                <style type="text/css">@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
                    @import url(https://fonts.googleapis.com/css?family=Merriweather);    </style>  <!--<![endif]-->
                <style type="text/css">  @media only screen and (min-width: 480px) {
                        .mj-column-per-100 {
                            width: 100% !important;
                        }
                    }</style>
            </head>
            <body style="background: #FFFFFF;">
            <div class="mj-container" style="background-color:#FFFFFF; direction: "><!--[if mso | IE]>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" align="center"
                    style="width:600px;">
                    <tr>
                        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">      <![endif]-->
                <div style="margin:0px auto;max-width:600px;background:#ECECEC;">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:#ECECEC;"
                        align="center" border="0">
                        <tbody>
                        <tr>
                            <td style="text-align:center;vertical-align:top;direction:;font-size:0px;padding:0px 0px 0px 0px;">
                                <!--[if mso | IE]>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="vertical-align:middle;width:600px;">      <![endif]-->
                                <div class="mj-column-per-100 outlook-group-fix"
                                    style="vertical-align:middle;display:inline-block;direction:;font-size:13px;text-align:right;width:100%;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" style="vertical-align:middle;"
                                        width="100%" border="0">
                                        <tbody>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:0px 0px 0px 0px;" align="center">
                                                <table role="presentation" cellpadding="0" cellspacing="0"
                                                    style="border-collapse:collapse;border-spacing:0px;" align="center"
                                                    border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td style="width:600px;"><a href="'.HOMEPAGE.'"
                                                                                    target="_blank"><img alt="" title=""
                                                                                                        height="auto"
                                                                                                        src="'.HOMEPAGE.'newsletter/header_image.jpg"
                                                                                                        style="border:none;border-radius:0px;display:block;font-size:13px;outline:none;text-decoration:none;width:100%;height:auto;"
                                                                                                        width="600"></a></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:0px 20px 0px 20px;"
                                                align="center">
                                                <div style="cursor:auto;color:#000000;font-family:Merriweather, Georgia, serif;font-size:10px;line-height:22px;text-align:center;">
                                                    <h1 style="font-family: &apos;Merriweather&apos;, Georgia, serif; font-size: 32px; color: #4E4E4E; line-height: 100%;">
                                                        <span style="font-size:22px;">لقد سجلت في '.SERVER_NAME.'.</span>
                                                    </h1></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:0px 43px 0px 43px;"
                                                align="right">
                                                <div style="cursor:auto;color:#000000;font-family:Merriweather, Georgia, serif;font-size:10px;line-height:22px;text-align:right;">
                                                    <p>
                                                        <span style="font-size:16px;">مرحباً <strong>'.$username.'</strong>,</span>
                                                    </p>
                                                    <p>
                                                        <span style="font-size:16px;">شكراً لتسجيلك في  '.SERVER_NAME.'.</span>
                                                    </p>
                                                    <p>
                                                        <span style="font-size:16px;">للتحقق من البريد الإلكتروني لي<strong>تنشيط</strong> يرجى النقر على الرابط في البريد الإلكتروني أو إدخال الرمز الذي تلقيته هنا للتحقق من ملكية عنوان البريد الإلكتروني.</span>
                                                    </p>
                                                    <p><br><span
                                                                style="font-size:16px;">رمز التفعيل: <strong>'.$act.'</strong></span>
                                                    </p></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:27px 0px 27px 0px;"
                                                align="center">
                                                <table role="presentation" cellpadding="0" cellspacing="0"
                                                    style="border-collapse:separate;" align="center" border="0">
                                                    <tbody>
                                                    <tr>
                                                        <td style="border:none;border-radius:24px;color:#fff;cursor:auto;padding:10px 25px;"
                                                            align="center" valign="middle" bgcolor="#6FDB59"><a
                                                                    href="'.HOMEPAGE.'activate.php?code='.$act.'"
                                                                    style="text-decoration:none;background:#6FDB59;color:#fff;font-family:Ubuntu, Helvetica, Arial, sans-serif, Helvetica, Arial, sans-serif;font-size:30px;font-weight:normal;line-height:120%;text-transform:none;margin:0px;"
                                                                    target="_blank">تفعيل</a></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="word-wrap:break-word;font-size:0px;padding:53px 20px 53px 20px;"
                                                align="center">
                                                <div style="cursor:auto;color:#949494;font-family:Merriweather, Georgia, serif;font-size:10px;line-height:22px;text-align:center;">
                                                    <p><span style="font-size:12px;">Copyright &#xA9; 2017&#xA0; Travian Speed Team, All rights reserved.&#xA0;<br>لقد تلقيت هذا البريد الإلكتروني لأنك سجلت على موقعنا&#xA0;</span>
                                                    </p></div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--[if mso | IE]>      </td></tr></table>      <![endif]--></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!--[if mso | IE]>      </td></tr></table>      <![endif]--></div>
            </body>
            </html>';
        
        $mail->MsgHTML($content); 
        if(!$mail->Send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
            //var_dump($mail);
        } else {
            echo "Email sent successfully";
        }
        
            
    }
}

$iRed = new iRedux;

//$iRed->sendActivate('gmy6200@gmail.com','iRedux',0,0,0);