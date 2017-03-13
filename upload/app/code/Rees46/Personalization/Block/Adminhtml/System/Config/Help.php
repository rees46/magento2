<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Block\Adminhtml\System\Config;

class Help extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Rees46\Personalization\Helper\Config $config,
        array $data = []
    )
    {
        $this->_config = $config;

        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->getTemplate()) {
            if (!$this->_config->isRees46Enabled()) {
                $this->setTemplate('system/config/help.phtml');
            } else {
            	$this->setTemplate('system/config/help_auth.phtml');
            }
        }

        return $this;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
