<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Config\Backend;

use Amasty\Number\Model\Number\Validator;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Number extends Value
{
    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Validator $validator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->validator = $validator;
    }

    /**
     * @return Value|void
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $this->validator->validatePattern($this->getEntityType(), $this->getValue());
    }

    /**
     * @return string
     */
    private function getEntityType(): string
    {
        $result = explode('/', $this->getPath());

        return $result[1] ?? '';
    }
}
