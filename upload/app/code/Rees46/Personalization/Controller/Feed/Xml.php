<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Controller\Feed;

class Xml extends \Magento\Framework\App\Action\Action
{
    const LIMIT = 500;

    protected $_forward;
    protected $_config;
    protected $_data;
    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $forward,
        \Rees46\Personalization\Helper\Config $config,
        \Rees46\Personalization\Helper\Data $data,
        \Rees46\Personalization\Helper\Logger $logger
    )
    {
        $this->_forward = $forward;
        $this->_config = $config;
        $this->_data = $data;
        $this->_logger = $logger;

        parent::__construct($context, $forward, $config, $data, $logger);
    }

    public function execute()
    {
        if ($this->_config->isRees46Enabled()) {
            if ($this->get('step')) {
                if ($this->get('step') == 1) {
                    $this->generateCurrencies();

                    return $this->resultRedirectFactory->create()->setPath('rees46/feed/xml', ['_query' => ['step' => '2']]);
                } elseif ($this->get('step') == 2) {
                    $this->generateCategories();

                    $this->recorder('    <offers>' . "\n", 'a');

                    return $this->resultRedirectFactory->create()->setPath('rees46/feed/xml', ['_query' => ['start' => 'start']]);
                }
            } elseif ($this->get('start')) {
                if ($this->get('start') != 'finish') {
                    if ($this->get('start') == 'start') {
                        $start = $this->generateOffers(0);
                    } else {
                        $start = $this->generateOffers($this->get('start'));
                    }

                    return $this->resultRedirectFactory->create()->setPath('rees46/feed/xml', ['_query' => ['start' => $start]]);
                } elseif ($this->get('start') == 'finish') {
                    $xml  = '    </offers>' . "\n";
                    $xml .= '  </shop>' . "\n";
                    $xml .= '</yml_catalog>';

                    $this->recorder($xml, 'a');

                    header('Content-Type: application/xml; charset=utf-8');
                    echo file_get_contents($this->file());
                }
            } else {
                if (is_file($this->fileCron()) && $this->_config->isCronEnabled()) {
                    header('Content-Type: application/xml; charset=utf-8');
                    echo file_get_contents($this->fileCron());
                } else {
                    $this->recorder('', 'w+');

                    $this->generateShop();

                    return $this->resultRedirectFactory->create()->setPath('rees46/feed/xml', ['_query' => ['step' => '1']]);
                }
            }
        } else {
            return $this->_forward->create()->forward('noroute');
        }
    }

    protected function get($param)
    {
        return $this->getRequest()->getParam($param);
    }

    protected function file()
    {
        return $this->_data->getDir('media') . '/rees46.xml';
    }

    protected function fileCron()
    {
        return $this->_data->getDir('media') . '/rees46_cron.xml';
    }

    protected function replacer($str)
    {
        return trim(str_replace('&#039;', '&apos;', htmlspecialchars(htmlspecialchars_decode($str, ENT_QUOTES), ENT_QUOTES)));
    }

    protected function recorder($xml, $mode)
    {
        if (!$fp = fopen($this->file(), $mode)) {
            $this->_logger->log('REES46: could not open xml file');
        } elseif (fwrite($fp, $xml) === false) {
            $this->_logger->log('REES46: XML file not writable');
        }

        fclose($fp);
    }

    protected function generateShop()
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . "\n";
        $xml .= '<yml_catalog date="' . date('Y-m-d H:i') . '">' . "\n";
        $xml .= '  <shop>' . "\n";
        $xml .= '    <name>' . $this->_data->getStoreName() . '</name>' . "\n";
        $xml .= '    <company>' . $this->_data->getStoreName() . '</company>' . "\n";
        $xml .= '    <url>' . $this->_data->getStoreUrl() . '</url>' . "\n";
        $xml .= '    <platform>Magento</platform>' . "\n";
        $xml .= '    <version>' . $this->_data->getVersion() . '</version>' . "\n";

        $this->recorder($xml, 'a');
    }

    protected function generateCurrencies()
    {
        $currencies = $this->_data->getCurrencies();

        $xml = '    <currencies>';

        foreach ($currencies as $code => $rate) {
            $xml .= "\n" . '      <currency id="' . $code . '" rate="' . number_format(1 / $rate, 4, '.', '') . '"/>';
        }

        $xml .= "\n" . '    </currencies>' . "\n";

        $this->recorder($xml, 'a');
    }

    protected function generateCategories()
    {
        $categories = $this->_data->getCategories();

        if (!empty($categories)) {
            $xml = '    <categories>';

            foreach ($categories as $category) {
                if ($category['parent_id']) {
                    $parent = ' parentId="' . $category['parent_id'] . '"';
                } else {
                    $parent = '';
                }

                $xml .= "\n" . '      <category id="' . $category['category_id'] . '"' . $parent . '>' . $this->replacer($category['name']) . '</category>';
            }

            $xml .= "\n" . '    </categories>' . "\n";

            $this->recorder($xml, 'a');
        }
    }

    protected function generateOffers($start)
    {
        $products = $this->_data->getProducts(self::LIMIT, $start);

        if (count($products) == self::LIMIT) {
            $start = $start + self::LIMIT;
        } else {
            $start = 'finish';
        }

        if (!empty($products)) {
            $xml = '';

            foreach ($products as $product) {
                if ($product['available']) {
                    $xml .= '      <offer id="' . $product['id'] . '" available="true">' . "\n";
                    $xml .= '        <url>' . $this->replacer($product['url']).'</url>' . "\n";
                    $xml .= '        <price>' . number_format($product['price'], 2, '.', '') . '</price>' . "\n";
                    $xml .= '        <currencyId>' . $this->_data->getStoreCurrency() . '</currencyId>' . "\n";

                    if (!empty($product['categories'])) {
                        foreach ($product['categories'] as $category_id) {
                            $xml .= '        <categoryId>' . $category_id . '</categoryId>' . "\n";
                        }
                    }

                    if ($product['image']) {
                        $xml .= '        <picture>' . $product['image'] . '</picture>' . "\n";
                    }

                    $xml .= '        <name>' . $this->replacer($product['name']) . '</name>' . "\n";

                    if ($product['manufacturer']) {
                        $xml .= '        <vendor>' . $this->replacer($product['manufacturer']) . '</vendor>' . "\n";
                    }

                    $xml .= '        <model>' . $this->replacer($product['model']) . '</model>' . "\n";
                    $xml .= '        <description><![CDATA[' . strip_tags(htmlspecialchars_decode($product['description']), '<h3>, <ul>, <li>, <p>, <br>') . ']]></description>' . "\n";
                    $xml .= '      </offer>' . "\n";
                }
            }

            $this->recorder($xml, 'a');
        }

        return $start;
    }
}
