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

use Symfony\Component\DomCrawler\Crawler;
#use Symfony\Component\HttpClient\Exception\TransportException;
#use Symfony\Component\HttpClient\HttpClient;



$client = new GuzzleHttp\Client([
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
]);


$ban_profiles = L_SqlStart('SELECT nickname FROM notfound_profile',[], 2);
$ban_profiles = array_column($ban_profiles, 'nickname');

$bd_profiles = L_SqlStart('SELECT nickname FROM profile',[], 2);
$bd_profiles = array_column($bd_profiles, 'nickname');


$urls = L_SqlStart('SELECT url FROM urls WHERE url LIKE "/invest/social/profile/%" AND instr(url, "-") = 0;', [], 2);
$urls = array_column($urls, 'url');

$nicknames = am($urls, function($a)
{
   $a = str_replace('/invest/social/profile/', '', $a);
   return trim($a, '/');
});

unset($urls);

$nicknames = array_diff($nicknames, $ban_profiles);
$nicknames = array_diff($nicknames, $bd_profiles);

unset($ban_profiles, $bd_profiles);

$nicknames = array_values($nicknames);

$size = sizeof($nicknames);

foreach($nicknames as $nick)
{
   /* $profile = L_SqlStart('SELECT * FROM profile WHERE nickname = :nick',[
      'nick' => $nick
   ], 1);

   if(isset($profile['update']))
   {
      $profile['update'] = intval($profile['update']);
      # обновляем если прошли сутки
      if( ($profile['update']-time()) < (3600*24) ) continue;
   } */



   $full_url = 'https://www.tinkoff.ru/api/invest-gw/social/v1/profile/nickname/'.
   $nick.
   '?sessionId='.
   'N7Grd4maknwNWfsnGeqdIPIQeNVF3jxN.ds-prod-api07'.
   '&appName='.
   'socialweb'.
   '&appVersion='.
   '1.41.0'.
   '&origin='.
   'web'.
   '&platform='.
   'web';

   echo $full_url . PHP_EOL;
   echo 'Осталось: ' . --$size . PHP_EOL;

   $response = requestExec($client, $full_url);

   msleep(mt_rand(3000,5000));

   if(is_array($response['html'])) dde($response);
   if(!isJson($response['html'])) dde($response);

   $arr = jsonDecode($response['html']);

   if(!isset($arr['payload'])) dde($arr);

   $arr = $arr['payload'];

   # проверка на удаленный профиль
   if(isset($arr['code']) && $arr['code'] === 'NotFound')
   {
      # добавляем в бд удаленный профиль
      L_SqlStart('INSERT INTO notfound_profile (nickname) VALUES (:nick);',[
         'nick' => $nick
      ]);
      continue;
   }

   # проверка необходимых ключей
   if(!isset($arr['id'])) dde($arr);
   if(!isset($arr['type'])) dde($arr);
   if(!isset($arr['status'])) dde($arr);
   if(!isset($arr['followersCount'])) dde($arr);
   if(!isset($arr['followingCount'])) dde($arr);


   L_SqlStart('INSERT INTO profile (update_info,id_api,nickname,[type],[status],followersCount,followingCount,yearRelativeYield,monthOperationsCount,totalAmountRange_lower,totalAmountRange_upper,[json]) VALUES (:update_info,:id_api,:nickname,:type,:status,:followersCount,:followingCount,:yearRelativeYield,:monthOperationsCount,:totalAmountRange_lower,:totalAmountRange_upper,:json);',
   [
      'update_info' => time(),
      'id_api' => $arr['id'],
      'nickname' => $nick,
      'type' => $arr['type'],
      'status' => $arr['status'],

      'followersCount' => $arr['followersCount'],
      'followingCount' => $arr['followingCount'],

      'monthOperationsCount' => $arr['statistics']['monthOperationsCount'] ?? null,

      'yearRelativeYield' => $arr['statistics']['yearRelativeYield'] ?? null,

      'totalAmountRange_upper' => $arr['statistics']['totalAmountRange']['upper'] ?? null,

      'totalAmountRange_lower' => $arr['statistics']['totalAmountRange']['lower'] ?? null,

      'json' => $response['html'],
   ]);

   #dde($arr);
}

