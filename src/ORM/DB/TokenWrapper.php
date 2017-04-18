<?php

namespace ORM\DB;

use Models\Token;
use Models\User;
use Providers\Mysql\FieldType\AbstractFieldType;

class TokenWrapper extends AbstractWrapper {

    const VALIDITY_TIME = 3600;

    public function getTableName() {
        return 'auth_token';
    }

    protected function getSchema() {
        return [
            'token' => ['method' => 'setToken', 'type' => AbstractFieldType::TYPE_TEXT, 'primary' => true],
            'user_id' => ['method' => 'setUser', 'type' => AbstractFieldType::TYPE_INT],
            'expires' => ['method' => 'setExpires', 'type' => AbstractFieldType::TYPE_DATETIME],
        ];
    }

    protected function factoryObject() {
        return new Token($this->app);
    }

    public function issueToken($user_id) {
        $token = $this->factoryObject();
        $token->setUser($user_id);
        $token->setExpires(time() + self::VALIDITY_TIME);
        $token->setToken(md5($user_id . time() . rand(0, 10000)));
        $this->clean($user_id);
        $token->save();
        return $token->get('token');
    }

    /**
     * @param $token_id
     * @return bool|User
     */
    public function useToken($token_id) {
        /**
         * @var $token Token
         */
        $token = $this->select($this->getAllFields())->where('token', '=', $token_id)->first();
        if (!$token)
            return false;
        $expires = strtotime($token->get('expires'));
        if ($expires < time()) {
            $this->delete($token_id);
            return false;
        }
        $this->clean($token->get('user_id'));
        $token->setExpires(time() + self::VALIDITY_TIME);
        if ($token->getErrors())
            return false;
        $token->save();
        $user = $this->app->getObjectCache()->getUserWrapper()->findById($token->get('user_id'));
        $user->setToken($token->get('token'));
        return $user;
    }

    public function clean($user_id) {
        $this->app->getMysql()->execute("DELETE FROM `" . $this->getTableName() . "` WHERE `user_id` = '" . intval($user_id) . "'");
    }

    public function cleanUp() {
        return $this->app->getMysql()->execute("DELETE FROM `" . $this->getTableName() . "` WHERE `expires`<NOW()");
    }


}