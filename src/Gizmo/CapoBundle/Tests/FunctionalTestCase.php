<?php
namespace Gizmo\CapoBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

abstract class FunctionalTestCase extends WebTestCase {

    protected $client;
    protected $entityManager;

    public static function setUpBeforeClass()
    {
        self::createClient();
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        self::createDatabase($application);
    }

    private static function createDatabase($application)
    {
        self::executeCommand($application, "doctrine:schema:drop", array("--force" => true));
        self::executeCommand($application, "doctrine:schema:create");
        self::executeCommand($application, "doctrine:fixtures:load", array("--fixtures" => __DIR__ . "/../DataFixtures/ORM/test"));
    }

    private static function executeCommand($application, $command, Array $options = array())
    {
        $options["-e"] = "test";
        $options["-q"] = null;
        $options["-n"] = true;
        $options = array_merge($options, array('command' => $command));
        return $application->run(new ArrayInput($options));
    }

    public function setUp()
    {
        $this->populateVariables();
    }

    protected function populateVariables()
    {
        $this->client = static::createClient();
        $container = static::$kernel->getContainer();
        $this->entityManager = $container->get('doctrine')->getEntityManager();
    }

    protected function api_retrieve_call(Array $options_override) //$url, $content_indicator, $total_indicator = null, $data = array())
    {
        $options_default = Array(
            'method' => 'POST',
            'url' => '',
            'content_indicator' => '',
            'total_indicator' => '',
            'data' => Array(),
            'zero_result' => false,
            'json_response' => true,
            'list_response' => true
        );

        $options = array_merge($options_default, $options_override);

        $client = static::createClient();

        $crawler = $client->request($options['method'], $options['url'], $options['data']);
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());

        if ($options['json_response'] && $options['list_response']) {
            $decoded = json_decode($response->getContent());
            if ($options['total_indicator'] == '') {
                $options['total_indicator'] = $options['content_indicator'] . '_total';
            }

            if ($options['zero_result']) {
                $this->assertEquals(0, $decoded->$options['total_indicator']);
                $this->assertEquals(0, count($decoded->$options['content_indicator']));
            } else {
                $this->assertGreaterThan(0, $decoded->$options['total_indicator']);
                $this->assertGreaterThan(0, count($decoded->$options['content_indicator']));
            }

            return $decoded;
        } else {
            return $response;
        }
    }

    protected function api_action_ok($options_override)
    {
        $options_default = Array(
            'url' => '',
            'data' => Array()
        );

        $options = array_merge($options_default, $options_override);

        $client = static::createClient();

        $crawler = $client->request('POST', $options['url'], $options['data']);
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());

        $decoded = json_decode($response->getContent());
        $this->assertSame('OK', $decoded->result);

        return $decoded;
    }

    protected function api_action_error($options_override)
    {
        $options_default = Array(
            'url' => '',
            'data' => Array(),
            'json_response' => true
        );

        $options = array_merge($options_default, $options_override);
        $client = static::createClient();

        $crawler = $client->request('POST', $options['url'], $options['data']);
        $response = $client->getResponse();
        $this->assertFalse($response->isOk());

        if ($options['json_response']) {
            $decoded = json_decode($response->getContent());
            $this->assertSame('ERROR', $decoded->result);
            return $decoded;
        } else {
            return $response;
        }
    }
}
