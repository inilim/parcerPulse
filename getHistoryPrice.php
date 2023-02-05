<?php
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Etc/GMT-3');
require_once __DIR__ . '/functions.php';
# Функции для работы с PDO SqLite
require_once __DIR__ . '/_INIL_connectLite.php';
require_once __DIR__ . '/vendor/autoload.php';
ini_set('memory_limit', '5024M');
timeRun();

# Пульс - социальная сеть для инвесторов и трейдеров


L_INIL_DB::$pathToFileDB = 'pulse.db';

function createFrom ():string
{
   $from = time();
   $from = $from - ( 3600*24*90 );
   return date('Y-m', $from) . '-' . date('d');
}

# ----------------------------------------------

$params = [
   # SSL off
   'verify' => false,
   # max redirects
   'max' => 4,
   # redirect on
   'allow_redirects' => true,
   'timeout'  => 5,
   'headers' => [
      'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36',
      'cookie' => '__P__wuid=4084a49d7e1847fc7f56539212a9f160; userType=Visitor; dsp_click_id=no%20dsp_click_id; ta_uid=1675019462552656777; utm_source=www.google.com; dmp.id=5605c428-9783-464c-979d-ee9e8e5cb59b; stDeIdU=11b9fd27-6fbb-4697-b9cf-86a2b9b1dfcf; tmr_lvid=f615a5d775d82c57673f733136e48be9; tmr_lvidTS=1675019466019; _ym_uid=1675019466289777285; _ym_d=1675019466; utm_date_set=1675019534154; dco.id=f0dcf12a-4e73-4b78-85bb-0000ccd419ef; ta_nr=return; timezone=Europe/Moscow; pageLanding=https%3A%2F%2Fwww.tinkoff.ru%2Finvest%2Fsocial%2Fprofile%2FFlexio%2Fac68ef4d-90ad-4621-9e76-d6979b32378b%2F; ta_visit_num=4; ta_visit_start_ts=1675354280864; dmp.sid=AWPb40wj09U; api_session_csrf_token_00b70e=4306aa50-6b28-4961-8905-ff92d5288d40.1675354956; __P__wuid_last_update_time=1675354280866; AMCVS_A002FFD3544F6F0A0A4C98A5%40AdobeOrg=1; AMCV_A002FFD3544F6F0A0A4C98A5%40AdobeOrg=-1124106680%7CMCIDTS%7C19391%7CMCMID%7C67838860840493691692678284730293088569%7CMCAAMLH-1675959081%7C6%7CMCAAMB-1675959081%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1675361481s%7CNONE%7CvVersion%7C5.2.0; s_cc=true; psid=N7Grd4maknwNWfsnGeqdIPIQeNVF3jxN.ds-prod-api07; _ym_isad=1; api_session_csrf_token_23375d=af3e9035-1d73-44f7-b70f-23cb0358d337.1675354972; api_session_csrf_token_eee295=270bed07-1a7a-4dc8-8b70-d987ee4d007b.1675355740; api_session=lOoJGb0083gS1Zq94f14WtMc7Tf2tBm5.m1-prod-api21; s_nr=1675355066488-Repeat; tmr_detect=1%7C1675355066536; tmr_reqNum=56; mediaInfo={%22width%22:1280%2C%22height%22:881%2C%22isTouch%22:false%2C%22retina%22:false}'
   ]
];

$full_url = 'https://www.tinkoff.ru/api/trading/symbols/candles?sessionId=6nOPnz413NHfMeiy3cL1KPuJXaDyiOfP.ds-prod-api132&appName=web&appVersion=1.296.0&origin=web';


$stocks = L_SqlStart('SELECT distinct id_stock as id, ticker FROM share_purchases',[], 2);

shuffle($stocks);

foreach($stocks as $stock)
{
   # историю за 3 месяца
   $from = createFrom();
   $currentDate = date('Y-m-d');
   $currentTime = date('H:i');

   # ----------------------------------------------

   $params[ GuzzleHttp\RequestOptions::JSON ] = $body = [
      'from' => $from . 'T' . $currentTime . ':00+03:00',
      'resolution' => 'D',
      'ticker' => $stock['ticker'],
      'to' => $currentDate . 'T' . $currentTime . ':00+03:00',
   ];

   echo json_encode($body) . PHP_EOL;

   $client = new GuzzleHttp\Client($params);

   # ----------------------------------------------

   msleep(mt_rand(3000,5000));
   
   $response = requestExec($client, $full_url, 'POST');
   
   if(is_array($response['html'])) dde($response);
   if(!isJson($response['html'])) dde($response['html']);
   
   $arr = jsonDecode($response['html']);
   
   if(!isset($arr['payload']['candles'])) dde($arr);
   
   $arr = $arr['payload']['candles'];
   
   if(sizeof($arr) === 0) dde($arr);
   
   $arr = am($arr, function($a) use ($stock)
   {
      $a['id_stock'] = $stock['id'];
      $a['unix'] = $a['date'];
      $a['date'] = date('d.m.Y', $a['date']);
      return $a;
   });

   L_execCommitPack('INSERT INTO history
   (id_stock,unix,[date],[open],high,low,[close],volume)
   VALUES
   (:id_stock,:unix,:date,:o,:h,:l,:c,:v) ON CONFLICT(id_stock,date)
   DO UPDATE SET
   open=:o,high=:h,low=:l,close=:c,volume=:v', $arr, 1);

   dde();
}