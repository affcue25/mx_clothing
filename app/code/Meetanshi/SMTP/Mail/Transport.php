<?php

namespace Meetanshi\SMTP\Mail;

use Closure;
use Exception;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Meetanshi\SMTP\Helper\Data;
use Meetanshi\SMTP\Mail\Rse\Mail;
use Meetanshi\SMTP\Model\LogsFactory as Elog;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Zend\Mail\Message;
use Zend_Exception;

/**
 * Class Transport
 * @package Meetanshi\SMTP\Mail
 */
class Transport
{
    /**
     * @var int Store Id
     */
    protected $_storeId;

    /**
     * @var Mail
     */
    protected $resourceMail;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var Registry $registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var
     */
    private $blockflag;
    /**
     * @var
     */
    private $returnVal;

    /**
     * Transport constructor.
     * @param Mail $resourceMail
     * @param Registry $registry
     * @param Data $helper
     * @param Elog $logFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Mail $resourceMail,
        Registry $registry,
        Data $helper,
        Elog $logFactory,
        LoggerInterface $logger
    )
    {
        $this->resourceMail = $resourceMail;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->logFactory = $logFactory;
        $this->logger = $logger;
    }

    /**
     * @param TransportInterface $subject
     * @param Closure $proceed
     * @throws MailException
     * @throws Zend_Exception
     * @throws \Zend_Mail_Transport_Exception
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        Closure $proceed
    )
    {
        $this->blockflag = false;
        $this->returnVal = 0;
        $string = trim(preg_replace('/\s\s+/', '', $this->helper->getConfigGeneral('blocklist_emails')));
        $blocklistEmails = explode(',', $string);
        $this->_storeId = $this->registry->registry('mp_smtp_store_id');
        $transport = $this->resourceMail->getTransport($this->_storeId);
        $message = $this->getMessage($subject);
        $message = $this->resourceMail->processMessage($message, $this->_storeId);

        if ($this->helper->versionCompare('2.2.8')) {
            $message = Message::fromString($message->getRawMessage())->setEncoding('utf-8');
        }
        if ($this->resourceMail->isModuleEnable($this->_storeId) && $message) {
            foreach ($message->getTo() as $address) {
                foreach ($blocklistEmails as $bmail => $bval) {
                    if ($bval == $address->getEmail()) {
                        $this->blockflag = true;
                    }
                }
            }
            if(!$this->blockflag && !$this->resourceMail->isDeveloperMode($this->_storeId)) {
                try {
                    if (!$this->resourceMail->isDeveloperMode($this->_storeId)) {
                        if ($this->helper->versionCompare('2.3.3')) {
                            $message->getHeaders()->removeHeader("Content-Disposition");
                        }
                        $transport->send($message);
                    }
                    if ($this->helper->versionCompare('2.2.8')) {
                        $messageTmp = $this->getMessage($subject);
                        if ($messageTmp && is_object($messageTmp)) {
                            $body = $messageTmp->getBody();
                            if (is_object($body) && $body->isMultiPart()) {
                                $message->setBody($body->getPartContent("0"));
                            }
                        }
                    }
                    $this->emailLog($message);
                } catch (Exception $e) {
                    $this->emailLog($message, false);
                    throw new MailException(new Phrase($e->getMessage()), $e);
                }
                $this->returnVal = 1;
            }
            else {
                $this->returnVal = 0;
            }
        }
        else {
            //$transport->send($message);
            return $proceed();
        }
    }

    /**
     * @param $transport
     * @return mixed|null
     */
    protected function getMessage($transport)
    {
        if ($this->helper->versionCompare('2.2.0')) {
            return $transport->getMessage();
        }

        try {
            $reflectionClass = new ReflectionClass($transport);
            $message = $reflectionClass->getProperty('_message');
        } catch (Exception $e) {
            return null;
        }

        $message->setAccessible(true);

        return $message->getValue($transport);
    }

    /**
     * @param $message
     * @param bool $status
     */
    protected function emailLog($message, $status = true)
    {
        if ($this->resourceMail->isEnableEmailLog($this->_storeId)) {

            $log = $this->logFactory->create();
            try {
                $log->saveLog($message, $status);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

}
