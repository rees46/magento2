<?php
/**
 * Copyright © 2017 REES46, INC. All rights reserved.
 */
namespace Rees46\Personalization\Model;

class Track
{
    protected $_js = [];

    public function __construct() {

    }

    public function add($js)
    {
        $this->_js[] = $js;

        return $this;
    }

    public function getJS()
    {
        return $this->_js;
    }
}
