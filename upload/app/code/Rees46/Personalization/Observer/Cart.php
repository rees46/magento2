<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Observer;

class Cart implements \Magento\Framework\Event\ObserverInterface
{
    protected $_cookie;

	public function __construct(
		\Rees46\Personalization\Helper\Cookie $cookie
	)
	{
        $this->_cookie = $cookie;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $js_data = array();

        foreach ($observer->getEvent()->getData('cart')->getQuote()->getAllVisibleItems() as $product) {
            $js_data[] = array(
                'id' => $product->getProductId(),
                'amount' => $product->getQty(),
            );
        }

        $js = 'r46(\'track\', \'cart\', ' . json_encode($js_data) . ');' . "\n";

        if ($this->_cookie->get('rees46_cart')) {
            $js = $this->_cookie->get('rees46_cart') . $js;
        }

        $this->_cookie->set('rees46_cart', $js);
	}
}
