<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */
namespace Warehouse\Foundation;

use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use Warehouse\Core\AccessToken;
use Warehouse\Core\Http;
use Warehouse\Utils\Log;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

/**
* Class App
*/
class App extends Container
{
    /**
     * Service Providers.
     *
     * @var array
     */
    protected $providers = [
        ServiceProviders\EnterpriseServiceProvider::class,
        ServiceProviders\WechatServiceProvider::class,
        ServiceProviders\EmailServiceProvider::class,
        ServiceProviders\SmsServiceProvider::class,
        ServiceProviders\ContactServiceProvider::class,
        ServiceProviders\ContactTagsServiceProvider::class,
        ServiceProviders\CustomerServiceProvider::class,
    ];

    public function __construct($config)
    {
        parent::__construct();

        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        if ($this['config']['debug']) {
            error_reporting(E_ALL);
        }

        $this->registerProviders();
        $this->registerBase();
        $this->initializeLogger();


        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));
    }
    /**
     * Add a provider.
     *
     * @param string $provider
     *
     * @return Application
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    /**
     * Set providers.
     *
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * Register providers.
     */
    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     * Register basic providers.
     */
    private function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };

        if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof CacheInterface) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache(sys_get_temp_dir());
            };
        }
        $this['access_token'] = function () {
            return new AccessToken(
                $this['config']['app_id'],
                $this['config']['secret'],
                $this['cache'],
                $this['config']['base_url']
            );
        };
    }

    /**
     * Initialize logger.
     */
    private function initializeLogger()
    {
        if (Log::hasLogger()) {
            return;
        }

        $logger = new Logger('yihecloud');

        if (!$this['config']['debug'] || defined('PHPUNIT_RUNNING')) {
            $logger->pushHandler(new NullHandler());
        } elseif ($this['config']['log.handler'] instanceof HandlerInterface) {
            $logger->pushHandler($this['config']['log.handler']);
        } elseif ($logFile = $this['config']['log.file']) {
            $logger->pushHandler(new StreamHandler(
                $logFile,
                $this['config']->get('log.level', Logger::WARNING),
                true,
                $this['config']->get('log.permission', null)
            ));
        }

        Log::setLogger($logger);
    }
}
