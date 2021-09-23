<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Ui\Component\Listing\Column;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $coreDate;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Wyomind\Framework\Helper\Progress
     */
    private $helperProgress;

    /**
     * @var string
     */
    private $module;

    /**
     * Name of the column that stores the scheduled tasks settings
     * @var null
     */
    private $field;

    /**
     * id/primary key 'label' used in the table
     * @var string
     */
    private $id;

    /**
     * Status constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param string $module
     * @param null $field
     * @param string $id
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\UrlInterface $urlBuilder,
        $module = 'Framework',
        $field = null,
        $id = 'id',
        array $components = [],
        array $data = []
    ) {
    
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->coreDate = $coreDate;
        $this->urlBuilder = $urlBuilder;
        $this->helperProgress = $objectManager->create("Wyomind\\" . $module . "\Helper\Progress");
        $this->field = $field;
        $this->module = $module;
        $this->id = $id;
        $this->objectManager = $objectManager;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    protected function prepareItem(array $item)
    {
        if (!isset($item[$this->field])) {
            $item[$this->field] = "";
        }
        $helperProgress = $this->helperProgress;

        try {
            $line = $helperProgress->readFlag($item[$this->id]);
            $stats = $helperProgress->getStats($item[$this->id]);

            if ($line['status'] == $helperProgress::SUCCEEDED) {
                $line['status'] = $this->checkCronTasks($line['status'], $item, $stats['mtime']);
            }

            switch ($line['status']) {
                case $helperProgress::SUCCEEDED:
                    $severity = 'notice';
                    $status = ($line['status']);
                    break;
                case $helperProgress::PENDING:
                    $severity = 'minor';
                    $status = ($line['status']);
                    break;
                case $helperProgress::PROCESSING:
                    $percent = round($line['percent']);
                    $severity = 'minor';
                    $status = ($line['status']) . ' [' . $percent . '%]';
                    break;
                case $helperProgress::HOLD:
                    $severity = 'major';
                    $status = ($line['status']);
                    break;
                case $helperProgress::FAILED:
                    $severity = 'critical';
                    $status = ($line['status']);
                    break;
                default:
                    $severity = 'critical';
                    $status = ($helperProgress::ERROR);
                    break;
            }
        } catch (\Exception $exception) {
            $severity = 'minor';
            $line['message'] = $exception->getMessage();
            $status = ($helperProgress::PENDING);
        }

        $tooltip = null;
        $tooltipClass = null;
        if (isset($line['message']) && $line['message'] != '') {
            $tooltip = "<div class=\"tooltip-content\">" . $line['message'] . "</div>";
            $tooltipClass = 'tooltip';
        }

        if ($status == $helperProgress::PENDING) {
            $tooltipClass = 'tooltip';
            $tooltip = "<div class=\"tooltip-content\">" . __('Next Schedule') . " " . $this->getNextSchedule($item) . " </div > ";
        }

        $script = "<script language='javascript' type='text/javascript'>"
            . "var updater_url='" . $this->urlBuilder->getUrl('wyomind_framework/progress/updater') . "'"
            . "</script>";

        return $script . "<span class='$tooltipClass grid-severity-$severity updater' data-module='" . $this->module . "' "
            . "data-field='" . $this->field . "' data-cron='" . $item[$this->field] . "' data-id='" . $item[$this->id] . "' >"
            . "<span> " . __($status) . "</span> " . $tooltip . "</span> ";
    }

    /**
     * @param $status
     * @param array $item
     * @param $mtime
     * @return mixed
     */
    protected function checkCronTasks($status, $item, $mtime)
    {
        $helperProgress = $this->helperProgress;
        $cron = [];
        $offset = $this->coreDate->getGmtOffset();
        $cron['current']['localTime'] = $this->coreDate->timestamp(time() + $offset);
        $cron['file']['localTime'] = $this->coreDate->timestamp($mtime + $offset);

        if (isset($item[$this->field])) {
            $cronExpr = json_decode($item[$this->field]);
            $i = 0;

            if (isset($cronExpr->days)) {
                foreach ($cronExpr->days as $day) {
                    foreach ($cronExpr->hours as $hour) {
                        $time = explode(':', $hour);

                        if (date('l', $cron['current']['localTime']) == $day) {
                            $cron['tasks'][$i]['localTime'] = strtotime($this->coreDate->date('Y-m-d', time() + $offset)) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        } else {
                            $cron['tasks'][$i]['localTime'] = strtotime('last ' . $day, $cron['current']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        }
                        if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime']) {
                            $status = $helperProgress::PENDING;
                            continue 2;
                        }
                    }
                }
            }

            return $status;
        } else {
            return $helperProgress::SUCCEEDED;
        }
    }

    protected function getNextSchedule($item)
    {
        $offset = $this->coreDate->getGmtOffset();
        $cron['current']['localTime'] = $this->coreDate->timestamp(time() + $offset);
        $cron['current']['gmtTime'] = $this->coreDate->gmtTimestamp();
        if (isset($item[$this->field])) {
            $cronExpr = json_decode($item[$this->field]);

            $schedules = [];

            if (isset($cronExpr->days)) {
                foreach ($cronExpr->days as $day) {
                    foreach ($cronExpr->hours as $hour) {
                        $time = explode(':', $hour);
                        if (date('l', $cron['current']['localTime']) == $day) {
                            $schedule = strtotime($this->coreDate->date('Y-m-d', time() + $offset)) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        } else {
                            $schedule = strtotime('next ' . $day, $cron['current']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        }
                        if ($schedule > $cron['current']['localTime']) {
                            $schedules[] = $schedule;
                        }
                    }
                }
            }
            sort($schedules);

            if (!empty($schedules)) {
                return $this->objectManager->create(
                    'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
                )->formatDate(
                    date('Y-m-d H:i:s', $schedules[0]),
                    \IntlDateFormatter::MEDIUM,
                    true
                );
            } else {
                return __('not set');
            }
        } else {
            return "";
        }
    }
}
