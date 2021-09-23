<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation\EntityValidator;

use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavResource;

class EavAttribute implements FieldValidatorInterface
{
    /**
     * @var array
     */
    private $validationResByAttrId = [];

    /**
     * @var EavResource
     */
    private $eavResource;

    public function __construct(EavResource $eavResource)
    {
        $this->eavResource = $eavResource;
    }

    public function validate(array $row, string $field): bool
    {
        if (isset($row[$field])) {
            $attributeId = trim($row[$field]);

            if (!empty($attributeId)) {
                if (!isset($this->validationResByAttrId[$attributeId])) {
                    $validAttrIds = $this->eavResource->getValidAttributeIds(
                        [$attributeId]
                    );
                    $this->validationResByAttrId[$attributeId] = !empty($validAttrIds);
                }

                return $this->validationResByAttrId[$attributeId];
            }
        }

        return true;
    }
}
