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
                    'success' => true,
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
}
