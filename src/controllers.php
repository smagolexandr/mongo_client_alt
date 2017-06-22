<?php

use App\Entity\ConditionParser;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//Request::setTrustedProxies(array('127.0.0.1'));

$app->match('/', function (Request $request) use ($app) {
    $response = [];
    $data = array(
//        'sql' => "select * from people where age > 20 AND name = 'Olexandr' OR (name = 'Elizabeth' AND age = 19) limit 10"
//        'sql' => 'select name,age from people where age < 20 order by name desc, age asc limit 2 offset 1'
        'sql' => 'select * from people'
//        'sql' => 'select name,_id from people offset 1 where age < 20 limit 2 order by name desc, age asc '
    );

    $form = $app['form.factory']->createBuilder(FormType::class, $data)
        ->add('sql', null ,[
            'label' => false
        ])
        ->getForm();

    $response['sql'] = $form->createView();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

        if (isset($data['sql'])) {

            $query = strtolower($data['sql']);

            $av_operators = ['select', 'from', 'where', 'order by', 'limit', 'offset'];

            foreach ($av_operators as $operator) {
                if (strpos($query, $operator) !== false) {
                    $operators[$operator] = strpos($query, $operator);
                }
            }

            asort($operators);

            foreach ($operators as $operator => $start) {
                $next = next($operators);
                switch ($operator) {
                    case "select":
                        $projections_string = trim(substr($query,
                            $operators['select'] + 6,
                            $next - ($operators['select'] + 6)));
                        if ($projections_string == "*") {
                            $projections = null;
                        } else {
                            $projections = array_fill_keys(array_map('trim', explode(
                                ",", $projections_string)),
                                1);
                            if (strpos($query, "_id")) {
                                $projections["_id"] = 1;
                            } else {
                                $projections["_id"] = 0;
                            }
                        }
                        break;

                    case "from":
                        $table = trim(substr($query,
                            $operators['from'] + 4,
                                $next == false ? strlen($data['sql']) : $next - ($operators['from'] + 4) ));

                        break;

                    case "limit":
                        $limit_start = strpos($query, $operator) + 5;
                        $limit_end = strpos($query, " ", $limit_start + 1);
                        if ($limit_end == false) {
                            $limit_end = strlen($query);
                        }
                        $limit = intval(trim(substr($data['sql'], $limit_start, $limit_end - $limit_start)));
                        break;

                    case "offset":
                        $limit_start = strpos($query, "offset") + 6;
                        $limit_end = strpos($query, " ", $limit_start + 1);
                        if ($limit_end == false) {
                            $limit_end = strlen($data['sql']);
                        }
                        $offset = intval(trim(substr($data['sql'], $limit_start, $limit_end - $limit_start)));
                        break;

                    case "where":
                        $where_str = trim(substr($data['sql'], $start + 5, $next - ($start + 5)));

                        $parser = new ConditionParser();
                        $where = $parser->parse($where_str);
                        break;

                    case "order by":
                        $order_start = strpos($query, "order by") + 8;


                        $exp = explode(' ', trim(substr($data['sql'], $order_start, $next == false ? strlen($data['sql']) : $next - $order_start )));

                        if (count($exp) != 2) {
                            return false;
                        }
                        $orderField = $exp[0];
                        $orderDirection = strtoupper($exp[1]);

                        if (!in_array($orderDirection, ['ASC', 'DESC'])) {
                            return false;
                        }
                        $order = [$orderField => ($orderDirection == 'ASC' ? 1 : -1)];
                        break;

                }
            }

            if (isset($table)) {
                $collection = $app['mongodb']->test->$table;
            }

            $response['table'] = $table;

            $items =[];

           $cursor = $collection->find(
                isset($where) ? $where : [],
                ['projection' => $projections,
                    "limit" => isset($limit) ? $limit : null,
                    "skip" => isset($offset) ? $offset : null,
                    "sort" => isset($order)? $order : null,
                ]
            );

            foreach ($cursor as $document) {
                array_push($items, $document);
            }
            $response['items'] = $items;
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