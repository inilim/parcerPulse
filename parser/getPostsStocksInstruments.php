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


# ----------------------------------------------


function getIdStock (string $ticker):int|false
{
   static $list = [];

   if($list === [])
   {
      $res = L_SqlStart('SELECT * FROM stocks',[], 2);
      $list = array_combine(
         array_column($res, 'ticker'),
         array_column($res, 'id')
      );
      unset($res);
   }

   if(isset($list[$ticker])) return intval($list[$ticker]);

   addStock($ticker);
   $id = L_SqlStart('SELECT id FROM stocks WHERE ticker = :ticker',[
      'ticker' => $ticker
   ], 1);
   $list = [];
   if(isset($id['id'])) return intval($id['id']);
   return false;
}

function addStock (string $ticker):void
{
   L_SqlStart('INSERT INTO stocks (ticker) VALUES (:ticker)',[
      'ticker' => $ticker
   ]);
}

function addPosts (array $items, int $idProfile):void
{
   $values = [];
   foreach($items as $item)
   {
      $values[] = [
         'id_profile' => $idProfile,
         'id_api' => $item['id'],
         'likesCount' => $item['likesCount'],
         'commentsCount' => $item['commentsCount'],
         'inserted' => strtotime($item['inserted'])
      ];
   }
   unset($items);

   L_execCommitPack('INSERT INTO posts (id_profile,id_api,likesCount,commentsCount,inserted) VALUES (:id_profile,:id_api,:likesCount,:commentsCount,:inserted) ON CONFLICT(id_profile,id_api) DO UPDATE SET likesCount=:likesCount, commentsCount=:commentsCount', $values, 1);
}

function getIdPost (string $id_api):int|false
{
   $id = L_SqlStart('SELECT id FROM posts WHERE id_api = :id_api',[
      'id_api' => $id_api
   ], 1);

   if(isset($id['id'])) return intval($id['id']);
   return false;
}

function addInstruments (array $items):void
{
   $values = [];
   $sql = 'INSERT INTO instruments (id_post,id_stock,currency,relativeDailyYield,price,lastPrice,relativeYield) VALUES (:id_post,:id_stock,:currency,:relativeDailyYield,:price,:lastPrice,:relativeYield)';
   foreach($items as $post)
   {
      $post['id_post'] = getIdPost($post['id']);

      if($post['id_post'] === false) dde($post);

      foreach($post['instruments'] as $instrument)
      {
         $instrument['id_stock'] = getIdStock($instrument['ticker']);

         if($instrument['id_stock'] === false) dde($instrument);

         $values[] = [
            'id_post' => $post['id_post'],
            'id_stock' => $instrument['id_stock'],
            'currency' => sttolower($instrument['currency']),
            'relativeDailyYield' => $instrument['relativeDailyYield'],
            'price' => $instrument['price'],
            'lastPrice' => $instrument['lastPrice'],
            'relativeYield' => $instrument['relativeYield'],
         ];
      }
      L_execCommitPack($sql, $values, 100);
   }
   unset($items, $post, $instrument);

   L_execCommitPack($sql, $values, 1);
}


# ----------------------------------------------


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

# ----------------------------------------------

$profiles = L_SqlStart('SELECT id, id_api FROM profile WHERE update_posts is null',[], 2);

$size = sizeof($profiles);


# ----------------------------------------------

foreach($profiles as $profile)
{
   $full_url = 'https://www.tinkoff.ru/api/invest-gw/social/v1/profile/'.
   $profile['id_api'].
   '/post?'.
   'limit='.
   '55'.
   '&sessionId='.
   'SnK57qw2WchK2nm2vv6ApTEqwLE62YUO.ds-prod-api50'.
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

   msleep(mt_rand(3000,4000));

   if(is_array($response['html'])) dde($response);
   if(!isJson($response['html'])) dde($response);

   $arr = jsonDecode($response['html']);

   if(!isset($arr['payload'])) dde($arr);
   $arr = $arr['payload'];


   # помечаем профиль
   L_SqlStart('UPDATE profile SET update_posts = :time WHERE id = :id',[
      'id' => $profile['id'],
      'time' => time()
   ]);

   
   if(!isset($arr['items'])) dde($arr);
   if(sizeof($arr['items']) === 0) continue;

   # добавляем посты
   addPosts($arr['items'], $profile['id']);
   # добавляем покупки
   addInstruments($arr['items']);

}
