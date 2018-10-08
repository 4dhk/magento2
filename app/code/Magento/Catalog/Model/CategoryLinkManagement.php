<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * Class CategoryLinkManagement
 */
class CategoryLinkManagement implements \Magento\Catalog\Api\CategoryLinkManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkRepositoryInterface
     */
    protected $categoryLinkRepository;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * CategoryLinkManagement constructor.
     *
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productLinkFactory = $productLinkFactory;
    }


    private function createCustomAttributesObject($name,$value){
        $response = new \stdClass();
        $response->attribute_code = $name;
        $response->value = $value;
        return $response;
    }
    private function moveAttributesToCustomAttributes($keys,$data){
        $data['custom_attributes'] = [];
        if($keys){
            foreach($keys as $key){
                if(isset($data[$key])) {
                    $data['custom_attributes'][] = $this->createCustomAttributesObject($key, $data[$key]);
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }
    /**
     * {@inheritdoc}
     */
    public function getAssignedProducts($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        if(isset($_GET['page'])){
            $page = $_GET['page'];
        }else{
            $page = 1;
        }
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
        $products = $category->getProductCollection();
        $products->setPageSize(10);
        $products->setCurPage($page);

        $products->addFieldToSelect(
            '*'
        );

        /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $links */
        $response = new \StdClass();
        $links = [];

        /** @var \Magento\Catalog\Model\Product $product */
        $productItems = $products->getItems();
        if($productItems) {
            foreach ($products->getItems() as $product) {
                /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface $link */
                /*$link = $this->productLinkFactory->create();
                $link->setSku($product->getSku())
                    ->setPosition($product->getData('cat_index_position'))
                    ->setCategoryId($category->getId());*/


                $link = new \stdClass();
                //$link = $product->getData();


                $movekeys = [
                    'short_description',
                    'meta_title',
                    'meta_keyword',
                    'meta_description',
                    'image',
                    'small_image',
                    'thumbnail',
                    'options_container',
                    'required_options',
                    'has_options',
                    'url_key',
                    'tax_class_id',
                    'gift_message_available',
                    'sirent_pricingtype',

                    'sirent_quantity',
                    'sirent_rental_type',
                    'sirent_serial_numbers_use',
                    'sirent_disable_shipping',

                    'sirent_use_times',
                    'sirent_padding',
                    'sirent_min',
                    'sirent_max',
                    'sirent_turnover_before',
                    'sirent_turnover_after',
                    'sirent_future_limit',
                    'sirent_allow_overbooking',
                    'sirent_global_exclude_dates',
                    'sirent_allow_extend_order',
                    'sirent_fixed_length',
                    'sirent_enable_buyout',
                    'sirent_buyout_price',
                    'sirent_excludeddays_from',
                    //'type',
                    'sirent_hotel_mode',
                    'search_weight'

                ];

                $link = $this->moveAttributesToCustomAttributes($movekeys, $product->getData());

                $link['id'] = $link['entity_id'];
                unset($link['entity_id']);
                unset($link['description']);


                /*$link->id = $product->getData('entity_id');
                $link->sku = $product->getSku();
                $link->name = $product->getName();

                $link->position = $product->getData('cat_index_position');
                $link->category_id = $category->getId();*/

                $links[] = $link;
            }
        }
        $response->items = $links;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;

        return $links;
    }

    /**
     * Assign product to given categories
     *
     * @param string $productSku
     * @param \int[] $categoryIds
     * @return bool
     */
    public function assignProductToCategories($productSku, array $categoryIds)
    {
        $product = $this->getProductRepository()->get($productSku);
        $assignedCategories = $this->getProductResource()->getCategoryIds($product);
        foreach (array_diff($assignedCategories, $categoryIds) as $categoryId) {
            $this->getCategoryLinkRepository()->deleteByIds($categoryId, $productSku);
        }

        foreach (array_diff($categoryIds, $assignedCategories) as $categoryId) {
            /** @var \Magento\Catalog\Api\Data\CategoryProductLinkInterface $categoryProductLink */
            $categoryProductLink = $this->productLinkFactory->create();
            $categoryProductLink->setSku($productSku);
            $categoryProductLink->setCategoryId($categoryId);
            $categoryProductLink->setPosition(0);
            $this->getCategoryLinkRepository()->save($categoryProductLink);
        }
        $productCategoryIndexer = $this->getIndexerRegistry()->get(Indexer\Product\Category::INDEXER_ID);
        if (!$productCategoryIndexer->isScheduled()) {
            $productCategoryIndexer->reindexRow($product->getId());
        }
        return true;
    }

    /**
     * Retrieve product repository instance
     *
     * @return \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private function getProductRepository()
    {
        if (null === $this->productRepository) {
            $this->productRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\ProductRepositoryInterface');
        }
        return $this->productRepository;
    }

    /**
     * Retrieve product resource instance
     *
     * @return ResourceModel\Product
     */
    private function getProductResource()
    {
        if (null === $this->productResource) {
            $this->productResource = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Model\ResourceModel\Product');
        }
        return $this->productResource;
    }

    /**
     * Retrieve category link repository instance
     *
     * @return \Magento\Catalog\Api\CategoryLinkRepositoryInterface
     */
    private function getCategoryLinkRepository()
    {
        if (null === $this->categoryLinkRepository) {
            $this->categoryLinkRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\CategoryLinkRepositoryInterface');
        }
        return $this->categoryLinkRepository;
    }

    /**
     * Retrieve indexer registry instance
     *
     * @return \Magento\Framework\Indexer\IndexerRegistry
     */
    private function getIndexerRegistry()
    {
        if (null === $this->indexerRegistry) {
            $this->indexerRegistry = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Indexer\IndexerRegistry');
        }
        return $this->indexerRegistry;
    }
}
