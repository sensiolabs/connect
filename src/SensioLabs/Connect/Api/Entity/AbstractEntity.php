<?php

/*
 * This file is part of the SensioLabs Connect package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Connect\Api\Entity;

use SensioLabs\Connect\Api\Api;
use SensioLabs\Connect\Api\Model\Form;
use Symfony\Component\Form\Util\PropertyPath;

/**
 * AbstractEntity.
 *
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
abstract class AbstractEntity implements \ArrayAccess
{
    private $selfUrl;
    private $alternateUrl;
    private $properties;
    private $forms;
    private $api;

    public function __construct($selfUrl = null, $alternateUrl = null)
    {
        $this->selfUrl = $selfUrl;
        $this->alternateUrl = $alternateUrl;
        $this->properties = array();
        $this->forms = array();
        $this->configure();
    }

    public function setApi(Api $api)
    {
        $this->api = $api;
    }

    public function getApi()
    {
        return $this->api;
    }

    public function refresh()
    {
        $response = $this->getApi()->get($this->selfUrl);
        $fresh = $response['entity'];
        foreach ($this->properties as $key => $property) {
            $this->set($key, $fresh->get($key));
        }

        $this->setForms($fresh->getForms());

        return $this;
    }

    public function __toString()
    {
        return $this->selfUrl;
    }

    public function addProperty($name, $default = null)
    {
        $this->properties[$name] = $default;

        return $this;
    }

    public function setForms($forms)
    {
        $this->forms = $forms;
    }

    public function addForm($formId, Form $form)
    {
        $this->forms[$formId] = $form;
    }

    public function getForm($formId)
    {
        return $this->forms[$formId];
    }
    
    public function getForms()
    {
        return $this->forms;
    }

    public function submit($formId, AbstractEntity $entity = null)
    {
        $form = $this->forms[$formId];
        $fields = $form->getFields();

        if (null === $entity) {
            $entity = $this;
        }
        
        foreach ($fields as $key => $value) {
            if ($entity->has($key)) {
                $fields[$key] = $entity->get($key);
            }
        }

        $response = $this->api->submit($form->getAction(), $form->getMethod(), $fields);

        return $response;
    }

    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));

        if ('set' === $method) {
            if (!array_key_exists(0, $arguments)) {
                throw new \LogicException(sprintf('Please provide a value to set %s with', $name));
            }
            $this->set($property, $arguments[0]);
        } elseif ('get' === $method) {
            return $this->get($property);
        } elseif ('add' === $method) {
            $this->add($property, $arguments[0]);
        }
    }

    public function set($property, $value)
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new \LogicException(sprintf('Property %s is not present in instance of "%s".', $property, get_class($this)));
        }

        $this->properties[$property] = $value;
    }

    public function get($property)
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new \LogicException(sprintf('Property %s is not present in instance of "%s".', $property, get_class($this)));
        }

        return $this->properties[$property];
    }

    public function has($property)
    {
        return array_key_exists($property, $this->properties);
    }

    public function add($property, $value)
    {
        if (!array_key_exists($property, $this->properties)) {
            throw new \LogicException(sprintf('Property "%s" is not present in instance of "%s".', $property, get_class($this)));
        }

        $this->properties[$property][] = $value;
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->properties);
    }

    public function offsetGet($index)
    {
        return $this->get($index);
    }

    public function offsetSet($index, $value)
    {
        $this->set($index, $value);
    }

    public function offsetUnset($index)
    {
        throw new \BadMethodCallException('Not available.');
    }

    public function getAlternateUrl()
    {
        return $this->alternateUrl;
    }

    public function getSelfUrl()
    {
        return $this->selfUrl;
    }

    abstract protected function configure();
}

