<?php
/**
 * @Author: 杨超
 * @Date:   2019-05-17 13:16:55
 * @Last Modified by:   杨超
 * @Last Modified time: 2019-05-17 13:16:55
 *
 * Copyright (C) 2019  玫瑰视界网络科技有限公司
 */


namespace Service\Core;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    /**
     * Base URL
     */
    protected $baseUrl = "";

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



    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';

    /**
     * Constructor.
     *
     * @param \EasyWeChat\Core\AccessToken $accessToken
     */
    public function __construct()
    {
        
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

        return $contents;
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

}
