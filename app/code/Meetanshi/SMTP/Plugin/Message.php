<?php
namespace Meetanshi\SMTP\Plugin;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Meetanshi\SMTP\Mail\Rse\Mail;

/**
 * Class Message
 * @package Meetanshi\SMTP\Plugin
 */
class Message
{
    /**
     * @var Mail
     */
    protected $resourceMail;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * Message constructor.
     * @param Mail $resourceMail
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        Mail $resourceMail,
        SenderResolverInterface $senderResolver
    ) {
        $this->resourceMail = $resourceMail;
        $this->senderResolver = $senderResolver;
    }

    /**
     * @param TransportBuilderByStore $subject
     * @param $from
     * @param $store
     * @return array
     * @throws MailException
     */
    public function beforeSetFromByStore(TransportBuilderByStore $subject, $from, $store)
    {
        $result = $this->senderResolver->resolve($from, $store);
        $this->resourceMail->setFromByStore($result['email'], $result['name']);

        return [$from, $store];
    }
}