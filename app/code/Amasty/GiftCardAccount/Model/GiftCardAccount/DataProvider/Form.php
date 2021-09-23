<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\DataProvider;

use Amasty\GiftCard\Api\CodeRepositoryInterface;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var CodeRepositoryInterface
     */
    private $codeRepository;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    public function __construct(
        CollectionFactory $accountCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        Repository $accountRepository,
        CodeRepositoryInterface $codeRepository,
        UrlInterface $url,
        DataPersistorInterface $dataPersistor,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $accountCollectionFactory->create();
        $this->orderRepository = $orderRepository;
        $this->accountRepository = $accountRepository;
        $this->codeRepository = $codeRepository;
        $this->url = $url;
        $this->dataPersistor = $dataPersistor;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        foreach ($this->collection->getData() as $accountData) {
            $account = $this->accountRepository->getById(
                (int)$accountData[GiftCardAccountInterface::ACCOUNT_ID]
            );
            $this->loadedData[$accountData[GiftCardAccountInterface::ACCOUNT_ID]] = $this->prepareData(
                $account->getData()
            );
        }
        $data = $this->dataPersistor->get(\Amasty\GiftCardAccount\Model\GiftCardAccount\Account::DATA_PERSISTOR_KEY);

        if (!empty($data)) {
            $account = $this->accountRepository->getEmptyAccountModel();
            $account->setData($data);

            if ($accountId = $account->getId()) {
                $accountData = $this->prepareData($account->getData());
            } else {
                $accountData = $account->getData();
            }
            $this->loadedData[$account->getId()] = isset($this->loadedData[$account->getId()])
                ? array_merge($this->loadedData[$account->getId()], $accountData)
                : $accountData;
            $this->dataPersistor->clear(\Amasty\GiftCardAccount\Model\GiftCardAccount\Account::DATA_PERSISTOR_KEY);
        }

        return $this->loadedData;
    }

    /**
     * Prepare data for display in tabs in edit form
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareData(array $data): array
    {
        $result = [];

        if ($code = $data[GiftCardAccountInterface::CODE_MODEL] ?? null) {
            $result['code'] = $code->getCode();
        } elseif ($codeId = $data[GiftCardAccountInterface::CODE_ID] ?? null) {
            try {
                $code = $this->codeRepository->getById((int)$codeId);
                $result['code'] = $code->getCode();
            } catch (LocalizedException $e) {
                null; //do nothing
            }
        }

        if ($orderItemId = $data[GiftCardAccountInterface::ORDER_ITEM_ID] ?? null) {
            try {
                $orderItem = $this->orderItemRepository->get((int)$orderItemId);
                $order = $this->orderRepository->get($orderItem->getOrderId());
                $result['order'] = [
                    'increment_id' => $order->getIncrementId(),
                    'link' => $this->url->getUrl(
                        'sales/order/view',
                        ['order_id' => $order->getEntityId()]
                    )
                ];
                $result['recipient_name'] =
                    $orderItem->getProductOptionByCode(GiftCardOptionInterface::RECIPIENT_NAME);
                $result['recipient_email'] =
                    $orderItem->getProductOptionByCode(GiftCardOptionInterface::RECIPIENT_EMAIL);
            } catch (LocalizedException $e) {
                null; //do nothing
            }
        }
        $result = array_merge($result, [
            GiftCardAccountInterface::STATUS => $data[GiftCardAccountInterface::STATUS] ?? '',
            GiftCardAccountInterface::WEBSITE_ID => $data[GiftCardAccountInterface::WEBSITE_ID] ?? '',
            GiftCardAccountInterface::INITIAL_VALUE => $data[GiftCardAccountInterface::INITIAL_VALUE] ?? '',
            GiftCardAccountInterface::CURRENT_VALUE => $data[GiftCardAccountInterface::CURRENT_VALUE] ?? '',
            GiftCardAccountInterface::EXPIRED_DATE => $data[GiftCardAccountInterface::EXPIRED_DATE] ?? '',
            GiftCardAccountInterface::COMMENT => $data[GiftCardAccountInterface::COMMENT] ?? ''
        ]);
        $result[GiftCardAccountInterface::ACCOUNT_ID] = $data[GiftCardAccountInterface::ACCOUNT_ID];

        return $result;
    }
}
