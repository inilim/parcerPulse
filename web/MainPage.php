<?php

Class MainPage
{
   private int $currentPage;
   private int $countRecords;
   private int $maxPages;
   private null|Render $objRender;

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
      $data = $this->getRecords();
      
      $this->objRender->render('main', [
         'json' => json_encode($data)
      ]);
   }

   private function getRecords ()
   {
      $data = '[{"290344":{"id_profile":8282,"diff_percent":3.3,"diff_price":0.38,"change_days":3,"before":11.28,"after":11.66,"ticker":"CNTL","currency":"rub"},"290342":{"id_profile":8282,"diff_percent":0.2,"diff_price":0.2,"change_days":3,"before":101.7,"after":101.9,"ticker":"NKNC","currency":"rub"},"290341":{"id_profile":8282,"diff_percent":3.2,"diff_price":10.45,"change_days":3,"before":311.95,"after":322.4,"ticker":"GLTR","currency":"rub"}},{"84338":{"id_profile":1117,"diff_percent":0.8,"diff_price":0.22,"change_days":3,"before":27.68,"after":27.9,"ticker":"AFLT","currency":"rub"},"84339":{"id_profile":1117,"diff_percent":1.8,"diff_price":2.68,"change_days":3,"before":149.96,"after":152.64,"ticker":"SBER","currency":"rub"}},{"43755":{"id_profile":4232,"diff_percent":1.8,"diff_price":16.1,"change_days":3,"before":880.8,"after":896.9,"ticker":"RU000A1013N6","currency":"rub"},"43754":{"id_profile":4232,"diff_percent":-0.4,"diff_price":-3.8,"change_days":3,"before":934,"after":930.2,"ticker":"RU000A1006C3","currency":"rub"}},{"51837":{"id_profile":3982,"diff_percent":6,"diff_price":5.19,"change_days":3,"before":81.5,"after":86.69,"ticker":"TSM","currency":"usd"},"51836":{"id_profile":3982,"diff_percent":1.3,"diff_price":1.25,"change_days":3,"before":97.4,"after":98.65,"ticker":"AMZN","currency":"usd"}},{"199667":{"id_profile":4935,"diff_percent":-3.3,"diff_price":-5.27,"change_days":3,"before":165.8,"after":160.53,"ticker":"GAZP","currency":"rub"}},{"296949":{"id_profile":8550,"diff_percent":1.9,"diff_price":2.17,"change_days":3,"before":110.82,"after":112.99,"ticker":"BABA","currency":"usd"}},{"291422":{"id_profile":8321,"diff_percent":-3.5,"diff_price":-3000,"change_days":3,"before":89950,"after":86950,"ticker":"TRNFP","currency":"rub"},"291421":{"id_profile":8321,"diff_percent":-2.2,"diff_price":-1900,"change_days":3,"before":88450,"after":86550,"ticker":"TRNFP","currency":"rub"},"291420":{"id_profile":8321,"diff_percent":0.3,"diff_price":250,"change_days":3,"before":87500,"after":87750,"ticker":"TRNFP","currency":"rub"},"291419":{"id_profile":8321,"diff_percent":0.6,"diff_price":500,"change_days":3,"before":87750,"after":88250,"ticker":"TRNFP","currency":"rub"},"291418":{"id_profile":8321,"diff_percent":-1.2,"diff_price":-1050,"change_days":3,"before":89600,"after":88550,"ticker":"TRNFP","currency":"rub"}},{"161023":{"id_profile":3592,"diff_percent":17.3,"diff_price":9.14,"change_days":3,"before":43.62,"after":52.76,"ticker":"COIN","currency":"usd"},"161022":{"id_profile":3592,"diff_percent":1.8,"diff_price":2.68,"change_days":3,"before":149.96,"after":152.64,"ticker":"SBER","currency":"rub"},"161019":{"id_profile":3592,"diff_percent":2.7,"diff_price":4.2,"change_days":3,"before":149.3,"after":153.5,"ticker":"SBER","currency":"rub"},"161018":{"id_profile":3592,"diff_percent":-1.5,"diff_price":-2.19,"change_days":3,"before":152.64,"after":150.45,"ticker":"SBER","currency":"rub"},"161017":{"id_profile":3592,"diff_percent":0.6,"diff_price":0.93,"change_days":3,"before":150.45,"after":151.38,"ticker":"SBER","currency":"rub"}},{"43350":{"id_profile":623,"diff_percent":19.1,"diff_price":34.11,"change_days":3,"before":144.25,"after":178.36,"ticker":"TSLA","currency":"usd"},"43349":{"id_profile":623,"diff_percent":9.2,"diff_price":17.48,"change_days":3,"before":172.45,"after":189.93,"ticker":"TSLA","currency":"usd"},"43348":{"id_profile":623,"diff_percent":7.3,"diff_price":8.83,"change_days":3,"before":112.06,"after":120.89,"ticker":"SPOT","currency":"usd"}},{"105409":{"id_profile":1799,"diff_percent":-3,"diff_price":-45,"change_days":3,"before":1557,"after":1512,"ticker":"FIVE","currency":"rub"},"105407":{"id_profile":1799,"diff_percent":-0.6,"diff_price":-3,"change_days":3,"before":488.2,"after":485.2,"ticker":"VKCO","currency":"rub"},"105408":{"id_profile":1799,"diff_percent":-13.4,"diff_price":-0.343,"change_days":3,"before":2.894,"after":2.551,"ticker":"NGG3","currency":"rub"},"105406":{"id_profile":1799,"diff_percent":0,"diff_price":0,"change_days":3,"before":4665,"after":4665,"ticker":"MGNT","currency":"rub"},"105404":{"id_profile":1799,"diff_percent":-0.3,"diff_price":-2.1,"change_days":3,"before":619.1,"after":617,"ticker":"PIKK","currency":"rub"},"105405":{"id_profile":1799,"diff_percent":-1.7,"diff_price":-44,"change_days":3,"before":2623,"after":2579,"ticker":"SMLT","currency":"rub"},"105403":{"id_profile":1799,"diff_percent":-4.9,"diff_price":-453.5,"change_days":3,"before":9720,"after":9266.5,"ticker":"PLZL","currency":"rub"}},{"240663":{"id_profile":6249,"diff_percent":0.5,"diff_price":0.37,"change_days":3,"before":68.43,"after":68.8,"ticker":"USDRUB","currency":"rub"},"240664":{"id_profile":6249,"diff_percent":-0.2,"diff_price":-0.02,"change_days":3,"before":10.149,"after":10.129,"ticker":"CNYRUB","currency":"rub"},"240661":{"id_profile":6249,"diff_percent":0.4,"diff_price":0.65,"change_days":3,"before":151.47,"after":152.12,"ticker":"SBER","currency":"rub"},"240662":{"id_profile":6249,"diff_percent":0.4,"diff_price":0.65,"change_days":3,"before":151.47,"after":152.12,"ticker":"SBER","currency":"rub"},"240656":{"id_profile":6249,"diff_percent":-0.7,"diff_price":-1.1,"change_days":3,"before":153.5,"after":152.4,"ticker":"SBER","currency":"rub"},"240657":{"id_profile":6249,"diff_percent":-5.7,"diff_price":-4.15,"change_days":3,"before":77,"after":72.85,"ticker":"AMD","currency":"usd"},"240660":{"id_profile":6249,"diff_percent":0.6,"diff_price":1.2,"change_days":3,"before":192.2,"after":193.4,"ticker":"NVDA","currency":"usd"},"240658":{"id_profile":6249,"diff_percent":1.7,"diff_price":2.41,"change_days":3,"before":141.11,"after":143.52,"ticker":"AAPL","currency":"usd"},"240659":{"id_profile":6249,"diff_percent":1.3,"diff_price":1.25,"change_days":3,"before":97.4,"after":98.65,"ticker":"AMZN","currency":"usd"}},{"245017":{"id_profile":6429,"diff_percent":-0.1,"diff_price":-0.5,"change_days":3,"before":347.35,"after":346.85,"ticker":"ROSN","currency":"rub"},"245016":{"id_profile":6429,"diff_percent":1.6,"diff_price":30,"change_days":3,"before":1800,"after":1830,"ticker":"LNZLP","currency":"rub"},"245015":{"id_profile":6429,"diff_percent":-3.3,"diff_price":-5.27,"change_days":3,"before":165.8,"after":160.53,"ticker":"GAZP","currency":"rub"},"245014":{"id_profile":6429,"diff_percent":1.9,"diff_price":2.89,"change_days":3,"before":149.34,"after":152.23,"ticker":"SBER","currency":"rub"},"245012":{"id_profile":6429,"diff_percent":0.1,"diff_price":6.5,"change_days":3,"before":4597,"after":4603.5,"ticker":"MGNT","currency":"rub"},"245013":{"id_profile":6429,"diff_percent":2.8,"diff_price":12.8,"change_days":3,"before":451.9,"after":464.7,"ticker":"POLY","currency":"rub"},"245011":{"id_profile":6429,"diff_percent":0.7,"diff_price":1.12,"change_days":3,"before":152.4,"after":153.52,"ticker":"SBER","currency":"rub"},"245010":{"id_profile":6429,"diff_percent":-3,"diff_price":-14.3,"change_days":3,"before":483.5,"after":469.2,"ticker":"POLY","currency":"rub"},"245009":{"id_profile":6429,"diff_percent":6.2,"diff_price":31.5,"change_days":3,"before":476,"after":507.5,"ticker":"POLY","currency":"rub"},"245008":{"id_profile":6429,"diff_percent":3,"diff_price":15.3,"change_days":3,"before":493.8,"after":509.1,"ticker":"POLY","currency":"rub"},"245006":{"id_profile":6429,"diff_percent":-0.8,"diff_price":-3.9,"change_days":3,"before":513.3,"after":509.4,"ticker":"POLY","currency":"rub"}}]';
      return json_decode($data, 1);
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
      return 12;
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