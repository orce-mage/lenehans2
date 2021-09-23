<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\DataProvider\Rule\Form;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Controller\Adminhtml\Rule\Save;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var array|null
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PoolInterface
     */
    private $pool;

    public function __construct(
        DataPersistorInterface $dataPersistor,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->dataPersistor = $dataPersistor;
        $this->pool = $pool;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getData()
    {
        if ($this->loadedData === null) {
            $ruleData = $this->dataPersistor->get(Save::RULE_PERSISTENT_NAME);
            if ($ruleData) {
                $this->dataPersistor->clear(Save::RULE_PERSISTENT_NAME);
            }
            if (isset($ruleData[RuleInterface::ID])) {
                $this->loadedData[$ruleData[RuleInterface::ID]]['rule'] = $ruleData;
                $this->dataPersistor->clear(Save::RULE_PERSISTENT_NAME);
            } else {
                foreach ($this->getSearchResult()->getItems() as $rule) {
                    $this->loadedData[$rule->getId()]['rule'] = $rule->getData();
                }
            }

            $this->loadedData = $this->loadedData ?: [];
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $this->loadedData = $modifier->modifyData($this->loadedData);
            }
        }

        return $this->loadedData;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
