<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Helper;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_config;
    protected $_curl;
    protected $_data;
    protected $_logger;

	public function __construct(
        \Rees46\Personalization\Helper\Config $config,
        \Rees46\Personalization\Helper\Curl $curl,
        \Rees46\Personalization\Helper\Data $data,
        \Rees46\Personalization\Helper\Logger $logger
	)
	{
        $this->_config = $config;
        $this->_curl = $curl;
        $this->_data = $data;
        $this->_logger = $logger;
	}

    public function rees46ShopCategories()
    {
        $categories = array();

        $return = $this->_curl->query('GET', 'https://rees46.com/api/categories');

        if (isset($return['result'])) {
            $categories = $return['result'];
        }

        return json_decode($categories, true);
    }

    public function rees46LeadTracking()
    {
        if (!$this->_config->isLeadTracking()) {
            $url = 'https://rees46.com/trackcms/magento?';

            $params = array(
                'website' => $this->_data->getStoreUrl(),
                'email' => $this->_data->getUserEmail(),
                'first_name' => $this->_data->getUserFirstName(),
                'last_name' => $this->_data->getUserLastName(),
                'phone' => $this->_data->getStorePhone(),
                'city' => $this->_data->getStoreCity(),
                'country' => $this->_data->getStoreCountry(),
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_URL, $url . http_build_query($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_exec($ch);
            curl_close($ch);

            $this->_config->setValue('rees46/actions/action_lead', true);
        }
    }

    public function rees46UserRegister($params = array())
    {
        $json = array();

        if ($params['category'] == '') {
            $json['error'] = __('Incorrect value for Product Category field.');
        }

        if ($params['country_code'] == '') {
            $json['error'] = __('Incorrect value for Country field.');
        }

        if ($params['last_name'] == '') {
            $json['error'] = __('Incorrect value for Last Name field.');
        }

        if ($params['first_name'] == '') {
            $json['error'] = __('Incorrect value for First Name field.');
        }

        if ($params['phone'] == '') {
            $json['error'] = __('Incorrect value for Phone Number field.');
        }

        if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL) || $params['email'] == '') {
            $json['error'] = __('Incorrect value for Email field.');
        }

        if (isset($json['error'])) {
            return $json;
        }

        $curl_data = array();

        $curl_data['email'] = $params['email'];
        $curl_data['phone'] = $params['phone'];
        $curl_data['first_name'] = $params['first_name'];
        $curl_data['last_name'] = $params['last_name'];
        $curl_data['country_code'] = $params['country_code'];

        $return = $this->_curl->query('POST', 'https://rees46.com/api/customers', json_encode($curl_data));

        $result = json_decode($return['result'], true);

        if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
            $json['error'] = __('Could not register an account. Please, check the form was filled out correctly.');

            $this->_logger->log('REES46: could not register user (' . $return['info']['http_code'] . ').');
        } else {
            if (isset($result['duplicate'])) {
                $json['error'] = __('Account already exists. Please, authorize.');
            } else {
                $this->_config->setValue('rees46/general/api_key', $result['api_key']);
                $this->_config->setValue('rees46/general/api_secret', $result['api_secret']);
                $this->_config->setValue('rees46/general/api_category', $params['category']);

                $json['success'] = __('Account successfully registered.');
            }
        }

        return $json;
    }

    public function rees46ShopRegister($params = array())
    {
        $json = array();

        if ($this->_config->getValue('rees46/general/api_key') == '') {
            $json['error'] = __('Incorrect value for API Key.');
        }

        if ($this->_config->getValue('rees46/general/api_secret') == '') {
            $json['error'] = __('Incorrect value for API Secret.');
        }

        if ($this->_config->getValue('rees46/general/api_category') == '') {
            $json['error'] = __('Incorrect value for Product Category.');
        }

        if (isset($json['error'])) {
            return $json;
        }

        $curl_data = array();

        $curl_data['api_key'] = $this->_config->getValue('rees46/general/api_key');
        $curl_data['api_secret'] = $this->_config->getValue('rees46/general/api_secret');
        $curl_data['url'] = $this->_data->getStoreUrl();
        $curl_data['name'] = $this->_data->getStoreName();
        $curl_data['category'] = $this->_config->getValue('rees46/general/api_category');
        $curl_data['yml_file_url'] = $this->_data->getStoreUrl() . 'xml';
        $curl_data['cms_id'] = 10;

        $return = $this->_curl->query('POST', 'https://rees46.com/api/shops', json_encode($curl_data));

        $result = json_decode($return['result'], true);

        if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
            $json['error'] = __('Could not create a store.');

            $this->_logger->log('REES46: could not create a store (' . $return['info']['http_code'] . ').');
        } else {
            $this->_config->setValue('rees46/general/store_key', $result['shop_key']);
            $this->_config->setValue('rees46/general/secret_key', $result['shop_secret']);

            $json['success'] = __('Store successfully created.');
        }

        return $json;
    }

    public function rees46ShopXML($params = array())
    {
        $json = array();

        $curl_data = array();

        if ($this->_config->getValue('rees46/general/secret_key') != '') {
            $curl_data['store_secret'] = $this->_config->getValue('rees46/general/secret_key');
        } elseif ($params['secret_key'] != '') {
            $curl_data['store_secret'] = $params['secret_key'];
        } else {
            $json['error'] = __('Incorrect value for Secret Key field.');
        }

        if ($this->_config->getValue('rees46/general/store_key') != '') {
            $curl_data['store_key'] = $this->_config->getValue('rees46/general/store_key');
        } elseif ($params['store_key'] != '') {
            $curl_data['store_key'] = $params['store_key'];
        } else {
            $json['error'] = __('Incorrect value for Store Key field.');
        }

        if (isset($json['error'])) {
            return $json;
        }

        $curl_data['yml_file_url'] = $this->_data->getStoreUrl() . 'rees46/feed/xml';

        $return = $this->_curl->query('PUT', 'https://rees46.com/api/shop/set_yml', json_encode($curl_data));

        if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
            $json['error'] = __('Could not export product feed.');

            $this->_logger->log('REES46: export xml (' . $return['info']['http_code'] . ').');
        } else {
            $this->_config->setValue('rees46/actions/action_auth', true);
            $this->_config->setValue('rees46/actions/action_xml', true);
            $this->_config->setValue('rees46/general/store_key', $curl_data['store_key']);
            $this->_config->setValue('rees46/general/secret_key', $curl_data['store_secret']);

            $json['success'] = __('Product feed successfully exported to REES46.');
        }

        return $json;
    }

    public function rees46ShopOrders($params = array())
    {
        $json = array();

        $curl_data = array();

        if ($this->_config->getValue('rees46/general/secret_key') != '') {
            $curl_data['shop_secret'] = $this->_config->getValue('rees46/general/secret_key');
        } else {
            $json['error'] = __('Incorrect value for Secret Key field.');
        }

        if ($this->_config->getValue('rees46/general/store_key') != '') {
            $curl_data['shop_id'] = $this->_config->getValue('rees46/general/store_key');
        } else {
            $json['error'] = __('Incorrect value for Store Key field.');
        }

        if (isset($json['error'])) {
            return $json;
        }

        $next = $params['next'];

        $limit = 1000;

        $filter_data = array(
            'start' => ($next - 1) * $limit,
            'limit' => $limit,
        );

        if ($filter_data['start'] < 0) {
            $filter_data['start'] = 0;
        }

        $results = $this->_data->getOrders($filter_data);

        $results_total = $results->getSize();

        $export_data = array();

        if ($results->count() > 0) {
            foreach ($results as $result) {
                $order_products = array();

                foreach ($result->getAllVisibleItems() as $product) {
                    $order_products[] = array(
                        'id' => $product->getProductId(),
                        'price' => (double)$product->getBasePrice(),
                        'categories' => $this->_data->getProductCategories($product->getProductId()),
                        'is_available' => $this->_data->getProductStock($product->getProductId()),
                        'amount' => (int)$product->getQtyOrdered(),
                    );
                }

                $export_data[] = array(
                    'id' => $result->getId(),
                    'user_id' => $result->getCustomerId(),
                    'user_email' => $result->getCustomerEmail(),
                    'date' => $result->getCreatedAt(),
                    'items' => $order_products,
                );
            }

            $curl_data['orders'] = $export_data;

            $return = $this->_curl->query('POST', 'http://api.rees46.com/import/orders', json_encode($curl_data));
 
            if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                $json['error'] = __('Could not export orders.');

                $this->_logger->log('REES46: export orders (' . $return['info']['http_code'] . ').');
            } else {
                $this->_config->setValue('rees46/actions/action_order', true);

                if ($results_total > $next * $limit) {
                    $json['next'] = $next + 1;

                    $json['success'] = sprintf(__('%s out of %s orders successfully exported to REES46.'), $next * $limit, $results_total);
                } else {
                    $json['success'] = sprintf(__('%s orders successfully exported to REES46.'), $results_total);
                }
            }
        } else {
            $this->_config->setValue('rees46/actions/action_order', true);

            $json['success'] = __('No available orders for export.');
        }

        return $json;
    }

    public function rees46ShopCustomers($params = array())
    {
        $json = array();

        $curl_data = array();

        if ($this->_config->getValue('rees46/general/secret_key') != '') {
            $curl_data['shop_secret'] = $this->_config->getValue('rees46/general/secret_key');
        } else {
            $json['error'] = __('Incorrect value for Secret Key field.');
        }

        if ($this->_config->getValue('rees46/general/store_key') != '') {
            $curl_data['shop_id'] = $this->_config->getValue('rees46/general/store_key');
        } else {
            $json['error'] = __('Incorrect value for Store Key field.');
        }

        if (isset($json['error'])) {
            return $json;
        }

        $next = $params['next'];

        $limit = 1000;

        $filter_data = array(
            'start' => ($next - 1) * $limit,
            'limit' => $limit,
        );

        if ($filter_data['start'] < 0) {
            $filter_data['start'] = 0;
        }

        $results = $this->_data->getCustomers($filter_data);

        $results_total = $results->getSize();

        $export_data = array();

        if ($results->count() > 0) {
            foreach ($results as $result) {
                $export_data[] = array(
                    'id' => $result->getId(),
                    'email' => $result->getEmail(),
                );
            }

            $curl_data['audience'] = $export_data;

            $return = $this->_curl->query('POST', 'http://api.rees46.com/import/audience', json_encode($curl_data));

            if ($return['info']['http_code'] < 200 || $return['info']['http_code'] >= 300) {
                $json['error'] = __('Could not export customers.');

                $this->_logger->log('REES46: export customers (' . $return['info']['http_code'] . ').');
            } else {
                $this->_config->setValue('rees46/actions/action_customer', true);

                if ($results_total > $next * $limit) {
                    $json['next'] = $next + 1;

                    $json['success'] = sprintf(__('%s out of %s customers successfully exported to REES46.'), $next * $limit, $results_total);
                } else {
                    $json['success'] = sprintf(__('%s customers successfully exported to REES46.'), $results_total);
                }
            }
        } else {
            $this->_config->setValue('rees46/actions/action_customer', true);

            $json['success'] = __('No available customers for export.');
        }

        return $json;
    }

    public function rees46ShopFiles()
    {
        $json = array();

        $dir = $this->_data->getRootDir() . '/';

        $files = array(
            'manifest.json',
            'push_sw.js'
        );

        foreach ($files as $key => $file) {
            if (!is_file($dir . $file)) {
                $ch = curl_init();

                $url = 'https://raw.githubusercontent.com/rees46/web-push-files/master/' . $file;

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                if ($info['http_code'] < 200 || $info['http_code'] >= 300) {
                    $this->_logger->log('REES46: could not load ' . $file . ' (' . $info['http_code'] . ')');
                } else {
                    file_put_contents($dir . $file, $result);
                }
            }

            if (is_file($dir . $file)) {
                if ($file == 'manifest.json') {
                    $this->_config->setValue('rees46/actions/action_file1', true);
                } elseif ($file == 'push_sw.js') {
                    $this->_config->setValue('rees46/actions/action_file2', true);
                }

                $json['success'][$key] = sprintf(__('%s successfully loaded.'), $file);
            } else {
                $json['error'][$key] = sprintf(__('Could not load %s.'), $file);
            }
        }

        return $json;
    }

    public function rees46ShopFinish()
    {
        $url = 'https://rees46.com/api/customers/login';
        $api_key = $this->_config->getValue('rees46/general/api_key');
        $api_secret = $this->_config->getValue('rees46/general/api_secret');

        $json  = '<form action="' . $url . '" method="post" id="submitShopFinish" target="_blank">';
        $json .= '<input type="hidden" name="api_key" value="' . $api_key . '">';
        $json .= '<input type="hidden" name="api_secret" value="' . $api_secret . '">';
        $json .= '</form>';

        return $json;
    }

    public function rees46DisableProduct($product_id)
    {
        $curl_data['shop_id'] = $this->_config->getValue('rees46/general/store_key');
        $curl_data['shop_secret'] = $this->_config->getValue('rees46/general/secret_key');
        $curl_data['item_ids'] = $product_id;

        $return = $this->_curl->query('POST', 'http://api.rees46.com/import/disable', json_encode($curl_data));

        $this->_logger->log('REES46: Excluded of recomended product_id [' . $product_id . '] (' . $return['info']['http_code'] . ').');
    }

    public function rees46SyncOrders($order_data, $order_status_id)
    {
        $curl_data['shop_id'] = $this->_config->getValue('rees46/general/store_key');
        $curl_data['shop_secret'] = $this->_config->getValue('rees46/general/secret_key');
        $curl_data['orders'] = $order_data;

        $return = $this->_curl->query('POST', 'http://api.rees46.com/import/sync_orders', json_encode($curl_data));

        $this->_logger->log('REES46: autoexport status [' . $order_status_id . '] of order_id [' . $order_data['id'] . '] (' . $return['info']['http_code'] . ').');
    }
}
