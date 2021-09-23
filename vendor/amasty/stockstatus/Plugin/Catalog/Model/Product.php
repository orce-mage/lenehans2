<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Catalog\Model;

use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\App\RequestInterface;

class Product
{
    const PRODUCT_VIEW = 'catalog/product/view';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Processor
     */
    private $processor;

    public function __construct(
        Processor $processor,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->processor = $processor;
    }

    /**
     * Fix overwrite bundle select options by js magento
     * @param ProductModel $subject
     * @param string|null $result
     * @return string
     */
    public function afterGetName(ProductModel $subject, ?string $result): ?string
    {
        $this->processor->execute([$subject]);
        /** Check if product is an bundle selection */
        if ($subject->getSelectionCanChangeQty() !== null
            && strpos($this->request->getPathInfo(), self::PRODUCT_VIEW) !== false
            && $subject->getExtensionAttributes()->getStockstatusInformation()->getStatusId()
        ) {
            $stockStatus = strip_tags(
                $subject->getExtensionAttributes()
                    ->getStockstatusInformation()
                    ->getStatusMessage()
            );
            $result .= ' (' . $stockStatus . ')';
        }

        return $result;
    }
}
