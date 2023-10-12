<?php
header('Content-Type: text/html; charset=UTF-8');

set_time_limit(0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

//////////////////////////////////
//   ****  SQL SETTINGS  ****   //
//////////////////////////////////
define("SQL_SERVER", "%SSERVER%");
define("SQL_USER", "%SUSER%");
define("SQL_PASS", "%SPASS%");
define("SQL_DB", "%SDB%");

define('APP_MAIN_PATH', dirname(realpath(__DIR__)));
define('APP_BASE_PATH', realpath(__DIR__));
include_once(realpath(__DIR__)."/DB.php");
$sData = $db->queryFetch("SELECT * FROM config");

define("SERVER_NAME",$sData['SERVER_NAME']); // عنوان السيرفر
define("DEFAULT_GOLD",$sData['DEFAULT_GOLD']);  // الذهب عند التسجيل
define("AUCTIONTIME",$sData['AUCTIONTIME']); // وقت المزاد بالثانية
define("GP_LOCATE", $sData['GP_LOCATE']); // خليها
define("OPENING", $sData['OPENING']); // الافتتاح 
define("REF_POP",500); //
define("REF_GOLD",50);
define("OASISX",$sData['OASISX']); // نسب الواحات
define("SPEED", $sData['SPEED']); // سرعة السيرفر
define("MAX_FILES",1000);
define("MAX_FILESH",3000);
define("IMGQUALITY",50);
define("MOMENT_TRAIN",$sData['MOMENT_TRAIN']); // التدريب فورا اذا كانت سرعة التدريب 0 ثانية
define("QUEST",True);
define("ARTEFACTS",$sData['ARTEFACTS']); // موعد التحف
define("WW_PLAN",$sData['WW_PLAN']); // موعد مخططات البناء
define("CRANNY_CAP",$sData['CRANNY_CAP']); // حجم المخابيء
define("ADV_TIME",86400/$sData['ADV_TIME']); // وقت المغامرة
define("TRAPPER_CAPACITY",$sData['TRAPPER_CAPACITY']); // حجم الافخاخ

define("TRI5BES", FALSE); // TRUE OR FALSE

define("MAX_UNIT",60); // 
define("MAX_TRIBE",6);
// ***** Change storage capacity
define("STORAGE_MULTIPLIER",$sData['STORAGE_MULTIPLIER']); // حجم المخازن
define("STORAGE_BASE",800*STORAGE_MULTIPLIER);

// ***** World size
// Defines world size. NOTICE: DO NOT EDIT!!
define("WORLD_MAX", "100");

define("INCREASE_SPEED",$sData['INCREASE_SPEED']); // سرعة القوات

// ***** Beginners Protection
define("PHOUR", "3600");
define("PROTECTIOND",$sData['PROTECTIOND']);
$timestoup = 0;
$fromstart=time()-OPENING;
if($fromstart>=42300){
$timestoup=floor($fromstart/42300);
}

define("PROTECTION",PROTECTIOND);
// ***** Trader capacity
// Values: 1 (normal), 3 (3x speed) etc...
define("TRADER_CAPACITY",$sData['TRADER_CAPACITY']); // 

define("INCLUDE_ADMIN",True);

define("CP", "1");

// ***** PLUS
//Plus account lenght
define("PLUS_TIME",$sData['PLUS_TIME']);
//+25% production lenght
define("PLUS_PRODUCTION",$sData['PLUS_PRODUCTION']);
// ***** через сколько клеток начинается действие арены
define("TS_THRESHOLD",20);


//////////////////////////////////////////
//   ****  DO NOT EDIT SETTINGS  ****   //
//////////////////////////////////////////
define("ALLOW_ALL_TRIBE",false);
define("USRNM_MIN_LENGTH",3);
define("PW_MIN_LENGTH",4);
define("BANNED",0);
define("MULTIHUNTER",8);
define("ADMIN",9);
define("COOKIE_EXPIRE", 60*60*24*7);
define("COOKIE_PATH", "/");
define("HOMEPAGE", $sData['HOMEPAGE']);
define("MAXLENGHT","15");
define("RADIUS",2); //делитель для максимального радиуса выпадения артов,например если карта 400 а делитель 2 то дальше 200х200 арт НЕ улетит


// New setting
// True -> تفعيل , False -> تعطيل
define("MAX_LEVEL", TRUE); // تفعيل تطوير المبني للمستوي الأخير بالذهب
define("MAX_LEVEL_COST", 15); // تكلفة تطوير المبني للمستوي الأخير بالذهب

define("FINISH_ALL",TRUE); // تفعيل انهاء  التدريب فورا بالذهب 
define("FINISH_ALL_COST",30); // تكلفة انهاء التدريب فورا
define("MEDALS", 14400); // توزيع الاوسمة كل كم بالثانية

define("farmList", 20); // وقت الانتظار بين الهجوم على قائمة المزارع
define("maxOasisRes", 10000000); // موارد الواحات

define("DEMOLISH_FULL",5); // سعر الهدم بالكامل

// البريد الخاص بالموقع في حالة عدم استعمال SMTP
define("adminMail", $sData['adminMail']);

$config = array(
    // Paypal
    'paypalStatus' => 'live', // live - sandbox 
    'paypalAPIUser' => 'Mr.Allan1995_api1.gmail.com',
    'paypalAPIPwd' => 'KAF84QLQUVLSG8MG',
    'paypalAPISign' => 'A.aoQnn6HHEWV66QZgB6ZU4PrbdoAuEm6NMo3es104HvatKOHhl2ckTM',

    // SMTP Setting
    'SMTPHost' => 'smtp.gmail.com',
    'SMTPUser' => '', // mail
    'SMTPPass' => '', // password

    // اسعار بلاس
    'goldClub' => $sData['goldClub'],
    'Plus' => $sData['Plus'],
    'addonProduction' => $sData['addonProduction'],

    // اعدادات التفعيل
    // 1 -> يجب التفعيل
    // 0 -> تحويل تلقائي لكود التفعيل
    'needActivate' => 0,

    // اعدادات مميزات ترافيان بلاس
    // 0 -> تعطيل
    // 1 -> تشغيل
    // ضع السعر 0 لتعطيل الخاصية
    'plusFeatures' => $sData['plusFeatures'], // تعطيل أو تشغيل
    'storageUpgrade' => $sData['storageUpgrade'], // سعر زيادة التخزين
    '25pFaster' => $sData['25pFaster'], // سعر +25% تدريب أسرع
    'allSmithy' => $sData['allSmithy'], // سعر ترقية الكل بالحداد
    'searchAll' => $sData['searchAll'], // سعر البحث عن جميع الوحدات بالأكاديمية
    'resources01' => $sData['resources01'], // سعر باقة الموارد الأولي
    'resources02' => $sData['resources02'], // سعر باقة الموارد الثانية
    'resources03' => $sData['resources03'], // سعر باقة الموارد الثالثة
    'protect01' => $sData['protect01'], // سعر ساعة الحماية
    'protect02' => $sData['protect02'], // سعر 3 ساعات حماية
    'protect03' => $sData['protect03'], // سعر 6 ساعات حماية

    'resources01A' => $sData['resources01A'], // كمية الموارد الأولي
    'resources02A' => $sData['resources02A'], // كمية الموارد الثانية
    'resources03A' => $sData['resources03A'], // كمية الموارد الثالثة
);

// id -> لا تغيره
// gold -> كمية الذهب
// cost -> التكلفة
// currency -> العملة
$packages	= array (
    array ( 
        'id' 		  => 1,
        'gold'		=> '350',
        'cost'		=> '2.49',
        'currency'	=> 'USD',
        'img'       => 'Travian_Facelift_1.png'
    ),
    array ( 
        'id' 		  => 2,
        'gold'		=> '1600',
        'cost'		=> '4.99',
        'currency'	=> 'USD',
        'img'       => 'Travian_Facelift_2.png'
    ),
    array ( 
        'id' 		  => 3,
        'gold'		=> '3300',
        'cost'		=> '9.99',
        'currency'	=> 'USD',
        'img'       => 'Travian_Facelift_3.png'
    ),
        array ( 
        'id' 		  => 4,
        'gold'		=> '8450',
        'cost'		=> '24.99',
        'currency'	=> 'USD',
        'img'       => 'Travian_Facelift_4.png'
    ),	
    array ( 
        'id' 		  => 5,
        'gold'		=> '17250',
        'cost'		=> '49.99',
        'currency'	=> 'USD',
        'img'       => 'Travian_Facelift_5.png'
    ),
    array ( 
        'id' 		  => 6,
        'gold'		=> '36000',
        'cost'		=> '99.99',
        'currency'	=> 'USD',
        'img'       => 'Travian_Facelift_6.png'
    )
);
