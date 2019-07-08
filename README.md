    <?php
    use Service\Foundation\App;

    $app = new App(env('APP_ENV'));

    //采购单详情
    $order_detail = $app->order->getOrderDetail(["purchase_no"=>"PN55617560225495"]);

    //采购单列表
    $order_list = $app->order->getOrderList(['need_order_list'=>true]);

    //商品列表
    $good_list = $app->Good->getGoodsCategoryList();
