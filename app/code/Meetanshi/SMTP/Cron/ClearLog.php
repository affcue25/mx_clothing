<?php

namespace Meetanshi\SMTP\Cron;

use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Meetanshi\SMTP\Helper\Data;
use Meetanshi\SMTP\Model\ResourceModel\Logs\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ClearLog
 * @package Meetanshi\SMTP\Cron
 */
class ClearLog
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $collectionLog;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * ClearLog constructor.
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param CollectionFactory $collectionLog
     * @param Data $helper
     */
    public function __construct(
        LoggerInterface $logger,
        DateTime $date,
        CollectionFactory $collectionLog,
        Data $helper
    )
    {
        $this->logger = $logger;
        $this->date = $date;
        $this->collectionLog = $collectionLog;
        $this->helper = $helper;
    }

    /**
     * Clean Email Log after X day(s)
     *
     * @return $this
     */
    public function execute()
    {

        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $day = (int)$this->helper->getConfigGeneral('clean_email');
        if (isset($day) && $day > 0) {
            $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;

            $logs = $this->collectionLog->create()
                ->addFieldToFilter('created_at', ['lteq' => date('Y-m-d H:i:s', $timeEnd)]);


            foreach ($logs as $log) {
                try {
                    $log->delete();
                } catch (Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $this;
    }
}
