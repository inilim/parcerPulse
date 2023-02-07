<?php

Class Render
{
   # имя файла компонента
   private string $file;
   # путь до файла компонента
   private string $path;
   # имя файла layout
   public string $layout = 'base';
   # путь до файла layout
   private string $pathLayout;
   # максимальное количество вложенности
   public int $nesting = 6;
   # имя папки с компонентами и layouts
   public string $dir = 'view';
   # функция для логирования
   public ?bool $existLog;

   # private array $files = [];

   public function __construct ()
   {
      if(ob_get_level()) ob_end_clean();
      $this->existLog = function_exists('writeLog');
   }

   /**
    * Рендеринг без layout
    */
   private function run (array $vars = []):string
   {
      if(sizeof($vars)) extract($vars, EXTR_OVERWRITE);
      
      ob_start();
      if(!$this->checkNestingRendering())
      {
         ob_end_clean();
         return '';
      }
      require $this->path;
      return ob_get_clean();
   }

   /**
    * Рендеринг с layout
    */
   private function _run (array $vars = []):string
   {
      $content = $this->run($vars);
      if(sizeof($vars)) extract($vars, EXTR_OVERWRITE);

      if(!$this->checkLayout()) return '';

      ob_start();
      require $this->pathLayout;
      return ob_get_clean();
   }

   /**
    * выбор режима рендеринга
    */
   private function select (array $vars = []):string
   {
      if(ob_get_level() === 0)
      {
         return $this->_run($vars);
      }
      else
      {
         return $this->run($vars);
      }
   }

   /**
    * проверка существования layout
    */
   private function checkLayout ():bool
   {
      $layout = $this->addFormat($this->layout);
      $this->pathLayout = __DIR__ . '/' . $this->dir . '/layouts/' . $layout;

      if(is_file($this->pathLayout)) return true;
      $this->log(['not found layout: ' . $this->pathLayout]);
      return false;
   }

   /**
    * проверка на количество вложенности
    */
   private function checkNestingRendering ():bool
   {
      if(ob_get_level() > $this->nesting)
      {
         $this->log(['stop nesting rendering.']);
         return false;
      }
      # $this->files[] = $this->file;
      return true;
   }

   /**
    * проверка существования файла
    */
   private function checkFile ():bool
   {
      if(is_file($this->path)) return true;
      $this->log(['not found template: ' . $this->file]);
      return false;
   }

   /**
   * Добавляем постфикс\формат php
   */
   private function addFormat (string $name):string
   {
      $name = str_ireplace('.php', '', $name);
      return $name . '.php';
   }

   private function setFile (string $nameTemplate):void
   {
      $this->file = $this->addFormat($nameTemplate);
      $this->path = __DIR__ . '/' . $this->dir . '/components/' . $this->file;
   }

   /**
    * Вывести результат рендеринга
    *
    * @param boolean $eol - удалить переносы-пробелы-табуляции между тегами в случаи если других символов там нет
    */
   public function render (string $nameTemplate, array $vars = [], bool $eol = false):void
   {
      $this->setFile($nameTemplate);
      if($this->checkFile())
      {
         if($eol)
         {
            echo $this->removeEOLBetweenTags(
               $this->select($vars)
            );
         }
         else
         {
            echo $this->select($vars);
         }
      }
   }

   /**
    * вернуть результат рендеринга без layout
    */
   public function getRender (string $nameTemplate, array $vars = []):string
   {
      $this->setFile($nameTemplate);
      if($this->checkFile())
      {
         return $this->run($vars);
      }
      return '';
   }

   private function removeEOLBetweenTags (string $html):string
   {
      $regex = '(\\s|\\n|\\r|\\r\\n|\\t)+';
      $html = preg_replace('#^' . $regex . '\<#', '<', $html);
      $html = preg_replace('#/>' . $regex . '$#', '>', $html);
      return preg_replace('#\>' . $regex . '\<#', '><', $html);
   }

   private function log (array $log):void
   {
      if($this->existLog) @writeLog('errorsRender.log', other: $log);
   }
}