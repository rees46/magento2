<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Observer;

class Checkout implements \Magento\Framework\Event\ObserverInterface
{
    protected $_order;
    protected $_track;

	public function __construct(
        \Magento\Checkout\Model\Session $order,
        \Rees46\Personalization\Model\Track $track
	)
	{
        $this->_order = $order;
        $this->_track = $track;
	}

    protected function getOrder()
    {
        return $this->_order->getLastRealOrder();
    }

    protected function getOrderId()
    {
        return $this->getOrder()->getIncrementId();
    }

    protected function getOrderTotal()
    {
        return $this->getOrder()->getGrandTotal();
    }

    protected function getOrderProducts()
    {
        return $this->getOrder()->getAllVisibleItems();
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $js_data = array();

        $js_data['order'] = (int)$this->getOrderId();
        $js_data['order_price'] = (int)$this->getOrderTotal();

        foreach ($this->getOrderProducts() as $order_product) {
            $js_data['products'][] = array(
                'id' => (int)$order_product->getProductId(),
                'price' => (int)$order_product->getBasePrice(),
                'amount' => (int)$order_product->getQtyOrdered(),
            );
        }

        $js = 'r46(\'track\', \'purchase\', ' . json_encode($js_data) . ');';

        $this->_track->add($js);
	}
}
