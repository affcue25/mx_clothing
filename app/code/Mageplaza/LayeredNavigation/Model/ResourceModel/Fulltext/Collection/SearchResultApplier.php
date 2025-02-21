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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Zend_Db_Expr;
use Zend_Db_Select_Exception;

/**
 * Resolve specific attributes for search criteria.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchResultApplier implements SearchResultApplierInterface
{
    /**
     * @var Collection|\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $collection;

    /**
     * @var SearchResultInterface
     */
    private $searchResult;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * SearchResultApplier constructor.
     *
     * @param Collection $collection
     * @param SearchResultInterface $searchResult
     * @param int $size
     * @param int $currentPage
     * @param ScopeConfigInterface|null $scopeConfig
     * @param MetadataPool|null $metadataPool
     */
    public function __construct(
        Collection $collection,
        SearchResultInterface $searchResult,
        int $size,
        int $currentPage,
        ?ScopeConfigInterface $scopeConfig = null,
        ?MetadataPool $metadataPool = null
    ) {
        $this->collection         = $collection;
        $this->searchResult       = $searchResult;
        $this->size               = $size;
        $this->currentPage        = $currentPage;
        $this->scopeConfig        = $scopeConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->metadataPool       = $metadataPool ?? ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        if (empty($this->searchResult->getItems())) {
            $this->collection->getSelect()->where('NULL');

            return;
        }

        //        $ids = $this->getProductIdsBySaleability();
        $ids = [];

        if (count($ids) == 0) {
            $items = $this->sliceItems($this->searchResult->getItems(), $this->size, $this->currentPage);
            foreach ($items as $item) {
                $ids[] = (int) $item->getId();
            }
        }
        $this->collection->getSelect()
            ->where('e.entity_id IN (?)', $ids);
        try {
            $isHasOrder = $this->collection->getSelect()->getPart('order');
        } catch (Zend_Db_Select_Exception $e) {
            $isHasOrder = false;
        }
        if (!$isHasOrder) {
            $orderList = implode(',', $ids);
            $this->collection->getSelect()->reset(Select::ORDER)
                ->order(new Zend_Db_Expr("FIELD(e.entity_id,$orderList)"));
        }
    }

    /**
     * Slice current items
     *
     * @param array $items
     * @param int $size
     * @param int $currentPage
     *
     * @return array
     */
    private function sliceItems(array $items, int $size, int $currentPage): array
    {
        if ($size !== 0) {
            // Check that current page is in a range of allowed page numbers, based on items count and items per page,
            // than calculate offset for slicing items array.
            $itemsCount           = count($items);
            $maxAllowedPageNumber = ceil($itemsCount / $size);
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            if ($currentPage > $maxAllowedPageNumber) {
                $currentPage = $maxAllowedPageNumber;
            }

            $offset = $this->getOffset($currentPage, $size);
            $items  = array_slice($items, $offset, $size);
        }

        return $items;
    }

    /**
     * Get offset for given page.
     *
     * @param int $pageNumber
     * @param int $pageSize
     *
     * @return int
     */
    private function getOffset(int $pageNumber, int $pageSize): int
    {
        return ($pageNumber - 1) * $pageSize;
    }

    /**
     * Fetch filtered product ids sorted by the saleability and other applied sort orders
     *
     * @return array
     */
    private function getProductIdsBySaleability(): array
    {
        $ids = [];

        if ($this->hasShowOutOfStockStatus()) {
            return $ids;
        }

        if ($this->collection->getFlag('has_stock_status_filter')
            || $this->collection->getFlag('has_category_filter')) {
            $categoryId     = null;
            $searchCriteria = $this->searchResult->getSearchCriteria();
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if ($filter->getField() === 'category_ids') {
                        $categoryId = $filter->getValue();
                        break 2;
                    }
                }
            }

            if ($categoryId) {
                $resultSet = $this->categoryProductByCustomSortOrder((int) $categoryId);
                foreach ($resultSet as $item) {
                    $ids[] = (int) $item['entity_id'];
                }
            }
        }

        return $ids;
    }

    /**
     * Fetch product results by custom sort orders
     *
     * @param int $categoryId
     *
     * @return array
     * @throws LocalizedException
     * @throws Zend_Db_Select_Exception
     */
    private function categoryProductByCustomSortOrder(int $categoryId): array
    {
        $storeId        = $this->collection->getStoreId();
        $searchCriteria = $this->searchResult->getSearchCriteria();
        $sortOrders     = $searchCriteria->getSortOrders() ?? [];
        $sortOrders     = array_merge(['is_salable' => Select::SQL_DESC], $sortOrders);

        $connection = $this->collection->getConnection();
        $query      = clone $connection->select()
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET)
            ->reset(Select::COLUMNS);
        $query->from(
            ['e' => $this->collection->getTable('catalog_product_entity')],
            ['e.entity_id']
        );
        $query->join(
            ['cat_index' => $this->collection->getTable('catalog_category_product_index_store' . $storeId)],
            'cat_index.product_id = e.entity_id'
            . ' AND cat_index.category_id = ' . $categoryId
            . ' AND cat_index.store_id = ' . $storeId,
            ['cat_index.position']
        );

        $productIds = [];
        foreach ($this->searchResult->getItems() as $item) {
            $productIds[] = $item->getId();
        }

        $query->where('e.entity_id IN(?)', $productIds);

        foreach ($sortOrders as $field => $dir) {
            if ($field === 'name') {
                $entityTypeId   = $this->collection->getEntity()->getTypeId();
                $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
                $linkField      = $entityMetadata->getLinkField();
                $query->joinLeft(
                    ['product_var' => $this->collection->getTable('catalog_product_entity_varchar')],
                    "product_var.{$linkField} = e.{$linkField} AND product_var.attribute_id =
                    (SELECT attribute_id FROM eav_attribute WHERE entity_type_id={$entityTypeId}
                    AND attribute_code='name')",
                    ['product_var.value AS name']
                );
            } elseif ($field === 'price') {
                $query->joinLeft(
                    ['price_index' => $this->collection->getTable('catalog_product_index_price')],
                    'price_index.entity_id = e.entity_id'
                    . ' AND price_index.customer_group_id = 0'
                    . ' AND price_index.website_id = (Select website_id FROM store WHERE store_id = '
                    . $storeId . ')',
                    ['price_index.max_price AS price']
                );
            }
            $columnFilters = [];
            $columnsParts  = $query->getPart('columns');
            foreach ($columnsParts as $columns) {
                $columnFilters[] = $columns[2] ?? $columns[1];
            }
            if (in_array($field, $columnFilters, true)) {
                $query->order(new Zend_Db_Expr("{$field} {$dir}"));
            }
        }

        $query->limit(
            $searchCriteria->getPageSize(),
            $searchCriteria->getCurrentPage() * $searchCriteria->getPageSize()
        );

        return $connection->fetchAssoc($query) ?? [];
    }

    /**
     * Returns if display out of stock status set or not in catalog inventory
     *
     * @return bool
     */
    private function hasShowOutOfStockStatus(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            ScopeInterface::SCOPE_STORE
        );
    }
}
