<?php

namespace Models;

use ORM\DB\AbstractWrapper;
use ORM\DB\Query\Field;
use Providers\Logger\LoggerProvider;
use Providers\Mysql\PDOProvider;
use Providers\Time\TimeProvider;
use Models\Application;

abstract class AbstractModel {
    protected $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function __get($name) {
        throw new \Exception('Unknown property "' . $name . '" in get ' . get_class($this));
    }

    public function __set($name, $value) {
        throw new \Exception('Unknown property "' . $name . '" set in ' . get_class($this));
    }

    public function __call($name, $arguments) {
        $rawProperty = preg_replace('/^set/', '', $name);
        $parts = [];
        $bigChar = '';
        foreach (preg_split('/([A-Z])/', $rawProperty, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
            if (preg_match('/[A-Z]/', $part)) {
                $bigChar = $part;
            } else {
                $parts[] = lcfirst($bigChar) . $part;
            }
        }
        $property = implode("_", array_diff($parts, ['']));

        if (property_exists($this, $property) && isset($arguments[0])) {
            $this->$property = $arguments[0];
        } else {
            throw new \Exception('Unknown method "' . $name . '" call in ' . get_class($this));
        }
    }


    /**
     * @return LoggerProvider
     */
    protected function getLogger() {
        return $this->app['logger'];
    }

    /**
     * @return PDOProvider
     */
    protected function getMysql() {
        return $this->app['mysql'];
    }

    /**
     * @return TimeProvider
     */
    protected function getTimeProvider() {
        return $this->app['time'];
    }


    protected function getHiddenProperties() {
        return ['app'];
    }

    public function getProperties($database = false) {
        $properties = [];
        foreach (get_object_vars($this) as $key => $value) {
            if (!in_array($key, $this->getHiddenProperties())) {
                if ($database && !in_array($key, $this->getWrapper()->getAllFields()))
                    continue;
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    public function setProperties($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && !in_array($key, $this->getHiddenProperties())) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function get($key) {
        if (property_exists($this, $key) && !in_array($key, $this->getHiddenProperties())) {
            return $this->$key;
        }
        return null;
    }

    // Redefine this for model
    public function getErrors() {
        return false;
    }

    // Redefine this for model
    abstract public function getWrapper();

    public function beforeSave() {
        return true;
    }

    public function save() {
        $this->beforeSave();
        /**
         * @var $wrapper AbstractWrapper
         */
        $wrapper = $this->getWrapper();
        $data = $this->getProperties(true);
        $primary = $wrapper->getPrimaryKeys();
        /**
         * @var $field Field
         */
        foreach ($primary as $index => $field)
            $primary[$index] = $field->getName();

        if (count($primary) && $this->get($primary[0])) {
            $wrapper->replace($data)->execute();
        } else {
            $wrapper->insert(array_diff_key($data, array_fill_keys(array_values($primary), 1)))->execute();
            if (in_array('id', $primary))
                $this->setProperties(['id' => (int)$this->getMysql()->getLastId()]);
        }
    }

    public function delete($key = 'id') {
        $wrapper = $this->getWrapper();
        if ($this->get($key)) {
            $wrapper->delete($this->get($key))->execute();
        }
    }
}
