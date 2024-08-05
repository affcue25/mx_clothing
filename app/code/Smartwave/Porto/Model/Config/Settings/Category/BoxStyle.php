<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class BoxStyle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'box_shadow', 'label' => __('Box Shadow')],
        	['value' => 'box_full', 'label' => __('Box Shadow Full Details')],
        	['value' => 'box_border', 'label' => __('Box Border')],
        	['value' => 'box_border_padding', 'label' => __('Box Border With Padding')]
        ];
    }

    public function toArray()
    {
        return [
        	'box_shadow' => __('Box Shadow'),
        	'box_full' => __('Box Shadow Full Details'),
        	'box_border' => __('Box Border'),
        	'box_border_padding' => __('Box Border With Padding')
        ];
    }
}
