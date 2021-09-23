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
use Symfony\Component\Console\Output\OutputInterface;
use Mirasvit\CacheWarmer\Api\Data\UserAgentInterface;
use Mirasvit\CacheWarmer\Api\Service\SessionServiceInterface;

class CrawlService
{
    /**
     * @var array
     */
    private $cookies = [];
    /**
     * @var CurlServiceFactory
     */
    private $curlServiceFactory;


    /**
     * CrawlService constructor.
     * @param CurlServiceFactory $curlServiceFactory
     */
    public function __construct(
        CurlServiceFactory $curlServiceFactory
    ) {
        $this->curlServiceFactory   = $curlServiceFactory;
    }

    /**
     * @param string $httpHeader
     * @param string $url
     * @return array
     */
    public function parseCookie($httpHeader, $url)
    {
        //convert cookie string to array
        $parts = explode(";", $httpHeader);

        $cookie = [];
        foreach ($parts as $v) {
            $v = trim($v);
            parse_str($v, $vv);
            $cookie[key($vv)] = reset($vv);
        }
        reset($cookie);
        $cookie['key'] = key($cookie);
        $cookie['value'] =  array_shift($cookie);
        ;
        if (!isset($cookie['path'])) {
            $cookie['path'] = '/';
        }
        if (!isset($cookie['domain'])) {
            $cookie['domain'] = parse_url($url, PHP_URL_HOST);
        }
        return $cookie;
    }

    /**
     * @param string $url
     * @param string $content
     */
    public function parseCookies($url, $content)
    {
        preg_match_all('/^Set-Cookie:(.*);/mi', $content, $matches);
        foreach ($matches[1] as $httpHeader) {
            $cookie = $this->parseCookie($httpHeader, $url);
            $path = $cookie['domain'].$cookie['path'];
            if (!isset($this->cookies[$path])) {
                $this->cookies[$path] = [];
            }

            if ($cookie['value'] == "deleted") {
                unset($this->cookies[$path][$cookie['key']]);
            } else {
                $this->cookies[$path][$cookie['key']] = $cookie['value'];
            }
        }
    }

    /**
     * @param string          $url
     * @param string          $sessionDataCookie
     * @param OutputInterface $output
     * @param string|bool     $userAgent
     * @return array
     */
    public function getUrls($url, $sessionDataCookie, OutputInterface $output, $userAgent = false)
    {
        $result = $this->makeRequest($url, $sessionDataCookie, $userAgent);
        $output->writeln("<comment>".$result['curl']."</comment>");

        $response = $result['response'];

        if ($response->getCode() == 401) {
            $output->writeln("<error>Can't open URL. 401 Authorization Required. Set HTTP Access info in the extension settings</error>");
            return [];
        }
        if ($response->getCode() >= 400) {
            $output->writeln("<error>Can't open URL. Response Code: {$response->getCode()}</error>");
            return [];
        }
        $content = $response->getBody();
        $this->parseCookies($url, $content);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        if ($content) {
            $dom->loadHTML($content);
        }
        libxml_clear_errors();

        $links = $dom->getElementsByTagName('a');

        $urlInfo = parse_url($url);
        $baseUrl = $urlInfo['scheme'] . '://' . $urlInfo['host'];
        $result = [];
        /** @var \DOMElement $link */
        foreach ($links as $link) {
            $l = $link->getAttribute('href');
            ;
            if (strpos($l, "/") === 0) { //absolute url like "/xxx/yyy"
                $l = $baseUrl.$l;
            }
            $result[] = $l;
        }

        return $result;
    }

    /**
     * @param string      $url
     * @param string      $sessionDataCookie
     * @param string|bool $userAgent
     *
     * @return array
     */
    public function makeRequest($url, $sessionDataCookie, $userAgent = false)
    {
        $curlService = $this->curlServiceFactory->create();
        $channel = $curlService->initChannel();
        $channel->setUrl($url);
        $channel->setOption(CURLOPT_FOLLOWLOCATION, true);
        $channel->setOption(CURLOPT_HEADER, true);

        $userAgent = $userAgent ?: UserAgentInterface::DESKTOP_USER_AGENT;

        $channel->setUserAgent($userAgent);

        // apply cookies
        // we sort array to make sure that
        // cookies with path x.com/ are applied before cookies with path x.com/de/
        $keys = array_map('strlen', array_keys($this->cookies));
        array_multisort($keys, SORT_DESC, $this->cookies);
        foreach ($this->cookies as $domainPath => $v) {
            // if cookie domain is not .x.com
            if ($domainPath[0] !== ".") {
                $domainPath = "://".$domainPath;
            }
            if (strpos($url, $domainPath) === false) {
                continue;
            }

            foreach ($v as $k => $v2) {
                $channel->addCookie($k, $v2);
            }
        }
        if ($sessionDataCookie) {
            $channel->addCookie(SessionServiceInterface::SESSION_COOKIE, $sessionDataCookie);
        }

        $response = $curlService->request($channel);

        return ['response' => $response, 'curl' => $channel->getCUrl()];
    }
}
