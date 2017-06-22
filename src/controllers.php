<?php

use App\Entity\Query;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->match('/', function (Request $request) use ($app) {
    $response = [];
    $data = array(
        'sql' => 'select * from people'
    );

    $form = $app['form.factory']->createBuilder(FormType::class, $data)
        ->add('sql', null, [
            'label' => false
        ])
        ->getForm();

    $response['sql'] = $form->createView();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

        if (isset($data['sql'])) {
            $mosql = new Query($data['sql']);

            $response['table'] = $mosql->getTable();
            $response['items'] = $mosql->execute($app['mongodb']);
        }
    }

    return $app['twig']->render('index.html.twig', $response);
})
    ->method('GET|POST')
    ->bind('homepage');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/' . $code . '.html.twig',
        'errors/' . substr($code, 0, 2) . 'x.html.twig',
        'errors/' . substr($code, 0, 1) . 'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
