<?php

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->match('/', function (Request $request) use ($app) {

    $response = [];

    $data = array(
        'sql' => 'select * from people'
    );

    $form = $app['form.factory']->createBuilder(FormType::class, $data)
        ->add('sql')
        ->add('submit', SubmitType::class, [
            'label' => 'Save',
        ])
        ->getForm();

    $response['sql'] = $form->createView();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();
        if (isset($data['sql'])) {

            if (strpos($data['sql'], "select") !== false) {

                $q = strpos($data['sql'], "from");

                if (trim(substr($data['sql'], 7, $q - 7)) == "") {
                    return false;
                }

                if (trim(substr($data['sql'], 7, $q - 7)) == "*"){
                    $projections = null;
                } else {
                    $projections = array_fill_keys(array_map('trim', explode(",", substr($data['sql'], 7, $q - 7))), 1) ;
                    $projections["_id"] = 0;
                }


                if (strpos($data['sql'],"limit") !== null) {
                    $limit_start = strpos($data['sql'],"limit") + 5;
                    $limit_end = strpos($data['sql']," ", $limit_start+1);
                    if ($limit_end == false ){
                        $limit_end = strlen($data['sql']);
                    }
                    $limit = intval(trim(substr($data['sql'], $limit_start, $limit_end - $limit_start)));
                }

                if (strpos($data['sql'],"offset") !== null) {
                    $limit_start = strpos($data['sql'],"offset") + 6;
                    $limit_end = strpos($data['sql']," ", $limit_start+1);
                    if ($limit_end == false ){
                        $limit_end = strlen($data['sql']);
                    }
                    $offset = intval(trim(substr($data['sql'], $limit_start, $limit_end - $limit_start)));
                }


                $items = new ArrayObject();
                $collection = $app['mongodb']->test->people;

                $cursor = $collection->find(
                    [
//                        '$or' => [
//                            ['name' => 'Olexandr'],
//                            ['name' => 'Elizabeth'],
//                        ],
                    ],
                    ['projection' => $projections,
                        "limit" => $limit ? $limit : null,
                        "skip" => $offset ? $offset : null,
                        "sort" => ['age' => -1]
                    ]
                );

                foreach ($cursor as $document) {
                    $items->append($document);
                }
                dump($items);
                $response['items'] = $items;
            }
        }
    }
    return $app['twig']->render('index.html.twig', $response);
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
