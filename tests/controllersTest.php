<?php

use Silex\WebTestCase;

class controllersTest extends WebTestCase
{
    public function testGetHomepage()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('SQL to Mongo', $crawler->filter('body')->text());
    }

    public function testQuery()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('.simform')->form();
        $client->setServerParameter("HTTP_X-Requested-With" , "XMLHttpRequest");
        $client->submit($form, ["form[sql]" => 'select * from people where name = "Olexandr"']);

        $response = $client->getResponse();
        $this->assertContains('Olexandr', $response->getContent());
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../src/app.php';
        require __DIR__.'/../config/dev.php';
        require __DIR__.'/../src/controllers.php';
        $app['session.test'] = true;

        return $this->app = $app;
    }
}
