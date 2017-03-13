<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Block\Adminhtml\System\Config;

class Actions extends \Magento\Config\Block\System\Config\Form\Field
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
        	$this->setTemplate('system/config/actions.phtml');
        }

        return $this;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getActionStatus($path)
    {
        return $this->_config->getValue($path);
    }
}
