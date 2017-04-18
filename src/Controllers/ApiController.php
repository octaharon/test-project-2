<?php

namespace Controllers;

use Models\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Models\Application;

class ApiController {

    public function loginAction(Application $app) {
        /**
         * @var $user User
         */

        $app->getObjectCache()->getTokenWrapper()->cleanUp();

        if ($app->getRequest()->get('token')) {
            $token = $app->getRequest()->get('token');
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($token);
            if ($user == false)
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'token' => 'Invalid token'
                    ]
                ]);
            return new JsonResponse([
                'success' => true,
                'user' => $user->getApiData()
            ]);
        }
        $email = $app->getRequest()->get('email');
        $user = $app->getObjectCache()->getUserWrapper()->findByField('email', $email)->first();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'email' => 'User not found'
                ]
            ]);
        }
        if (!($user->checkPassword($app->getRequest()->get('password')))) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'password' => 'Invalid password'
                ]
            ]);
        }
        $user->setToken($app->getObjectCache()->getTokenWrapper()->issueToken($user->get('id')));
        return new JsonResponse([
            'success' => true,
            'user' => $user->getApiData()
        ]);
    }

    public function signupAction(Application $app) {

        $result = $app->getObjectCache()->getUserWrapper()->signup([
            'email' => $app->getRequest()->get('email'),
            'password' => $app->getRequest()->get('password')
        ]);

        if (is_array($result)) {
            return new JsonResponse([
                'errors' => $result,
                'success' => false
            ]);
        }

        return new JsonResponse([
            'email' => $result->get('email'),
            'success' => true
        ]);
    }

    public function reminderAction(Application $app) {
        return new JsonResponse($app->getObjectCache()->getUserWrapper()->restorePassword($app->getRequest()->get('email')));
    }

    public function logoutAction(Application $app) {
        if ($app->getRequest()->get('token')) {
            $token = $app->getRequest()->get('token');
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($token);
            if ($user == false)
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'token' => 'Invalid token'
                    ]
                ]);
            $app->getObjectCache()->getTokenWrapper()->clean($user->get('id'));
            $app->getSessionProvider()->invalidate();
            return new JsonResponse([
                'success' => true,
            ]);
        }
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'token' => 'Invalid token'
            ]
        ]);
    }

    public function readUsersAction(Application $app) {
        /**
         * @var $user User
         */
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if ($user && ($user->isManager() || $user->isAdmin())) {
                $data = $app->getObjectCache()->getUserWrapper()->getAll();
                return new JsonResponse([
                    'success' => true,
                    'data' => array_map(function (User $u) {
                        return $u->getApiData();
                    }, $data)
                ]);
            }
        }
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'token' => 'Permission denied'
            ]
        ]);
    }

    public function updateUsersAction(Application $app) {
        /**
         * @var $user User
         * @var $subject User
         */
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if ($user && ($user->isManager() || $user->isAdmin())) {
                $subject = $app->getObjectCache()->getUserWrapper()->findById($app->getRequest()->get('id'));
                if (!$subject)
                    return new JsonResponse([
                        'success' => false,
                        'errors' => [
                            'id' => 'User not found'
                        ]
                    ]);
                $subject->setEmail($app->getRequest()->get('email'));
                $password = null;
                if (strlen($app->getRequest()->get('password')))
                    $password = $subject->setPassword($app->getRequest()->get('password'))->get('password');

                if ($user->isAdmin()) //only admin can change roles
                {
                    if ($app->getRequest()->get('is_manager'))
                        $subject->setRole(User::ROLE_MANAGER);
                    else
                        $subject->revokeRole(User::ROLE_MANAGER);
                    if ($app->getRequest()->get('is_admin'))
                        $subject->setRole(User::ROLE_ADMIN);
                    else
                        $subject->revokeRole(User::ROLE_ADMIN);
                }
                if ($errors = $subject->getErrors()) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $errors
                    ]);
                }

                $subject->save();

                return new JsonResponse([
                    'success' => true,
                    'password' => $password
                ]);
            }
        }
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'token' => 'Permission denied'
            ]
        ]);
    }

    public function createUsersAction(Application $app) {
        /**
         * @var $user User
         * @var $subject User
         */
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if ($user && ($user->isManager() || $user->isAdmin())) {
                $subject = $app->getObjectCache()->getUserWrapper()->factoryObject();
                $subject->setEmail($app->getRequest()->get('email'));
                if (strlen($app->getRequest()->get('password')))
                    $password = $subject->setPassword($app->getRequest()->get('password'));
                else
                    $password = $subject->generateRandomPassword(10);

                if ($user->isAdmin()) //only admin can set roles
                {
                    if ($app->getRequest()->get('is_manager'))
                        $subject->setRole(User::ROLE_MANAGER);
                    else
                        $subject->revokeRole(User::ROLE_MANAGER);
                    if ($app->getRequest()->get('is_admin'))
                        $subject->setRole(User::ROLE_ADMIN);
                    else
                        $subject->revokeRole(User::ROLE_ADMIN);
                }
                if ($errors = $subject->getErrors()) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => $errors
                    ]);
                }
                $subject->save();

                return new JsonResponse([
                    'id' => $subject->get('id'),
                    'success' => true,
                    'password' => $password
                ]);
            }
        }
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'token' => 'Permission denied'
            ]
        ]);
    }

    public function deleteUsersAction(Application $app) {
        /**
         * @var $user User
         * @var $subject User
         */
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if ($user && ($user->isManager() || $user->isAdmin())) {
                $subject = $app->getObjectCache()->getUserWrapper()->findById($app->getRequest()->get('id'));
                if (!$subject)
                    return new JsonResponse([
                        'success' => false,
                        'errors' => [
                            'id' => 'User not found'
                        ]
                    ]);
                $subject->delete();
                return new JsonResponse([
                    'success' => true
                ]);
            }
        }
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'token' => 'Permission denied'
            ]
        ]);
    }


}
