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

$instrs = L_SqlStart('SELECT distinct id_profile, id_instr FROM calcInstruments',[],2);

foreach($instrs as $instr)
{
   $calcInstr = L_SqlStart('SELECT * FROM calcInstruments WHERE id_profile = :id_profile AND id_instr = :id_instr ORDER BY unix ASC',[
      'id_profile' => $instr['id_profile'],
      'id_instr' => $instr['id_instr'],
   ],2);

   if(sizeof($calcInstr) < 2) continue;

   # берем день покупки
   $first = current($calcInstr);
   $genDates = genDates($first['unix']);

   # берем только последующие дни
   $calcInstr = array_slice($calcInstr, 1);

   $dates = array_column($calcInstr, 'date');

   # проверяем что последующие дни присутствуют
   if( sizeof($genDates) !== sizeof(array_filter($dates, fn($a) => in_array($a, $genDates))) ) continue;

   unset($dates);

   # берем только сгенерированное количество
   $calcInstr = array_slice($calcInstr, 0, sizeof($genDates));

   dd($first);
   dd($genDates);
   dde($calcInstr);
}