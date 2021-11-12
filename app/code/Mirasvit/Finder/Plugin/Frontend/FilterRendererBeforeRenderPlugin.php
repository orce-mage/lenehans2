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
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Finder\Plugin\Frontend;

use Magento\LayeredNavigation\Block\Navigation\FilterRenderer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Mirasvit\Core\Service\CompatibilityService;

/**
 * @see \Magento\LayeredNavigation\Block\Navigation\FilterRenderer::render()
 */
class FilterRendererBeforeRenderPlugin
{
    public function beforeRender(FilterRenderer $subject, FilterInterface $filter): ?FilterInterface
    {
        if (CompatibilityService::is24()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $subject->setProductLayerViewModel($objectManager->get('Magento\LayeredNavigation\ViewModel\Layer\Filter'));
        }

        return null;
    }
}
