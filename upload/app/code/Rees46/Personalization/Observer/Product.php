<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Observer;

class Product implements \Magento\Framework\Event\ObserverInterface
{
    protected $_data;
    protected $_track;
    protected $_image;

    public function __construct(
        \Rees46\Personalization\Helper\Data $data,
        \Rees46\Personalization\Model\Track $track,
        \Magento\Catalog\Helper\Image $image
    )
    {
        $this->_data = $data;
        $this->_track = $track;
        $this->_image = $image;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $js  = 'r46(\'track\', \'view\', {';
        $js .= ' id: ' . (int)$product->getId() . ',';
        $js .= ' stock: ' . $this->_data->getProductStock($product->getId()) . ',';
        $js .= ' price: ' . $product->getFinalPrice() . ',';
        $js .= ' name: \'' . $product->getName() . '\',';
        $js .= ' categories: ' . json_encode($product->getCategoryIds()) . ',';
        $js .= ' image: \'' . $this->_image->init($product, 'category_page_list')->getUrl() . '\',';
        $js .= ' url: \'' . $product->getProductUrl($product->getId()) . '\'';
        $js .= '});' . "\n";

        $this->_track->add($js);
    }
}
