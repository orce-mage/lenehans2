<?php
/**
 * Initial configuration data container. Provides interface for reading initial config values
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageBig\MbFrame\Framework\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Initial extends \Magento\Framework\App\Config\Initial
{
    /**
     * Cache identifier used to store initial config
     */
    const CACHE_ID = 'initial_config';

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Config metadata
     *
     * @var array
     */
    protected $_metadata = [];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Initial constructor.
     * @param ThemeId                                      $theme
     * @param \Magento\Framework\App\Config\Initial\Reader $reader
     * @param \Magento\Framework\App\Cache\Type\Config     $cache
     * @param SerializerInterface|null                     $serializer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \MageBig\MbFrame\Framework\App\Config\ThemeId $theme,
        \MageBig\MbFrame\Framework\App\Config\Initial\Reader $reader,
        \Magento\Framework\App\Cache\Type\Config $cache,
        SerializerInterface $serializer = null
    ) {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(SerializerInterface::class);
        $themeId = $theme->getThemeId();
        $data = $cache->load(self::CACHE_ID.$themeId);
        if (!$data) {
            $data = $reader->read();
            $cache->save($this->serializer->serialize($data), self::CACHE_ID.$themeId);
        } else {
            $data = $this->serializer->unserialize($data);
        }
        $this->_data = $data['data'];
        $this->_metadata = $data['metadata'];
    }

    /**
     * Get initial data by given scope
     *
     * @param string $scope Format is scope type and scope code separated by pipe: e.g. "type|code"
     * @return array
     */
    public function getData($scope)
    {
        list($scopeType, $scopeCode) = array_pad(explode('|', $scope), 2, null);

        if (ScopeConfigInterface::SCOPE_TYPE_DEFAULT == $scopeType) {
            return isset($this->_data[$scopeType]) ? $this->_data[$scopeType] : [];
        } elseif ($scopeCode) {
            return isset($this->_data[$scopeType][$scopeCode]) ? $this->_data[$scopeType][$scopeCode] : [];
        }
        return [];
    }

    /**
     * Get configuration metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }
}
