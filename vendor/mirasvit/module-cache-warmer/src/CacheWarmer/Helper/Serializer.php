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



namespace Mirasvit\CacheWarmer\Helper;

use \Mirasvit\Core\Service\CompatibilityService;

class Serializer
{
    /**
     * @var null | \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer = null;

    private $isVersion21 = false;

    public function __construct()
    {
        $this->isVersion21 = CompatibilityService::is21();

        if (!$this->isVersion21) {
            $this->serializer = CompatibilityService::getObjectManager()
                ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
    }

    /**
     * @param array $data
     * @return string
     */
    public function serialize($data)
    {
        if ($this->isVersion21 && isset($data['type']) && strpos($data['type'], 'Rule') !== false) {
            /** mp comment start **/
            return serialize($data);
            /** mp comment end **/
            /** mp uncomment start
            return "a:0:{}";
            mp uncomment end **/
        }
        return $this->serializer ? $this->serializer->serialize($data) : \Zend_Json::encode($data);
    }

    /**
     * @param string $string
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function unserialize($string)
    {
        if ('[]' == $string || false === $string || null === $string || '' === $string) {
            return [];
        }

        try {
            $result = $this->serializer ? $this->serializer->unserialize($string) : \Zend_Json::decode($string);
        } catch (\Exception $e) {
            /** mp comment start */
            $result = unserialize($string);
            /** mp comment end */
        }

        return is_array($result) ? $result : [];
    }
}
