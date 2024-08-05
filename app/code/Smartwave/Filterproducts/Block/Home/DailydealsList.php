<?php

namespace Smartwave\Filterproducts\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;


class DailydealsList extends \Magento\Catalog\Block\Product\ListProduct {

    protected $_collection;

    protected $categoryRepository;

    protected $_resource;

    const DEFAULT_SHOW_PAGER = false;

    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    const PAGE_VAR_NAME = 'np';

    protected $_pager;

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

    }

    protected function _getProductCollection() {
        return $this->getProducts();
    }

    public function getCurrentPage()
    {
        return abs((int)$this->getRequest()->getParam($this->getData('page_var_name')));
    }

    public function getProducts() {
        $storeId = $this->_storeManager->getStore()->getId();
        $category_id = $this->getData("category_id");
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);
        if(!$category_id) {
            $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        }
        $category = $this->categoryRepository->get($category_id);

        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('small_image')
            ->addAttributeToSelect('thumbnail')
            ->addUrlRewrite()
            ->addStoreFilter($storeId);

        if(isset($category) && $category) {
          $collection->addCategoryFilter($category);
        }

        $collection->getSelect()
            ->joinLeft(['dai' => $collection->getTable('sw_dailydeals_dailydeal')], 'dai.sw_product_sku = e.sku')
            ->where('dai.sw_deal_enable=1')
            ->where(
                'dai.sw_date_from <= "'.$this->getDayDate().'" or dai.sw_date_from IS NULL'
            )->where(
                'dai.sw_date_to >= "'.$this->getDayDate().'" or dai.sw_date_to IS NULL'
            );
          $collection
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getCurrentPage());
        return $collection;
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
        if (!$this->hasData('show_pager')) {
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
                    'widget.new.product.list.pager'
                );

                $this->_pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit($this->getProductsPerPage())
                    ->setTotalLimit($this->getProductsCount())
                    ->setCollection($this->_getProductCollection());
            }
            if ($this->_pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->_pager->toHtml();
            }
        }
        return '';
    }

    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->_getProductCollection());
        return parent::_beforeToHtml();
    }

    public function getDayDate()
    {
        return $this->_localeDate->date()->format('Y-m-d H:i:s');
    }
}
