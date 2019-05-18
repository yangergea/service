<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */


namespace Warehouse\Core;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Warehouse\Exceptions\HttpException;
use Warehouse\Exceptions\Exception;
use Warehouse\Utils\Collection;
use Warehouse\Utils\Log;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    /**
     * Base URL
     */
    protected $baseUrl = "https://api.growthcloud.cn";

    /**
     * Http instance.
     *
     * @var \Warehouse\Core\Http
     */
    protected $http;

    /**
     * The request token.
     *
     * @var \Warehouse\Core\AccessToken
     */
    protected $accessToken;


    protected $cachePrefix = "Warehouse.api";

    protected $apiFunction = null;

    protected $debug = false;

    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';

    /**
     * Constructor.
     *
     * @param \EasyWeChat\Core\AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken, Cache $cache = null, $baseUrl = null, $debug = false)
    {
        $this->cache = $cache;
        $this->debug = $debug;
        $this->setAccessToken($accessToken);
        if (!is_null($baseUrl)) {
            $this->setBaseUrl($baseUrl);
        }
    }

    /**
     * Set cache instance.
     *
     * @param \Doctrine\Common\Cache\Cache $cache
     *
     * @return AccessToken
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Return the cache manager.
     *
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }

    /**
     *  set the base Url
     */

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Return the http instance.
     *
     * @return \EasyWeChat\Core\Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (count($this->http->getMiddlewares()) === 0) {
            $this->registerHttpMiddlewares();
        }

        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param \EasyWeChat\Core\Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Return the current accessToken.
     *
     * @return \EasyWeChat\Core\AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the request token.
     *
     * @param \EasyWeChat\Core\AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Parse JSON from response and check error.
     *
     * @param string $method
     * @param array  $args
     *
     * @return \EasyWeChat\Support\Collection
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();

        if (!empty($args)) {
            if ($args[0][0] == '/') {
                $url = $this->baseUrl.$args[0];
                $args[0] = $url;
            }
        }
        // Log::debug(json_encode($args));

        $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));

        $this->checkAndThrow($contents);

        return new Collection($contents);
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // access token
        $this->http->addMiddleware($this->accessTokenMiddleware());
        // log
        $this->http->addMiddleware($this->logMiddleware());
    }

    /**
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->accessToken) {
                    return $handler($request, $options);
                }

                $field = $this->accessToken->getTokenType();
                $token = $this->accessToken->getToken();
                $tokenStr = "{$field} {$token}";
                // $request = $request->withHeader("accept", "application/json");
                $request = $request->withHeader("Authorization", $tokenStr);
                // Log::debug(json_encode($request->getHeaders()));

                return $handler($request, $options);
            };
        };
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} ".json_encode($options));
            Log::debug('Request headers:'.json_encode($request->getHeaders()));
        });
    }


    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     *
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        if (isset($contents['errcode']) && 0 !== $contents['errcode']) {
            if (empty($contents['errmsg'])) {
                $contents['errmsg'] = 'Unknown';
            }

            throw new HttpException($contents['errmsg'], $contents['errcode']);
        }
    }


    /**
     * Get cache key.
     *
     * @return string $this->cacheKey
     */
    public function getCacheKey($cacheKey = null)
    {
        if (!isset($cacheKey)) {
            throw new InvalidArgumentException("Error: No cacheKey given", 1);
        }
        if (is_null($this->apiFunction)) {
            throw new InvalidArgumentException("Error: no function name", 1);
        }
        $key = "{$this->cachePrefix}.{$this->apiFunction}.{$cacheKey}";
        return $key;
    }

    /**
     * Store to cache
     */
    public function store($cacheKey, $value, $expires = 7200)
    {
        if ($expires <= 0) {
            throw new InvalidArgumentException("Error: expire time less then zero", 1);
        }
        Log::debug("Set Cache Key:".$this->getCacheKey($cacheKey));
        $this->getCache()->save($this->getCacheKey($cacheKey), $value, $expires);
        return $this;
    }

    /**
     * Get from cache
     */
    public function get($cacheKey)
    {
        $cacheKey = $this->getCacheKey($cacheKey);
        Log::debug("Get Object from Cache Key:".$cacheKey);
        return $this->getCache()->fetch($cacheKey);
    }
}
