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
 * Good 
 */
 Class Good extends AbstractAPI
 {
     const GET_CATEGORY_LIST = '/category/getGoodsCategoryList';//获取商品类目列表

     const SERVICE = [
        'local' => '47.98.49.232:8012',
        'test' => '127.0.0.1:8012',
        'testing' => '127.0.0.1:8012',
        'pre_production' => '172.17.21.169:8092',
        'production' => '172.17.21.169:8092'
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
      public function getGoodsCategoryList(string $param='')
      {
          return $this->parseJSON('post', [self::GET_CATEGORY_LIST,$param]);
      }

 }