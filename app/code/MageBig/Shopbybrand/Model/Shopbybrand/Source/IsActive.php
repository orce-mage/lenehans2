<?php
namespace MageBig\Shopbybrand\Model\Shopbybrand\Source;
class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
	protected $brand;
	public function __construct(\MageBig\Shopbybrand\Model\Brand $brand)
    {
        $this->brand = $brand;
    }
	public function toOptionArray()
	{
		$options[] = ['label' => '', 'value' => ''];
		$availableOptions = $this->brand->getAvailableStatuses();
		foreach ($availableOptions as $key => $value) {
			$options[] = [
				'label' => $value,
				'value' => $key,
			];
		}
		return $options;
	}
}