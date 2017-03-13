<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Block\Frontend\Widget;

class Recommendation extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface {
    protected $_registry;
    protected $_cart;
    protected $_query;
    protected $_config;
    protected $_brand;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,       
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Search\Model\QueryFactory $query,
        \Rees46\Personalization\Helper\Config $config,
        \Rees46\Personalization\Model\Config\Source\Product\Manufacturer $brand,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_cart = $cart;
        $this->_query = $query;
        $this->_config = $config;
        $this->_brand = $brand;

        parent::__construct($context, $data);
    }

	public function _toHtml()
	{
		if ($this->_config->isRees46Enabled()) {
		    $params = array();

		    $settings = array(
		    	'type' => $this->getData('block_type'),
		    	'title' => $this->getData('title'),
		    	'limit' => $this->getData('limit'),
		    	'template' => $this->getData('block_template'),
		    	'discount' => $this->getData('discount'),
		    	'brands' => $this->getData('brands'),
		    	'exclude_brands' => $this->getData('exclude_brands'),
		    	'css' => '',
		    );

			if ($settings['template'] == 'Rees46_Personalization::widget/basic.phtml') {
				$settings['css'] = 'r46(\'add_css\', \'recommendations\');' . "\n";
			}

			if ($this->getRequest()->getControllerName() == 'product' && $this->_registry->registry('current_product')->getId()) {
				$item = (int)$this->_registry->registry('current_product')->getId();
			}

			if ($this->getRequest()->getControllerName() == 'category' && $this->_registry->registry('current_category')->getId()) {
				$category = (int)$this->_registry->registry('current_category')->getId();
			}

			if ($this->_cart->getQuote()) {
				foreach ($this->_cart->getQuote()->getAllVisibleItems() as $product) {
					$cart[] = $product->getProductId();
				}
			}

			if ($this->getRequest()->getRouteName() == 'catalogsearch' && $this->_query->get()->getQueryText() != '') {
				$search_query = $this->_query->get()->getQueryText();
			}

			if ($settings['limit'] > 0) {
				$params['limit'] = (int)$settings['limit'];
			} else {
				$params['limit'] = 5;
			}

			$params['discount'] = (int)$settings['discount'];

			if ($settings['brands'] || $settings['exclude_brands']) {
				foreach ($this->_brand->getAllOptions() as $brand) {
					$brands[$brand['value']] = $brand['label'];
				}
			}

			if ($settings['brands']) {
				foreach (explode(',', (string)$settings['brands']) as $id) {
					$params['brands'][] = $brands[$id];
				}
			}

			if ($settings['exclude_brands']) {
				foreach (explode(',', (string)$settings['exclude_brands']) as $id) {
					$params['exclude_brands'][] = $brands[$id];
				}
			}

			if ($settings['type'] == 'interesting') {
				if (isset($item)) {
					$params['item'] = $item;
				}

				if (isset($cart)) {
					$params['cart'] = $cart;
				}

				$settings['params'] = json_encode($params, true);
			} elseif ($settings['type'] == 'also_bought') {
				if (isset($item)) {
					$params['item'] = $item;

					if (isset($cart)) {
						$params['cart'] = $cart;
					}

					$settings['params'] = json_encode($params, true);
				}
			} elseif ($settings['type'] == 'similar') {
				if (isset($item) && isset($cart)) {
					$params['item'] = $item;
					$params['cart'] = $cart;

					$settings['params'] = json_encode($params, true);
				}
			} elseif ($settings['type'] == 'popular') {
				if (isset($category)) {
					$params['category'] = $category;
				}

				if (isset($cart)) {
					$params['cart'] = $cart;
				}

				$settings['params'] = json_encode($params, true);
			} elseif ($settings['type'] == 'see_also') {
				if (isset($cart)) {
					$params['cart'] = $cart;

					$settings['params'] = json_encode($params, true);
				}
			} elseif ($settings['type'] == 'recently_viewed') {
				$settings['params'] = json_encode($params, true);
			} elseif ($settings['type'] == 'buying_now') {
				if (isset($item)) {
					$params['item'] = $item;
				}

				if (isset($cart)) {
					$params['cart'] = $cart;
				}

				$settings['params'] = json_encode($params, true);
			} elseif ($settings['type'] == 'search') {
				if (isset($search_query)) {
					$params['search_query'] = $search_query;

					if (isset($cart)) {
						$params['cart'] = $cart;
					}

					$settings['params'] = json_encode($params, true);
				}
			} elseif ($settings['type'] == 'supply') {
				if (isset($item)) {
					$params['item'] = $item;
				}

				if (isset($cart)) {
					$params['cart'] = $cart;
				}

				$settings['params'] = json_encode($params, true);
			}

			$settings['module_id'] = md5(http_build_query($settings));

		    $this->setData('settings', $settings);
		    $this->setTemplate('widget/rees46.phtml');

		    return parent::_toHtml();
		}
	}
}
