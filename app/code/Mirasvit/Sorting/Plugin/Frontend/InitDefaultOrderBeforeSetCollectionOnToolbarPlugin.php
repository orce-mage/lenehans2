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

namespace Mirasvit\Sorting\Plugin\Frontend;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Framework\App\RequestInterface;
use Mirasvit\Sorting\Service\CriteriaManagementService;

/**
 * @see \Magento\Catalog\Block\Product\ProductList\Toolbar::setCollection()
 */
class InitDefaultOrderBeforeSetCollectionOnToolbarPlugin
{
    private $criteriaManagement;

    private $request;

    public function __construct(
        CriteriaManagementService $criteriaManagement,
        RequestInterface $request
    ) {
        $this->criteriaManagement = $criteriaManagement;
        $this->request            = $request;
    }

    /**
     * Initialize default sort order and direction.
     *
     * @param Toolbar                            $subject
     * @param \Magento\Framework\Data\Collection $collection
     */
    public function beforeSetCollection(Toolbar $subject, $collection)
    {
        $criterion = $this->criteriaManagement->getCurrentCriterion(
            $this->request->getParam('product_list_order')
        );

        if ($criterion) {
            $subject->setDefaultOrder($criterion->getCode());
            $subject->setDefaultDirection($this->criteriaManagement->getDefaultDirection($criterion));
        }
    }

    /**
     * @param Toolbar $subject
     * @param object  $result
     *
     * @return string
     */
    public function afterGetCurrentDirection(Toolbar $subject, $result)
    {
        $criterion = $this->criteriaManagement->getCurrentCriterion(
            $this->request->getParam('product_list_order')
        );

        if (!$criterion) {
            return $result;
        }

        return $this->request->getParam('product_list_dir')
            ? $this->request->getParam('product_list_dir')
            : $this->criteriaManagement->getDefaultDirection($criterion);
    }
}
