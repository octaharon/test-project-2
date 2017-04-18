<?php

namespace ORM\DB;

use Models\User;
use Providers\Mysql\FieldType\AbstractFieldType;

class UserWrapper extends AbstractWrapper {
    public function getTableName() {
        return 'users';
    }

    protected function getSchema() {
        return [
            'id' => ['method' => 'setId', 'type' => AbstractFieldType::TYPE_INT, 'primary' => true],
            'password' => ['method' => 'setPassword'],
            'email' => ['method' => 'setEmail'],
            'role' => ['method' => 'setRole']
        ];
    }

    public function factoryObject() {
        return new User($this->app);
    }

    /**
     * @param $fields
     * @return array|User
     */
    public function signup($fields) {
        $user = new User($this->app);
        $user->setProperties($fields);

        $errors = $user->getErrors();
        if (!$errors) {
            $user->encodePassword();
            $user->save();

            $subject = 'Successfully registered on ' . $this->app->getRequest()->getHost();
            $text = 'Welcome, stranger!';
            @$mailResult = $this->app['mail']->sendMail($user->get('email'), $subject, $text);
            if (!$mailResult)
                return [
                    'email' => "Can't send mail"
                ];
            return $user;
        }


        return $errors;
    }

    public function restorePassword($email) {
        /**
         * @var $user User
         */
        $user = $this->findByField('email', $email)->first();

        if (!$user) {
            return ['success' => false, 'errors' => 'E-mail not found'];
        }

        $subject = 'New password for account on ' . $this->app->getRequest()->getHost();
        $text = 'Your new password is ' . $user->generateRandomPassword(10, true);
        @$mailResult = $this->app['mail']->sendMail($user->get('email'), $subject, $text);
        if (!$mailResult)
            return [
                'email' => "Can't send mail"
            ];

        return ['success' => true];
    }
}