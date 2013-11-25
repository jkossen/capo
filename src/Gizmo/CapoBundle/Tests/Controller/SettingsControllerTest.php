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

class SettingsControllerTest extends FunctionalTestCase
{
    /**
     * Test index page
     */
    public function testIndexAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/settings/index/');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
    }

    /**
     * Test cacti instances page
     */
    public function testCactiInstancesAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/settings/cacti_instances/');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
    }

    /**
     * Test group access page
     */
    public function testGroupAccessAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/settings/groups/');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
    }

    /**
     * Test user access page
     */
    public function testUserAccessAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/settings/users/');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
    }

    /**
     * Test eventlog page
     */
    public function testEventLogAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/settings/event_log/');
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());
    }
}
