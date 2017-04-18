<?php

namespace ORM\DB;

use Models\Run;
use Models\User;
use Providers\Mysql\FieldType\AbstractFieldType;

class RunsWrapper extends AbstractWrapper {
    public function getTableName() {
        return 'runs';
    }

    protected function getSchema() {
        return [
            'id' => ['method' => 'setId', 'type' => AbstractFieldType::TYPE_INT, 'primary' => true],
            'user_id' => ['method' => 'setUser', 'type' => AbstractFieldType::TYPE_INT],
            'date' => ['method' => 'setDate', 'type' => AbstractFieldType::TYPE_DATE],
            'duration' => ['method' => 'setDuration', 'type' => AbstractFieldType::TYPE_INT],
            'distance' => ['method' => 'setDistance', 'type' => AbstractFieldType::TYPE_INT]
        ];
    }

    public function factoryObject() {
        return new Run($this->app);
    }

    public function deleteByUserId($user_id) {
        $this->app->getMysql()->execute("DELETE FROM `{$this->getTableName()}` WHERE `user_id`=" . intval($user_id));
    }

    public function loadFiltered($user_id, $date_from = null, $date_to = null) {
        $q = $this->select($this->getAllFields())->where('user_id', '=', $user_id);
        if ($date_from) {
            if (is_string($date_from))
                $date_from = strtotime($date_from);
            if (is_numeric($date_from))
                $date_from = date('c', $date_from);
            $q->where('date', '>=', $date_from);
        }
        if ($date_to) {
            if (is_string($date_to))
                $date_to = strtotime($date_to);
            if (is_numeric($date_to))
                $date_to = date('c', $date_to);
            $q->where('date', '<=', $date_to);
        }
        $q->order('date', 'DESC');
        return $q->execute();

    }
}