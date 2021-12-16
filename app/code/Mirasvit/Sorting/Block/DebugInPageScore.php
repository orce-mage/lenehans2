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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Sorting\Block;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class DebugInPageScore extends Template
{
    private $registry;

    /**
     * @var string
     */
    protected $_template = "Mirasvit_Sorting::debug_in_page_score.phtml";

    public function __construct(
        Registry $registry,
        Template\Context $context,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    public function getName(): string
    {
        return $this->getData('name');
    }

    public function getScore(): ?float
    {
        return $this->getData('score');
    }

    public function getWeight(): int
    {
        return $this->getData('weight');
    }

    public function getValue(): string
    {
        return (string)$this->getData('value');
    }

    public function getProduct(): ProductInterface
    {
        return $this->getData('product');
    }

    public function getProductData(string $key): string
    {
        if ($key === 'position') {
            $key = 'cat_index_position';

            /** @var Category $cc */
            $cc = $this->registry->registry('current_category');

            if ($cc && isset($cc->getProductsPosition()[$this->getProduct()->getId()])) {
                return $cc->getProductsPosition()[$this->getProduct()->getId()];
            }
        }

        return (string)$this->getProduct()->getData($key);
    }
}
