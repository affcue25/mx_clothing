<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Helper;

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;
use Algolia\AlgoliaSearch\SearchClient;
use Exception;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as CatalogCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as CatalogCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\ResourceModel\Product\Collection as ReportsCollection;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as ReportsCollectionFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellerProduct;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Search\Model\Config\Source\Search;

/**
 * Class Data
 * @package Mageplaza\Search\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpsearch';
    const MAX_QUERY_RESULT   = 'max_query_results';

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var Config
     */
    protected $catalogConfig;

    /**
     * @var PricingHelper
     */
    protected $_priceHelper;

    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CollectionFactory
     */
    protected $_customerGroupFactory;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var CategoryFactory
     */

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CatalogCollectionFactory
     */
    protected $_productsFactory;

    /**
     * @var BestSellerProduct
     */
    protected $bestsellersCollection;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var ReportsCollectionFactory
     */
    protected $reportCollectionFactory;

    /**
     * @var Currency
     */
    protected $_currency;

    /**
     * @var Configurable
     */
    protected $_catalogProductTypeConfigurable;

    /**
     * @var SearchClient
     */
    protected $client;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param CollectionFactory $customerGroupCollectionFactory
     * @param Escaper $escaper
     * @param PricingHelper $priceHelper
     * @param Visibility $catalogProductVisibility
     * @param Config $catalogConfig
     * @param Session $customerSession
     * @param FormatInterface $localeFormat
     * @param CategoryFactory $categoryFactory
     * @param CatalogCollectionFactory $productsFactory
     * @param ReportsCollectionFactory $reportCollectionFactory
     * @param BestSellerProduct $bestsellersCollection
     * @param TimezoneInterface $localDate
     * @param Currency $currency
     * @param Configurable $catalogProductTypeConfigurable
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        CollectionFactory $customerGroupCollectionFactory,
        Escaper $escaper,
        PricingHelper $priceHelper,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        Session $customerSession,
        FormatInterface $localeFormat,
        CategoryFactory $categoryFactory,
        CatalogCollectionFactory $productsFactory,
        ReportsCollectionFactory $reportCollectionFactory,
        BestSellerProduct $bestsellersCollection,
        TimezoneInterface $localDate,
        Currency $currency,
        Configurable $catalogProductTypeConfigurable,
        WriterInterface $configWriter
    ) {
        $this->_customerGroupFactory           = $customerGroupCollectionFactory;
        $this->_escaper                        = $escaper;
        $this->_priceHelper                    = $priceHelper;
        $this->productVisibility               = $catalogProductVisibility;
        $this->catalogConfig                   = $catalogConfig;
        $this->_customerSession                = $customerSession;
        $this->localeFormat                    = $localeFormat;
        $this->categoryFactory                 = $categoryFactory;
        $this->_productsFactory                = $productsFactory;
        $this->bestsellersCollection           = $bestsellersCollection;
        $this->_localeDate                     = $localDate;
        $this->reportCollectionFactory         = $reportCollectionFactory;
        $this->_currency                       = $currency;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->configWriter                    = $configWriter;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getSearchBy($store = null)
    {
        $searchBy = $this->getConfigGeneral('search_by', $store);

        return self::jsonEncode(explode(',', $searchBy));
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrentCurrencyRate()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();

        return $this->storeManager->getStore()->getBaseCurrency()->getRate($currencyCode);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getDisplay($store = null)
    {
        $searchBy = $this->getConfigGeneral('display', $store);

        return self::jsonEncode(explode(',', $searchBy ?: ''));
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnableSuggestion($storeId = null)
    {
        return $this->getConfigGeneral('search_by/enable', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSortBy($storeId = null)
    {
        return $this->getConfigGeneral('search_by/sort_by', $storeId);
    }

    /**
     * Create json file to contain product data
     * @return array
     */
    public function createJsonFile()
    {
        $errors         = [];
        $customerGroups = $this->_customerGroupFactory->create();
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($customerGroups as $group) {
                try {
                    $this->createJsonFileForStore($store, $group->getId());
                } catch (Exception $e) {
                    $errors[] = __(
                        'Cannot generate data for store %1 and customer group %2, %3',
                        $store->getCode(),
                        $group->getCode(),
                        $e->getMessage()
                    );
                }
            }
        }
        if ($errors) {
            $this->_logger->error('Cannot create file Js for Mp_Search module', $errors);
        }

        return $errors;
    }

    /**
     * @param $store
     * @param $group
     *
     * @return $this
     */
    public function createJsonFileForStore($store, $group)
    {
        if (!$this->isEnabled($store->getId())) {
            return $this;
        }

        $newProducts = $this->createJsonFileProduct(
            $store,
            $this->getNewProducts($store, $group),
            Search::NEW_PRODUCTS
        );

        $mostViewedProducts = $this->createJsonFileProduct(
            $store,
            $this->getMostViewedProducts($store, $group),
            Search::MOST_VIEWED_PRODUCTS
        );

        $bestsellers = $this->createJsonFileProduct($store, $this->getBestSellers($store, $group), Search::BESTSELLERS);

        if (!$this->isAlgoliaSearch($store->getId())) {
            $products = $this->createJsonFileProduct(
                $store,
                $this->getProducts($store, $group),
                Search::PRODUCT_SEARCH
            );

            $this->getMediaHelper()->createJsFile(
                $this->getJsFilePath($group, $store),
                ';var mp_products_search = ' . $products . ';'
            );
        }
        $this->getMediaHelper()->createJsFile(
            $this->getAdditionJsFilePath($group, $store),
            ';var mp_new_product_search = ' . $newProducts . ';
            var mp_most_viewed_products = ' . $mostViewedProducts . ';
            var mp_bestsellers = ' . $bestsellers . ';'
        );

        return $this;
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigGeneral('enabled', $storeId) && $this->isModuleOutputEnabled();
    }

    /**
     * @param $store
     * @param $collection
     * @param $option
     *
     * @return $this|string
     */
    public function createJsonFileProduct($store, $collection, $option)
    {
        if (!$this->isEnabled($store->getId())) {
            return $this;
        }

        $productList     = [];
        $maxQueryResults = $this->getConfigGeneral(self::MAX_QUERY_RESULT);
        $resultCount     = 0;

        /** @var Product $product */
        foreach ($collection as $product) {
            /** The maximum results of [Most Viewed | New Products| Bestsellers] is $maxQueryResults*/
            if ($option === Search::PRODUCT_SEARCH
                || ($option !== Search::PRODUCT_SEARCH && $resultCount < $maxQueryResults)
            ) {
                if (!$product->getId() || !$product->getName()) {
                    continue;
                }

                $productPrice = $product->getFinalPrice();
                if ($product->getTypeId() === 'bundle') {
                    $productPrice   = $product->getMinPrice() . '-' . $product->getMaxPrice();
                    $currencySymbol = $this->_currency->getCurrencySymbol();
                    $productPrice   = str_replace($currencySymbol, '', $productPrice);
                } elseif ($product->getTypeId() === 'grouped') {
                    $productPrice = $product->getMinimalPrice();
                }

                $productList[] = [
                    'value' => $product->getName(),
                    //sku
                    's'     => $product->getSku(),
                    //categoryIds
                    'c'     => $this->getProductCategories($product),
                    //short description
                    'd'     => $this->getProductDescription($product, $store),
                    //price
                    'p'     => $productPrice,
                    //image
                    'i'     => $this->getMediaHelper()->getProductImage($product),
                    //product url
                    'u'     => $this->getProductUrl($product),
                    'o'     => $option
                ];
            }

            $resultCount++;
        }

        return self::jsonEncode($productList);
    }

    /**
     * Get Product Categories
     *
     * @param Product $product
     *
     * @return array
     */
    public function getProductCategories($product)
    {
        $parentIds  = [];
        $categories = $this->categoryFactory->create();
        foreach ($product->getCategoryIds() as $id) {
            $parentIds[]        = $id;
            $categoryCollection = $categories->load($id);
            foreach ($categoryCollection->getParentIds() as $parentId) {
                $parentIds[] = $parentId;
            }
        }

        return $parentIds;
    }

    /**
     * @param $product
     * @param $store
     *
     * @return array|bool|string
     */
    protected function getProductDescription($product, $store)
    {
        $attributeHtml = strip_tags((string) $product->getShortDescription());
        $attributeHtml = trim($this->_escaper->escapeHtml($attributeHtml));

        $limitDesLetter = (int) $this->getConfigGeneral('max_letter_numbers', $store->getId());
        if ($limitDesLetter > 0 && strlen($attributeHtml) > $limitDesLetter) {
            $attributeHtml = mb_substr($attributeHtml, 0, $limitDesLetter, mb_detect_encoding($attributeHtml));
            $attributeHtml .= '...';
        }

        return $attributeHtml;
    }

    /**
     * @return Media
     */
    public function getMediaHelper()
    {
        return $this->objectManager->get(Media::class);
    }

    /**
     * @param Product $product
     *
     * @return bool|string
     */
    protected function getProductUrl($product)
    {
        $requestPath = $product->getRequestPath();
        if (!$requestPath) {
            $productUrl = $product->getProductUrl();
            $pos        = strpos($productUrl, 'catalog/product/view');
            if ($pos !== false) {
                $requestPath = substr($productUrl, $pos + 20);
            }
        }

        return $requestPath;
    }

    /**
     * @param $store
     * @param $group
     *
     * @return CatalogCollection
     */
    public function getProducts($store, $group)
    {
        /** @var CatalogCollection $collection */
        $collection = $this->_productsFactory->create();
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());

        return $collection;
    }

    /**
     * @param $store
     * @param $group
     *
     * @return DataObject[]
     */
    public function getNewProducts($store, $group)
    {
        $collection          = $this->_productsFactory->create();
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate   = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $ids                 = [];

        $collectionBoth = clone $collection;
        $collectionBoth->addAttributeToFilter('news_from_date', ['date' => true, 'to' => $todayStartOfDayDate])
            ->addAttributeToFilter('news_to_date', ['date' => true, 'from' => $todayEndOfDayDate]);

        if ($collectionBoth->getSize() !== 0) {
            foreach ($collectionBoth->getData() as $v) {
                $ids[] = $v['entity_id'];
            }
        }
        $collectionBoth->clear();

        $collectionFrom = clone $collection;
        $collectionFrom->addAttributeToFilter('news_from_date', ['date' => true, 'to' => $todayStartOfDayDate]);

        if ($collectionFrom->getSize() !== 0) {
            foreach ($collectionFrom->getData() as $v) {
                $ids[] = $v['entity_id'];
            }
        }
        $collectionFrom->clear();

        $collectionTo = clone $collection;
        $collectionTo->addAttributeToFilter('news_to_date', ['date' => true, 'from' => $todayEndOfDayDate]);

        if ($collectionTo->getSize() !== 0) {
            foreach ($collectionTo->getData() as $v) {
                $ids[] = $v['entity_id'];
            }
        }
        $collectionTo->clear();

        $ids = array_unique($ids);
        $collection->addIdFilter($ids)
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());

        return $collection->getItems();
    }

    /**
     * @param $store
     * @param $group
     *
     * @return DataObject[]
     */
    public function getMostViewedProducts($store, $group)
    {
        $productIds = [];
        /** @var ReportsCollection $collection */
        $mostViewedProducts = $this->reportCollectionFactory->create()->addViewsCount();

        foreach ($mostViewedProducts as $product) {
            $productIds[] = $product->getData()['entity_id'];
        }

        $collection = $this->_productsFactory->create()->addIdFilter($productIds);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $collection->getSelect()->order("find_in_set(e.entity_id,'" . implode(',', $productIds) . "')");

        return $collection->getItems();
    }

    /**
     * @param $store
     * @param $group
     *
     * @return DataObject[]
     */
    public function getBestSellers($store, $group)
    {
        $productIds  = [];
        $bestSellers = $this->bestsellersCollection->create()
            ->setModel('Magento\Catalog\Model\Product')
            ->addStoreFilter($store->getId())
            ->setPeriod('year')->addOrder('product_id');
        foreach ($bestSellers as $product) {
            $productParent = $this->_catalogProductTypeConfigurable->getParentIdsByChild($product->getProductId());
            if (isset($productParent[0])) {
                $productIds[] = $productParent[0];
            } else {
                $productIds[] = $product->getProductId();
            }
        }

        $collection = $this->_productsFactory->create()->addIdFilter($productIds);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $productIds) . ')'));

        return $collection->getItems();
    }

    /**
     * @param $customerGroupId
     * @param $store
     *
     * @return string
     */
    public function getJsFilePath($customerGroupId, $store)
    {
        return Media::TEMPLATE_MEDIA_PATH . '/' . $store->getCode() . '_' . $customerGroupId . '.js';
    }

    /**
     * @param $customerGroupId
     * @param $store
     *
     * @return string
     */
    public function getAdditionJsFilePath($customerGroupId, $store)
    {
        return Media::TEMPLATE_MEDIA_PATH . '/' . $store->getCode() . '_' . $customerGroupId . '_addition.js';
    }

    /**
     * Create json file to contain new product, most viewed, bestsellers data
     * @return array
     */
    public function createAdditionJsonFile()
    {
        $errors         = [];
        $customerGroups = $this->_customerGroupFactory->create();
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($customerGroups as $group) {
                try {
                    $this->createAdditionJsonFileForStore($store, $group->getId());
                } catch (Exception $e) {
                    $errors[] = __(
                        'Cannot generate data for store %1 and customer group %2, %3',
                        $store->getCode(),
                        $group->getCode(),
                        $e->getMessage()
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * A cron job will run this function everyday
     *
     * @param $store
     * @param $group
     *
     * @return $this
     */
    public function createAdditionJsonFileForStore($store, $group)
    {
        $newProducts = $this->createJsonFileProduct(
            $store,
            $this->getNewProducts($store, $group),
            Search::NEW_PRODUCTS
        );

        $mostViewedProducts = $this->createJsonFileProduct(
            $store,
            $this->getMostViewedProducts($store, $group),
            Search::MOST_VIEWED_PRODUCTS
        );

        $bestsellers = $this->createJsonFileProduct($store, $this->getBestSellers($store, $group), Search::BESTSELLERS);

        $this->getMediaHelper()->createJsFile(
            $this->getAdditionJsFilePath($group, $store),
            ';var mp_new_product_search = ' . $newProducts . ';
            var mp_most_viewed_products = ' . $mostViewedProducts . ';
            var mp_bestsellers = ' . $bestsellers . ';'
        );

        return $this;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getJsFileUrl()
    {
        $customerGroupId = $this->_customerSession->getCustomerGroupId();

        /** @var Store $store */
        $store = $this->storeManager->getStore();

        $mediaDirectory = $this->getMediaHelper()->getMediaDirectory();
        $filePath       = $this->getJsFilePath($customerGroupId, $store);
        if (!$mediaDirectory->isFile($filePath)) {
            $this->createJsonFileForStore($store, $customerGroupId);
        }

        return $this->getMediaHelper()->getMediaUrl($filePath) . "?v={$this->getVersionFile()}";
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getAdditionJsFileUrl()
    {
        $customerGroupId = $this->_customerSession->getCustomerGroupId();

        /** @var Store $store */
        $store = $this->storeManager->getStore();

        $mediaDirectory = $this->getMediaHelper()->getMediaDirectory();
        $filePath       = $this->getAdditionJsFilePath($customerGroupId, $store);
        if (!$mediaDirectory->isFile($filePath)) {
            $this->createJsonFileForStore($store, $customerGroupId);
        }

        return $this->getMediaHelper()->getMediaUrl($filePath) . "?v={$this->getVersionFile()}";
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategoryTree()
    {
        $categoriesOptions = [0 => __('All Categories')];

        $maxLevel           = max(0, (int) $this->getConfigGeneral('category/max_depth')) ?: 2;
        $parent             = $this->storeManager->getStore()->getRootCategoryId();
        $categories         = $this->categoryFactory->create();
        $categoryCollection = $categories->getCategories($parent, 1, false, true);
        if ($categories->getUseFlatResource()) {
            foreach ($categoryCollection as $category) {
                if ($category->getParentId() == $parent) {
                    $this->getCategoryOptions($category, $categoriesOptions, $maxLevel);
                }
            }
        } else {
            foreach ($categoryCollection as $category) {
                $this->getCategoryOptions($category, $categoriesOptions, $maxLevel);
            }
        }

        return $categoriesOptions;
    }

    /**
     * @param $category
     * @param $options
     * @param $level
     * @param string $htmlPrefix
     *
     * @return $this
     */
    protected function getCategoryOptions($category, &$options, $level, $htmlPrefix = '')
    {
        if ($level <= 0) {
            return $this;
        }
        $level--;

        $options[$category->getId()] = $htmlPrefix . $category->getName();

        $htmlPrefix .= '- ';
        if (!empty($this->getChildCategories($category))) {
            foreach ($this->getChildCategories($category) as $childCategory) {
                $this->getCategoryOptions($childCategory, $options, $level, $htmlPrefix);
            }
        }

        return $this;
    }

    /**
     * @param $category
     *
     * @return mixed
     */
    public function getChildCategories($category)
    {
        return $category->getChildrenCategories();
    }

    /**
     * @return string
     */
    public function getPriceFormat()
    {
        return self::jsonEncode($this->localeFormat->getPriceFormat());
    }

    /**
     *  Set version File Js After generation in Media folder
     */
    public function setVersionFile()
    {
        $version = $this->getVersionFile();
        $version++;
        $this->configWriter->save(static::CONFIG_MODULE_PATH . '/general/search_version_file', $version);
    }

    /**
     * Get version of File Js.
     *
     * @return int
     */
    public function getVersionFile(): int
    {
        return (int) $this->getConfigGeneral('search_version_file') ?: 0;
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isAlgoliaSearch($storeId = null)
    {
        return (int) $this->getModuleConfig('algolia_search/enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getAlgoliaApplicationId($storeId = null)
    {
        return (string) $this->getModuleConfig('algolia_search/application_id', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getAlgoliaSearchApiKey($storeId = null)
    {
        return (string) $this->getModuleConfig('algolia_search/search_only_api_key', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getAlgoliaApiKey($storeId = null)
    {
        return (string) $this->getModuleConfig('algolia_search/api_key', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getAlgoliaIndexPrefix($storeId = null)
    {
        return $this->getModuleConfig('algolia_search/index_prefix', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getAlgoliaAppApiKeys($storeId = null)
    {
        return [
            'applicationId' => $this->getAlgoliaApplicationId($storeId),
            'searchKey'     => $this->getAlgoliaSearchApiKey($storeId),
            'apiKey'        => $this->getAlgoliaApiKey($storeId)
        ];
    }

    /**
     * @return array
     */
    public function syncDataToAlgolia()
    {
        $errors = [];
        foreach ($this->storeManager->getStores() as $store) {
            $indexName = $this->getIndexName($store);
            $indexData = $this->getProductsRecords($store);
            try {
                $this->addObjects($indexData, $indexName, $store->getId());
            } catch (Exception $e) {
                $errors[] = __(
                    'Cannot generate data for store %1, %2',
                    $store->getCode(),
                    $e->getMessage()
                );
            }
        }

        return $errors;
    }

    /**
     * @param $objects
     * @param $indexName
     * @param $storeId
     *
     * @throws MissingObjectId
     */
    public function addObjects($objects, $indexName, $storeId)
    {
        if ($this->getAlgoliaApplicationId($storeId) && $this->getAlgoliaApiKey($storeId)) {
            $this->client = SearchClient::create(
                $this->getAlgoliaApplicationId($storeId),
                $this->getAlgoliaApiKey($storeId)
            );

            $index = $this->client->initIndex($indexName);
            $index->saveObjects($objects, [
                'autoGenerateObjectIDIfNotExist' => true,
            ]);
        }
    }

    /**
     * @param $store
     *
     * @return string
     */
    public function getIndexName($store)
    {
        return $this->getAlgoliaIndexPrefix($store->getId()) . $store->getCode() . "_products";
    }

    /**
     * @param $store
     *
     * @return array
     */
    public function getProductsRecords($store)
    {
        $collection = $this->_productsFactory->create();
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());
        $productList = [];

        foreach ($collection as $product) {
            $productPrice = $product->getFinalPrice();
            if ($product->getTypeId() === 'bundle') {
                $productPrice   = $product->getMinPrice() . '-' . $product->getMaxPrice();
                $currencySymbol = $this->_currency->getCurrencySymbol();
                $productPrice   = str_replace($currencySymbol, '', $productPrice);
            } elseif ($product->getTypeId() === 'grouped' || $product->getTypeId() === 'configurable') {
                $productPrice = $product->getMinimalPrice();
            }

            $productList[] = [
                'objectID'    => $product->getId(),
                'name'        => $product->getName(),
                'sku'         => $product->getSku(),
                'description' => $this->getProductDescription($product, $store),
                'image'       => $this->_getUrl('/') . 'media/catalog/product/' . $this->getMediaHelper()->getProductImage($product),
                'url'         => $this->_getUrl($this->getProductUrl($product)),
                'price'       => $productPrice,
                'categories'  => $this->getProductCategories($product)
            ];
        }

        return $productList;
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function canShowAlgolia()
    {
        $store = $this->getStore();

        if ($this->isEnabled($store->getId()) && $this->isAlgoliaSearch($store->getId())) {
            return 'Mageplaza_Search::algolia_search.phtml';
        }

        return 'Magento_Search::form.mini.phtml';
    }

    /**
     * @param $storeId
     *
     * @return array|mixed
     */
    public function getMinQueryLength($storeId)
    {
        return $this->getConfigValue('catalog/search/min_query_length', $storeId);
    }

    /**
     * @param $block
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAlgoliaSearchConfiguration($block)
    {
        $store = $this->getStore();

        return [
            "baseUrl"            => trim($block->getBaseUrl(), '/') . '/',
            "baseImageUrl"       => $this->getMediaHelper()->getSearchMediaUrl(),
            "priceFormat"        => self::jsonDecode($this->getPriceFormat()),
            "displayInfo"        => self::jsonDecode($this->getDisplay($store->getId())),
            "isEnableSuggestion" => $this->isEnableSuggestion($store->getId()),
            "sortBy"             => $this->getSortBy($store->getId()),
            "currencyRate"       => number_format($this->getCurrentCurrencyRate(), '4'),
            "lookupLimit"        => (int) $this->getConfigGeneral('max_query_results') ?: 10,
            "algoliaApiKey"      => $this->getAlgoliaAppApiKeys($store->getId()),
            "indexName"          => $this->getIndexName($store),
            "autocomplete"       => [
                "enabled" => $this->isEnableSuggestion($store->getId()),
            ],
            "instant"            => [
                "enabled" => $this->isAlgoliaSearch($store->getId())
            ],
            "minQueryLength"     => (int) $this->getMinQueryLength($store->getId())
        ];
    }
}
