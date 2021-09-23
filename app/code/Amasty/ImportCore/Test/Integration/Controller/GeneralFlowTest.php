<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Test\Integration\Controller;

use Amasty\ImportCore\Model\Process\ResourceModel\Process as ProcessResource;
use Magento\Framework\Shell;

class GeneralFlowTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    const IDENTITY = 'someIdentity';
    const ENTITY = 'test_entity';
    const BEHAVIOR = 'addDirect';

    protected $resource = 'Amasty_ImportCore::import';
    protected $uri = 'backend/amimport/import/index';

    protected function setUp(): void
    {
        parent::setUp();

        $shell = $this->createMock(Shell::class);
        $this->_objectManager->addSharedInstance($shell, Shell::class);
    }

    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(Shell::class);

        parent::tearDown();
    }

    /**
     * @magentoDbIsolation disabled
     * @see \Amasty\ImportCore\Controller\Adminhtml\Import\Validate::execute
     * @magentoConfigFixture current_store web/unsecure/base_url http://localhost/
     */
    public function testValidate()
    {
        /** @var ProcessResource $processResource */
        $processResource = $this->_objectManager->get(ProcessResource::class);
        $processResource->getConnection()->delete(
            $processResource->getMainTable()
        );

        $this->getRequest()->setParam('processIdentity', self::IDENTITY);
        $this->getRequest()->setParam('entity_code', self::ENTITY);
        $this->getRequest()->setParam('behavior', self::BEHAVIOR);
        $this->getRequest()->setParam(
            'fields',
            [
                self::ENTITY => [
                    'enabled' => "1",
                    'fields' => [
                        [
                            'code' => 'entity_id',
                        ]
                    ]
                ]
            ]
        );
        $this->dispatch('backend/amimport/import/validate');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('success', $data['type']);
        $this->assertCount(1, $data);
    }

    /**
     * @see \Amasty\ImportCore\Controller\Adminhtml\Import\Import::execute
     * @magentoConfigFixture current_store web/unsecure/base_url http://localhost/
     * @depends testValidate
     */
    public function testImport()
    {
        $this->getRequest()->setParam('processIdentity', self::IDENTITY);
        $this->dispatch('backend/amimport/import/import');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('success', $data['type']);
        $this->assertCount(1, $data);
    }

    /**
     * @see \Amasty\ImportCore\Controller\Adminhtml\Import\Status::execute
     * @magentoConfigFixture current_store web/unsecure/base_url http://localhost/
     * @depends testImport
     */
    public function testStatus()
    {
        $this->getRequest()->setParam('processIdentity', self::IDENTITY);
        $this->dispatch('backend/amimport/import/status');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('starting', $data['status']);
    }

    /**
     * @see \Amasty\ImportCore\Controller\Adminhtml\Import\Cancel::execute
     * @magentoConfigFixture current_store web/unsecure/base_url http://localhost/
     * @depends testStatus
     */
    public function testCancel()
    {
        $this->getRequest()->setParam('processIdentity', self::IDENTITY);
        $this->dispatch('backend/amimport/import/cancel');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals('success', $data['type']);
        $this->assertCount(1, $data);
    }

    /**
     * @magentoConfigFixture current_store web/unsecure/base_url http://localhost/
     * @codingStandardsIgnoreStart
     */
    public function testAclNoAccess()
    {
        return parent::testAclNoAccess();
    }
}
