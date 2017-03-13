<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Model\Config\Source\Product;

class Manufacturer implements \Magento\Framework\Option\ArrayInterface
{
    protected $_eav;

    public function __construct(
        \Magento\Eav\Model\Config $eav
    )
    {
        $this->_eav = $eav;
    }

    public function toArray()
    {
        return $options[] = $this->_eav->getAttribute('catalog_product', 'manufacturer')->getSource()->getAllOptions();
    }

    public function toOptionArray()
    {
        return $this->toArray();
    }

    public function getAllOptions()
    {
        return $this->toArray();
    }
}
