<?php

namespace Meetanshi\SMTP\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Protocol
 * @package Meetanshi\SMTP\Model\Config\Source
 */
class Protocol implements ArrayInterface
{
    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => __('None')
            ],
            [
                'value' => 'ssl',
                'label' => __('SSL')
            ],
            [
                'value' => 'tls',
                'label' => __('TLS')
            ],
        ];

        return $options;
    }
}
