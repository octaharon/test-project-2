<?php

namespace Providers\Mysql;
use Providers\Mysql\FieldType\AbstractFieldType;
use Silex\Application;


/**
 * PDOStatment wrapper.
 */
class PDOResult {

    /**
     * Statement.
     *
     * @var \PDOStatement
     */
    private $statement;

    private $app;

    public function __construct(\PDOStatement $statement, Application $app) {

        $this->statement = $statement;
        $this->app = $app;

    }

    /**
     * @param $key
     * @param $types
     *
     * @return AbstractFieldType
     */
    private function getType($key, $types) {

        $type = $this->app['mysql.field_type_provider']->getFieldType(null);

        if (array_key_exists($key, $types)) {
            $type = $this->app['mysql.field_type_provider']->getFieldType($types[$key]);
        }

        return $type;

    }

    public function rawFetch() {
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function convertRawRow(array $row, array $types = []) {
        foreach ($row as $key => $value) {
            $type = $this->getType($key, $types);
            $row[$key] = $type->convertFromDB($value);
        }
        return $row;
    }

    /**
     * Fetch next row.
     *
     * @param array $types
     *
     * @return array
     */
    public function fetch(array $types = array()) {

        $result = $this->rawFetch();

        if ($result == false) {
            return false;
        }

        return $this->convertRawRow($result, $types);
    }

    /**
     * Fetch all rows.
     *
     * @param $types
     *
     * @return array
     */
    public function fetchAll(array $types = array()) {
        $result = $this->statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $rowKey => $row) {
            $result[$rowKey] = $this->convertRawRow($row, $types);
        }

        return $result;
    }

    /**
     * Get row count.
     *
     * @return integer
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }

} 