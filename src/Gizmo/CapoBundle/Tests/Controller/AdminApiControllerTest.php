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

class AdminApiControllerTest extends FunctionalTestCase
{
    /**
     * Test retrieving users
     */
    public function testGetUsersAction()
    {
        $options = Array(
            'url' => '/api/admin/get_users/',
            'content_indicator' => 'users'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test retrieving apiaccounts
     */
    public function testGetApiAccountsAction()
    {
        $options = Array(
            'url' => '/api/admin/get_api_accounts/',
            'content_indicator' => 'api_accounts'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test retrieving groups
     */
    public function testGetGroupsAction()
    {
        $options = Array(
            'url' => '/api/admin/get_groups/',
            'content_indicator' => 'groups'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test retrieving cacti instances
     */
    public function testGetCactiInstancesAction()
    {
        $options = Array(
            'url' => '/api/admin/get_cacti_instances/',
            'content_indicator' => 'cacti_instances'
        );

        $this->api_retrieve_call($options);

        // test with group_id
        $options['data'] = Array(
            'group_id' => 1
        );

        $this->api_retrieve_call($options);

        // test with nonexisting group_id
        $options['data'] = Array(
            'group_id' => 99999
        );
        $options['zero_result'] = true;

        $this->api_retrieve_call($options);

        // test with exclude_group_id
        $options['data'] = Array(
            'exclude_group_id' => 1
        );
        $options['zero_result'] = false;

        $this->api_retrieve_call($options);

        // test with nonexisting exclude_group_id
        $options['data'] = Array(
            'exclude_group_id' => 99999
        );

        $this->api_retrieve_call($options);

        // test with api_account_id
        $options['data'] = Array(
            'api_account_id' => 1
        );

        $this->api_retrieve_call($options);

        // test with nonexisting api_account_id
        $options['data'] = Array(
            'api_account_id' => 9999
        );
        $options['zero_result'] = true;

        $this->api_retrieve_call($options);

        // test with exclude_api_account_id
        $options['data'] = Array(
            'exclude_api_account_id' => 1
        );
        $options['zero_result'] = false;

        $this->api_retrieve_call($options);

        // test with nonexisting exclude_api_account_id
        $options['data'] = Array(
            'exclude_api_account_id' => 9999
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test retrieving event logs
     */
    public function testGetEventLogAction()
    {
        $options = Array(
            'url' => '/api/admin/get_event_log/',
            'content_indicator' => 'loglines'
        );

        $this->api_retrieve_call($options);
    }

    /**
     * Test granting access to cacti instance
     */
    public function testEnableCactiInstanceForGroupAction()
    {
        $options = Array(
            'url' => '/api/admin/enable_cacti_instance_for_group/',
            'data' => Array(
                'cacti_instance_id' => 1,
                'group_id' => 1
            )
        );

        $this->api_action_ok($options);

        // no group_id
        $options['data'] = Array(
            'cacti_instance_id' => 1,
        );

        $this->api_action_error($options);

        // nonexistant group_id
        $options['data'] = Array(
            'cacti_instance_id' => 1,
            'group_id' => 99999
        );

        $this->api_action_error($options);

        // no cacti_instance_id
        $options['data'] = Array(
            'group_id' => 1
        );

        $this->api_action_error($options);

        // nonexistant cacti_instance_id
        $options['data'] = Array(
            'cacti_instance_id' => 99999,
            'group_id' => 1
        );

        $this->api_action_error($options);
    }

    /**
     * Test revoking access to cacti instance
     */
    public function testDisableCactiInstanceForGroupAction()
    {
        $options = Array(
            'url' => '/api/admin/disable_cacti_instance_for_group/',
            'data' => Array(
                'cacti_instance_id' => 1,
                'group_id' => 1
            )
        );

        $this->api_action_ok($options);

        $options['data'] = Array(
            'cacti_instance_id' => 1,
        );

        $this->api_action_error($options);

        $options['data'] = Array(
            'cacti_instance_id' => 1,
            'group_id' => 99999
        );

        $this->api_action_error($options);

        $options['data'] = Array(
            'group_id' => 1,
        );

        $this->api_action_error($options);

        $options['data'] = Array(
            'cacti_instance_id' => 99999,
            'group_id' => 1
        );

        $this->api_action_error($options);
    }

    /**
     * Test granting access to cacti instance
     */
    public function testEnableCactiInstanceForApiUserAction()
    {
        $options = Array(
            'url' => '/api/admin/enable_cacti_instance_for_api_user/',
            'data' => Array(
                'cacti_instance_id' => 1,
                'api_user_id' => 1
            )
        );

        $this->api_action_ok($options);

        // no group_id
        $options['data'] = Array(
            'cacti_instance_id' => 1,
        );

        $this->api_action_error($options);

        // nonexistant group_id
        $options['data'] = Array(
            'cacti_instance_id' => 1,
            'api_user_id' => 99999
        );

        $this->api_action_error($options);

        // no cacti_instance_id
        $options['data'] = Array(
            'api_user_id' => 1
        );

        $this->api_action_error($options);

        // nonexistant cacti_instance_id
        $options['data'] = Array(
            'cacti_instance_id' => 99999,
            'api_user_id' => 1
        );

        $this->api_action_error($options);
    }

    /**
     * Test revoking access to cacti instance
     */
    public function testDisableCactiInstanceForApiUserAction()
    {
        $options = Array(
            'url' => '/api/admin/disable_cacti_instance_for_api_user/',
            'data' => Array(
                'cacti_instance_id' => 1,
                'api_user_id' => 1
            )
        );

        $this->api_action_ok($options);

        $options['data'] = Array(
            'cacti_instance_id' => 1,
        );

        $this->api_action_error($options);

        $options['data'] = Array(
            'cacti_instance_id' => 1,
            'api_user_id' => 99999
        );

        $this->api_action_error($options);

        $options['data'] = Array(
            'api_user_id' => 1,
        );

        $this->api_action_error($options);

        $options['data'] = Array(
            'cacti_instance_id' => 99999,
            'api_user_id' => 1
        );

        $this->api_action_error($options);
    }

    /**
     * Test creating new Groups
     */
    public function testCreateGroupAction()
    {
        $options = Array(
            'url' => '/api/admin/group/create/',
            'data' => Array(
                'name' => 'mynewgroup'
            )
        );

        $decoded = $this->api_action_ok($options);
        $this->assertGreaterThan(0, $decoded->group_id);

        // try to create another with the same name
        $options['data'] = array('name' => 'mynewgroup');

        $this->api_action_error($options);

        // try to create another without name
        $options['data'] = array();

        $this->api_action_error($options);

        // try to create another with an empty name
        $options['data'] = array('name' => '');

        $this->api_action_error($options);
    }

    /**
     * Test creating new ApiUsers
     */
    public function testCreateApiUserAction()
    {
        $options = Array(
            'url' => '/api/admin/api_user/create/',
            'data' => Array(
                'username' => 'test-apiusername'
            )
        );

        $decoded = $this->api_action_ok($options);
        $this->assertGreaterThan(0, $decoded->api_user_id);

        // try to create another with the same username
        $options['data'] = array('username' => 'test-apiusername');

        $this->api_action_error($options);

        // try to create another without username
        $options['data'] = array();

        $this->api_action_error($options);

        // try to create another with an empty username
        $options['data'] = array('username' => '');

        $this->api_action_error($options);
    }

    /**
     * Test creating new Cacti Instances
     */
    public function testCreateCactiInstanceAction()
    {
        $options = Array(
            'url' => '/api/admin/cacti_instance/create/',
            'data' => Array(
                'name' => 'mynewcacti',
                'base_url' => 'http://mynewcacti.com/cacti/',
                'import_date' => '1970-01-01 00:00',
                'active' => 1,
                'queue_import' => 0
            )
        );


        $decoded = $this->api_action_ok($options);
        $this->assertGreaterThan(0, $decoded->cacti_instance_id);

        // Try to create another with the same name
        $options['data'] = Array(
            'name' => 'mynewcacti',
            'base_url' => 'http://mynewcacti2.com/cacti/',
            'import_date' => '1970-01-01 00:00',
            'active' => 1,
            'queue_import' => 0
        );
        $this->api_action_error($options);

        // Try to create another with the same base url
        $options['data'] = Array(
            'name' => 'mynewcacti2',
            'base_url' => 'http://mynewcacti.com/cacti/',
            'import_date' => '1970-01-01 00:00',
            'active' => 1,
            'queue_import' => 0
        );
        $this->api_action_error($options);

        // Try to create another without a name
        $options['data'] = Array(
            'base_url' => 'http://mynewcacti.com/cacti/',
            'import_date' => '1970-01-01 00:00',
            'active' => 1,
            'queue_import' => 0
        );
        $this->api_action_error($options);

        // Try to create another without a base_url
        $options['data'] = Array(
            'name' => 'mynewcacti',
            'import_date' => '1970-01-01 00:00',
            'active' => 1,
            'queue_import' => 0
        );
        $this->api_action_error($options);

        // Without name
        $options['data'] = Array(
            'name' => '',
            'base_url' => 'http://mynewcacti.com/cacti/'
        );

        $this->api_action_error($options);

        // Without base_url
        $options['data'] = Array(
            'name' => 'mynewcactiwithoutbaseurl',
            'base_url' => ''
        );

        $this->api_action_error($options);

    }

    /**
     * Test creating new Cacti Instances, XML output format
     */
    public function testCreateCactiInstanceActionXML()
    {
        $client = static::createClient();
        $data = array(
            'name' => 'mynewcacti2',
            'base_url' => 'http://mynewcacti2.com/cacti/',
            'format' => 'xml');
        $url = '/api/admin/cacti_instance/create/';
        
        $crawler = $client->request('POST', $url, $data);
        $response = $client->getResponse();

        $this->assertTrue($response->isOk());

        // test XML validity
        $expected = new \DOMDocument;
        $is_loaded = $expected->loadXML($response->getContent());
        $this->assertTrue($is_loaded);
    }

    /**
     * Test changing a Cacti Instance
     */
    public function testUpdateCactiInstanceAction()
    {
        $new_name = 'cacti1_updated';
        $new_base_url = 'http://myupdatedcacti.com/cacti/';
        $new_active = false;

        $options = Array(
            'url' => '/api/admin/cacti_instance/update/',
            'data' => Array(
                'id' => '1',
                'name' => $new_name,
                'base_url' => $new_base_url,
                'active' => $new_active
            )
        );

        $this->api_action_ok($options);

        $data = array('q' => $new_name, 'active_only' => 0);
        $url = '/api/get_cacti_instances/';

        $client = static::createClient();
        $crawler = $client->request('POST', $url, $data);
        $response = json_decode($client->getResponse()->getContent());

        $this->assertSame('1', $response->cacti_instances_total);
        $this->assertSame($new_name, $response->cacti_instances[0]->name);
        $this->assertSame($new_base_url, $response->cacti_instances[0]->base_url);
        $this->assertSame($new_active, $response->cacti_instances[0]->active);
    }

    /**
     * Test changing a Cacti Instance without giving an id
     *
     * Should return a 404 error
     */
    public function testUpdateCactiInstanceActionWithoutId()
    {
        $new_name = 'cacti1_updated';
        $new_base_url = 'http://myupdatedcacti.com/cacti/';
        $new_active = false;

        $client = static::createClient();
        $data = array(
            'id' => '',
            'name' => $new_name,
            'base_url' => $new_base_url,
            'active' => $new_active
        );
        $url = '/api/admin/cacti_instance/update/';

        $crawler = $client->request('POST', $url, $data);
        $response = $client->getResponse();

        $this->assertFalse($response->isOk());
    }

    /**
     * Test changing a Cacti Instance with a nonexistant id
     *
     * Should return a 404 error
     */
    public function testUpdateCactiInstanceActionWithNonexistantId()
    {
        $new_name = 'cacti1_updated';
        $new_base_url = 'http://myupdatedcacti.com/cacti/';
        $new_active = false;

        $client = static::createClient();
        $data = array(
            'id' => '84938432478234',
            'name' => $new_name,
            'base_url' => $new_base_url,
            'active' => $new_active
        );
        $url = '/api/admin/cacti_instance/update/';

        $crawler = $client->request('POST', $url, $data);
        $response = $client->getResponse();

        $this->assertFalse($response->isOk());
    }

    public function testChangeGroupForUserAction()
    {
        $options = Array(
            'url' => '/api/admin/user/change_group/',
            'data' => Array(
                'group_id' => 2,
                'user_id' => 1,
            )
        );

        $this->api_action_ok($options);

        // nonexistant group
        $options['data'] = Array(
            'group_id' => 99999,
            'user_id' => 1,
        );

        $this->api_action_error($options);

        // nonexistant user
        $options['data'] = Array(
            'group_id' => 2,
            'user_id' => 99999,
        );

        $this->api_action_error($options);

        // without group_id
        $options['data'] = Array(
            'user_id' => 1,
        );

        $this->api_action_error($options);

        // without user_id
        $options['data'] = Array(
            'group_id' => 2,
        );

        $this->api_action_error($options);
    }

    public function testUpdateGroupAction()
    {
        // change name
        $options = Array(
            'url' => '/api/admin/group/update/',
            'data' => Array(
                'id' => 1,
                'name' => 'new_name',
            )
        );

        $this->api_action_ok($options);

        // nonexistant id
        $options['data'] = Array(
            'id' => 99999,
            'name' => 'new_name',
        );

        $this->api_action_error($options);

        // without id
        $options['data'] = Array(
            'name' => 'new_name',
        );

        $this->api_action_error($options);

    }

    public function testUpdateUserAction()
    {
        // change name
        $options = Array(
            'url' => '/api/admin/user/update/',
            'data' => Array(
                'id' => 1,
                'username' => 'testuser-renamed',
            )
        );

        $this->api_action_ok($options);

        // without id
        $options['data'] = Array(
            'enabled' => 0,
        );

        $this->api_action_error($options);

        // nonexistant id
        $options['data'] = Array(
            'id' => 99999,
            'enabled' => 0,
        );

        $this->api_action_error($options);
    }

    public function testUpdateApiUserAction()
    {
        // change name
        $options = Array(
            'url' => '/api/admin/api_user/update/',
            'data' => Array(
                'id' => 1,
                'username' => 'testapiuser1-renamed',
            )
        );

        $this->api_action_ok($options);

        // without id
        $options['data'] = Array(
            'active' => 0,
        );

        $this->api_action_error($options);

        // nonexistant id
        $options['data'] = Array(
            'id' => 99999,
            'active' => 0,
        );

        $this->api_action_error($options);
    }
}
