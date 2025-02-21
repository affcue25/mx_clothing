<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class ButtonPos implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'top', 'label' => __('Top')],
        	['value' => 'center', 'label' => __('Center')],
        	['value' => 'bottom', 'label' => __('Bottom')],
        	['value' => 'outside', 'label' => __('Outside')]
        ];
    }

    public function toArray()
    {
        return [
        	'left' => __('Left'),
        	'center' => __('Center'),
        	'bottom' => __('Bottom'),
        	'outside' => __('Outside')
        ];
    }
}
