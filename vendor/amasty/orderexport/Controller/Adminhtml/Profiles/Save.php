<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends \Amasty\Orderexport\Controller\Adminhtml\Profiles
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $id = $this->getRequest()->getParam('entity_id');

                if (!empty($data['static_fields'])) {
                    $data['static_fields'] = \Zend_Json::encode($data['static_fields']);
                }

                /** For Magento Version >= 2.2.6 */
                if (!empty($data['serialized_options'])) {
                    $serializedOptions = json_decode($data['serialized_options'], JSON_OBJECT_AS_ARRAY);
                    $defaults = [];
                    foreach ($serializedOptions as $serializedOption) {
                        $option = [];
                        parse_str($serializedOption, $option);
                        if (isset($option['default'][0])) {
                            $defaults[] = $option['default'][0];
                        } else {
                            $data = array_replace_recursive($data, $option);
                        }
                    }
                }

                if ($id) {
                    try {
                        $profile = $this->profilesRepository->getById($id);
                    } catch (NoSuchEntityException $e) {
                        throw $e;
                    }
                } else {
                    $profile = $this->profilesRepository->create();
                }

                if (isset($data['store_ids']) && is_array($data['store_ids'])) {
                    $data['store_ids'] = implode(',', $data['store_ids']);
                }

                if (isset($data['filter_customergroup_ids'])) {
                    $data['filter_customergroup_ids'] = serialize($data['filter_customergroup_ids']);
                } else {
                    $data['filter_customergroup'] = false;
                }

                if (isset($data['filter_status'])) {
                    $data['filter_status'] = serialize($data['filter_status']);
                } else {
                    $data['filter_status'] = serialize([]);
                }

                if (isset($data['cron_schedule'])) {
                    $pattern = '/^((?:[1-9]?\d|\*)\s*(?:(?:[\/-][1-9]?\d)|(?:,[1-9]?\d)+)?\s*){5}$/';

                    if (!preg_match($pattern, $data['cron_schedule'])) {
                        throw new LocalizedException(__('Invalid crontab.'));
                    }
                }

                if (isset($data['mapping_delete'])) {
                    foreach ($data['mapping_delete'] as $del_id => $del_val) {
                        if ($del_val) {
                            unset($data['mapping_options'][$del_id]);
                        }
                    }
                }

                $mappings = [];
                $optCnt   = 10000;

                if (isset($data['mapping_options'])) {
                    foreach ($data['mapping_options'] as $map_id => $map_val) {
                        $map_stored            = 'option_' . $optCnt--; // to make it revert counting from 1000,999,998 up to 1
                        $mappings[$map_stored] = [
                            'id' => $map_stored,
                            'option' => $map_val,
                            'value' => isset($data['mapping_values'][$map_id])
                            && !empty($data['mapping_values'][$map_id])
                                ? $data['mapping_values'][$map_id]
                                : mb_substr($map_val, stripos($map_val, '.') + 1),
                            'order' => isset($data['mapping_order'][$map_id]) ? $data['mapping_order'][$map_id] : 0
                        ];
                    }
                }

                $data['field_mapping'] = serialize($mappings);
                $profile->setData($data);
                $this->backendSession->setPageData($profile->getData());
                $this->profilesRepository->save($profile);
                $this->messageManager->addSuccessMessage(__('The profile is saved.'));
                $this->backendSession->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_orderexport/*/edit', ['id' => $profile->getId()]);

                    return;
                }
                $this->_redirect('amasty_orderexport/*/');

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('entity_id');

                if (!empty($id)) {
                    $this->_redirect('amasty_orderexport/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_orderexport/*/new');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_orderexport/*/');

                return;
            }
        }

        $this->_redirect('amasty_orderexport/*/');
    }
}
