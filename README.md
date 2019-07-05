# service
ole service

  示例：
  <?php
  
  use Service\Foundation\App;

  $app = new App(env('APP_ENV'));
  
  //采购单详情
  $order_detail = $app->order->getOrderDetail(["purchase_no"=>"PN55617560225495"]);
