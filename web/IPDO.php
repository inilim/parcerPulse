<?php

class IPDO
{
	private string $host = 'localhost';
	private string $name = 'pulse';
	private string $login = 'root';
	private string $pass = '';
	private int $flagLastId = 0;
   /**
    * Соединение с БД PDO
    */
   private ?PDO $link = null;
   private int $lenQuery = 350;

	const FETCH_ALL = 2;
	const FETCH_ONCE = 1;

	static bool $getNonAssocArray = false;
	static ?IPDO $obj = null;

   function __construct()
   {
      self::$obj = $this;
   }

   private function defineConst (): int
   {
      if(self::$getNonAssocArray)
      {
         self::$getNonAssocArray = false;
         return PDO::FETCH_NUM;
      }
      else return PDO::FETCH_ASSOC;
   }

   public function closeConnect (): void
   {
      $this->link = null;
   }

   /**
   *@param int $s = 0 не вытаскивать результат, $s = 1 вытащить один результат, $s = 2 вытащить все.
   */
  public function run (
      string $sqlQuery,
      array $values = [],
      int $fetch = 0,
      int $flagLastId = 0
   ):array
   {
      $this->flagLastId = $flagLastId;
      $result = $this->tryMainProccess($sqlQuery, $values);
      return $this->defineFetch($result, $fetch);
   }

   private function defineFetch (array &$result, int $fetch): array
   {
      if(!isset($result['statement'])) return [];

      if($fetch === IPDO::FETCH_ONCE)
      {
         $list = $result['statement']->fetch(PDO::FETCH_ASSOC);
         return $list === false ? [] : $list;
      }
      if($fetch === IPDO::FETCH_ALL)
      {
         $list = $result['statement']->fetchAll($this->defineConst());
         return $list === false ? [] : $list;
      }

      return $result;
   }

   /**
    * Если ассоциативный многомерный массив содержит один и тот же ключ, делаем его одномерным обычным массивом
    */
   private function defineMultidimensional (array &$list): array
   {
      # надо проверить функцию
      $first = current($list);
      if(sizeof($first) === 1)
      {
         $list = array_column($list, current(array_keys($first)));
         return $list;
      }
      return $list;
   }

   private function tryMainProccess (string &$sqlQuery, array &$values = []): array
   {
      try
      {
         return $this->mainProccess($sqlQuery, $values);
      }
      catch(Throwable $e)
      {
         $sqlQuery = $this->shortQuery($sqlQuery);
         $this->saveErrors([
            'throwMsg' => $e->getMessage(),
            'Trace' => $this->handlerTrace($e),
         ], $sqlQuery);
         return [];
      }
   }

   private function mainProccess (string &$sqlQuery, array &$values = []): array
   {
      # соединение
      $this->connectDB();

      if(is_null($this->link)) throw new PDOException('Нету соединения. is_null');
      if(!is_object($this->link)) throw new PDOException('new PDO вернул не обьект');

      # NOT IN (el,el,el)
      $sqlQuery = $this->convertList($values, $sqlQuery);

      # подготовка запроса
      $stm = $this->link->prepare($sqlQuery);

      if(is_bool($stm)) throw new PDOException('$stm === false. prepare вернул false. ' . json_encode($this->link->errorInfo()));
      
      # Устанавливаем параметры к запросу
      $this->setBindParams($stm, $values);
      
      # выполнить запрос
      if( !$stm->execute() ) throw new PDOException('$stm->execute === false.' . json_encode($stm->errorInfo()));

      return $this->defineResult($stm);
   }

   /**
    * В момент создания PDO может выбросить исключение PDOException
    *
    * @return PDO|null
    * @throws PDOException
    */
   private function connectDB ():void
   {
      if(is_null($this->link))
      {
         $this->link = new PDO(
            'mysql:dbname=' .
               $this->name .
            ';host=' .
               $this->host,
            $this->login,
            $this->pass
         );
         $this->link->exec('SET NAMES utf8mb4');
      }
   }

   private function convertList (array &$values, string &$sqlQuery):string
   {
      foreach($values as $key => $val)
      {
         if(is_array($val))
         {
            $sqlQuery = str_replace(':'.$key, implode(',', $val), $sqlQuery);
            unset( $values[$key] );
         }
      }

      return $sqlQuery;
   }

   private function setBindParams (PDOStatement &$stm, array &$values): void
   {
      #$v = [];# массив для отладки
      # &$val требование от bindParam https://www.php.net/manual/ru/pdostatement.bindparam.php#98145
      foreach($values as $key => &$val)
      {
         $mask = ':' . $key;
         if(isInt($val))
         {
            $val = intval($val);
            #$v[] = $key . ' - ' . $val . ' int';
            # INT - хранит любое число в диапазоне от -2147683648 до 2147483647.
            if($val > 2147483647 || $val < -2147483648)
            {
               $stm->bindParam($mask, $val, PDO::PARAM_STR);
            }
            else
            {
               $stm->bindParam($mask, $val, PDO::PARAM_INT);
            }
         }
         elseif(is_null($val))
         {
            #$v[] = $key . ' - null ' . gettype($val);
            $stm->bindParam($mask, $val, PDO::PARAM_NULL);
         }
         else
         {
            $val = trim($val);
            #$v[] = $key . ' - ' . $val . ' ' . gettype($val);
            $stm->bindParam($mask, $val, PDO::PARAM_STR);
         }
         unset($values[$key]);
      }
      #print_r($v);
   }

   private function defineResult (PDOStatement &$stm): array
   {
      $lastInsertId = ($this->flagLastId === 0) ? 0 : intval($this->link->lastInsertId());

      return [
         'statement' => $stm,
         'countChanges' => $stm->rowCount(),
         'lastInsertId' => $lastInsertId,
      ];
   }

   private function saveErrors (array $exceptionData, string $sqlQuery):void
   {
      $t = [];
      $t['query'] = $sqlQuery;
      $t['exceptionData'] = $exceptionData;

      unset($exceptionData, $sqlQuery);

      $logFile = 'errorsSQL.log';
      if(is_dir(__DIR__ . '/logs'))
      {
         $logFile = __DIR__ . '/logs/' . $logFile;
      }

      $hash = sha1(json_encode($t), false);

      # если файл логов не создан, пытаемся создать
      if(!is_file($logFile))
      {
         $status = @file_put_contents($logFile, '');
         if($status === false) return;
      }
      # если лог файл большой, тогда больше записывать не будем
      elseif((filesize($logFile) / 1048576) >= 5) return;
      # игнорируем логи которые ранее были
      elseif(str_contains(file_get_contents($logFile), $hash)) return;

      $t['hash'] = $hash;
      $t['date'] = date('d.m.Y H:i');
      
      @file_put_contents($logFile, json_encode($t, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
   }

   /**
    * обработка массива что выдал catch
    */
   private function handlerTrace (PDOException $e):array
   {
      $trace = array_map(function($a)
      {
         unset($a['args'], $a['type']);
         return str_replace([__DIR__, '\\'], ['', '/'], implode(' | ', $a));
      }, $e->getTrace());
      unset($e);
      $trace = preg_grep('(iPDO)', $trace);

      if($trace !== false) return $trace;
      return [];
   }

   /**
    * форматируем запрос для логов
    */
   private function shortQuery (string &$sqlQuery):string
   {
      $sqlQuery = str_replace(["\n", "\r", "\r\n", "\t"], ' ', $sqlQuery);
      $sqlQuery = preg_replace('#\ {2,}#', ' ', $sqlQuery);
      if(strlen($sqlQuery) > $this->lenQuery) return substr($sqlQuery, 0, $this->lenQuery) . '...';
      return $sqlQuery;
   }
}

function iPDO (
   string $sql_query,
   array $array = [],
   int $s = 0,
   int $flagLastId = 0
):array
{
   if(is_null(IPDO::$obj)) new IPDO();
   return IPDO::$obj->run($sql_query, $array, $s, $flagLastId);
}

function closeiPDO ()
{
   if(is_object(IPDO::$obj)) IPDO::$obj->closeConnect();
}