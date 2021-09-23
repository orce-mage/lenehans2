<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Ui\DataProvider\Export;

use Amasty\ExportCore\Api\Config\ProfileConfigInterfaceFactory;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\FormProvider;
use Amasty\ExportCore\Model\Process\ResourceModel\CollectionFactory;
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
        ProfileConfigInterfaceFactory $profileConfigFactory,
        FormProvider $formProvider,
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
            if (!empty($selectedEntity->getDescription())) {
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
                            'visible' => true,
                            'index' => 'controls',
                            'component' => 'Amasty_ExportCore/js/controls',
                            'template' => 'Amasty_ExportCore/controls',
                            'statusUrl' => $this->url->getUrl('amexport/export/status'),
                            'cancelUrl' => $this->url->getUrl('amexport/export/cancel'),
                            'downloadUrl' => $this->url->getUrl(
                                'amexport/export/download',
                                ['processIdentity' => '_process_identity_']
                            )
                        ]
                    ]
                ]
            ];
        }

        return $meta;
    }
}
