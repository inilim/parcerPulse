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
   ]
];

$full_url = 'https://www.tinkoff.ru/api/trading/symbols/candles?sessionId=6nOPnz413NHfMeiy3cL1KPuJXaDyiOfP.ds-prod-api132&appName=web&appVersion=1.296.0&origin=web';


$stocks = L_SqlStart('SELECT distinct id_stock as id, ticker FROM share_purchases',[], 2);

shuffle($stocks);

foreach($stocks as $stock)
{
   # история за 3 месяца
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