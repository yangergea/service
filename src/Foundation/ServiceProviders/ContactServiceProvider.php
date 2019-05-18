<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */

namespace Warehouse\Foundation\ServiceProviders;

use Warehouse\Contact\Contact;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ContactServiceProvider implements ServiceProviderInterface
{
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
        $contact = function ($pimple) {
            return new Contact(
                $pimple['access_token'],
                $pimple['cache'],
                $pimple['config']["base_url"],
                $pimple['config']['debug']
            );
        };

        $pimple['contact'] = $contact;
    }

}
