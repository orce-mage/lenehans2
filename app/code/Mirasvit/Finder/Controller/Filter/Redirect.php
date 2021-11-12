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
use Magento\Framework\Controller\Result\RedirectFactory;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Repository\FinderRepository;
use Mirasvit\Finder\Service\FinderService;

class Redirect implements ActionInterface
{
    private $finderRepository;

    private $finderService;

    private $context;

    private $redirectFactory;

    public function __construct(
        FinderRepository $finderRepository,
        FinderService $finderService,
        RedirectFactory $redirectFactory,
        Context $context
    ) {
        $this->finderRepository = $finderRepository;
        $this->finderService    = $finderService;
        $this->redirectFactory  = $redirectFactory;
        $this->context          = $context;
    }

    public function execute()
    {
        $finderId = (int)$this->context->getRequest()->getParam(FinderInterface::ID);

        $finder = $this->finderRepository->get($finderId);
        if (!$finder) {
            $httpReferrer = $this->context->getRequest()->getServer('HTTP_REFERER');

            return $this->redirectFactory->create()->setUrl($httpReferrer);
        }

        $resultUrl = $this->finderService->getResultUrl($finder);

        return $this->redirectFactory->create()->setUrl($resultUrl);
    }
}
