<?php

namespace Smartwave\Filterproducts\Block\Widget;

use Magento\Catalog\Api\CategoryRepositoryInterface;


class Products extends \Magento\Catalog\Block\Product\ListProduct implements \Magento\Widget\Block\BlockInterface {

    protected $_collection;

    protected $categoryRepository;

    protected $_resource;

    protected $_pager;

    private $serializer;

    const DISPLAY_TYPE_PRODUCTS = 'latest_products';

    const DEFAULT_SHOW_PAGER = false;

    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    const PAGE_VAR_NAME = 'np';

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->_collection = $collection;
        $this->_resource = $resource;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);

        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Set template
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->setTemplate(
            $this->getData('layout_type') ? 'Smartwave_Filterproducts::widget/'.$this->getData('layout_type').'.phtml' : 'Smartwave_Filterproducts::widget/grid.phtml'
        );

        $html = parent::_toHtml();
        return $html;
    }

    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->createCollection());
        return parent::_beforeToHtml();
    }

    public function createCollection()
    {
      $category_id = '';
      if($this->getData('category_ids')!=''){
        if(explode('/', $this->getData('category_ids'))[0] == 'category'){
          $category_id = explode('/', $this->getData('category_ids'))[1];
        }else{
          $category_id = $this->getData('category_ids');
        }
      }
      $collection = clone $this->_collection;
      $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);

      if(!$category_id) {
          $category_id = $this->_storeManager->getStore()->getRootCategoryId();
      }
      $category = $this->categoryRepository->get($category_id);

      if ($this->getData('store_id') !== null) {
          $collection->setStoreId($this->getData('store_id'));
      }

      $collection->addMinimalPrice()
          ->addFinalPrice()
          ->addTaxPercents()
          ->addAttributeToSelect('name')
          ->addAttributeToSelect('image')
          ->addAttributeToSelect('small_image')
          ->addAttributeToSelect('thumbnail')
          ->addAttributeToSelect('special_from_date')
          ->addAttributeToSelect('special_to_date')
          ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
          ->addUrlRewrite()
          ->addStoreFilter();

      if(isset($category) && $category)
      {
        $collection->addCategoryFilter($category);
      }
      switch ($this->getDisplayType())
      {
          case 'new_products':
              $collection->addAttributeToFilter(
                    'news_from_date',
                    ['date' => true, 'to' => $this->getEndOfDayDate()],
                    'left')
                ->addAttributeToFilter(
                    'news_to_date',
                    [
                        'or' => [
                            0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left')
                ->addAttributeToSort(
                    'news_from_date',
                    'desc');
              break;
          case 'featured_products':
              $collection->addAttributeToFilter('sw_featured', 1, 'left');
              break;
          case 'bestseller_products':
              $collection->getSelect()
                  ->joinLeft(['soi' => $collection->getTable('sales_order_item')], 'soi.product_id = e.entity_id', ['SUM(soi.qty_ordered) AS ordered_qty'])
                  ->join(['order' => $collection->getTable('sales_order')], "order.entity_id = soi.order_id",['order.state'])
                  ->where("order.state <> 'canceled' and soi.parent_item_id IS NULL AND soi.product_id IS NOT NULL")
                  ->group('soi.product_id')
                  ->order('ordered_qty DESC');
              /*$collection->getSelect()
                ->join(['bestsellers' => $collection->getTable('sales_bestsellers_aggregated_yearly')],
                    'e.entity_id = bestsellers.product_id AND bestsellers.store_id = '.$this->getData('store_id'),
                    ['qty_ordered','rating_pos'])
                ->order('rating_pos');*/
              break;
          case 'sale_products':
              $collection->addAttributeToFilter('special_price', ['neq' => ''])
              ->addAttributeToFilter(
                    'special_from_date',
                    ['date' => true, 'to' => $this->getEndOfDayDate()],
                    'left')
                ->addAttributeToFilter(
                    'special_to_date',
                    [
                        'or' => [
                            0 => ['date' => true, 'from' => $this->getStartOfDayDate()],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left')
                ->addAttributeToSort(
                    'news_from_date',
                    'desc');
              break;
          case 'deal_products':
              $collection->getSelect()
                  ->joinLeft(['dai' => $collection->getTable('sw_dailydeals_dailydeal')], 'dai.sw_product_sku = e.sku')
                  ->where('dai.sw_deal_enable=1')
                  ->where(
                      'dai.sw_date_from <= "'.$this->getDayDate().'" or dai.sw_date_from IS NULL'
                  )->where(
                      'dai.sw_date_to >= "'.$this->getDayDate().'" or dai.sw_date_to IS NULL'
                  );
              break;
          default:
              $collection->addAttributeToSort('created_at','desc');
              break;
      }

      $collection->setPageSize($this->getPageSize())
                 ->setCurPage($this->getCurrentPage());

      if($this->getDisplayType() == 'featured_products') {
        $collection->getSelect()->order('rand()');
      }

      return $collection;
    }

    public function getCurrentPage()
    {
        return abs((int)$this->getRequest()->getParam($this->getData('page_var_name')));
    }

    public function getCacheKeyInfo()
    {
        return array_merge(
            parent::getCacheKeyInfo(),
            [
                $this->getDisplayType(),
                $this->_storeManager->getStore()->getId(),
                $this->getProductsPerPage(),
                (int) $this->getRequest()->getParam($this->getData('page_var_name'), 1),
                $this->serializer->serialize($this->getRequest()->getParams())
            ]
        );
    }

    public function getDisplayType()
    {
        if (!$this->hasData('display_type')) {
            $this->setData('display_type', self::DISPLAY_TYPE_PRODUCTS);
        }
        return $this->getData('display_type');
    }

    public function getProductsCount()
    {
        if (!$this->hasData('product_count')) {
            return parent::getProductsCount();
        }
        return $this->getData('product_count');
    }

    public function getProductsPerPage()
    {
        if (!$this->hasData('products_per_page')) {
            $this->setData('products_per_page', self::DEFAULT_PRODUCTS_PER_PAGE);
        }
        return $this->getData('products_per_page');
    }

    public function showPager()
    {
        if (!$this->hasData('show_pager'))
        {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    protected function getPageSize()
    {
        return $this->showPager() ? $this->getProductsPerPage() : $this->getProductsCount();
    }

    public function getPagerHtml()
    {
        if ($this->showPager()) {
            if (!$this->_pager) {
                $this->_pager = $this->getLayout()->createBlock(
                    \Magento\Catalog\Block\Product\Widget\Html\Pager::class,
                    $this->getWidgetPagerBlockName()
                );

                $this->_pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit($this->getProductsPerPage())
                    ->setTotalLimit($this->getProductsCount())
                    ->setCollection($this->getProductCollection());
            }
            if ($this->_pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->_pager->toHtml();
            }
        }
        return '';
    }

    private function getWidgetPagerBlockName()
    {
        $pageName = $this->getData('page_var_name');
        $pagerBlockName = 'widget.porto.filter.products.pager';

        if (!$pageName) {
            return $pagerBlockName;
        }

        return $pagerBlockName . '.' . $pageName;
    }

    public function getDayDate()
    {
        return $this->_localeDate->date()->format('Y-m-d H:i:s');
    }

    public function getStartOfDayDate()
    {
        return $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    }

    public function getEndOfDayDate()
    {
        return $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
    }
}
