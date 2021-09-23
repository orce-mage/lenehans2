<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Service;

use Mirasvit\CacheWarmer\Service\Curl\CurlChannel;
use Mirasvit\CacheWarmer\Service\Curl\CurlChannelFactory;
use Mirasvit\CacheWarmer\Service\Curl\CurlResponse;
use Mirasvit\CacheWarmer\Service\Curl\CurlResponseFactory;

class CurlService
{
    /**
     * @var CurlChannelFactory
     */
    private $channelFactory;

    /**
     * @var CurlResponseFactory
     */
    private $responseFactory;

    /**
     * @var array
     */
    private $responseHeaders;

    /**
     * CurlService constructor.
     * @param CurlChannelFactory $channelFactory
     * @param CurlResponseFactory $responseFactory
     */
    public function __construct(
        CurlChannelFactory $channelFactory,
        CurlResponseFactory $responseFactory
    ) {
        $this->channelFactory  = $channelFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param int $n
     * @return CurlChannel[]
     */
    public function initMultiChannel($n)
    {
        $channels = [];
        for ($i = 0; $i < $n; $i++) {
            $channels[] = $this->initChannel();
        }

        return $channels;
    }

    /**
     * @return CurlChannel
     */
    public function initChannel()
    {
        return $this->channelFactory->create();
    }

    /**
     * @param CurlChannel[] $channels
     * @return CurlResponse[]
     */
    public function multiRequest(array $channels)
    {
        $result = [];

        if (function_exists('curl_multi_init')) {
            $mch = curl_multi_init();

            $chs = [];

            foreach ($channels as $idx => $channel) {
                $chs[$idx] = $this->getCh($channel);
                curl_multi_add_handle($mch, $chs[$idx]);
                /** mp comment start */
                if (php_sapi_name() === 'cli') {
                    echo "\n" . $channel->getCUrl() . "\n\n";
                }
                /** mp comment end */
            }

            do {
                $execReturnValue = curl_multi_exec($mch, $isRunning);
                usleep(1000);
            } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);

            // Loop and continue processing the request
            while ($isRunning && $execReturnValue == CURLM_OK) {
                if (curl_multi_select($mch) == -1) {
                    usleep(1);
                }

                do {
                    $mrc = curl_multi_exec($mch, $isRunning);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            // Extract the content
            foreach ($channels as $idx => $channel) {
                $ch        = $chs[$idx];
                $chId      = (string)$ch;
                $curlError = curl_error($ch);

                if (isset($this->responseHeaders[$chId])) {
                    $headers = $this->responseHeaders[$chId];
                } else {
                    $headers = [];
                }

                // for unknown reason, on some servers, multi curl returns Could not resolve host error
                // however, tests shown that page is still fetched. so we can ignore it.
                if (!$curlError || strpos($curlError, "Could not resolve host") !== false) {
                    $body = curl_multi_getcontent($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                } else {
                    $body = $curlError;
                    $code = 500;
                }

                $response = $this->responseFactory->create();

                $response->set($channel, $code, $headers, $body);

                $result[] = $response;

                curl_multi_remove_handle($mch, $chs[$idx]);
                curl_close($chs[$idx]);
            }
        } else {
            foreach ($channels as $channel) {
                $result[] = $this->request($channel);
            }
        }

        return $result;
    }

    /**
     * @param CurlChannel $channel
     * @return resource
     */
    private function getCh(CurlChannel $channel)
    {
        $ch = $channel->getCh();

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "");

        return $ch;
    }

    /**
     * @param CurlChannel $channel
     * @return CurlResponse
     */
    public function request(CurlChannel $channel)
    {
        $ch   = $this->getCh($channel);
        $chId = (string)$ch;

        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $err = curl_errno($ch);

        if ($err) {
            $body = curl_error($ch);
            $code = 500;
        }

        $headers = isset($this->responseHeaders[$chId]) ? $this->responseHeaders[$chId] : [];

        curl_close($ch);

        $response = $this->responseFactory->create();
        $response->set($channel, $code, $headers, $body);

        return $response;
    }

    /**
     * @param string $ch
     * @param string $data
     * @return int
     */
    protected function parseHeaders($ch, $data)
    {
        $chId = (string)$ch;
        $name = $value = '';

        $out = explode(": ", trim($data), 2);
        if (count($out) == 2) {
            $name  = $out[0];
            $value = $out[1];
        }

        if (strlen($name)) {
            $this->responseHeaders[$chId][$name] = $value;
        }

        return strlen($data);
    }
}
