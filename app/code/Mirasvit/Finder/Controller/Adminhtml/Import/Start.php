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

namespace Mirasvit\Finder\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Mirasvit\Finder\Model\Import\FileProcessor;
use Mirasvit\Finder\Repository\FinderRepository;
use Mirasvit\Finder\Service\ImportOptionService;
use Mirasvit\Finder\Ui\Import\Form\Field\Mode;

class Start extends Action implements ActionInterface
{
    private $fileProcessor;

    private $finderRepository;

    private $importOptionService;

    private $jsonFactory;

    public function __construct(
        FileProcessor $fileProcessor,
        FinderRepository $finderRepository,
        ImportOptionService $importOptionService,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        $this->fileProcessor       = $fileProcessor;
        $this->finderRepository    = $finderRepository;
        $this->importOptionService = $importOptionService;
        $this->jsonFactory         = $jsonFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $error   = true;
        $message = __('Something went wrong.');

        if ($data = $this->getRequest()->getParams()) {
            $error   = false;
            $message = __('Data were successfully imported.');

            $finderId = (int)$data['finder_id'];
            $finder   = $this->finderRepository->get($finderId);

            $isOverwrite = $data['import_mode'] == Mode::MODE_OVERWRITE;

            $files = $data['import_file'];
            foreach ($files as $file) {
                $path = $this->fileProcessor->getAbsoluteTmpFilePath($file);

                try {
                    $this->importOptionService->importFile($finder, $path, $isOverwrite);
                } catch (\Exception $e) {
                    $resultJson = $this->jsonFactory->create();
                    $resultJson->setData(
                        [
                            'messages' => [$e->getMessage()],
                            'error'    => true,
                        ]
                    );

                    return $resultJson;
                }
            }
        }

        $resultJson = $this->jsonFactory->create();

        $resultJson->setData(
            [
                'message' => $message,
                'error'   => $error,
                'data'    => [
                    'finder_id' => empty($finderId) ? null : $finderId,
                ],
            ]
        );

        return $resultJson;
    }
}
