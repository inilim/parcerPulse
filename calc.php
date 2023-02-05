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

/**
 * получить активных пользователей за указанный период.
 * Активность определяется покупкой акций
 */
function getActiveProfile (?int $term = null):array
{
   $term = $term ?? (3600*24*30);

   $res = L_SqlStart('SELECT id_profile, cnt_instrs FROM group_instruments
   WHERE (:currentTime-inserted) < :term',[
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



dd(getCurrencyStocks());