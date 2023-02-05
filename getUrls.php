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


Class Vars
{
   public static $start = 0;
   public static $end = 100;
}

L_INIL_DB::$pathToFileDB = 'pulse.db';

use Symfony\Component\DomCrawler\Crawler;
#use Symfony\Component\HttpClient\Exception\TransportException;
#use Symfony\Component\HttpClient\HttpClient;

function getUrls (string $content):array
{
   $crw = new Crawler($content);

   $urls = $crw->filter('a')->each(function(Crawler $node)
   {
      return $node->attr('href') ?? '';
   });


   $urls = am($urls, function($a)
   {
      $a = urldecode( trim($a) );

      # убираем все что идет после "?"
      $a = preg_replace('#\?.+#', '', $a);
      # убираем все что идет после "#"
      $a = preg_replace('#\#.+#', '', $a);

      return $a;
   });


   $urls = array_filter($urls, fn($a) => $a !== '');
   $urls = array_filter($urls, fn($a) => $a !== '/');
   $urls = array_filter($urls, fn($a) => stpos($a, '/invest/') === 0);

   $urls = array_filter($urls, function($a)
   {
      if(str_contains($a, '/social/profile/')) return true;
      if(str_contains($a, '/stocks/')) return true;
      if(str_contains($a, '/bonds/')) return true;
      if(str_contains($a, '/etfs/')) return true;
   });

   

   $urls = array_unique($urls);
   $urls = array_values($urls);

   return $urls;
}


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


$add_url = [];

while(1)
{
   $urls = L_SqlStart('SELECT * FROM urls WHERE status = 0 LIMIT 100', [], 2);

   if(sizeof($urls) === 0) break;

   foreach($urls as $url)
   {

      msleep(mt_rand(3000, 5000));

      $full_url = 'https://www.tinkoff.ru' . $url['url'];

      $res = requestExec($client, $full_url);

      #unset($res['headers']['content-security-policy']);
      #$res['headers'] = jsonEncode($res['headers']);

      if(is_array($res['html']))
      {
         $res['html'] = current($res['html']);
      }

      $res['redirects'] = jsonEncode([$res['redirects']]);

      $new_urls = getUrls($res['html']);
      
      $res['html'] = '';

      # помечаем ссылку
      L_SqlStart('UPDATE urls SET status = 1,[update] = :up,code = :code,redirects = :red,count_find_urls = :count WHERE id = :id;', [
         'id' => $url['id'],
         'up' => time(),
         'red' => $res['redirects'],
         'code' => $res['status'],
         'count' => sizeof($new_urls),
      ]);

      echo $full_url . ' - ' . sizeof($new_urls) . PHP_EOL;

      if(sizeof($new_urls) === 0) msleep(mt_rand(13000, 15000));


      foreach($new_urls as $new_url)
      {
         $values[] = [
            'url' => $new_url,
            'in' => time(),
         ];
      }


      L_execCommitPack('INSERT INTO urls (url,status,[insert]) VALUES (:url,0,:in);', $values, 1);
      

   }#foreach

   

   #break;

}#while



L_execCommitPack('INSERT INTO urls (url,status,[insert]) VALUES (:url,0,:in);', $values, 1);




#dde($urls);







# requestExec($client, $full_url);