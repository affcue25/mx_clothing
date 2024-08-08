<?php
namespace Meetanshi\SMTP\Model\Config\Source;

/**
 * Class Hosts
 * @package Meetanshi\SMTP\Model\Config\Source
 */
class Hosts
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'Gmail',
                'label' => __('Gmail')
            ],
            [
                'value' => 'Zoho',
                'label' => __('Zoho')
            ],
            [
                'value' => 'amazon_ses',
                'label' => __('Amazon Ses')
            ],
            [
                'value' => 'mandrill',
                'label' => __('Mandrill')
            ]
        ];

        return $options;
    }
}