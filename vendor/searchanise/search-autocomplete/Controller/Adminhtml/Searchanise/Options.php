<?php

namespace Searchanise\SearchAutocomplete\Controller\Adminhtml\Searchanise;

use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Searchanise\SearchAutocomplete\Model\Configuration;

class Options extends Action
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Context $context, Configuration $configuration)
    {
        $this->configuration = $configuration;

        parent::__construct($context);
    }

    public function execute()
    {
        // No content
    }
}
