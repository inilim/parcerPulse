<?php
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Etc/GMT-3');
require_once __DIR__ . '/_functions.php';
# Функции для работы с PDO SqLite
require_once __DIR__ . '/_INIL_connectLite.php';
require_once __DIR__ . '/vendor/autoload.php';
ini_set('memory_limit', '5024M');

# Пульс - социальная сеть для инвесторов и трейдеров

L_INIL_DB::$pathToFileDB = 'pulse.db';

/**
 * получить активных пользователей за указанный период.
 * Активность определяется покупкой акций
 */
function getActiveProfile (?int $term = null):array
{
   $term = $term ?? (3600*24*30);

   $res = L_SqlStart('SELECT id_profile, count(*) as cnt_instrs FROM share_purchases
   WHERE (:currentTime-inserted) < :term GROUP BY id_profile',[
      'currentTime' => time(),
      'term' => $term
   ], 2);

   return $res;
}

/**
 * получить валюту к акциям
 */
function getCurrencyStocks ():array
{
   $res = L_SqlStart('SELECT distinct id_stock as id, currency FROM instruments',[],2);

   $res = array_combine(
      array_column($res, 'id'),
      array_column($res, 'currency')
   );

   return $res;
}

/**
 * Получить покупки за период у конкретного профиля
 */
function getInstrumentsPeriod (int $idProfile, ?int $term = null):array
{
   $term = $term ?? (3600*24*30);

   $res = L_SqlStart('SELECT * FROM share_purchases WHERE (:currentTime-inserted) < :term AND id_profile = :id',[
      'id' => $idProfile,
      'currentTime' => time(),
      'term' => $term
   ],2);

   $res = am($res, function($a)
   {
      $a['date'] = date('d.m.Y', $a['inserted']);
      return $a;
   });

   return $res;
}

/**
 * получить динамику конкретной акции
 */
function getHistoryStock (int $idStock):array
{
   $res = L_SqlStart('SELECT * FROM history WHERE id_stock = :id ORDER BY unix ASC',[
      'id' => $idStock,
   ],2);

   $res = array_combine(
      array_column($res, 'date'),
      $res
   );

   return $res;
}

/**
 * вычеслить следующий день от указанного
 */
function nextDay (int $unixtime):string
{
   $d = strtotime('+1 day', $unixtime);
   return date('d.m.Y', $d); 
}

/**
 * вычеслить предыдущий день от указанного
 */
function beforeDay (int $unixtime):string
{
   $d = strtotime('-1 day', $unixtime);
   return date('d.m.Y', $d); 
}



# -------------------------------------

$values = [];

# получить профили у которых были покупки
$profiles = getActiveProfile();

shuffle($profiles);

foreach($profiles as $profile)
{

   #dde($profile);
   # берем купленные акции у конкретного профиля
   $stocks = getInstrumentsPeriod($profile['id_profile']);

   foreach($stocks as $stock)
   {
      # пропускаем сегодняшние и вчерашние покупки
      if($stock['date'] === date('d.m.Y')) continue;
      if(beforeDay($stock['inserted']) === beforeDay(time())) continue;

      # получаем динамику к текущей акции
      $stock['history'] = getHistoryStock($stock['id_stock']);

      # api тинькофа хренова выдает динамику
      if($stock['history'] === []) continue;
      if(!isset($stock['history'][ $stock['date'] ])) continue;

      #echo $stock['date'] . PHP_EOL;
      #echo $stock['price'] . PHP_EOL;

      $unixBuy = $stock['inserted'];
      $unixBuy = strtotime('today', $unixBuy);

      $calc = array_filter($stock['history'], function($a) use ($unixBuy)
      {
         return $unixBuy < $a['unix'];
      });

      # берем закрытую цену
      $calc = array_combine(
         array_column($calc, 'date'),
         array_column($calc, 'close')
      );

      foreach($calc as $date => $item)
      {
         $values[] = [
            'unix' => getUnix('d.m.Y', $date),
            'date' => $date,
            'id_profile' => $profile['id_profile'],
            'id_instr' => $stock['id'],
            'id_stock' => $stock['id_stock'],
            'close_price' => $item
         ];
      }

      unset($calc);

      L_execCommitPack('INSERT INTO calcInstruments (unix,[date],id_profile,id_instr,id_stock,close_price) VALUES (:unix,:date,:id_profile,:id_instr,:id_stock,:close_price);', $values, 1);

      dde();
   }
}

