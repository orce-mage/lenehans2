<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Cart;

use Psr\Log\LoggerInterface;

class AddStockstatusToCartHtml
{
    const PRODUCT_NAME_ELEMENT_REGEX = '@\<(span|strong)[^\>]+?class=\"product-item-name\"\>.*?\<\/(span|strong)\>@s';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(string $stockStatusHTML, string $cartItemHtml)
    {
        if (!preg_match(self::PRODUCT_NAME_ELEMENT_REGEX, $cartItemHtml, $matches)) {
            $this->logger->error(__('Product name element to add stockstatus was not found')->render());
        } else {
            $productNameElement = $matches[0];
            $cartItemHtml = str_replace(
                $productNameElement,
                $productNameElement . $stockStatusHTML,
                $cartItemHtml
            );
        }

        return $cartItemHtml;
    }
}
