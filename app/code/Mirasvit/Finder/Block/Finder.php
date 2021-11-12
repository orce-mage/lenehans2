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

namespace Mirasvit\Finder\Block;

use Magento\Framework\View\Element\Template;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Block\Filter\FilterBlockFactory;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\FinderRepository;

class Finder extends Template
{
    private $finderRepository;

    private $filterRepository;

    private $filterBlockFactory;

    private $finder;

    /**
     * @var Filter\AbstractFilter[]
     */
    private $filterBlocks = [];

    public function __construct(
        FinderRepository $finderRepository,
        FilterRepository $filterRepository,
        FilterBlockFactory $filterBlockFactory,
        Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);

        $this->finderRepository   = $finderRepository;
        $this->filterRepository   = $filterRepository;
        $this->filterBlockFactory = $filterBlockFactory;

        $this->finder = $this->finderRepository->get((int)$this->getData(FinderInterface::ID));

        if ($this->finder) {
            $this->initFilters();
        }
    }

    public function getCssAsset(): string
    {
        return $this->_assetRepo->createAsset('Mirasvit_Finder::css/chosen/chosen.css')->getUrl();
    }

    public function getFinder(): ?FinderInterface
    {
        return $this->finder;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        $collection = $this->filterRepository->getCollection();
        $collection->addFieldToFilter(FilterInterface::FINDER_ID, $this->getFinder()->getId())
            ->setOrder(FilterInterface::POSITION, 'asc');

        return $collection->getItems();
    }

    public function getFilterHtml(FilterInterface $filter): string
    {
        return $this->filterBlocks[$filter->getId()]->toHtml();
    }

    public function toHtml(): ?string
    {
        if (!$this->getFinder()) {
            return null;
        }

        $template = $this->getData(FinderInterface::BLOCK_TEMPLATE)
            ? $this->getData(FinderInterface::BLOCK_TEMPLATE)
            : $this->getFinder()->getBlockTemplate();
        $this->setTemplate($template);

        return parent::toHtml();
    }

    public function getJsHtml(): string
    {
        /** @var Js $js */
        $js = $this->getLayout()->createBlock(Js::class);
        $js->setFinder($this->getFinder());

        return $js->toHtml();
    }

    private function initFilters(): void
    {
        $filters = $this->getFilters();

        $disableNextFilter = false;
        foreach ($filters as $filter) {
            $block = $this->filterBlockFactory->create($filter);
            $block->setFinder($this->getFinder());

            $this->filterBlocks[$filter->getId()] = $block;

            $block->setIsDisabled($disableNextFilter);

            if ($filter->isRequired() && count($block->getValue()) === 0) {
                $disableNextFilter = true;
            }
        }
    }
}
