<?php
namespace Models;

use Symfony\Component\Security\Core\User\UserInterface;
use Models\Application;

class Token extends AbstractModel {
    protected $token;
    protected $user_id;
    protected $expires;

    public function __construct($app) {
        parent::__construct($app);
    }

    public function beforeSave() {
        if (is_numeric($this->expires))
            $this->expires = date('c', $this->expires);
    }

    public function getWrapper() {
        return $this->app->getObjectCache()->getTokenWrapper();
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function setUser($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @param $expires String|\DateTime|Number
     * @return $this
     */
    public function setExpires($expires) {
        $this->expires = $expires;
        if (is_a($expires,'DateTime'))
            $this->expires=$expires->getTimestamp();
        if (is_numeric($this->expires))
            $this->expires = date('c', $this->expires);
        return $this;
    }


    public function getErrors() {
        $errors = [];

        $user = $this->app->getObjectCache()->getUserWrapper()->findById($this->user_id);
        if (!$user) {
            $errors['user_id'] = 'User not found';
        }

        if (strtotime($this->expires) < time()) {
            $errors['expires'] = 'Token already expired';
        }

        return $errors;
    }

}