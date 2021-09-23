<?php

namespace Searchanise\SearchAutocomplete\Model\Indexer;

use \Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use \Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Symfony\Component\Console\Output\ConsoleOutput;
use \Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;

class Queue implements IndexerActionInterface, MviewActionInterface
{
    const INDEXER_ID = 'searchanise_queue';

    /**
     * @var ApiSeHelper
     */
    private $apiSe;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ConsoleOutput
     */
    private $output;

    public function __construct(
        ApiSeHelper $apiSe,
        ManagerInterface $messageManager,
        ConsoleOutput $output
    ) {
        $this->apiSe = $apiSe;
        $this->messageManager = $messageManager;
        $this->output = $output;
    }

    public function execute($ids)
    {
        return $this;
    }
    
    public function executeFull()
    {
        if (!$this->apiSe->getIsIndexEnabled()) {
            // Indexing was not enabled, skipped
            return;
        }

        if (!$this->apiSe->checkParentPrivateKey()) {
            if (php_sapi_name() === 'cli') {
                $this->output->writeln("Searchanise was not registered yet.");
            }

            $this->messageManager->addErrorMessage($errorMessage);

            return;
        }

        try {
            $result = $this->apiSe->async();

            if (php_sapi_name() === 'cli') {
                $this->output->writeln("Searchanise queue index status: " . $result);
            }
        } catch (\Exception $e) {
            if (php_sapi_name() === 'cli') {
                $this->output->writeln(__('Searchanise queue index error: [%1] %2', $e->getCode(), $e->getMessage()));
            }
        }
    }

    public function executeList(array $ids)
    {
        return $this;
    }

    public function executeRow($id)
    {
        return $this;
    }
}
