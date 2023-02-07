<?php
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Etc/GMT-3');
require_once __DIR__ . '/_functions.php';
# Функции для работы с PDO SqLite
require_once __DIR__ . '/_INIL_connectLite.php';
require_once __DIR__ . '/vendor/autoload.php';
ini_set('memory_limit', '5024M');


L_INIL_DB::$pathToFileDB = 'pulse.db';

function genDates (int $startUnix, int $days = 3):array
{
   $endUnix = strtotime('+' . ++$days . ' day', $startUnix);
	$period = new DatePeriod(
		new DateTime( date('Y-m-d', $startUnix) ),
		new DateInterval('P1D'),
		new DateTime( date('Y-m-d', $endUnix) )
	);

   $arr = [];

	foreach ($period as $value)
	{
		$arr[] = $value->format('d.m.Y');
	}

   return array_slice($arr, 1);
}

# -------------------------------------

$profiles = L_SqlStart('SELECT distinct id_profile FROM calcInstruments',[],2);

# $changeDays определить разницу в течении № дней
$changeDays = 3;
$sql = 'INSERT INTO calcProductiveProfiles (id_profile,id_instr,id_stock,diff_percent,diff_price,change_days,[before],[after]) VALUES (:id_profile,:id_instr,:id_stock,:diff_percent,:diff_price,:change_days,:before,:after);';
$values = [];

# -------------------------------------

foreach($profiles as $profile)
{
   $array = L_SqlStart('SELECT * FROM calcInstruments WHERE id_profile = :id_profile ORDER BY unix ASC',[
      'id_profile' => $profile['id_profile']
   ],2);

   $instrs = [];
   foreach($array as $key => $val)
   {
      $instrs[$val['id_instr']][] = $val;
      unset($array[$key]);
   }
   unset($array);

   # -------------------------------------

   foreach($instrs as $calcInstr)
   {
      $instr = current($calcInstr);

      if(sizeof($calcInstr) < 2) continue;

      # берем день покупки
      $first = current($calcInstr);
      $genDates = genDates($first['unix'], $changeDays);

      # берем только последующие дни
      $calcInstr = array_slice($calcInstr, 1);

      $dates = array_column($calcInstr, 'date');

      # проверяем что последующие дни присутствуют
      if( sizeof($genDates) !== sizeof(array_filter($dates, fn($a) => in_array($a, $genDates))) ) continue;

      unset($dates);

      # берем только сгенерированное количество
      $calcInstr = array_slice($calcInstr, 0, sizeof($genDates));

      $end = end($calcInstr);

      $diffPercent = round((1 - $first['close_price'] / $end['close_price']) * 100, 1); 
      $diffPrice = round($end['close_price'] - $first['close_price'], 3);

      #echo $diffPercent . ' | ' . $diffPrice . PHP_EOL;

      $values[] = [
         'id_profile' => $instr['id_profile'],
         'id_instr' => $instr['id_instr'],
         'id_stock' => $instr['id_stock'],
         'diff_percent' => $diffPercent,
         'diff_price' => $diffPrice,
         'change_days' => $changeDays,
         'before' => $first['close_price'],
         'after' => $end['close_price'],
      ];
   }

   L_execCommitPack($sql, $values, 100);
}

L_execCommitPack($sql, $values, 1);






