<?php
namespace Meetanshi\SMTP\Model\ResourceModel\Logs;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Meetanshi\SMTP\Model\ResourceModel\Logs as ResLogs;
use \Meetanshi\SMTP\Model\Logs as MLogs;

/**
 * Class Collection
 * @package Meetanshi\SMTP\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
     * @type string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init( MLogs::class, ResLogs::class);
    }

    /**
     * Truncate table emails log
     *
     * @return void
     */
    public function clearLog()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
