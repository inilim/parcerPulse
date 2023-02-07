<?php

Class MainPage
{
   private int $currentPage;
   private int $countRecords;
   private int $maxPages;

   public function __construct()
   {
      return $this;
   }

   public function run (int $page = 0)
   {
      $this->currentPage = $page;
      $this->countRecords = $this->getCountRecords();
      $this->maxPages = $this->getMaxCountPages();

      if($this->currentPage > $this->maxPages)
      {
         $this->currentPage = $this->maxPages;
      }

      $this->handle();
   }

   private function handle ()
   {
      $data = $this->getRecords();

      # render exit();
   }

   private function getRecords ()
   {
      $offset = calcOffset($this->currentPage, $this->maxPages, 12);

      $res = SqlStart('SELECT * FROM profiles LIMIT :limit OFFSET :offset',[
         'limit' => 12,
         'offset' => $offset
      ],2);

      return $res;
   }

   /**
   * вернуть количество записей
   */
   private function getCountRecords ():int
   {
      $cnt = SqlStart('SELECT count(*) as cnt FROM profiles', [] , 1)['cnt'] ?? 0;
      return $cnt;
   }

   /**
   * Определить и получить количество страниц на главной странице (Main)
   */
   private function getMaxCountPages (): int
   {
      return calcMaxPages(12, $this->countRecords);
   }
}