<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Block\Frontend;

class Init extends \Magento\Framework\View\Element\Template
{
    protected $_config;
    protected $_cookie;
    protected $_track;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Rees46\Personalization\Helper\Config $config,
        \Rees46\Personalization\Helper\Cookie $cookie,
        \Rees46\Personalization\Model\Track $track,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_cookie = $cookie;
        $this->_track = $track;

        parent::__construct($context, $data);
    }

    public function getTrackingCode()
    {
        $js = '';
        $js .= 'r46(\'init\', \'' . $this->_config->getValue('rees46/general/store_key') . '\');' . "\n";

        if ($this->_cookie->get('rees46_customer')) {
            $js .= $this->_cookie->get('rees46_customer');

            $this->_cookie->delete('rees46_customer');
        }

        if ($this->_cookie->get('rees46_cart')) {
            $js .= $this->_cookie->get('rees46_cart');

            $this->_cookie->delete('rees46_cart');
        }

        foreach ($this->_track->getJS() as $event) {
            $js .= $event;
        }

        return $js;
    }

    protected function _toHtml()
    {
        if (!$this->_config->isRees46Enabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
