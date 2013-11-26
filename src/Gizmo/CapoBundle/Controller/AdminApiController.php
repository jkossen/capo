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

namespace Gizmo\CapoBundle\Controller;

/**
 * The Capo Admin API controller
 *
 * This class provides functions for updating and adding Capo data
 *
 * @author Jochem Kossen <jochem@jkossen.nl>
 */
class AdminApiController extends BaseController
{
    /**
     * Get array of users
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response encoded array of users
     */
    public function getUsersAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('q', 'text'),
            Array('page_limit', 'integer'),
            Array('page', 'integer'),
            Array('active_users_only', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $users = $em
            ->getRepository('GizmoCapoBundle:User')
            ->getUsers($data);

        return $this->_encoded_response($users, $format);
    }

    /**
     * Get array of groups
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response encoded array of groups
     */
    public function getGroupsAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('q', 'text'),
            Array('page_limit', 'integer'),
            Array('page', 'integer'),
            Array('active_groups_only', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $groups = $em
            ->getRepository('GizmoCapoBundle:Group')
            ->getGroups($data);

        return $this->_encoded_response($groups, $format);
    }

    /**
     * Get array of API accounts
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response encoded array of API accounts
     */
    public function getApiAccountsAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('q', 'text'),
            Array('page_limit', 'integer'),
            Array('page', 'integer'),
            Array('active_accounts_only', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $groups = $em
            ->getRepository('GizmoCapoBundle:ApiUser')
            ->getApiUsers($data);

        return $this->_encoded_response($groups, $format);
    }

    /**
     * Get array of event log messages
     *
     * @return Response encoded array of event log messages
     */
    public function getEventLogAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('q', 'text'),
            Array('page_limit', 'integer'),
            Array('page', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();

        $event_logs = $em
            ->getRepository('GizmoCapoBundle:EventLog')
            ->getLogLines($data);

        return $this->_encoded_response($event_logs, $format);
    }

    /**
     * Get array of cacti instances
     *
     * @return Response encoded array of cacti instances
     */
    public function getCactiInstancesAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('page_limit', 'integer'),
            Array('page', 'integer'),
            Array('exclude_group_id', 'integer'),
            Array('exclude_api_account_id', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();

        $exclude_query = false;
        
        if (intval($data['exclude_group_id']) > 0) {
            $data['exclude_id'] = $data['exclude_group_id'];
            $exclude_query = $em->getRepository('GizmoCapoBundle:Group')
                                ->getCactiInstanceIdsQuery(
                                    Array('id' => $data['exclude_group_id'])
                                );
        } elseif (intval($data['exclude_api_account_id']) > 0) {
            $data['exclude_id'] = $data['exclude_api_account_id'];
            $exclude_query = $em->getRepository('GizmoCapoBundle:ApiUser')
                                ->getCactiInstanceIdsQuery(
                                    Array('id' => $data['exclude_api_account_id'])
            );
        }


        $cacti_instances = $em
            ->getRepository('GizmoCapoBundle:CactiInstance')
            ->getCactiInstances($data, $exclude_query);

        return $this->_encoded_response($cacti_instances, $format);
    }

    /**
     * Grant access to cacti instance for group
     *
     * @return Response OK
     */
    public function enableCactiInstanceForGroupAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('group_id', 'integer'),
            Array('cacti_instance_id', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (!isset($data['group_id'])) {
            return $this->_api_fail(
                'No group id given',
                $format);
        }

        if (!isset($data['cacti_instance_id'])) {
            return $this->_api_fail(
                'No cacti instance id given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();

        $group = $em->getRepository('GizmoCapoBundle:Group')
                   ->findOneBy(array('id' => $data['group_id']));
        $ci = $em->getRepository('GizmoCapoBundle:CactiInstance')
                 ->findOneBy(array('id' => $data['cacti_instance_id']));

        if (! $group) {
            return $this->_api_fail(
                'No such group',
                $format);
        }

        if (! $ci) {
            return $this->_api_fail(
                'No such cacti instance',
                $format);
        }

        $group->addCactiInstance($ci);

        $em->flush();

        $this->_log_event(__FUNCTION__,
                          'group_id:' . $data['group_id'] . ', ' . 'cacti_instance_id:' . $data['cacti_instance_id'],
                          'granted access to cacti instance ' . $ci->getId() . ' (' . $ci->getName() . ') for group ' . $group->getId() . ' (' . $group->getName() . ')'
        );

        return $this->_encoded_response(array('result' => 'OK'), $format);
    }

    /**
     * Revoke access to cacti instance for group
     *
     * @return Response OK
     */
    public function disableCactiInstanceForGroupAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('group_id', 'integer'),
            Array('cacti_instance_id', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('GizmoCapoBundle:Group')
            ->findOneBy(array('id' => $data['group_id']));

        $ci = $em->getRepository('GizmoCapoBundle:CactiInstance')
            ->findOneBy(array('id' => $data['cacti_instance_id']));

        if (! $group) {
            return $this->_api_fail(
                'No such group',
                $format);
        }

        if (! $ci) {
            return $this->_api_fail(
                'No such cacti instance',
                $format);
        }

        $group->removeCactiInstance($ci);

        $em->flush();

        $this->_log_event(__FUNCTION__,
                          'group_id:' . $data['group_id'] . ', ' . 'cacti_instance_id:' . $data['cacti_instance_id'],
                          'revoked access to cacti instance ' . $ci->getId() . ' (' . $ci->getName() . ') for group ' . $group->getId() . ' (' . $group->getName() . ')'
        );

        return $this->_encoded_response(array('result' => 'OK'), $format);
    }

    /**
     * Grant access to cacti instance for group
     *
     * @return Response OK
     */
    public function enableCactiInstanceForApiUserAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('api_user_id', 'integer'),
            Array('cacti_instance_id', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (!isset($data['api_user_id'])) {
            return $this->_api_fail(
                'No api user id given',
                $format);
        }

        if (!isset($data['cacti_instance_id'])) {
            return $this->_api_fail(
                'No cacti instance id given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();

        $api_user = $em->getRepository('GizmoCapoBundle:ApiUser')
                   ->findOneBy(array('id' => $data['api_user_id']));
        $ci = $em->getRepository('GizmoCapoBundle:CactiInstance')
                 ->findOneBy(array('id' => $data['cacti_instance_id']));

        if (! $api_user) {
            return $this->_api_fail(
                'No such api user',
                $format);
        }

        if (! $ci) {
            return $this->_api_fail(
                'No such cacti instance',
                $format);
        }

        $api_user->addCactiInstance($ci);

        $em->flush();

        $this->_log_event(__FUNCTION__,
                          'api_user_id:' . $data['api_user_id'] . ', ' . 'cacti_instance_id:' . $data['cacti_instance_id'],
                          'granted access to cacti instance ' . $ci->getId() . ' (' . $ci->getName() . ') for api user ' . $api_user->getId() . ' (' . $api_user->getUsername() . ')'
        );

        return $this->_encoded_response(array('result' => 'OK'), $format);
    }

    /**
     * Revoke access to cacti instance for group
     *
     * @return Response OK
     */
    public function disableCactiInstanceForApiUserAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('api_user_id', 'integer'),
            Array('cacti_instance_id', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $api_user = $em->getRepository('GizmoCapoBundle:ApiUser')
            ->findOneBy(array('id' => $data['api_user_id']));

        $ci = $em->getRepository('GizmoCapoBundle:CactiInstance')
            ->findOneBy(array('id' => $data['cacti_instance_id']));

        if (! $api_user) {
            return $this->_api_fail(
                'No such api user',
                $format);
        }

        if (! $ci) {
            return $this->_api_fail(
                'No such cacti instance',
                $format);
        }

        $api_user->removeCactiInstance($ci);

        $em->flush();

        $this->_log_event(__FUNCTION__,
                          'api_user_id:' . $data['api_user_id'] . ', ' . 'cacti_instance_id:' . $data['cacti_instance_id'],
                          'revoked access to cacti instance ' . $ci->getId() . ' (' . $ci->getName() . ') for api user ' . $api_user->getId() . ' (' . $api_user->getUsername() . ')'
        );

        return $this->_encoded_response(array('result' => 'OK'), $format);
    }

    /**
     * Put user in a different group
     *
     * @return Response OK
     */
    public function changeGroupForUserAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('group_id', 'integer'),
            Array('user_id', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (!isset($data['group_id'])) {
            return $this->_api_fail(
                'No group id given',
                $format);
        }

        if (!isset($data['user_id'])) {
            return $this->_api_fail(
                'No user id given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();

        $group = $em->getRepository('GizmoCapoBundle:Group')
                   ->findOneBy(array('id' => $data['group_id']));
        $user = $em->getRepository('GizmoCapoBundle:User')
                 ->findOneBy(array('id' => $data['user_id']));

        if (! $group) {
            return $this->_api_fail(
                'No such group',
                $format);
        }

        if (! $user) {
            return $this->_api_fail(
                'No such user',
                $format);
        }

        $user->setGroup($group);

        $em->flush();

        $this->_log_event(__FUNCTION__,
                          'group_id:' . $data['group_id'] . ', ' . 'user_id:' . $data['user_id'],
                          'changed group for user ' . $user->getId() . ' (' . $user->getUserName() . ') to group ' . $group->getId() . ' (' . $group->getName() . ')'
        );

        return $this->_encoded_response(array('result' => 'OK'), $format);
    }

    /**
     * Change properties of an existing Cacti Instance
     *
     * @return Response encoded response
     */
    public function updateCactiInstanceAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('id', 'integer'),
            Array('name', 'text'),
            Array('base_url', 'url'),
            Array('active', 'integer'),
            Array('queue_import', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['id'])) {
            return $this->_api_fail(
                'No cacti instance id given',
                $format);
        } else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('GizmoCapoBundle:CactiInstance');
            $obj = $repo->updateCactiInstance($data['id'], $data);

            if (! $obj) {
                return $this->_api_fail(
                    'No such cacti instance',
                    $format);
            } else {
                $em->persist($obj);
                $em->flush();

                $this->_log_event(__FUNCTION__,
                                  'name:' . $data['name'] . ', base_url:' . $data['base_url'] .  ', active:' . $data['active'] . ', queue_import:' . $data['queue_import'],
                                  'updated cacti instance ' . $obj->getId() . ' with data: name:' . $data['name'] . ', base_url:' . $data['base_url'] .  ', active:' . $data['active'] . ', queue_import:' . $data['queue_import']
                );

                $response['result'] = 'OK';
            }
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Change properties of an existing group
     *
     * @return Response encoded response
     */
    public function updateGroupAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('id', 'integer'),
            Array('name', 'text'),
            Array('active', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['id'])) {
            return $this->_api_fail(
                'No group id given',
                $format);
        } else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('GizmoCapoBundle:Group');
            $obj = $repo->updateGroup($data['id'], $data);

            if (! $obj) {
                return $this->_api_fail(
                    'No such group',
                    $format);
            } else {
                $em->persist($obj);
                $em->flush();

                $this->_log_event(__FUNCTION__,
                                  'name:' . $data['name'] .  ', active:' . $data['active'],
                                  'updated group ' . $obj->getId() . ' with data: name:' . $data['name'] . ', active:' . $data['active']
                );

                $response['result'] = 'OK';
            }
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Change properties of an existing group
     *
     * @return Response encoded response
     */
    public function updateApiUserAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('id', 'integer'),
            Array('username', 'text'),
            Array('password', 'text'),
            Array('active', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['id'])) {
            return $this->_api_fail(
                'No ApiUser id given',
                $format);
        } else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('GizmoCapoBundle:ApiUser');
            $obj = $repo->updateApiUser($data['id'], $data);

            if (! $obj) {
                return $this->_api_fail(
                    'No such ApiUser',
                    $format);
            } else {
                $em->persist($obj);
                $em->flush();

                $this->_log_event(__FUNCTION__,
                                  'name:' . $data['username'] .  ', active:' . $data['active'],
                                  'updated api user ' . $obj->getId() . ' with data: username:' . $data['username'] . ', password: ***, active:' . $data['active']
                );

                $response['result'] = 'OK';
            }
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Change properties of an existing user
     *
     * @return Response encoded response
     */
    public function updateUserAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('id', 'integer'),
            Array('enabled', 'integer'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['id'])) {
            return $this->_api_fail(
                'No user id given',
                $format);
        } else {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('GizmoCapoBundle:User');
            $obj = $repo->updateUser($data['id'], $data);

            if (! $obj) {
                return $this->_api_fail(
                    'No such user',
                    $format);
            } else {
                $em->persist($obj);
                $em->flush();

                $this->_log_event(__FUNCTION__,
                                  'enabled:' . $data['enabled'],
                                  'updated user ' . $obj->getId() . ' with data: enabled:' . $data['enabled']
                );

                $response['result'] = 'OK';
            }
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Create new Cacti Instance
     *
     * @return Response encoded response
     */
    public function createCactiInstanceAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('name', 'text'),
            Array('base_url', 'url'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['name'])) {
            return $this->_api_fail(
                'Cacti Instance Name required',
                $format);
        }

        if (empty($data['base_url'])) {
            return $this->_api_fail(
                'Cacti Instance Base URL required',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('GizmoCapoBundle:CactiInstance');

        $existing_obj = $repo->findOneByName($data['name']);
        if ($existing_obj) {
            return $this->_api_fail(
                'Cacti Instance with this name already exists',
                $format);
        }

        $existing_obj = $repo->findOneBy(array('base_url' => $data['base_url']));
        if ($existing_obj) {
            return $this->_api_fail(
                'Cacti Instance with this URL already exists',
                $format);
        }

        $obj = $repo->createCactiInstance($data['name'], $data['base_url']);

        if (! $obj) {
            return $this->_api_fail(
                'Failed to create Cacti Instance',
                $format);
        } else {
            $em->persist($obj);
            $em->flush();

            $this->_log_event(__FUNCTION__,
                              'name:' . $data['name'] . ', base_url:' . $data['base_url'],
                              'created cacti instance ' . $obj->getId() . ' with data: name:' . $data['name'] . ', base_url:' . $data['base_url']
            );

            $response['result'] = 'OK';
            $response['cacti_instance_id'] = $obj->getId();
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Create new Group
     *
     * @return Response encoded response
     */
    public function createGroupAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('name', 'text'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['name'])) {
            return $this->_api_fail(
                'Group name required',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('GizmoCapoBundle:Group');

        $existing_obj = $repo->findOneByName($data['name']);
        if ($existing_obj) {
            return $this->_api_fail(
                'Group with this name already exists',
                $format);
        }

        $obj = $repo->createGroup($data['name']);

        if (! $obj) {
            return $this->_api_fail(
                'Failed to create Group',
                $format);
        } else {
            $em->persist($obj);
            $em->flush();

            $this->_log_event(__FUNCTION__,
                              'name:' . $data['name'],
                              'created group ' . $obj->getId() . ' with data: name:' . $data['name']
            );

            $response['result'] = 'OK';
            $response['group_id'] = $obj->getId();
        }

        return $this->_encoded_response($response, $format);
    }
    /**
     * Create new Group
     *
     * @return Response encoded response
     */
    public function createApiUserAction()
    {
        $this->_need_admin_privileges();

        $form = Array(
            Array('username', 'text'),
            Array('format', 'text')
        );

        $data = $this->_get_request_data($form);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['username'])) {
            return $this->_api_fail(
                'Username required',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('GizmoCapoBundle:ApiUser');

        $existing_obj = $repo->findOneByUsername($data['username']);
        if ($existing_obj) {
            return $this->_api_fail(
                'ApiUser with this username already exists',
                $format);
        }

        $obj = $repo->createApiUser($data['username']);

        if (! $obj) {
            return $this->_api_fail(
                'Failed to create ApiUser',
                $format);
        } else {
            $em->persist($obj);
            $em->flush();

            $this->_log_event(__FUNCTION__,
                              'name:' . $data['username'],
                              'created api user ' . $obj->getId() . ' with data: username:' . $data['username']
            );

            $response['result'] = 'OK';
            $response['api_user_id'] = $obj->getId();
        }

        return $this->_encoded_response($response, $format);
    }
}
