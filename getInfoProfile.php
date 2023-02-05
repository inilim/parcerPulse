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
}

