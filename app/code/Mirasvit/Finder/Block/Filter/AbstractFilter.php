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

namespace Mirasvit\Finder\Block\Filter;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\Template;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Service\FilterCriteriaService;
use Mirasvit\Finder\Service\FilterService;

abstract class AbstractFilter extends Template
{
    private $filter;

    private $finder;

    private $isDisabled = false;

    private $filterService;

    private $searchCriteriaService;

    private $sessionManager;

    public function __construct(
        FilterService $filterService,
        FilterCriteriaService $searchCriteriaService,
        SessionManagerInterface $sessionManager,
        Template\Context $context,
        array $data = []
    ) {
        $this->filterService         = $filterService;
        $this->searchCriteriaService = $searchCriteriaService;
        $this->sessionManager        = $sessionManager;

        parent::__construct($context, $data);
    }

    public function getFinder(): FinderInterface
    {
        return $this->finder;
    }

    public function setFinder(FinderInterface $finder): AbstractFilter
    {
        $this->finder = $finder;

        return $this;
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function setFilter(FilterInterface $filter): AbstractFilter
    {
        $this->filter = $filter;

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function setIsDisabled(bool $value): AbstractFilter
    {
        $this->isDisabled = $value;

        return $this;
    }

    /**
     * @return FilterOptionInterface[]
     */
    public function getOptions(): array
    {
        return $this->filterService->getOptions(
            $this->getFilter(),
            $this->searchCriteriaService->getFilterCriteria($this->getFinderUrl())
        );
    }

    public function getValue(): array
    {
        $criteria = $this->searchCriteriaService->getFilterCriteria($this->getFinderUrl());

        $options = [];
        foreach ($criteria->getFilters() as $filter) {
            if ($filter->getFilterId() === $this->getFilter()->getId()) {
                $options = array_merge($options, $filter->getOptionIds());
            }
        }

        return $options;
    }

    private function getFinderUrl(): string
    {
        $finderUrl = (string)$this->_request->getParam('finder');

        if (!$finderUrl) {
            $storedData = $this->sessionManager->getFinderData();
            if (isset($storedData[$this->getFinder()->getId()])) {
                $finderUrl = $storedData[$this->getFinder()->getId()]['finder'];
            }
        }

        return $finderUrl;
    }
}
