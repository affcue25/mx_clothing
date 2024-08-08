<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class QuantityPos implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'below', 'label' => __('Below Info')],
        	['value' => 'above', 'label' => __('Above Info')],
        	['value' => 'image_bottom', 'label' => __('Image Bottom')],
        	['value' => 'image_center', 'label' => __('Image Center')]
        ];
    }

    public function toArray()
    {
        return [
        	'below' => __('Below Info'),
        	'above' => __('Above Info'),
        	'image_bottom' => __('Image Bottom'),
        	'image_center' => __('Image Center')
        ];
    }
}
