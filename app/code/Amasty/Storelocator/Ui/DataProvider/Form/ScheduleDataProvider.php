<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\DataProvider\Form;

use Amasty\Storelocator\Model\ResourceModel\Schedule\Collection;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Ui\Component\Form\ScheduleMinutesTime;
use Amasty\Storelocator\Ui\Component\Form\ScheduleHoursTime;
use Amasty\Storelocator\Ui\Component\Form\ScheduleStatus;
use Amasty\Base\Model\Serializer;

/**
 * Class ScheduleDataProvider
 */
class ScheduleDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    const OPEN_TIME = 'from';
    const START_BREAK_TIME = 'break_from';
    const END_BREAK_TIME = 'break_to';
    const CLOSE_TIME = 'to';
    const MINUTES = 'minutes';
    const HOURS = 'hours';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ScheduleMinutesTime
     */
    private $scheduleMinutesTime;

    /**
     * @var ScheduleHoursTime
     */
    private $scheduleHoursTime;

    /**
     * @var ScheduleStatus
     */
    private $scheduleStatus;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        Data $helper,
        ScheduleMinutesTime $scheduleMinutesTime,
        ScheduleHoursTime $scheduleHoursTime,
        ScheduleStatus $scheduleStatus,
        Serializer $serializer,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->helper = $helper;
        $this->scheduleMinutesTime = $scheduleMinutesTime;
        $this->scheduleHoursTime = $scheduleHoursTime;
        $this->scheduleStatus = $scheduleStatus;
        $this->serializer = $serializer;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        /**
         * It is need for support of several fieldsets.
         * For details @see \Magento\Ui\Component\Form::getDataSourceData
         */
        if ($data['totalRecords'] > 0) {
            $scheduleId = (int)$data['items'][0]['id'];
            $scheduleModel = $this->collection->getItemById($scheduleId);
            $scheduleData = $scheduleModel->getData();
            if ($scheduleData['schedule']) {
                $scheduleData['schedule'] = $this->serializer->unserialize($scheduleData['schedule']);
            }
            $data[$scheduleId] = $scheduleData;
        }

        return $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();

        foreach ($this->helper->getDaysNames() as $key => $day) {
            $this->createStatusSelect(
                $key,
                $key . '_status',
                $day->getText() . ' Schedule',
                $this->scheduleStatus->toOptionArray()
            );
            foreach ($this->getTimeTypes() as $timeKey => $timeTitle) {
                $this->createContainer($key, $timeKey, $timeTitle->getText());
                $this->createTimeSelect(
                    $key,
                    $timeKey,
                    $timeTitle->getText(),
                    'hours',
                    $this->scheduleHoursTime->toOptionArray()
                );
                $this->createTimeSelect(
                    $key,
                    $timeKey,
                    $timeTitle->getText(),
                    'minutes',
                    $this->scheduleMinutesTime->toOptionArray()
                );
            }

            if ($key === "monday") {
                $this->createCopyScheduleButton($key);
            }
        }

        return $this->meta;
    }

    /**
     * Create container for schedule
     *
     * @param string $key
     * @param string $timeKey
     * @param string $timeTitle
     */
    private function createContainer($key, $timeKey, $timeTitle)
    {
        $configuration = &$this->meta['general']['children'][$key]['children'][$timeKey]['arguments']['data']['config'];

        $configuration['label'] = $timeTitle;
        $configuration['visible'] = true;
        $configuration['componentType'] = 'container';
        $configuration['component'] = 'Magento_Ui/js/form/components/group';
        $configuration['type'] = 'group';
        $configuration['breakLine'] = false;
    }

    /**
     * Create container for schedule
     *
     * @param string $key
     * @param string $timeKey
     * @param string $timeTitle
     * @param string $unitsKey
     * @param array $options
     */
    private function createTimeSelect($key, $timeKey, $timeTitle, $unitsKey, $options)
    {
        $configuration = &$this->meta['general']['children'][$key]['children']
                          [$timeKey]['children'][$unitsKey]['arguments']['data']['config'];

        $configuration['label'] = $timeTitle;
        $configuration['additionalClasses'] = 'admin__field-small';
        $configuration['componentType'] = 'select';
        $configuration['dataScope'] = 'schedule' . '.' . $key . '.' . $timeKey . '.' . $unitsKey;
        $configuration['options'] = $options;
    }

    /**
     * Create container for schedule
     *
     * @param string $key
     * @param string $timeKey
     * @param string $timeTitle
     * @param array $options
     */
    private function createStatusSelect($key, $timeKey, $timeTitle, $options)
    {
        $configuration = &$this->meta['general']['children'][$key]['children'][$timeKey]['arguments']['data']['config'];

        $configuration['label'] = $timeTitle;
        $configuration['additionalClasses'] = 'admin__field-small';
        $configuration['componentType'] = 'select';
        $configuration['dataScope'] = 'schedule' . '.' . $key . '.' . $timeKey;
        $configuration['options'] = $options;
    }

    /**
     * Get time types
     *
     * @return array
     */
    private function getTimeTypes()
    {
        return [
            self::OPEN_TIME => __('Open Time'),
            self::START_BREAK_TIME => __('Start of Break'),
            self::END_BREAK_TIME => __('End of Break'),
            self::CLOSE_TIME => __('Close Time')
        ];
    }

    /**
     * Create 'Copy schedule' button
     *
     * @param string $key
     */
    private function createCopyScheduleButton($key)
    {
        $configuration = &$this->meta['general']['children'][$key]['children']
                          ['fill_schedule_button']['arguments']['data']['config'];

        $configuration['formElement'] = 'input';
        $configuration['visible'] = true;
        $configuration['componentType'] = 'field';
        $configuration['elementTmpl'] = 'Amasty_Storelocator/form/element/fillschedule';
    }
}
