<?php
namespace MageBig\Shopbybrand\Api\Data;

interface BrandInterface
{
	const ENTITY_ID = 'entity_id';
	const IS_ACTIVE = 'is_active';
	const OPTION_ID = 'option_id';

	
    public function getOptionId();
	public function getIsActive();
	
	public function setOptionId($optionId);
	public function setIsActive($isActive);
		
}