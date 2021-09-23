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



namespace Mirasvit\CacheWarmer\Model\ResourceModel\Page;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Model\Page;
use Psr\Log\LoggerInterface as Logger;

class Grid extends SearchResult
{
    /**
     * @var string
     */
    protected $document = Page::class;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = PageInterface::TABLE_NAME,
        $resourceModel = 'Mirasvit\CacheWarmer\Model\ResourceModel\Page'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * {@inheritDoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == PageInterface::WARM_RULE_IDS) {
            $values  = $condition['in'];
            $newCond = [];

            foreach ($values as $value) {
                $newCond[] = ['finset' => $value];
            }

            $condition = $newCond;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
