<?php

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->match('/', function (Request $request) use ($app) {

    $data = array(
        'sql' => 'select * from people'
    );

    $form = $app['form.factory']->createBuilder(FormType::class, $data)
        ->add('sql')
        ->add('submit', SubmitType::class, [
            'label' => 'Save',
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();
        if (isset($data['sql'])) {

        }
        dump($data);
    }


    $people = new ArrayObject();
    $collection = $app['mongodb']->test->people;

    $cursor = $collection->find();

    foreach ($cursor as $document) {
        $people->append($document);
    }

//    dump($people);

    return $app['twig']->render('index.html.twig', ["people" => $people, "sql" => $form->createView()]);
})
->method('GET|POST')
->bind('homepage')
;

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
