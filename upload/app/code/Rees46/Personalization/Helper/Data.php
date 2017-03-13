<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
	protected $_store;
    protected $_storeInfo;
    protected $_auth;
    protected $_orders;
    protected $_product;
    protected $_products;
    protected $_image;
    protected $_stock;
    protected $_customer;
    protected $_currency;
    protected $_category;
    protected $_directory;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Backend\Model\Auth\Session $auth,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orders,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $products,
        \Magento\Catalog\Helper\Image $image,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stock,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customer,
        \Magento\Directory\Model\CurrencyFactory $currency,
        \Magento\Catalog\Helper\Category $category,
        \Magento\Framework\App\Filesystem\DirectoryList $directory
	)
	{
        $this->_objectManager = $objectManager;
		$this->_store = $store;
        $this->_storeInfo = $storeInfo;
        $this->_auth = $auth;
        $this->_orders = $orders;
        $this->_product = $product;
        $this->_products = $products;
        $this->_image = $image;
        $this->_stock = $stock;
        $this->_customer = $customer;
        $this->_currency = $currency;
        $this->_category = $category;
        $this->_directory = $directory;

		parent::__construct($context);
	}

    public function getVersion()
    {
        return $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
    }

    public function store()
    {
        return $this->_store->getStore();
    }

    public function getStoreId()
    {
        return $this->store()->getId();
    }

    public function getStoreUrl()
    {
        return $this->store()->getBaseUrl();
    }

    public function getStoreName()
    {
        return $this->_storeInfo->getStoreInformationObject($this->store())->getName();
    }

    public function getStorePhone()
    {
        return $this->_storeInfo->getStoreInformationObject($this->store())->getPhone();
    }

    public function getStoreCity()
    {
        return $this->_storeInfo->getStoreInformationObject($this->store())->getCity();
    }

    public function getStoreCountry()
    {
        return $this->_storeInfo->getStoreInformationObject($this->store())->getCountry();
    }

    public function getStoreCountryCode()
    {
        return $this->_storeInfo->getStoreInformationObject($this->store())->getCountryId();
    }

    public function getStoreCurrency()
    {
        return $this->store()->getCurrentCurrencyCode();
    }

    public function getStoreCurrencyRate()
    {
        return $this->store()->getCurrentCurrencyRate();
    }

    public function getCurrencies($skipBaseNotAllowed = false)
    {
        return $this->_currency->create()->getCurrencyRates($this->getStoreCurrency(), $this->store()->getAvailableCurrencyCodes($skipBaseNotAllowed));
    }

    public function getUserEmail()
    {
        return $this->_auth->getUser()->getEmail();
    }

    public function getUserFirstName()
    {
        return $this->_auth->getUser()->getFirstName();
    }

    public function getUserLastName()
    {
        return $this->_auth->getUser()->getLastName();
    }

    public function getOrders($data = array())
    {
        $orders = $this->_orders->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('customer_id')
            ->addAttributeToSelect('customer_email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToFilter('created_at', array('gt' => date('Y-m-d H:i:s', strtotime('-6 months'))))
            ->addAttributeToSort('entity_id', 'ASC');

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 1;
            }

            $orders->setCurPage($data['start'])->setPageSize($data['limit']);
        }

        return $orders;
    }

    public function getProducts($limit = 0, $start = 0)
    {
        $products = array();

        $collection = $this->_products->create()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addAttributeToSelect('manufacturer')
            ->addAttributeToSelect('image')
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addStoreFilter($this->getStoreId())
            ->addUrlRewrite()
            ->addFinalPrice()
            ->addAttributeToSort('entity_id', 'ASC')
            ->setCurPage($start)
            ->setPageSize($limit);

        foreach ($collection as $product) {
            $products[] = array(
                'id' => $product->getId(),
                'available' => $this->getProductStock($product->getId(), $this->getStoreId()),
                'url' => $product->getProductUrl(),
                'price' => $product->getFinalPrice(),
                'categories' => $product->getCategoryIds(),
                'image' => $this->_image->init($product, 'category_page_list')->setImageFile($product->getImage())->getUrl(),
                'name' => $product->getName(),
                'manufacturer' => $product->getAttributeText('manufacturer'),
                'model' => $product->getSku(),
                'description' => $product->getDescription(),
            );
        }

        return $products;
    }

    public function getProductCategories($productId = null)
    {
        return $this->_product->load($productId)->getCategoryIds();
    }

    public function getProductStock($productId = null, $storeId = null)
    {
        $stock = $this->_stock->getStockItem($productId, $storeId)->getIsInStock();

        if (!$stock) {
            $stock = 0;
        }

        return $stock;
    }

    public function getCustomers($data = array())
    {
        $customers = $this->_customer->create()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('email')
            ->addAttributeToSort('entity_id', 'ASC');

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 1;
            }

            $customers->setCurPage($data['start'])->setPageSize($data['limit']);
        }

        return $customers;
    }

    public function getCategories()
    {
        $categories = array();

        $collection = $this->_category->getStoreCategories(false, true, true);

        foreach ($collection as $category) {
            $categories[] = array(
                'category_id' => $category->getId(),
                'parent_id' => $category->getParentId(),
                'name' => $category->getName(),
            );
        }

        return $categories;
    }

    public function getRootDir()
    {
        return $this->_directory->getRoot();
    }

    public function getDir($path = null)
    {
        return $this->_directory->getPath($path);
    }
}
