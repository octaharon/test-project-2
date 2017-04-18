<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Models\Application;

class WebSiteController {
    public function indexAction(Application $app) {
        return $app->getTwig()->render('/base_html.twig');
    }

    public function assetsAction(Application $app, $path) {
        $twig = clone $app->getTwig();
        $twig->setLoader(new \Twig_Loader_String());
        
        $file = ROOT . '/views/assets/' . $path . '.twig';

        if(!is_file($file)) {
            return $app->getTwig()->render('/404.html.twig', ['path' => $path . '.twig']);
        }

        $tpl = trim(file_get_contents($file));
        if (substr($tpl, 0, 2) != '{%') {
            $tpl = "{%raw%}\n$tpl\n{%endraw%}\n";
        }

        return $twig->render($tpl);
    }
}