<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class BlockPos implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'default', 'label' => __('Default')],
        	['value' => 'center', 'label' => __('Center')],
        	['value' => 'full', 'label' => __('Full Block')]
        ];
    }

    public function toArray()
    {
        return [
        	'default' => __('Default'),
        	'center' => __('Center'),
        	'full' => __('Full Block')
        ];
    }
}
