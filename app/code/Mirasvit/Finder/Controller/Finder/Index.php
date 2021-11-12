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

namespace Mirasvit\Finder\Controller\Finder;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Finder\Model\ConfigProvider;
use Mirasvit\Finder\Service\FinderService;

class Index extends Action implements ActionInterface
{
    private $storeManager;

    private $categoryRepository;

    private $registry;

    private $context;

    private $configProvider;

    private $redirectFactory;

    private $finderService;

    private $design;

    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepository $categoryRepository,
        Registry $registry,
        ConfigProvider $configProvider,
        RedirectFactory $redirectFactory,
        FinderService $finderService,
        DesignInterface $design,
        Context $context
    ) {
        $this->storeManager       = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->registry           = $registry;
        $this->configProvider     = $configProvider;
        $this->redirectFactory    = $redirectFactory;
        $this->finderService      = $finderService;
        $this->design             = $design;
        $this->context            = $context;

        parent::__construct($context);
    }

    public function execute()
    {
        // need this for m2.3.5
        $this->design->getDesignTheme()->setArea('frontend');

        if ($this->configProvider->isFriendlyUrl()) {
            $url = $this->finderService->getFriendlyUrl();
            if ($url) {
                return $this->redirectFactory->create()->setUrl($url);
            }
        }

        /** @var Page $page */
        $page = $this->context->getResultFactory()->create(ResultFactory::TYPE_PAGE);

        $this->initRootCategory($page);

        return $page;
    }

    private function initRootCategory(Page $page): void
    {
        $rootId = $this->storeManager->getStore()->getRootCategoryId();

        $category = $this->categoryRepository->get($rootId, $this->storeManager->getStore()->getId());

        $this->registry->register('current_category', $category);

        $pageType = $this->getPageType($category);

        if (!$category->hasChildren()) {
            // Two levels removed from parent.  Need to add default page type.
            $parentPageType = strtok($pageType, '_');
            $page->addPageLayoutHandles(['type' => $parentPageType], null, false);
        }
        $page->addPageLayoutHandles(['type' => $pageType], null, false);
        $page->addPageLayoutHandles(['displaymode' => strtolower($category->getDisplayMode())], null, false);
        $page->addPageLayoutHandles(['id' => $category->getId()]);

        $page->getConfig()->addBodyClass('page-products');

        $page->getConfig()->getTitle()->set($category->getName());
    }

    private function getPageType(Category $category): string
    {
        $hasChildren = $category->hasChildren();
        if ($category->getIsAnchor()) {
            return $hasChildren ? 'layered' : 'layered_without_children';
        }

        return $hasChildren ? 'default' : 'default_without_children';
    }
}
