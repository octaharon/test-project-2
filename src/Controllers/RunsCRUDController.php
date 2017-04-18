<?php

namespace Controllers;

use Models\User;
use Models\Token;
use Models\Run;
use Symfony\Component\HttpFoundation\JsonResponse;
use Models\Application;

class RunsCRUDController {

    protected function checkAccess(Application $app, User $actor, $user_id) {
        if ($user_id != $actor->get('id') && !$actor->isAdmin()) {
            return false;
        }
        return true;
    }

    protected function deny() {
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'token' => 'Permission denied'
            ]
        ], 403);
    }

    public function readRunsAction(Application $app) {
        /**
         * @var $user User
         */
        $subject_id = $app->getRequest()->get('id');
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if (!$user)
                return $this->deny();
            if (!$subject_id) {
                $subject_id = $user->get('id');
            } else {
                if (!$this->checkAccess($app, $user, $subject_id))
                    return $this->deny();
            }
            $data = $app->getObjectCache()->getRunsWrapper()->loadFiltered(
                $subject_id,
                $app->getRequest()->get('date_from'),
                $app->getRequest()->get('date_to')
            );
            return new JsonResponse([
                'success' => true,
                'data' => array_map(function (Run $model) {
                    $d = $model->getProperties();
                    $d['date'] = date('Y-m-d', strtotime($d['date']));
                    return $d;
                }, $data)
            ]);

        }
        return $this->deny();
    }

    public function updateRunsAction(Application $app) {
        $subject_id = $app->getRequest()->get('id');
        /**
         * @var $subject Run
         */
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if (!$user)
                return $this->deny();
            $subject = $app->getObjectCache()->getRunsWrapper()->findById($subject_id);
            if (!$subject)
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'id' => 'Record not found'
                    ]
                ], 404);
            if (!$this->checkAccess($app, $user, $subject->get('user_id')))
                return $this->deny();

            if ($app->getRequest()->get('user_id') && $user->isAdmin()) //only admin can change record user_id
            {
                $subject->setUser($app->getRequest()->get('user_id'));
            }

            if ($app->getRequest()->get('distance'))
                $subject->setDistance($app->getRequest()->get('distance'));
            if ($app->getRequest()->get('duration'))
                $subject->setDuration($app->getRequest()->get('duration'));
            if ($app->getRequest()->get('date'))
                $subject->setDate($app->getRequest()->get('date'));

            if ($errors = $subject->getErrors()) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $errors
                ]);
            }
            $subject->save();

            return new JsonResponse([
                'success' => true
            ]);
        }
        return $this->deny();
    }

    public function createRunsAction(Application $app) {
        /**
         * @var $subject Run
         */
        $user_id = $app->getRequest()->get('user_id');
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if (!$user)
                return $this->deny();
            $subject = $app->getObjectCache()->getRunsWrapper()->factoryObject();
            if (!$user_id)
                $user_id = $user->get('id');
            else
                if (!$this->checkAccess($app, $user, $user_id))
                    return $this->deny();

            $subject->setUser($user_id);
            $subject->setDistance($app->getRequest()->get('distance'));
            $subject->setDuration($app->getRequest()->get('duration'));
            $subject->setDate($app->getRequest()->get('date'));

            if ($errors = $subject->getErrors()) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => $errors
                ]);
            }
            $subject->save();

            return new JsonResponse([
                'id' => $subject->get('id'),
                'user' => $app->getObjectCache()->getUserWrapper()->findById($user_id)->get('email'),
                'success' => true
            ]);
        }
        return $this->deny();
    }

    public function deleteRunsAction(Application $app) {
        $subject_id = $app->getRequest()->get('id');
        /**
         * @var $subject Run
         */
        if ($app->getRequest()->get('token')) {
            $user = $app->getObjectCache()->getTokenWrapper()->useToken($app->getRequest()->get('token'));
            if (!$user)
                return $this->deny();
            $subject = $app->getObjectCache()->getRunsWrapper()->findById($subject_id);
            if (!$subject)
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'id' => 'Record not found'
                    ]
                ], 404);
            if (!$this->checkAccess($app, $user, $subject->get('user_id')))
                return $this->deny();


            $subject->delete();

            return new JsonResponse([
                'success' => true
            ]);
        }
        return $this->deny();
    }


}
