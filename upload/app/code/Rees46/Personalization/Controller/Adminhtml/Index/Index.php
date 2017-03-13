<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    public function execute()
    {

    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Rees46_Personalization::settings');
    }
}
