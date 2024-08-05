<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class ButtonAction implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'top', 'label' => __('Top')],
        	['value' => 'center', 'label' => __('Center')],
        	['value' => 'hide', 'label' => __('Hide')]
        ];
    }

    public function toArray()
    {
        return [
        	'left' => __('Left'),
        	'center' => __('Center'),
        	'hide' => __('Hide')
        ];
    }
}
