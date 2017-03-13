<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Helper;

class Logger extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_logger;
    protected $_config;
    protected $_status;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Rees46\Personalization\Helper\Config $config
    )
    {
        $this->_logger = $logger;
        $this->_config = $config;
        $this->_status = $this->_config->isLogEnabled();
    }

    public function log($message = '')
    {
        if ($this->_status) {
            $this->_logger->addDebug($message);
        }
    }
}
