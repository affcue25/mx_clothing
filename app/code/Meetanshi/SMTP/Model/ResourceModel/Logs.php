<?php
namespace Meetanshi\SMTP\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Log
 * @package Meetanshi\SMTP\Model\ResourceModel
 */
class Logs extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * Log constructor.
     * @param DateTime $date
     * @param Context $context
     */
    public function __construct(
        DateTime $date,
        Context $context
    ) {
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        $this->_init('mt_email_logs', 'id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        return parent::_beforeSave($object);
    }
}
