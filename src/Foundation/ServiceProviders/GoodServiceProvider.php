<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */

namespace Service\Foundation\ServiceProviders;

use Service\Service\Good;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GoodServiceProvider implements ServiceProviderInterface
{
    public function __construct($app_env)
    {
        $this->app_env = $app_env;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['Good'] = new Good($this->app_env);
    }

}
