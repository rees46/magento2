<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Helper;

class Curl extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function query($type, $url, $params = null)
    {
        $data = array();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_URL, $url);

        if (isset($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $data['result'] = curl_exec($ch);
        $data['info'] = curl_getinfo($ch);

        curl_close($ch);

        return $data;
    }
}
