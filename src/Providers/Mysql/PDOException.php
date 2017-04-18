<?php

namespace Providers\Mysql;

class PDOException extends \Exception {

    const ANSI_CODE_TABLE_NOT_FOUND = '42S02';
    const ANSI_CODE_NOT_CONNECTION = 'HY000';

    private $driverCode;
    private $ansiCode;
    private $errorMessage;
    private $sql;
    private $params;
    private $types;

    public function __construct($obj, $sql, $params, $types) {
        $info = $obj->errorInfo();

        $this->errorMessage = $info[2];
        $this->driverCode = $info[1];
        $this->ansiCode = $info[0];
        $this->sql = $sql;
        $this->params = $params;
        $this->types = $types;

        $message = "MysqlError\n";
        $message .= $this->errorMessage."(".$this->driverCode.", ".$this->ansiCode.")\n";
        $message .= "Query: ".$sql."\n";
        $message .= "Params: ".json_encode($params)."\n";
        $message .= "Types: ".json_encode($types);

        parent::__construct($message);
    }

    public function getDriverCode() {
        return $this->driverCode;
    }

    public function getAnsiCode() {
        return $this->ansiCode;
    }

    public function isTableNotFoundError() {
        return $this->ansiCode == self::ANSI_CODE_TABLE_NOT_FOUND;
    }

    public function isNotConnection() {
        return $this->ansiCode == self::ANSI_CODE_NOT_CONNECTION;
    }
} 