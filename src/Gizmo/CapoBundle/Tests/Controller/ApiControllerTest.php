<?php
/*
    Capo, a web interface for querying multiple Cacti instances
    Copyright (C) 2013  Jochem Kossen <jochem@jkossen.nl>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gizmo\CapoBundle\Tests\Controller;

use Gizmo\CapoBundle\Controller\ApiController;
use Gizmo\CapoBundle\Tests\FunctionalTestCase;

class ApiControllerTest extends FunctionalTestCase
{
    /**
     * Test to make sure GET and POST are treated equally
     */
    public function testGetAndPostAreEqual()
    {
        $options = Array(
            'url' => '/api/get_hosts/',
            'content_indicator' => 'hosts',
            'data' => Array(
                'cacti_instance' => 1
            )
        );

        $response_post = $this->api_retrieve_call($options);

        $options['method'] = 'GET';
        $response_get = $this->api_retrieve_call($options);
        
        $this->assertEquals($response_post, $response_get);
    }

    /**
     * Test for the GetCactiInstances action
     */
    public function testGetCactiInstancesAction()
    {
        $options = Array(
            'url' => '/api/get_cacti_instances/',
            'content_indicator' => 'cacti_instances'
        );

        // standard request
        $this->api_retrieve_call($options);

        // request for specific cacti instance
        $options['data'] = Array('q' => 'cacti_instance_test_01');
        $this->api_retrieve_call($options);

        // request for nonexistant cacti instance
        $options['data'] = Array('q' => 'nonexistant');
        $options['zero_result'] = true;
        $this->api_retrieve_call($options);
    }

    /**
     * Test for the getHosts action
     */
    public function testGetHostsAction()
    {
        $options = Array(
            'url' => '/api/get_hosts/',
            'content_indicator' => 'hosts'
        );

        $this->api_retrieve_call($options);

        // request for specific cacti instance
        $options['data'] = Array(
            'cacti_instance' => 1,
            'q' => 'host'
        );

        $this->api_retrieve_call($options);

        // request for nonexistant host
        $options['zero_result'] = true;
        $options['data'] = Array(
            'q' => 'nonexistant'
        );

        $this->api_retrieve_call($options);

        // request for non existant cacti instance
        $options['zero_result'] = true;
        $options['data'] = Array(
            'cacti_instance' => '9032930'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test for the getGraphTemplates action
     */
    public function testGetGraphTemplatesAction()
    {
        $options = Array(
            'url' => '/api/get_graph_templates/',
            'content_indicator' => 'graph_templates'
        );

        $this->api_retrieve_call($options);

        // request for specific cacti instance
        $options['data'] = Array(
            'cacti_instance' => 1,
            'q' => 'Graph Template'
        );

        $this->api_retrieve_call($options);

        // request for nonexistant host
        $options['zero_result'] = true;
        $options['data'] = Array(
            'q' => 'nonexistant'
        );

        $this->api_retrieve_call($options);

        // request for non existant cacti instance
        $options['zero_result'] = true;
        $options['data'] = Array(
            'cacti_instance' => '9032930'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test for getGraphsAction
     */
    public function testGetGraphsAction()
    {
        $options = Array(
            'url' => '/api/get_graphs/',
            'content_indicator' => 'graphs'
        );

        // standard request
        $response = $this->api_retrieve_call($options);

        $graphs_total_expected = $response->graphs_total;

        // Check that graph has a cacti_instance and graph_template object
        $first_graph = $response->graphs[0];
        $this->assertObjectHasAttribute('cacti_instance', $first_graph);
        $this->assertObjectHasAttribute('graph_template', $first_graph);

        // request with pagination
        $options['data'] = Array(
            'page_limit' => 5
        );

        $response = $this->api_retrieve_call($options);
        $this->assertEquals($graphs_total_expected, $response->graphs_total);
        $this->assertEquals(5, count($response->graphs));

        // request with pagination and limit
        $options['data'] = Array(
            'page_limit' => 5,
            'page' => 4
        );

        $response = $this->api_retrieve_call($options);
        $this->assertEquals($graphs_total_expected, $response->graphs_total);
        $this->assertEquals(3, count($response->graphs));

        // request with pagination and limit
        $options['data'] = Array(
            'page_limit' => 50000
        );
        $response = $this->api_retrieve_call($options);
        $this->assertEquals($graphs_total_expected, $response->graphs_total);

        // request with pagination and limit < 1
        $options['data'] = Array(
            'page_limit' => 0
        );
        $response = $this->api_retrieve_call($options);
        $this->assertEquals($graphs_total_expected, $response->graphs_total);

        // request with pagination and page < 1
        $options['data'] = Array(
            'page_limit' => 5,
            'page' => 0
        );
        $response = $this->api_retrieve_call($options);
        $this->assertEquals($graphs_total_expected, $response->graphs_total);

        // request for specific cacti instance
        $options['data'] = Array(
            'cacti_instance' => 1,
            'q' => 'Graph'
        );

        $this->api_retrieve_call($options);

        // request for specific graph template
        $options['data'] = Array(
            'graph_template' => 'Graph Template 03'
        );

        $this->api_retrieve_call($options);

        // request for nonexistant graph template
        $options['zero_result'] = true;
        $options['data'] = Array(
            'graph_template' => 'nonexistant'
        );

        $this->api_retrieve_call($options);

        // request for specific host
        $options['zero_result'] = false;
        $options['data'] = Array(
            'host' => 4
        );

        $this->api_retrieve_call($options);

        // request for nonexistant graph
        $options['zero_result'] = true;
        $options['data'] = Array(
            'q' => 'nonexistant'
        );
        $this->api_retrieve_call($options);

        // request for non existant cacti instance
        $options['zero_result'] = true;
        $options['data'] = Array(
            'cacti_instance' => '9032930'
        );
        $this->api_retrieve_call($options);
    }

    /**
     * Test for the getGraphsAction with xml output
     */
    public function testXmlGetGraphsAction()
    {
        $client = static::createClient();
        $url = '/api/get_graphs/';
        $data = Array(
            'format' => 'xml'
        );

        // standard request
        $crawler = $client->request('GET', $url, $data);
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());

        // test XML validity
        $expected = new \DOMDocument;
        $is_loaded = $expected->loadXML($response->getContent());
        $this->assertTrue($is_loaded);
    }

    /**
     * Test for getWeathermapsAction
     */
    public function testGetWeathermapsAction()
    {
        $options = Array(
            'url' => '/api/get_weathermaps/',
            'content_indicator' => 'weathermaps'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test for the getGraphTitles action
     */
    public function testGetGraphTitlesAction()
    {
        $options = Array(
            'url' => '/api/get_graph_titles/',
            'content_indicator' => 'graph_titles'
        );

        // standard request
        $this->api_retrieve_call($options);

        // request for specific cacti instance
        $options['data'] = Array(
            'cacti_instance' => 1,
            'q' => 'Graph Title'
        );

        $this->api_retrieve_call($options);

        // request for specific graph template
        $options['data'] = Array(
            'graph_template' => 3
        );

        $this->api_retrieve_call($options);

        // request for nonexistant graph template
        $options['zero_result'] = true;
        $options['data'] = Array(
            'graph_template' => 823590394,
        );

        $this->api_retrieve_call($options);

        // request for non existant cacti instance
        $options['zero_result'] = true;
        $data = Array(
            'cacti_instance' => '9032930',
        );

        $response = $this->api_retrieve_call($options);

        $this->assertEquals(0, $response->graph_titles_total);
    }

    /**
     * Test for the getGraph action
     */
    public function testGetGraphAction()
    {
        $options = Array(
            'url' => '/api/get_graph/',
            'list_response' => false
        );

        // No graph id given
        $this->api_action_error($options);

        // request for specific graph
        $options['data'] = Array(
            'id' => 11
        );

        $response = $this->api_retrieve_call($options);

        $output = json_decode($response->getContent());
        $this->assertObjectHasAttribute('graph', $output);
        $this->assertObjectNotHasAttribute('error', json_decode($response->getContent()));
        $this->assertEquals(1, count($output->graph));

        // Check that graph has a cacti_instance and graph_template object
        $graph = $output->graph[0];
        $this->assertObjectHasAttribute('cacti_instance', $graph);
        $this->assertObjectHasAttribute('graph_template', $graph);

        // request for specific nonexistant graph
        $options['data'] = Array(
            'id' => 111111111111
        );
        $this->api_action_error($options);
    }

    /**
     * Test for the getGraphSelections action
     */
    public function testGetGraphSelectionsAction()
    {
        $options = Array(
            'url' => '/api/get_graph_selections/',
            'content_indicator' => 'graph_selections'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test for the getGraphSelections action
     */
    public function testGetGraphSelectionGraphsAction()
    {
        $options = Array(
            'url' => '/api/get_graph_selection_graphs/',
            'data' => array('graph_selection_id' => 1),
            'content_indicator' => 'graph_selection_items',
            'total_indicator' => 'graph_selection_items_total'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test for the saveGraphSelection action
     */
    public function testSaveGraphSelectionAction()
    {
        $options = Array(
            'url' => '/api/save_graph_selection/'
        );

        $options['data'] = Array(
            'name' => 'test_selection_1',
            'graphs' => json_encode(Array(1, 2))
        );
        $this->api_action_ok($options);

        // without name
        $options['data'] = Array(
            'graphs' => json_encode(Array(1, 2))
        );
        $this->api_action_error($options);

        // without graphs
        $options['data'] = Array(
            'name' => 'test_selection_1'
        );
        $this->api_action_error($options);

        // nonexistant graphs
        $options['data'] = Array(
            'name' => 'test_selection_1',
            'graphs' => json_encode(Array(99998,99999))
        );
        $this->api_action_error($options);
    }

    /**
     * Test for the renameGraphSelection action
     */
    public function testRenameGraphSelectionAction()
    {
        $client = static::createClient();
        $options = Array(
            'url' => '/api/rename_graph_selection/',
            'data' => Array(
                'graph_selection' => 1,
                'name' => 'new_name_1'
            )
        );

        $this->api_action_ok($options);

        // no graph selection given
        $options['data'] = Array(
            'name' => 'new_name_1'
        );
        $this->api_action_error($options);

        // no name given
        $options['data'] = Array(
            'graph_selection' => 1
        );
        $this->api_action_error($options);

        // nonexistant graph selection given
        $options['data'] = Array(
            'graph_selection' => 99999,
            'name' => 'new_name_1'
        );
        $this->api_action_error($options);
    }

    /**
     * Test for the disableGraphSelection action
     */
    public function testDisableGraphSelectionAction()
    {
        $options = Array(
            'url' => '/api/disable_graph_selection/',
            'data' => Array(
                'graph_selection' => 1
            )
        );
        $this->api_action_ok($options);

        // no graph selection given
        $options['data'] = Array();
        $this->api_action_error($options);

        // nonexistant graph selection given
        $options['data'] = Array(
            'graph_selection' => 99999
        );
        $this->api_action_error($options);
    }

    public function testShowGraphAction()
    {
        // existing graph
        $options = Array(
            'url' => '/api/show_graph/1/1/',
            'json_response' => false
        );

        $this->api_retrieve_call($options);

        // wrong use of show graph
        $options['url'] = '/api/show_graph/';
        $response = $this->api_action_error($options);

        $this->assertTrue($response->isNotFound());

        // wrong use of show graph
        $options['url'] = '/api/show_graph/dd/dd/';
        $response = $this->api_action_error($options);

        $this->assertTrue($response->isNotFound());

        // nonexistant graph
        $options['url'] = '/api/show_graph/94932043928490129483021/1/';
        $response = $this->api_action_error($options);
        $this->assertTrue($response->isNotFound());

        // nonexisting rra
        $options['url'] = '/api/show_graph/1/28198049180/';
        $response = $this->api_action_error($options);
        $this->assertTrue($response->isNotFound());
     }

    public function testShowWmapAction()
    {
        // existing graph
        $options = Array(
            'url' => '/api/show_wmap/1/',
            'json_response' => false
        );

        $this->api_retrieve_call($options);

        // wrong use of show wmap
        $options['url'] = '/api/show_wmap/';
        $response = $this->api_action_error($options);

        $this->assertTrue($response->isNotFound());

        // wrong use of show wmap
        $options['url'] = '/api/show_wmap/dd/dd/';
        $response = $this->api_action_error($options);

        $this->assertTrue($response->isNotFound());

        // nonexistant wmap
        $options['url'] = '/api/show_wmap/94932043928490129483021/';
        $response = $this->api_action_error($options);

        $this->assertTrue($response->isNotFound());
     }

    private function getMockTemplating()
    {
        return $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\Engine', array(), array(), '', false, false);
    }
}

