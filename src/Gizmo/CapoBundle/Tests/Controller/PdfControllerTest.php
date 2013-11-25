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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PdfControllerTest extends WebTestCase
{
    public function testPdfSingleGraphAction()
    {
        $client = static::createClient();

        $url = '/pdf/single_graph/';

        // GET should give 404, only POST is supported
        $data = array('graph' => 1);
        $crawler = $client->request('GET', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());

        // POST with nonsense values
        $data = array('nonsense' => 1);
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());

        // existing graph
        $data = array('graph' => 1);
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isOk());

        // nonexisting graph
        $data = array('graph' => 3587902340283490);
        $crawler = $client->request('POST', $url, $data);
        $response = $client->getResponse();
        $this->assertTrue($response->isNotFound());

        // negative graph id
        $data = array('graph' => -4);
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());

        // non int graph id
        $data = array('graph' => 'djqwkl');
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());
    }

    public function testPdfGraphSelectionAction()
    {
        $client = static::createClient();

        $url = '/pdf/graph_selection/';

        // GET should give 404, only POST is supported
        $data = array('graph' => 1);
        $crawler = $client->request('GET', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());

        // correct request
        $data = array('graphs_selected' => json_encode(array(1, 2, 3, 4, 5, 6)));
        $crawler = $client->request('POST', $url, $data);
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());

        // POST with nonsense values
        $data = array('nonsense' => 1);
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());

        // POST with wrong content (should be json encoded array)
        $data = array('graphs_selected' => 'ddsdasd');
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());

        // some nonexisting graphs, but at least one existing -> OK
        $data = array('graphs_selected' => json_encode(array(1, 489320849023)));
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isOk());

        // negative graph id
        $data = array('graphs_selected' => json_encode(array(-4)));
        $crawler = $client->request('POST', $url, $data);
        $response = $client->getResponse();
        $this->assertTrue($client->getResponse()->isOk());

        // non int graph id
        $data = array('graphs_selected' => json_encode(array('djqwkl')));
        $crawler = $client->request('POST', $url, $data);
        $this->assertTrue($client->getResponse()->isNotFound());
    }
}
