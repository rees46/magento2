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

    public function createCollection()
    {
        $product_ids = [];
        $collection_ids = [];

        $product_ids = explode(',', $this->getParam('product_ids'));

        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addAttributeToFilter('entity_id', ['in' => $product_ids])
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addFinalPrice()
            ->addPriceData()
            ->addStoreFilter()
            ->setPageSize($this->getParam('limit'))
            ->setCurPage(1);
        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

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
