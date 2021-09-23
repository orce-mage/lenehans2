<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Ui\DataProvider\Import;

use Amasty\ImportCore\Api\Config\ProfileConfigInterfaceFactory;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\FormProvider;
use Amasty\ImportCore\Model\Process\ResourceModel\CollectionFactory;
use Magento\Framework\App\RequestInterface as HttpRequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var HttpRequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var FormProvider
     */
    private $formProvider;

    /**
     * @var ProfileConfigInterfaceFactory
     */
    private $profileConfigFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        EntityConfigProvider $entityConfigProvider,
        HttpRequestInterface $request,
        UrlInterface $url,
        FormProvider $formProvider,
        ProfileConfigInterfaceFactory $profileConfigFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->entityConfigProvider = $entityConfigProvider;
        $this->request = $request;
        $this->url = $url;
        $this->formProvider = $formProvider;
        $this->profileConfigFactory = $profileConfigFactory;
    }

    public function getData()
    {
        $data = [];

        if ($entityCode = $this->request->getParam('entity_code')) {
            $profileConfig = $this->profileConfigFactory->create();
            $profileConfig->setEntityCode($entityCode);
            $data[null] = array_merge(
                ['entity_code' => $entityCode],
                $this->formProvider->get(CompositeFormType::TYPE)->getData($profileConfig)
            );
        }

        return $data;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();
        $selectedEntityCode = $this->request->getParam('entity_code');

        if ($selectedEntityCode) {
            $selectedEntity = $this->entityConfigProvider->get($selectedEntityCode);
            if ($selectedEntity->getDescription()) {
                $meta['general']['children']['entity_code']['arguments']
                    ['data']['config']['notice'] = $selectedEntity->getDescription();
            }
            $meta = array_merge_recursive(
                $meta,
                $this->formProvider->get(CompositeFormType::TYPE)->getMeta($selectedEntity)
            );
            $meta['controls'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'container',
                            'visible' => false,
                            'index' => 'controls',
                            'component' => 'Amasty_ImportCore/js/controls',
                            'template' => 'Amasty_ImportCore/controls',
                            'statusUrl' => $this->url->getUrl('amimport/import/status'),
                            'cancelUrl' => $this->url->getUrl('amimport/import/cancel'),
                            'importUrl' => $this->url->getUrl('amimport/import/import')
                        ]
                    ]
                ]
            ];
        }

        return $meta;
    }
}
