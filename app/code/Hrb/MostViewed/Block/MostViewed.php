<?php

declare(strict_types=1);

/**
* Harsh Bhinsara.
*
* NOTICE OF LICENSE
*
* This source file is subject to the MIT License
* that is bundled with this package in the file LICENSE.md.
*
* @package   Hrb_MostViewed
* @author    Harsh Bhinsara
*/
namespace Hrb\MostViewed\Block;

use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as ReportCollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\ImageBuilder;

class MostViewed extends Template
{

    /**
     * Most Viewed block constructor
     *
     * @param Context $context
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Pricing\Render $priceRender
     * @param \Magento\Catalog\Block\Product\AbstractProduct $abstractProduct
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     */
    public function __construct(
        Template\Context $context,
        private readonly ReportCollectionFactory $reportCollectionFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Render $priceRender,
        private readonly AbstractProduct $abstractProduct,
        private readonly ImageBuilder $imageBuilder
    ) {
        parent::__construct($context);
    }

    /**
     * Get most viewed products collection.
     *
     * @return \Magento\Reports\Model\ResourceModel\Product\Collection|array
     */
    public function getMostViewedProducts()
    {
        if ($this->isEnabled()) {
            $collection = $this->reportCollectionFactory->create();
            $collection->addViewsCount()
                ->setPageSize($this->getConfigPageSize())
                ->setCurPage(1);
            return $collection;
        }
        return [];
    }

    /**
     * Check if the most viewed products module is enabled.
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue('catalog/most_viewed/enabled');
    }

    /**
     * Get the configured number of products to display.
     *
     * @return int
     */
    protected function getConfigPageSize(): int
    {
        return (int) $this->scopeConfig->getValue('catalog/most_viewed/number_of_list');
    }

    /**
     * Get the title for the most viewed products list.
     *
     * @return string|null
     */
    public function getMostViewListTitle(): ?string
    {
        return $this->scopeConfig->getValue('catalog/most_viewed/list_title');
    }

    /**
     * Get product details by product object.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($product)
    {
        $productId = $product->getEntityId();
        return $this->productRepository->getById($productId);
    }

    /**
     * Get the product image.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Framework\View\Element\Template
     */
    public function getImage($product): Template
    {
        return $this->abstractProduct->getImage($product, 'most_viewed_products_list');
    }

    /**
     * Get the product price.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getPrice($product): string
    {
        return $price = $this->priceRender->render(
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            $product,
            ['display_minimal_price' => true]
        );
    }
}
