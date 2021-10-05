<?php

declare(strict_types=1);

namespace MidoriWeb\Custom\Model;

use MidoriWeb\Custom\Api\DeliveryMessageManagementInterface;
use MidoriWeb\Custom\Api\Data\DeliveryMessageDataInterfaceFactory;
use MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterfaceFactory;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class DeliveryMessageManagement implements DeliveryMessageManagementInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DeliveryMessageDataInterfaceFactory
     */
    private $deliveryMessageDataFactory;

    /**
     * @var DeliveryMessageContentInterfaceFactory
     */
    private $deliveryMessageContentFactory;

    protected $localeDate;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        DeliveryMessageDataInterfaceFactory $deliveryMessageDataFactory,
        DeliveryMessageContentInterfaceFactory $deliveryMessageContentFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $localeDate
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->deliveryMessageDataFactory = $deliveryMessageDataFactory;
        $this->deliveryMessageContentFactory = $deliveryMessageContentFactory;
        $this->localeDate = $localeDate;
    }

    /**
     * @param int $productId
     * @return \MidoriWeb\Custom\Api\Data\DeliveryMessageDataInterface
     */
    public function getDeliveryMessageByProduct(
        int $productId
    ): \MidoriWeb\Custom\Api\Data\DeliveryMessageDataInterface {

        $deliveryMessageData = $this->deliveryMessageDataFactory->create();
        $deliveryMessageContent = $this->deliveryMessageContentFactory->create();

        $info = $this->getDeliveryInfo($productId);

        $deliveryMessageContent->setFromDay($info['from_day']);
        $deliveryMessageContent->setToDay($info['to_day']);
        $deliveryMessageContent->setToDate($info['to_date']);


        $deliveryMessageData->setDeliveryMessage($deliveryMessageContent);

        return $deliveryMessageData;
    }

    private function getDeliveryInfo($productId) {
        $result = [
            'from_day' => '',
            'to_day' => '',
            'to_date' => '',
        ];

        $product = $this->productRepository->getById($productId);

        if(strpos($product->getSku(), 'TP_') === false ) {
            return $result;
        }
        if(!$product->isInStock() || !$product->isAvailable()) {
            return $result;
        }

        $now = $this->localeDate->date('Y-m-d H:i:s');
        $nowDay = $this->localeDate->date('w');
        $nowHi = $this->localeDate->date('Hi');

        $nowDate = new \DateTime($now);

        $nextDate = new \DateTime($now);
        $nextDate->modify('+1 day');

        $next2Date = new \DateTime($now);
        $next2Date->modify('+2 day');

        ///////////////////////////////////////////////////////////////
        switch($nowDay) {
            case 1:case 2:case 3:
                if($nowHi < "1500") {
                    $result['from_day'] = "Today";
                    $result['to_day'] = "Tomorrow";
                    $result['to_date'] = "({$nextDate->format('l d/m/Y')})";
                }else {
                    $result['from_day'] = "Tomorrow";
                    $result['to_day'] = "";
                    $result['to_date'] = "{$next2Date->format('l d/m/Y')}";
                }
                break;
            case 4:
                if($nowHi < "1500") {
                    $result['from_day'] = "Today";
                    $result['to_day'] = "Tomorrow";
                    $result['to_date'] = "({$nextDate->format('l d/m/Y')})";
                }else {
                    $result['from_day'] = "Tomorrow";
                    $result['to_day'] = "";
                    $result['to_date'] = "";
                }
                break;
            case 5:
                if($nowHi < "1500") {
                    $result['from_day'] = "Today";
                    $result['to_day'] = "";
                    $result['to_date'] = "";
                }else {
                    $result['from_day'] = "Next Working Day";
                    $result['to_day'] = "";
                    $result['to_date'] = "";
                }
                break;
            case 6:case 0:
                $result['from_day'] = "Next Working Day";
                $result['to_day'] = "";
                $result['to_date'] = "";
                break;
            default:
                break;
        }


        return $result;
    }



}
