<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Helper\Data as locatorHelper;
use Amasty\Storelocator\Ui\DataProvider\Form\ScheduleDataProvider as Provider;

/**
 * Class Schedule
 */
class Schedule extends Column
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var locatorHelper
     */
    private $helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Serializer $serializer,
        locatorHelper $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $scheduleString = '';
                $scheduleArray = $this->serializer->unserialize($item['schedule']);
                if (is_array($scheduleArray)) {
                    foreach ($this->helper->getDaysNames() as $dayKey => $day) {
                        $scheduleString .= $day->getText() . ':<br />' .
                            $scheduleArray[$dayKey][Provider::OPEN_TIME][Provider::HOURS] . ':' .
                            $scheduleArray[$dayKey][Provider::OPEN_TIME][Provider::MINUTES] . ' - ' .
                            $scheduleArray[$dayKey][Provider::START_BREAK_TIME][Provider::HOURS] . ':' .
                            $scheduleArray[$dayKey][Provider::START_BREAK_TIME][Provider::MINUTES] . '<br />' .

                            $scheduleArray[$dayKey][Provider::END_BREAK_TIME][Provider::HOURS] . ':' .
                            $scheduleArray[$dayKey][Provider::END_BREAK_TIME][Provider::MINUTES] . ' - ' .
                            $scheduleArray[$dayKey][Provider::CLOSE_TIME][Provider::HOURS] . ':' .
                            $scheduleArray[$dayKey][Provider::CLOSE_TIME][Provider::MINUTES] . '<br />';
                    }
                    $item['schedule'] = $scheduleString;
                }
            }
        }

        return $dataSource;
    }
}
