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

namespace Mirasvit\Finder\Controller\Filter;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Repository\FilterRepository;
use Mirasvit\Finder\Repository\FinderRepository;
use Mirasvit\Finder\Service\FilterCriteriaService;
use Mirasvit\Finder\Service\FilterService;

class FindOptions implements ActionInterface
{
    private $finderRepository;

    private $filterRepository;

    private $filterService;

    private $searchCriteriaService;

    private $jsonFactory;

    private $sessionManager;

    private $context;

    public function __construct(
        FinderRepository $finderRepository,
        FilterRepository $filterRepository,
        FilterService $filterService,
        FilterCriteriaService $searchCriteriaService,
        JsonFactory $jsonFactory,
        SessionManagerInterface $sessionManager,
        Context $context
    ) {
        $this->finderRepository      = $finderRepository;
        $this->filterRepository      = $filterRepository;
        $this->filterService         = $filterService;
        $this->searchCriteriaService = $searchCriteriaService;
        $this->jsonFactory           = $jsonFactory;
        $this->sessionManager        = $sessionManager;
        $this->context               = $context;
    }

    public function execute()
    {
        $finderId  = (int)$this->context->getRequest()->getParam('finderId');
        $finderUrl = (string)$this->context->getRequest()->getParam('finder');

        $searchCriteria = $this->searchCriteriaService->getFilterCriteria($finderUrl);
        $filterIds      = (array)$this->context->getRequest()->getParam('filterIds');

        $this->sessionManager->setFinderData([
            $finderId => [
                'filters' => $filterIds,
                'finder'  => $finderUrl,
            ]
        ]);

        $data = [];
        foreach ($filterIds as $filterId) {
            $filterId = (int)$filterId;

            $filter = $this->filterRepository->get($filterId);

            $options = $this->filterService->getOptions($filter, $searchCriteria);

            $data[$filterId] = [];
            foreach ($options as $option) {
                $data[$filterId][] = [
                    FilterOptionInterface::ID      => $option->getId(),
                    FilterOptionInterface::NAME    => $option->getName(),
                    FilterOptionInterface::URL_KEY => $option->getUrlKey(),
                ];
            }
        }

        return $this->jsonFactory->create()->setData([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
