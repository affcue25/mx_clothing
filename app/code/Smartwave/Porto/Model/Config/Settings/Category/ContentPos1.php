<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class ContentPos1 implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'left', 'label' => __('Left')],
        	['value' => 'center', 'label' => __('Center')]
        ];
    }

    public function toArray()
    {
        return [
        	'left' => __('Left'),
        	'center' => __('Center')
        ];
    }
}
