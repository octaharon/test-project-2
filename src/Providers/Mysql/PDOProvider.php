<?php

namespace Providers\Mysql;

use Providers\Mysql\FieldType\AbstractFieldType;
use Providers\Mysql\FieldType\FieldTypeProvider;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * PDO provider
 */
class PDOProvider implements ServiceProviderInterface {

    /**
     * Application.
     *
     * @var Application
     */
    private $app;

    /**
     * PDO object.
     *
     * @var null|\PDO
     */
    private $pdo = null;

    private $isReconnected = false;

    public function getLastId() {
        return $this->getPDO()->lastInsertId();
    }

    private function checkConnection(PDOException $exception, $callback, $params = []) {
        if($exception->isNotConnection()) {
            $this->reconnect();
            if(!$this->isReconnected) {
                $this->isReconnected = true;
                $result = call_user_func_array($callback, $params);
                $this->isReconnected = false;
                return $result;
            }
        }

        $this->isReconnected = false;
        throw $exception;
    }

    public function reconnect() {
        $this->pdo = null;
    }

    public function startTransaction() {
        if (!$this->getPDO()->inTransaction()) {
            $success = $this->getPDO()->beginTransaction();
            $result = true;

            if(!$success) {
                $this->checkConnection(new PDOException($this->getPDO(), 'START TRANSACTION', [], []), [$this, 'startTransaction']);
            }
        } else {
            $result = false;
        }

        return $result;
    }

    public function commit() {

        if ($this->getPDO()->inTransaction()) {
            $this->getPDO()->commit();
        }
    }

    public function rollBack() {
        if ($this->getPDO()->inTransaction()) {
            $this->getPDO()->rollBack();
        }
    }

    private function getConvertedParams(array $params, array $types, &$sql) {

        $convertedParams = array();
        $pdoTypes = array();
        $skip = 0;

        foreach  ($params as $key => $value) {
            /**
             * @var $type AbstractFieldType
             */
            $type = $this->app['mysql.field_type_provider']->getFieldType(null);

            if (array_key_exists($key, $types)) {
                $type = $this->app['mysql.field_type_provider']->getFieldType($types[$key]);
            }

            $pdoType = $type->getPDOType($value);
            if ($pdoType === false) {

                if (is_int($key)) {
                    $key = $key - $skip;
                    $sqlArray = explode('?', $sql);
                    $sql = implode ('?', array_slice($sqlArray, 0, $key + 1));
                    $sql .= $type->convertToDB($value);
                    $sql .= implode ('?', array_slice($sqlArray, $key + 1, sizeof($sqlArray) - $key - 1));
                    $skip++;
                } else {
                    $sql = str_replace(':'.$key, $type->convertToDB($value), $sql);
                }

            } else {
                if (is_int($key)) {
                    $convertedParams[$key-$skip] = $type->convertToDB($value);
                } else {
                    $convertedParams[$key] = $type->convertToDB($value);
                }
                $pdoTypes[$key] = $pdoType;
            }
        }

        return [$convertedParams, $pdoTypes];
    }

    public function getHash($sql, $params = array(), $types = array()) {
        $convertedParams = $this->getConvertedParams($params, $types, $sql);

        $json = [
            'sql' => $sql,
            'params' => $convertedParams[0]
        ];
        return md5(json_encode($json));
    }

    /**
     * Execute pdo query.
     *
     * @param string $sql Sql query.
     * @param array $params Params.
     * @param array $types Types.
     *
     * @throws PDOException
     *
     * @return PDOResult
     */
    public function execute($sql, $params = array(), $types = array()) {

        list($convertedParams, $pdoTypes) = $this->getConvertedParams($params, $types, $sql);

        $statement = $this->getPDO()->prepare($sql);

        foreach($convertedParams as $key => $value) {

            $statement->bindValue(
                is_int($key) ? ($key+1) : ':'.$key,
                $value,
                $pdoTypes[$key]
            );
        }


        $result = $statement->execute();

        if (!$result) {
            $result = $this->checkConnection(new PDOException($statement, $sql, $params, $types), [$this, 'execute'], [$sql, $params, $types]);
            return $result;
        }

        return new PDOResult($statement, $this->app);
    }

    /**
     * Execute query and fetch all rows.
     *
     * @param string $sql         Sql query.
     * @param array  $params      Params.
     * @param array  $types       Types.
     * @param array  $resultTypes Result types.
     *
     * @return array
     */
    public function fetchAll($sql, $params = array(), $types = array(), $resultTypes = array()) {
        $result = $this->execute($sql, $params, $types);
        return $result->fetchAll($resultTypes);
    }

    /**
     * Get PDO.
     *
     * @return \PDO
     */
    private function getPDO() {
        if ($this->pdo === null) {
            $dsn = 'mysql:dbname='.$this->app['config']['mysql']['baseName'].';host='.$this->app['config']['mysql']['host'].';charset=UTF8';
            $this->pdo = new \PDO($dsn, $this->app['config']['mysql']['user'], $this->app['config']['mysql']['password']);
        }

        return $this->pdo;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['mysql'] = $this;
        $this->app = $app;
        $app->register(new FieldTypeProvider());
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}