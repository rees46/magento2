<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Observer;

class OrderStatus implements \Magento\Framework\Event\ObserverInterface
{
    protected $_config;
    protected $_api;

	public function __construct(
		\Rees46\Personalization\Helper\Config $config,
        \Rees46\Personalization\Helper\Api $api
	)
	{
        $this->_config = $config;
        $this->_api = $api;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $order = $observer->getEvent()->getOrder();

        $old_status = $order->getOrigData('status');
        $new_status = $order->getData('status');

        if ($old_status != $new_status) {
            $order_id = $order->getId();
            $order_status_id = $new_status;

            $rees46_order_created = explode(',', $this->_config->getValue('rees46/general/order_created'));;
            $rees46_order_completed = explode(',', $this->_config->getValue('rees46/general/order_completed'));
            $rees46_order_cancelled = explode(',', $this->_config->getValue('rees46/general/order_cancelled'));

            if ($rees46_order_created && in_array($order_status_id, $rees46_order_created)) {
                $status = 0;
            } elseif ($rees46_order_completed && in_array($order_status_id, $rees46_order_completed)) {
                $status = 1;
            } elseif ($rees46_order_cancelled && in_array($order_status_id, $rees46_order_cancelled)) {
                $status = 2;
            }

            if (isset($status)) {
                $order_data = array(
                    'id' => $order_id,
                    'status' => $status,
                );

                $this->_api->rees46SyncOrders($order_data, $order_status_id);
            }
        }
	}
}
