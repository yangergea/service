<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */
 namespace Warehouse\Lfx;

 use Warehouse\Core\AbstractAPI;
 use Warehouse\Exceptions\InvalidArgumentException;
/**
 * Contact Contact
 */
 Class Lfx extends AbstractAPI
 {
     const API_SHIP_ORDER = '/LfxApi/shipOrder';

     /**
      * create contact
      * @param   $contactInfo
      * @return string contactId
      */
      public function shipOrder($param)
      {
          if(empty($contactInfo)){
              throw new InvalidArgumentException("Error: contactInfo wrongful ",1);
          }
          return $this->parseJSON('post', [self::API_CREATE_CONTACT,$contactInfo]);
      }
      
 }