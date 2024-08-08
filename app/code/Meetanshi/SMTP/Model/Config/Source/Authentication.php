<?php

namespace Meetanshi\SMTP\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Authentication
 * @package Meetanshi\SMTP\Model\Config\Source
 */
class Authentication implements ArrayInterface
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
                'label' => __('NONE')
            ],
            [
                'value' => 'plain',
                'label' => __('PLAIN')
            ],
            [
                'value' => 'login',
                'label' => __('LOGIN')
            ],
            [
                'value' => 'crammd5',
                'label' => __('CRAM-MD5')
            ],
        ];

        return $options;
    }
}
