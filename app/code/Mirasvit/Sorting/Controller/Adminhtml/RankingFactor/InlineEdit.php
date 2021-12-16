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

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class InlineEdit extends RankingFactorAbstract
{
    private $jsonFactory;

    public function __construct(
        JsonFactory $jsonFactory,
        RankingFactorRepository $rankingFactorRepository,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        Context $context
    ) {
        $this->jsonFactory = $jsonFactory;

        parent::__construct($rankingFactorRepository, $registry, $resultForwardFactory, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $messages   = [];

        $postItems = $this->getRequest()->getParam('items', []);

        foreach ($postItems as $factorId => $data) {
            $model = $this->rankingFactorRepository->get($factorId);

            if (!$model) {
                $messages[] = __('This factor no longer exists.');
            }

            if (isset($data[RankingFactorInterface::NAME])) {
                $model->setName((string)$data[RankingFactorInterface::NAME]);
            }

            if (isset($data[RankingFactorInterface::IS_ACTIVE])) {
                $model->setIsActive((bool)$data[RankingFactorInterface::IS_ACTIVE]);
            }

            if (isset($data[RankingFactorInterface::IS_GLOBAL])) {
                $model->setIsGlobal((bool)$data[RankingFactorInterface::IS_GLOBAL]);
            }

            try {
                $this->rankingFactorRepository->save($model);
            } catch (\Exception $e) {
                $messages[] = $e->getMessage();
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => count($messages) ? true : false,
        ]);
    }
}
