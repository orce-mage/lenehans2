<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Test\Integration\Utils;

use Amasty\ExportCore\SchemaReader\Config\Reader as ConfigReader;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\TestFramework\Helper\Bootstrap;

trait ConfigManager
{
    /**
     * @param string $fixtureLocation Absolute path to config
     */
    public function overrideExportConfig(
        string $fixtureLocation
    ) {
        $objectManager = Bootstrap::getObjectManager();
        /** @var FileIteratorFactory $fileIteratorFactory */
        $fileIteratorFactory = $objectManager->get(FileIteratorFactory::class);
        $fileResolver = $this->createMock(FileResolverInterface::class);
        $fileResolver->method('get')->willReturn($fileIteratorFactory->create(
            [$fixtureLocation]
        ));

        $objectManager->addSharedInstance($fileResolver, 'custom_config_resolver');
        $objectManager->configure(
            [
                ConfigReader::class => [
                    'arguments' => [
                        'fileResolver' => ['instance' => 'custom_config_resolver'],
                    ],
                ],
            ]
        );
        $this->clearConfigCache();
    }

    public function revertExportConfigOverride()
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
        $cache->remove(\Amasty\ExportCore\SchemaReader\Config::CACHE_ID);
    }
}
