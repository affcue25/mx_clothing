<?php
namespace Smartwave\Dailydeals\Model\ResourceModel\Dailydeal;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'dailydeal_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sw_dailydeals_dailydeal_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'dailydeal_collection';

    /**
     * Define resource model
     *
     * @return void
     */

    protected function _construct()
    {
        $this->_init(\Smartwave\Dailydeals\Model\Dailydeal::class, \Smartwave\Dailydeals\Model\ResourceModel\Dailydeal::class);
        $this->_map['fields']['dailydeal_id'] = 'main_table.dailydeal_id';
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
     public function getSelectCountSql()
     {
         $countSelect = parent::getSelectCountSql();
         $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

         return $countSelect;
     }
     /**
      * Returns pairs dailydeal_id - title
      *
      * @return array
      */
     public function toOptionArray()
     {
         return $this->_toOptionArray('dailydeal_id', 'sw_product_sku');
     }

}
