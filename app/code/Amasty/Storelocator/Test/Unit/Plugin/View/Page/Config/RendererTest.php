<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Test\Plugin\View\Page\Config;

use Amasty\Storelocator\Plugin\View\Page\Config\Renderer;
use Amasty\Storelocator\Test\Unit\Traits;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class RendererTest
 *
 * @see Renderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RendererTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var MockObject|Renderer
     */
    private $plugin;

    /**
     * @var MockObject|\Magento\Framework\View\Page\Config
     */
    private $config;

    /**
     * @var MockObject|\Magento\Framework\View\Asset\GroupedCollection
     */
    private $groupedCollection;

    /**
     * @var MockObject|CacheInterface
     */
    private $cache;

    protected function setUp(): void
    {
        $this->config = $this->createMock(\Magento\Framework\View\Page\Config::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cache->expects($this->any())->method('load')->willReturnOnConsecutiveCalls(true, 0, false);

        $this->groupedCollection = $this->createMock(\Magento\Framework\View\Asset\GroupedCollection::class);

        $this->plugin = $this->getObjectManager()->getObject(
            Renderer::class,
            [
                'config' => $this->config,
                'cache' => $this->cache,
            ]
        );
    }

    /**
     * @covers Renderer::beforeRenderAssets
     */
    public function testBeforeRenderAssets()
    {
        $this->groupedCollection->expects($this->any())->method('getAll')->willReturn([]);

        $this->config->expects($this->exactly(2))->method('addPageAsset');
        $this->config->expects($this->any())->method('getAssetCollection')->willReturn($this->groupedCollection);

        $this->cache->expects($this->once())->method('save');

        $subject = $this->createMock(MagentoRenderer::class);

        $this->plugin->beforeRenderAssets($subject, []);
        $this->plugin->beforeRenderAssets($subject, []);
        $this->plugin->beforeRenderAssets($subject, []);
    }

    /**
     * @covers Renderer::isShouldLoadCss
     */
    public function testIsShouldLoadCss()
    {
        $file1 = $this->getObjectManager()->getObject(File::class);
        $file2 = $this->getObjectManager()->getObject(File::class);
        $file3 = $this->getObjectManager()->getObject(File::class);

        $this->setProperty($file1, 'filePath', 'css/styles-l.css');
        $this->setProperty($file2, 'filePath', 'css/styles-m.css');
        $this->setProperty($file3, 'filePath', 'test');

        $this->config->expects($this->any())->method('getAssetCollection')->willReturn($this->groupedCollection);
        $this->groupedCollection->expects($this->any())->method('getAll')
            ->willReturnOnConsecutiveCalls([], [$file1, $file2, $file3]);

        $this->assertEquals(1, $this->invokeMethod($this->plugin, 'isShouldLoadCss'));
        $this->assertEquals(0, $this->invokeMethod($this->plugin, 'isShouldLoadCss'));
    }
}
