<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Block\Frontend;

class Products extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    protected $_params = [];

    protected function getParam($param)
    {
        $_params = $this->getData('params');

        if (isset($_params[$param])) {
            return $_params[$param];
        }
    }

    protected function getParams()
    {
        $_params = $this->getData('params');

        if (!empty($_params)) {
            return $_params;
        }
    }

    public function getTitle()
    {
        return $this->getParam('title');
    }

    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            
            $link = $product->getUrlModel()->getUrl($product, $additional);

            if (parse_url($link, PHP_URL_QUERY)) {
                $link = $link . '&recommended_by=' . $this->getParam('type');
            } else {
                $link = $link . '?recommended_by=' . $this->getParam('type');
            }

            return $link;
        }

        return '#';
    }

    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    )
    {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id']              = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container']     = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                    ],
                ]
            );
        }

        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }

        return $price;
    }

    public function createCollection()
    {
        $product_ids = [];
        $collection_ids = [];

        $product_ids = explode(',', $this->getParam('product_ids'));

        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('entity_id', ['in' => $product_ids])
            ->addStoreFilter();

        if ($this->getParam('discount')) {
            $collection->getSelect()->where('price_index.final_price < price_index.price');
        }

        if ($this->getParam('brands')) {
            $brands = explode(',', $this->getParam('brands'));

            $collection->addAttributeToFilter('manufacturer', ['in' => $brands]);
        }

        if ($this->getParam('exclude_brands')) {
            $exclude_brands = explode(',', $this->getParam('exclude_brands'));

            $collection->addAttributeToFilter('manufacturer', ['nin' => $exclude_brands]);
        }

        $collection->setPageSize($this->getParam('limit'))->setCurPage(1);

        foreach ($collection->getItems() as $product_id) {
            $collection_ids[] = $product_id->getEntityId();
        }

        $results = array_diff($product_ids, $collection_ids);

        if (!empty($results)) {
            $_helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Rees46\Personalization\Helper\Api');

            foreach ($results as $product_id) {
                $_helper->rees46DisableProduct($product_id);
            }
        }    

        return $collection;
    }

    protected function _toHtml()
    {
        $this->setTemplate($this->getParam('template'));

        return parent::_toHtml();
    }
}
