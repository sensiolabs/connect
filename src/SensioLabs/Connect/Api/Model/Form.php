<?php

/*
 * This file is part of the SensioLabs Connect package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Connect\Api\Model;

/**
 * Form.
 *
 * @author Julien Galenski <julien.galenski@sensio.com>
 */
class Form
{
    private $action;
    private $method;
    private $fields;

    public function __construct($action, $method, $fields = array())
    {
        $this->action = $action;
        $this->method = $method;
        $this->fields = $fields;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }
    
    public function getAction()
    {
        return $this->action;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }
    
    public function getMethod()
    {
        return $this->method;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function addField($key, $value)
    {
        $this->fields[$key] = $value;
    }

    public function getField($key)
    {
        return $this->fields[$key];
    }

    public function getFields()
    {
        return $this->fields;
    }
}