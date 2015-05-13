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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Capo Base controller
 *
 * This class provides reusable functions for controllers
 *
 * @author Jochem Kossen <jochem@jkossen.nl>
 */
abstract class BaseController extends Controller
{
    protected $_privileges = null;

    /**
     * Determine environment
     *
     * @return String environment
     */
    protected function _get_environment()
    {
        $kernel = $this->get('kernel');
        return $kernel->getEnvironment();
    }

    /**
     * Determine if access control should be enabled or not
     *
     * @return bool enabled
     */
    protected function _access_control_enabled()
    {
        if ($this->_get_environment() === 'test') {
            return false;
        }

        return true;
    }

    /**
     * Convenience function for getting the Symfony user object
     *
     * @return Object user
     */
    protected function _get_user()
    {
        if ($this->_access_control_enabled()) {
            return $this->get('security.context')->getToken()->getUser();
        } else {
            return null;
        }
    }

    /**
     * Check and load user privileges
     *
     * @return Array array with privileges
     */
    protected function _get_privileges()
    {
        if (!is_array($this->_privileges)) {
            if ($this->_access_control_enabled()) {
                $admins = $this->container->getParameter('admins');

                if (count($admins) === 0) {
                    throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException('Please specify some admins in parameters.yml');
                }

                $user = $this->_get_user();
                $group = null;
                $is_api_user = (get_class($user) === 'Gizmo\CapoBundle\Entity\ApiUser');

                if (!$is_api_user) {
                    $group = $user->getGroup();
                }

                if ((! $is_api_user) && $group === null) {
                    $grpname = trim($user->getLdapGroup());

                    if ($grpname === '') {
                        $grpname = 'default';
                    }

                    $em = $this->getDoctrine()->getManager();
                    $repo = $em->getRepository('GizmoCapoBundle:Group');
                    $group = $repo->findOneByName($grpname);

                    if (! $group) {
                        $group = $repo->createGroup($grpname);
                        $em->persist($group);
                    }

                    $user->setGroup($group);
                    $em->persist($user);

                    $em->flush();
                }

                $this->_privileges['user'] = $user;
                $this->_privileges['api_user'] = $is_api_user;
                $this->_privileges['capo_user_id'] = $user->getId();
                $this->_privileges['capo_group_id'] = null;
                $this->_privileges['user_is_admin'] = false;

                if (!$is_api_user) {
                    $this->_privileges['group'] = $group;
                    $this->_privileges['capo_group_id'] = $group->getId();
                    $this->_privileges['user_is_admin'] = in_array($user->getUsername(), $admins);
                    $this->_privileges['user_has_access'] = false;
                    if (($this->_privileges['user_is_admin']) ||
                        ($group->getActive() && $group->getCactiInstances()->count() > 0)
                    ) {
                        $this->_privileges['user_has_access'] = true;
                    }
                } else {
                    if ($user->getActive() && $user->getCactiInstances()->count() > 0) {
                        $this->_privileges['user_has_access'] = true;
                    }
                }
            } else {
                $this->_privileges['user'] = $this->_get_user();
                $this->_privileges['api_user'] = false;
                $this->_privileges['capo_user_id'] = 1;
                $this->_privileges['capo_group_id'] = 1;
                $this->_privileges['user_is_admin'] = true;
                $this->_privileges['user_has_access'] = true;
            }
        }

        return $this->_privileges;
    }

    /**
     * If access control is enabled, check if the user is admin and bail out if
     * the user is not
     */
    protected function _need_admin_privileges()
    {
        $privileges = $this->_get_privileges();
        if (! $privileges['user_is_admin']) {
            throw $this->createNotFoundException('Not found');
        }
    }

    /**
     * Create an event log message
     */
    protected function _log_event($str_function, $str_args, $message=null)
    {
        $access_control_enabled = $this->_access_control_enabled();
        $logger = $this->get('event_logger');
        $logger->log(get_class($this), $str_function, $str_args, $message, $access_control_enabled);
    }

    /**
     * Get the GET or POST data
     *
     * Determine if it's a POST or GET request, then parse and return data
     *
     * @param Array $form_fields array of fields and corresponding field types
     *
     * @return Array request data
     */
    protected function _get_request_data(Array $form_fields)
    {
        $kernel = $this->get('kernel');
        $environment = $this->_get_environment();
        $request = $this->getRequest();

        $privileges = $this->_get_privileges();

        $fb = $this->createFormBuilder();
        foreach ($form_fields as $field) {
            $fb->add($field[0], $field[1]);
        }

        $form = $fb->getForm();

        if ($request->isMethod('POST')) {
            $form->bind($request->request->all());
        } else {
            $form->bind($request->query->all());
        }

        $data = $form->getData();

        $data['capo_user_id'] = $privileges['capo_user_id'];
        $data['capo_group_id'] = $privileges['capo_group_id'];
        $data['user_is_admin'] = $privileges['user_is_admin'];
        $data['api_user'] = $privileges['api_user'];

        return $data;
    }

    /**
     * Check if given format is supported, if not, return default format
     *
     * @param $format requested format
     *
     * @return String $format
     */
    protected function _get_supported_format($format) {
        $default_format = 'json';
        $supported_formats = Array('xml', 'json');

        if (in_array($format, $supported_formats)) {
            return $format;
        }

        return $default_format;
    }

    /**
     * Encode the input according to given format
     *
     * @param Array $array_input
     * @param $format format to encode to
     * @param $code HTTP response code
     *
     * @return Response encoded response
     */
    protected function _encoded_response(Array $array_input, $format, $code=200)
    {
        if ($format === 'xml') {
            $xml = $this->get('xml_conversion');
            $xml->parseArray($array_input);
            return new Response($xml->toString(), $code);
        } else {
            return new JsonResponse($array_input, $code);
        }
    }

    /**
     * Standardized error messages for the API
     *
     * @param String $message error message
     * @param String $format format to encode to
     * @param int $errcode HTTP error code to return
     *
     * @return Response encoded response
     */
    protected function _api_fail($message, $format, $errcode=400)
    {
        return $this->_encoded_response(
            array('result' => 'ERROR',
                  'message' => $message),
            $format,
            $errcode);
    }
}
