<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import;

use Amasty\Storelocator\Model\Import\Validator\RowValidatorInterface;
use \Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 */
class Validator extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     */
    protected $validators = [];

    /**
     * @param RowValidatorInterface[] $validators
     */
    public function __construct($validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * Check value is valid
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $returnValue = true;
        $this->_clearMessages();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * @param \Amasty\Storelocator\Model\Import\Location $context
     * @return $this
     */
    public function init($context)
    {
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }
        return $this;
    }
}
