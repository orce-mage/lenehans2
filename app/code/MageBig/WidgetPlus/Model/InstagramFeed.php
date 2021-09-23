<?php

namespace MageBig\WidgetPlus\Model;

class InstagramFeed
{

    private $cacheProxy;

    /**
     * Transient seconds
     *
     * @since    1.0.0
     * @access   private
     * @var      number $transient Transient time in seconds
     */
    private $transient_sec;

    /**
     * InstagramFeed constructor.
     *
     * @param \Magento\Framework\App\Cache\Proxy $cacheProxy
     * @param int $transient_sec
     */
    public function __construct(
        \Magento\Framework\App\Cache\Proxy $cacheProxy,
        $transient_sec = 3600
    ) {
        $this->cacheProxy = $cacheProxy;
        $this->transient_sec = $transient_sec;
    }

    /**
     * @param $search_user_id
     * @param $limit
     *
     * @return false|var|string
     */
    public function getPublicPhotos($search_user_id, $limit)
    {
        if (!empty($search_user_id)) {
            $url = 'https://www.instagram.com/' . $search_user_id . '/';

            $transient_name = 'magebig_' . crc32($url);

            if ($this->transient_sec > 0 && false !== ($data = $this->getTransient($transient_name))) {
                return ($data);
            }

            $rsp = json_decode(json_encode($this->getFallbackImages($search_user_id)));

            $images = [];
            for ($i = 0; $i < $limit; $i++) {
                if (isset($rsp->edge_owner_to_timeline_media->edges[$i])) {
                    $node = $rsp->edge_owner_to_timeline_media->edges[$i]->node;

                    if (isset($node->is_video) && $node->is_video == true) {
                        $type = 'video';
                    } else {
                        $type = 'image';
                    }

                    $image = [
                        'link' => $node->shortcode,
                        'comments' => $node->edge_media_to_comment->count,
                        'likes' => $node->edge_liked_by->count,
                        'thumbnail' => $node->thumbnail_src,
                        'small' => $node->thumbnail_resources[2]->src,
                        'original' => $node->display_url,
                        'type' => $type
                    ];
                    array_push($images, $image);
                }
            }

            if (isset($images) && $images != null) {
                $value = json_encode($images);
                $this->setTransient($transient_name, $value, $this->transient_sec);
                return $value;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    /**
     * @param $search_user_id
     *
     * @return bool
     */
    private function getFallbackImages($search_user_id)
    {
        //FALLBACK 12 ELEMENTS
        $page_res = $this->clientRequest('get', '/' . $search_user_id . '/');
        $user_data = '';
        if (!isset($page_res['http_code'])) {
            return $user_data;
        }
        switch ($page_res['http_code']) {
            case 404:
                break;

            case 200:
                $page_data_matches = [];

                $script = '#window\._sharedData\s*=\s*(.*?)\s*;\s*</script>#';
                if (!preg_match($script, $page_res['body'], $page_data_matches)) {
                    //Instagram reports: Parse script error
                    return '';
                } else {
                    $page_data = json_decode($page_data_matches[1], true);

                    if (!$page_data || empty($page_data['entry_data']['ProfilePage'][0]['graphql']['user'])) {
                        //Instagram reports: Content did not match expected
                        return '';
                    } else {
                        $user_data = $page_data['entry_data']['ProfilePage'][0]['graphql']['user'];

                        if ($user_data['is_private']) {
                            //Instagram reports: Content is private
                            return '';
                        }
                    }
                }

                break;

            default:
                break;
        }

        return $user_data;
    }

    /**
     * @param $type
     * @param $url
     *
     * @return array
     */
    private function clientRequest($type, $url)
    {
        $browser = 'Mozilla/5.0 (Windows NT 10.0; WOW64) ' . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.87 Safari/537.36';

        $this->index('client', [
            'base_url' => 'https://www.instagram.com/',
            'cookie_jar' => [],
            'headers' => [
                'User-Agent' => $browser,
                'Origin' => 'https://www.instagram.com',
                'Referer' => 'https://www.instagram.com',
                'Connection' => 'close'
            ]
        ]);
        $client = $this->index('client');
        $type = strtoupper($type);

        $url = (!empty($client['base_url']) ? rtrim($client['base_url'], '/') : '') . $url;
        $url_info = parse_url($url);

        $scheme = !empty($url_info['scheme']) ? $url_info['scheme'] : '';
        $host = !empty($url_info['host']) ? $url_info['host'] : '';
        $port = !empty($url_info['port']) ? $url_info['port'] : '';
        $path = !empty($url_info['path']) ? $url_info['path'] : '';
        $query_str = !empty($url_info['query']) ? $url_info['query'] : '';

        $headers = !empty($client['headers']) ? $client['headers'] : [];

        $headers['Host'] = $host;

        $client_cookies = $this->clientGetCookiesList($host);
        $cookies = $client_cookies;

        if ($cookies) {
            $request_cookies_raw = [];

            foreach ($cookies as $cookie_name => $cookie_value) {
                $request_cookies_raw[] = $cookie_name . '=' . $cookie_value;
            }
            unset($cookie_name, $cookie_data);

            $headers['Cookie'] = implode('; ', $request_cookies_raw);
        }

        $headers_raw_list = [];

        foreach ($headers as $header_key => $header_value) {
            $headers_raw_list[] = $header_key . ': ' . $header_value;
        }
        unset($header_key, $header_value);

        $transport_error = null;
        $curl_support = function_exists('curl_init');
        $sockets_support = function_exists('fsockopen');

        if (!$curl_support && !$sockets_support) {
            //Curl and sockets are not supported on this server

            return [
                'status' => 0,
                'transport_error' => 'php on web-server does not support curl and sockets'
            ];
        }

        if ($curl_support) {
            $curl = curl_init();
            $curl_options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_URL => $scheme . '://' . $host . $path . (!empty($query_str) ? '?' . $query_str : ''),
                CURLOPT_HTTPHEADER => $headers_raw_list,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 60,
            ];

            curl_setopt_array($curl, $curl_options);

            $response_str = curl_exec($curl);
            $curl_info = curl_getinfo($curl);

            curl_close($curl);

            if ($curl_info['http_code'] === 0) {
                //An error occurred while loading data;

                $transport_error = ['status' => 0, 'transport_error' => 'curl'];

                if (!$sockets_support) {
                    return $transport_error;
                }
            }
        }

        if (!$curl_support || $transport_error) {
            //Trying to load data using sockets

            $headers_str = implode("\r\n", $headers_raw_list);
            $ostr = "%s %s HTTP/1.1\r\n%s\r\n\r\n%s";
            $out = sprintf($ostr, $type, $path . (!empty($query_str) ? '?' . $query_str : ''), $headers_str, '');

            if ($scheme === 'https') {
                $scheme = 'ssl';
                $port = !empty($port) ? $port : 443;
            }

            $scheme = !empty($scheme) ? $scheme . '://' : '';
            $port = !empty($port) ? $port : 80;

            $sock = @fsockopen($scheme . $host, $port, $err_num, $err_str, 15);

            if (!$sock) {
                //An error occurred while loading data error_number: ' . $err_num . ', error_number: ' . $err_str);

                return [
                    'status' => 0,
                    'error_number' => $err_num,
                    'error_message' => $err_str,
                    'transport_error' => $transport_error ? 'curl and sockets' : 'sockets'
                ];
            }

            fwrite($sock, $out);

            $response_str = '';

            while ($line = fgets($sock, 128)) {
                $response_str .= $line;
            }

            fclose($sock);
        }

        @list ($response_headers_str, $response_body_encoded, $alt_body_encoded) = explode("\r\n\r\n", $response_str);

        if ($alt_body_encoded) {
            $response_headers_str = $response_body_encoded;
            $response_body_encoded = $alt_body_encoded;
        }

        $response_body = $response_body_encoded;
        $response_headers_raw_list = explode("\r\n", $response_headers_str);
        $response_http = array_shift($response_headers_raw_list);

        preg_match('#^([^\s]+)\s(\d+)([^\*]+)$#', $response_http, $response_http_matches);
        array_shift($response_http_matches);
        list ($response_http_protocol, $response_http_code, $response_http_message) = $response_http_matches;

        $response_headers = [];
        $response_cookies = [];
        foreach ($response_headers_raw_list as $header_row) {
            list ($header_key, $header_value) = explode(': ', $header_row, 2);

            if (strtolower($header_key) === 'set-cookie') {
                $cookie_params = explode('; ', $header_value);

                if (empty($cookie_params[0])) {
                    continue;
                }

                list ($cookie_name, $cookie_value) = explode('=', $cookie_params[0]);
                $response_cookies[$cookie_name] = $cookie_value;
            } else {
                $response_headers[$header_key] = $header_value;
            }
        }
        unset($header_row, $header_key, $header_value, $cookie_name, $cookie_value);

        if ($response_cookies) {
            $response_cookies['ig_or'] = 'landscape-primary';
            $response_cookies['ig_pr'] = '1';
            $response_cookies['ig_vh'] = rand(500, 1000);
            $response_cookies['ig_vw'] = rand(1100, 2000);

            $client['cookie_jar'][$host] = $this->arrayMergeAssoc($client_cookies, $response_cookies);
            $this->index('client', $client);
        }
        return [
            'status' => 1,
            'http_protocol' => $response_http_protocol,
            'http_code' => $response_http_code,
            'http_message' => $response_http_message,
            'headers' => $response_headers,
            'cookies' => $response_cookies,
            'body' => $response_body
        ];
    }

    /**
     * @param $domain
     *
     * @return array
     */
    private function clientGetCookiesList($domain)
    {
        $client = $this->index('client');
        $cookie_jar = $client['cookie_jar'];

        return !empty($cookie_jar[$domain]) ? $cookie_jar[$domain] : [];
    }

    /**
     * @param $key
     * @param null $value
     * @param bool $f
     *
     * @return mixed|null
     */
    private function index($key, $value = null, $f = false)
    {
        static $index = [];

        if ($value || $f) {
            $index[$key] = $value;
        }

        return !empty($index[$key]) ? $index[$key] : null;
    }

    /**
     * Helper function for fallback photos function
     * @return NULL
     */
    private function arrayMergeAssoc()
    {
        $mixed = null;
        $arrays = func_get_args();

        foreach ($arrays as $k => $arr) {
            if ($k === 0) {
                $mixed = $arr;
                continue;
            }

            $mixed = array_combine(array_merge(array_keys($mixed), array_keys($arr)),
                array_merge(array_values($mixed), array_values($arr)));
        }

        return $mixed;
    }

    /**
     * @param $handle
     *
     * @return mixed|string
     */

    public function getTransient($handle)
    {
        $data = $this->cacheProxy->load('magebig-transient-' . $handle);
        if ($data !== false) {
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * @param $handle
     * @param $value
     * @param int $expiration
     */

    public function setTransient($handle, $value, $expiration = 0)
    {
        $this->cacheProxy->save(serialize($value), 'magebig-transient-' . $handle, [], $expiration);
    }

    /**
     *  Delete transient
     *
     * @param  string  Handle
     */

    public function deleteTransient($handle)
    {
        $this->cacheProxy->remove('magebig-transient-' . $handle);
    }
}
