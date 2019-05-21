<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */
 namespace Service\Service;

 use Service\Core\AbstractAPI;

/**
 * Order 
 */
 Class Order extends AbstractAPI
 {
     const PURCHASE_ORDER_DETAIL = '/purchase/getOrderDetail';//采购单详情
     const PURCHASE_ORDER_LIST = '/purchase/getOrderList';//采购单列表

     const SERVICE = [
        'local' => '47.98.49.232:8011',
        'test' => '127.0.0.1:8011',
        'testing' => '127.0.0.1:8011',
        'pre_production' => '172.17.21.169:8091',
        'production' => '172.17.21.169:8091'
     ];

     public function __construct($app_env)
     {
          $this->baseUrl = self::SERVICE[$app_env];
     }

     /**
      * getOrderDetail
      * @param   $param
      * @return array
      */
      public function getOrderDetail(array $param)
      {
          return $this->parseJSON('post', [self::PURCHASE_ORDER_DETAIL,$param]);
      }

      /**
      * getOrderDetail
      * @param   $param
      * @return array
      */
      public function getOrderList(array $param=[])
      {
          return $this->parseJSON('post', [self::PURCHASE_ORDER_LIST,$param]);
      }

 }