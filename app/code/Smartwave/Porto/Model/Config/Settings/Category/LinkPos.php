<?php
namespace Smartwave\Porto\Model\Config\Settings\Category;

class LinkPos implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
        	['value' => 'default', 'label' => __('Default')],
        	['value' => 'image', 'label' => __('Link On Image')],
        	['value' => 'top', 'label' => __('Link Always Visible Before Text')],
        	['value' => 'hidden', 'label' => __('Hidden')]
        ];
    }

    public function toArray()
    {
        return [
        	'default' => __('Default'),
        	'image' => __('Link On Image'),
        	'top' => __('Link Always Visible Before Text'),
        	'hidden' => __('Hidden')
        ];
    }
}
