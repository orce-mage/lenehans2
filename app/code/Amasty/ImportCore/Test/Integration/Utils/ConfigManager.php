<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\Utils;

use Amasty\ImportCore\SchemaReader\Config\Reader as ConfigReader;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;

trait ConfigManager
{
    /**
     * @param string $fixtureLocation Absolute path to config
     */
    public function overrideImportConfig(
        string $fixtureLocation
    ) {
        $objectManager = Bootstrap::getObjectManager();
        /** @var FileIteratorFactory $fileIteratorFactory */
        $fileIteratorFactory = $objectManager->get(FileIteratorFactory::class);
        $fileResolver = $this->createMock(FileResolverInterface::class);
        $fileResolver->method('get')->willReturn($fileIteratorFactory->create(
            [$fixtureLocation]
        ));

        $objectManager->addSharedInstance($fileResolver, self::FILE_RESOLVER_CLASS);
        $objectManager->configure(
            [
                ConfigReader::class => [
                    'arguments' => [
                        'fileResolver' => ['instance' => self::FILE_RESOLVER_CLASS],
                    ],
                ],
            ]
        );
        $this->clearConfigCache();
    }

    public function revertImportConfigOverride()
    {
        Bootstrap::getObjectManager()->configure(
            [
                ConfigReader::class => [
                    'arguments' => [
                        'fileResolver' => ['instance' => FileResolverInterface::class],
                    ],
                ],
            ]
        );
        $this->clearConfigCache();
    }

    protected function clearConfigCache()
    {
        /** @var \Magento\Framework\App\Cache\Type\Config $cache */
        $cache = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache\Type\Config::class);
        $cache->remove(\Amasty\ImportCore\SchemaReader\Config::CACHE_ID);
    }
}
