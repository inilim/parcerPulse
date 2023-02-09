<?php

Class MainPage
{
   private int $currentPage;
   private int $countRecords;
   private int $maxPages;
   private null|Render $objRender;
   private int $maxRecords = 12;

   public function __construct(int $page = 0)
   {
      $this->currentPage = $page;
      #var_dump($this->currentPage);
      $this->countRecords = $this->getCountRecords();
      $this->maxPages = $this->getMaxCountPages();
      $this->objRender = new Render();

      if($this->currentPage > $this->maxPages)
      {
         $this->currentPage = $this->maxPages;
      }

      $this->handle();
   }

   private function handle ()
   {
      $idProfiles = $this->getProfiles();
      $data = $this->getRecords($idProfiles);
      
      $this->objRender->render('main', [
         'json' => json_encode($data)
      ]);
   }

   private function getProfiles ():array
   {
      $offset = calcOffset($this->currentPage, $this->maxPages, $this->maxRecords);

      $res = iPDO('SELECT distinct id_profile FROM deals LIMIT :limit OFFSET :offset',[
         'limit' => $this->maxRecords,
         'offset' => $offset
      ],2);

      $res = array_column($res, 'id_profile');

      return $res;
   }

   private function getRecords (array $idProfiles):array
   {
      $res = iPDO('SELECT * FROM deals WHERE id_profile in (:in)',[
         'in' => $idProfiles
      ],2);

      $arr = [];
      foreach($res as $key => $item)
      {
         if(!isset($arr[ $item['id_profile'] ])) $arr[ $item['id_profile'] ] = [];
         $arr[ $item['id_profile'] ][] = $item;
         unset($res[$key]);
      }

      return $arr;
   }

   /**
   * вернуть количество записей
   */
   private function getCountRecords ():int
   {
      $cnt = iPDO('SELECT count(distinct id_profile) as cnt FROM deals', [] , 1)['cnt'] ?? 0;
      return $cnt;
   }

   /**
   * Определить и получить количество страниц на главной странице (Main)
   */
   private function getMaxCountPages (): int
   {
      return calcMaxPages($this->maxRecords, $this->countRecords);
   }
}