<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class ContentPos2 implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'default', 'label' => __('Default')],
        	['value' => 'center', 'label' => __('Center')],
        	['value' => 'top', 'label' => __('Top')],
        	['value' => 'bottom', 'label' => __('Bottom')]
        ];
    }

    public function toArray()
    {
        return [
        	'default' => __('Default'),
        	'center' => __('Center'),
        	'top' => __('Top'),
        	'bottom' => __('Bottom')
        ];
    }
}
