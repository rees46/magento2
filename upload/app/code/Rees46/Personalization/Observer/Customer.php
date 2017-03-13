<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Observer;

class Customer implements \Magento\Framework\Event\ObserverInterface
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
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getGender() == 1) {
            $customer_gender = 'm';
        } elseif ($customer->getGender() == 2) {
            $customer_gender = 'f';
        } else {
            $customer_gender = null;
        }

        if ($customer->getDob()) {
            $customer_birthday = date('Y-m-d', strtotime($customer->getDob()));
        } else {
            $customer_birthday = null;
        }

        $js  = 'r46(\'profile\', \'set\', {';
        $js .= ' id: ' . (int)$customer->getId() . ',';
        $js .= ' email: \'' . $customer->getEmail() . '\',';
        $js .= ' gender: \'' . $customer_gender . '\',';
        $js .= ' birthday: \'' . $customer_birthday . '\'';
        $js .= '});' . "\n";

        $this->_cookie->set('rees46_customer', $js);
    }
}
