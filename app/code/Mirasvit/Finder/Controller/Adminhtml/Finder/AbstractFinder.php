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


declare(strict_types=1);

namespace Mirasvit\Finder\Controller\Adminhtml\Finder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\AbstractResult;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Repository\FinderRepository;

abstract class AbstractFinder extends Action
{
    protected $finderRepository;

    private   $context;

    public function __construct(
        FinderRepository $finderRepository,
        Context $context
    ) {
        $this->finderRepository = $finderRepository;
        $this->context          = $context;

        parent::__construct($context);
    }

    protected function initModel(): FinderInterface
    {
        $model = null;

        if ($this->getRequest()->getParam(FinderInterface::ID)) {
            $model = $this->finderRepository->get((int)$this->getRequest()->getParam(FinderInterface::ID));
        }

        return $model ? $model : $this->finderRepository->create();
    }

    protected function initPage(AbstractResult $page, string $title): void
    {
        $page->setActiveMenu('Magento_Catalog::catalog');
        $page->getConfig()->getTitle()->prepend(__('Product Finder'));
        $page->getConfig()->getTitle()->prepend(__($title));
    }

    protected function _isAllowed(): bool
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_Finder::finder');
    }
}
