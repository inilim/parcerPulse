<?php

function isInt ($i): bool
{
	if(is_null($i)
	|| is_bool($i)
	|| is_array($i)
	|| is_object($i))
	{
		return false;
	}
	if(preg_match('#^0$#', $i))
	{
		return true;
	}
	if(preg_match('#^\-?[1-9][0-9]{0,}$#', $i))
	{
		return true;
	}
	return false;
}

/**
 * Ожидание в миллисекундах. значение 100 = 0.1 секунде.
 */
function msleep(int $v): void
{
    usleep( (1000*$v) );
}

/**
 * guzzle получаем response html redirects headers status.
 */
function requestExec (GuzzleHttp\Client $client, string $url, string $method = 'GET'):array
{
   $redirects = [];
   try
   {
      # запрос
      $response = $client->request($method, $url,
      [
         'on_stats' => function (GuzzleHttp\TransferStats $stats) use (&$redirects)
         {
            $redirects[] = (string)$stats->getEffectiveUri();
         },
      ]);

      # заголовки
      $headers = [];
      foreach ($response->getHeaders() as $name => $values)
      {
         $headers[$name] = $values;
      }

      $body = $response->getBody();
      $body = $body->getContents();

      $res = [
         'response' => $response,
         # тело ответа
         'html' => $body,
         'headers' => $headers,
         'redirects' => array_slice($redirects, 1),
         # статус
         'status' => $response->getStatusCode()
      ];

      return $res;
   }
   catch(Throwable $e)
   {
      return [
         'html' => [
            'msg' => $e->getMessage()
         ],
         'response' => [],
         'headers' => [],
         'redirects' => [],
         'status' => 0
      ];
   }
}

function isJson(string $str):bool
{
	if(isInt($str)) return false;
	json_decode($str);
	return json_last_error() === JSON_ERROR_NONE;
}

function jsonDecode (string $json):array
{
    return json_decode($json, true);
}

/**
 * Модифицированный print_r
 * @param string $desc Заголовок для вывода.
 */
function dd ($mixed = null, string $desc = 'print'): void
{
    $info = debug_backtrace();
	$line = implode(' < ', array_column($info, 'line'));
	$vLine = file( $info[0]['file'] );
    $fLine = $vLine[ $info[0]['line'] - 1 ];

    $trace = [];
    foreach($info as $file)
    {
        if(isset($file['file']))
        {
            $trace[] = pathinfo($file['file'])['filename'];
        }
        else
        {
            $trace[] = 'undefined file';
        }
    }
    $trace = implode(' < ', $trace);

    echo '------------ INFO ------------' . PHP_EOL;
    echo 'Trace of files: ' . $trace . PHP_EOL;
	echo 'Line code: [' . trim($fLine) . ']' . PHP_EOL;
	echo 'Line number: ' . $line . PHP_EOL;
    echo '------------ ' . sttoupper($desc) . ' ------------' . PHP_EOL;
	if(is_array($mixed) || is_object($mixed))
    {
        print_r($mixed);
    }
    else
    {
        var_dump($mixed);
    }
	echo PHP_EOL;
	echo '------------ END ------------' . PHP_EOL . PHP_EOL;
}
/**
 * Модифицированный print_r с exit();
 * @param string $desc Заголовок для вывода.
 */
function dde ($mixed = null, string $desc = 'print'): void
{
	if($mixed === null)
	{
		exit('dde( NULL )');
	}
    $info = debug_backtrace();
	$line = implode(' < ', array_column($info, 'line'));
	$vLine = file( $info[0]['file'] );
    $fLine = $vLine[ $info[0]['line'] - 1 ];

    $trace = [];
    foreach($info as $file)
    {
        if(isset($file['file']))
        {
            $trace[] = pathinfo($file['file'])['filename'];
        }
        else
        {
            $trace[] = 'undefined file';
        }
    }
    $trace = implode(' < ', $trace);

    echo '------------ INFO ------------' . PHP_EOL;
    echo 'Trace of files: ' . $trace . PHP_EOL;
	echo 'Line code: [' . trim($fLine) . ']' . PHP_EOL;
	echo 'Line number: ' . $line . PHP_EOL;
    echo '------------ ' . sttoupper($desc) . ' ------------' . PHP_EOL;
    if(is_array($mixed) || is_object($mixed))
    {
        print_r($mixed);
    }
    else
    {
        var_dump($mixed);
    }
	echo PHP_EOL;
	echo '------------ END ------------ EXIT' . PHP_EOL . PHP_EOL;
	exit();
}

function am (array $arr, callable $fn): array
{
	return array_map($fn, $arr);
}

function subst (string $string, int $offset, ?int $length = null):string
{
    return mb_substr($string, $offset, $length, 'UTF-8');
}

/**
 * mb_strpos($str, $find, $offset, 'UTF-8')
 */
function stpos (string $str, string $find, int $offset = 0):int|false
{
    return mb_strpos($str, $find, $offset, 'UTF-8');
}

function sttolower (string $str):string
{
    return mb_strtolower($str, 'UTF-8');
}

function sttoupper (string $str):string
{
    return mb_strtoupper($str, 'UTF-8');
}

function stlen (string $str):int
{
    return mb_strlen($str, 'UTF-8');
}

function getUnix (string $pattern, string $date):int
{
    return DateTime::createFromFormat($pattern, $date)->getTimestamp();
}