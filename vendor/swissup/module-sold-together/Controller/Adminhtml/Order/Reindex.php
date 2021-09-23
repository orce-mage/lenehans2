<?php
namespace Swissup\SoldTogether\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;

class Reindex extends \Magento\Backend\App\Action
{
    /**
     * Prefix for data in backend session
     *
     * @var string
     */
    protected $_dataPrefix = "swissup_soldtogether_order";
    /**
     * Size of step in data processing
     *
     * @var integer
     */
    protected $_stepSize = 10;
    /**
     * Message on processing complete
     *
     * @var string
     */
    protected $_completeMessage = "All Orders have been indexed.";
    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context);
    }

    /**
     * Reindex orders action
     *
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        if (!$this->_session->hasData($this->_dataPrefix."_init")) {
            $this->getIndexerModel()->deleteAutogeneratedRelations();
            $this->_session->setData($this->_dataPrefix."_init", 1);
            $this->_session->setData($this->_dataPrefix."_step", 1);
            $this->_session->setData(
                $this->_dataPrefix."_count",
                $this->getIndexerModel()->getItemsToProcessCount()
            );
            if ($this->_session->getData($this->_dataPrefix."_count") < 1) {
                $this->messageManager->addNotice(__("We couldn't find data to process"));
                return $this->getResponse()->setBody(
                    $this->jsonEncoder->encode([
                        'finished'  => true
                    ])
                );
            }
        }

        $step = $this->_session->getData($this->_dataPrefix."_step");
        $count = $this->_session->getData($this->_dataPrefix."_count");
        // Index data
        $this->getIndexerModel()->reindex($step, $this->_stepSize);

        if (($step * $this->_stepSize) > $count) {
            $this->_session->unsetData($this->_dataPrefix."_init");
            $this->_session->unsetData($this->_dataPrefix."_count");
            $this->_session->unsetData($this->_dataPrefix."_step");
            $this->messageManager->addSuccess(__($this->_completeMessage));

            return $this->getResponse()->setBody(
                $this->jsonEncoder->encode([
                    'finished' => true
                ])
            );
        } else {
            $this->_session->setData(
                $this->_dataPrefix."_step",
                $this->_session->getData($this->_dataPrefix."_step") + 1
            );

            $percent = 100 * $step * $this->_stepSize / $count;
            $responseLoaderText = ($step * $this->_stepSize) . ' of ' . $count . ' - '
                . (int)$percent . '%';
            $this->getResponse()->setBody(
                $this->jsonEncoder->encode([
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ])
            );
        }
    }

    protected function getIndexerModel()
    {
        return $this->_objectManager
            ->get('Swissup\SoldTogether\Model\OrderIndexer');
    }
}
