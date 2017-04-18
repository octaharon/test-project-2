<?php
namespace Models;

use Symfony\Component\Security\Core\User\UserInterface;
use Models\Application;

class User extends AbstractModel implements \Serializable {
    protected $id;
    protected $password;
    protected $email;
    protected $role;
    protected $token;


    const ROLE_USER = 1;
    const ROLE_MANAGER = 2;
    const ROLE_ADMIN = 4;

    public function __construct($app) {
        parent::__construct($app);
        $this->token = null;
        $this->role = self::ROLE_USER;
    }


    public function hasRole($role) {
        return ($this->role & $role) != 0;
    }

    public function isAdmin() {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isManager() {
        return $this->hasRole(self::ROLE_MANAGER);
    }

    public function beforeSave() {
        $this->encodePassword();
    }

    public function getWrapper() {
        return $this->app->getObjectCache()->getUserWrapper();
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setRole($role) {
        if (!in_array($role, [
            self::ROLE_USER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN
        ])
        ) {
            $this->role = intval($role);
            return $this;
        }
        $this->role |= $role;
        return $this;
    }

    public function revokeRole($role) {
        if (!in_array($role, [
            self::ROLE_USER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN
        ])
        )
            return $this;
        $this->role = $this->role & ~$role;
        return $this;
    }

    public function clearRole() {
        $this->role = self::ROLE_USER;
    }

    public function getRole() {
        return $this->role;
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function serialize() {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->role
        ]);
    }

    public function unserialize($serialized) {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->role
            ) = unserialize($serialized);
    }

    public function getApiData() {
        return [
            'token' => $this->token,
            'id' => $this->id,
            'email' => $this->email,
            'is_manager' => $this->isManager(),
            'is_admin' => $this->isAdmin()
        ];
    }

    public function getErrors() {
        $errors = [];

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) == false) {
            $errors['email'] = 'Invalid e-mail';
        }

        if (!$this->validatePassword()) {
            $errors['password'] = 'Password should contain 8 to 16 latin letters, numbers and symbols !@#$_+-';
        }

        $user = $this->getWrapper()->findByField('email', $this->email)->first();
        if ($user && $user->id != $this->id) {
            $errors['email'] = 'E-mail already taken';
        }

        return $errors;
    }


    public function generateRandomPassword($len = 10, $save = false) {
        $symbols = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'), ['-', '+', '!', '@', '#', '$', '_']);
        $password = '';
        for ($i = 0; $i < $len; $i++) {
            $password .= $symbols[array_rand($symbols)];
        }
        $this->password = $password;
        if ($save) {
            $this->encodePassword();
            $this->save();
        }
        return $password;
    }

    protected function isPasswordEncoded() {
        return (strlen($this->password) == 60 && substr($this->password, 0, 4) == '$2y$');
    }


    public function validatePassword() {
        if ($this->isPasswordEncoded())
            return true;
        return preg_match('/^[\w\-\+\!\@\#\$]{8,16}$/', $this->password);
    }

    public function encodePassword() {
        if ($this->isPasswordEncoded())
            return $this->password;
        return $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function checkPassword($match) {
        return password_verify($match, $this->password);
    }
}