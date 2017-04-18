<?php
namespace Models;

use Models\Application;

class Run extends AbstractModel {
    protected $id;
    protected $user_id;
    protected $date;
    protected $duration;
    protected $distance;
    protected $speed;

    public function __construct($app) {
        parent::__construct($app);
    }

    public function beforeSave() {
        if (is_numeric($this->date))
            $this->date = date('c', $this->date);
    }

    public function getWrapper() {
        return $this->app->getObjectCache()->getRunsWrapper();
    }


    public function setUser($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    public function setDuration($duration) {
        $this->duration = intval($duration);
        if ($duration > 0)
            $this->speed = $this->distance / $this->duration;
    }

    public function setDistance($distance) {
        $this->distance = intval($distance);
        if ($this->duration > 0)
            $this->speed = $this->distance / $this->duration;
    }

    /**
     * @param $date String|\DateTime|Number
     * @return $this
     */
    public function setDate($date) {
        $this->date = $date;
        if (is_a($this->date, 'DateTime'))
            $this->date = $date->getTimestamp();
        if (is_numeric($this->date))
            $this->date = date('c', $this->date);
        return $this;
    }


    public function getErrors() {
        $errors = [];

        $user = $this->app->getObjectCache()->getUserWrapper()->findById($this->user_id);
        if (!$user) {
            $errors['user_id'] = 'User not found';
        }

        if ($this->distance <= 0) {
            $errors['distance'] = 'Distance must be a positive integer';
        }

        if ($this->duration <= 0) {
            $errors['duration'] = 'Duration must be a positive integer';
        }

        if (($this->distance > 560000) || ($this->duration > 290000) || ($this->speed > 12.5)) {
            $errors['speed'] = "Either you've made a mistake entering the data or you should contact Guiness world record committee";
        }

        return $errors;
    }

}