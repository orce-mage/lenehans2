<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Controller\Adminhtml\CodePool;

use Amasty\GiftCard\Model\Code\CodeGeneratorManagement;
use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Controller\Adminhtml\AbstractCodePool;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class GenerateCodes extends AbstractCodePool
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CodeGeneratorManagement
     */
    private $codeGeneratorManagement;

    public function __construct(
        Action\Context $context,
        CodeGeneratorManagement $codeGeneratorManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->codeGeneratorManagement = $codeGeneratorManagement;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($codePoolId = (int)$this->getRequest()->getParam(CodePoolInterface::CODE_POOL_ID)) {
            try {
                $data = $this->getRequest()->getParams();

                if (($data['qty'] ?? '') && ($data['template'] ?? '')) {
                    $this->codeGeneratorManagement->generateCodesByTemplate(
                        $codePoolId,
                        $data['template'],
                        (int)$data['qty']
                    );
                }

                if ($file = $this->getRequest()->getFiles('csv')) {
                    $this->codeGeneratorManagement->generateCodesByFile($codePoolId, $file);
                }
                $response = ['message' => __('Codes generation successfully completed.'), 'error' => false];
            } catch (LocalizedException $e) {
                $response = ['message' => $e->getMessage(), 'error' => true];
            } catch (\Exception $e) {
                $response = [
                    'message' => __('Something went wrong.. Please review log for more information.'),
                    'error'   => true
                ];
                $this->logger->critical($e);
            }
        } else {
            $response = ['message' => __('Please, save Code Pool before generating codes.'), 'error' => true];
        }

        return $result->setData($response);
    }
}
