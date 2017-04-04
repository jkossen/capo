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

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Capo JSON API controller
 *
 * This class provides functions for retrieving Capo data in JSON format
 *
 * @author Jochem Kossen <jochem@jkossen.nl>
 */
class ApiController extends BaseController
{
    /**
     * Get information about a Cacti graph
     *
     * The wanted graph id is given using GET or POST input data
     *
     * @return Response JSON encoded array of graph data
     */
    public function getGraphAction(Request $request)
    {
        $form = Array(
            Array('id', IntegerType::class),
            Array('format', TextType::class)
        );

        $response = Array();

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        if (!isset($data['id'])) {
            return $this->_api_fail(
                'No graph id given',
                $format);
        } else {
            $em = $this->getDoctrine()->getManager();
            $graph = $em->getRepository('GizmoCapoBundle:Graph')
                ->getGraph($data, true);

            if (! $graph) {
                return $this->_api_fail(
                    'No such graph',
                    $format);
            } else {
                $response['graph'] = $graph;
            }
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Get JSON encoded array of Cacti instances
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of Cacti instances
     */
    public function getCactiInstancesAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('active_only', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $cacti_instances = $em
            ->getRepository('GizmoCapoBundle:CactiInstance')
            ->getCactiInstances($data);

        return $this->_encoded_response($cacti_instances, $format);
    }

    /**
     * Get JSON encoded array of Cacti graphs in a selection
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of graph selections
     */
    public function getGraphSelectionGraphsAction(Request $request)
    {
        $form = Array(
            Array('graph_selection_id', IntegerType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $items = $em
            ->getRepository('GizmoCapoBundle:GraphSelection')
            ->getItems($data, true);

        return $this->_encoded_response($items, $format);
    }

    /**
     * Get JSON encoded array of Cacti graph selections
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of graph selections
     */
    public function getGraphSelectionsAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $graphs = $em
            ->getRepository('GizmoCapoBundle:GraphSelection')
            ->getGraphSelections($data, true);

        return $this->_encoded_response($graphs, $format);
    }

    /**
     * Create new Graph Selection
     *
     * @return Response encoded response
     */
    public function saveGraphSelectionAction(Request $request)
    {
        $form = Array(
            Array('name', TextType::class),
            Array('graphs', TextType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['name'])) {
            return $this->_api_fail(
                'Failed to create graph selection: no name given',
                $format);
        }

        $graph_ids = json_decode($data['graphs']);

        if (count($graph_ids) < 1) {
            return $this->_api_fail(
                'No graphs given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $graphs = $em->getRepository('GizmoCapoBundle:Graph')
                     ->findById($graph_ids);

        if (count($graphs) < 1) {
            return $this->_api_fail(
                'No graphs found',
                $format);
        }

        $repo = $em->getRepository('GizmoCapoBundle:GraphSelection');
        $user = $em->getRepository('GizmoCapoBundle:User')
                   ->findOneById($data['capo_user_id']);

        if (! $user) {
            return $this->_api_fail(
                'No such user',
                $format);
        }

        $obj = $repo->createGraphSelection($data['name'], $user, $graphs, $graph_ids);

        $current_selections = $repo->getGraphSelections($data, true);
        $nr_of_selections = $current_selections['graph_selections_total'];

        if (intval($nr_of_selections) >= intval($this->container->getParameter('max_selections_per_user'))) {
            return $this->_api_fail(
                'Nr of graph selections exceeded',
                $format);
        }

        if (! $obj) {
            return $this->_api_fail(
                'Failed to create Graph Selection',
                $format);
        } else {
            $em->persist($obj);
            $em->flush();

            $response['result'] = 'OK';
            $response['graph_selection_id'] = $obj->getId();
        }

        return $this->_encoded_response($response, $format);
    }

    /**
     * Disable Graph Selection
     *
     * @return Response encoded response
     */
    public function disableGraphSelectionAction(Request $request)
    {
        $form = Array(
            Array('graph_selection', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['graph_selection'])) {
            return $this->_api_fail(
                'No graph selection given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository('GizmoCapoBundle:GraphSelection')->disableGraphSelection($data);

        if ($obj) {
            $em->persist($obj);
            $em->flush();

            $this->_log_event(__FUNCTION__, 'graph_selection:' . $data['graph_selection']);
            return $this->_encoded_response(array('result' => 'OK', 'name' => $obj->getName()), $format);
        } else {
            return $this->_api_fail(
                'No such graph selection',
                $format);
        }
    }

    /**
     * Rename Graph Selection
     *
     * @return Response encoded response
     */
    public function renameGraphSelectionAction(Request $request)
    {
        $form = Array(
            Array('graph_selection', IntegerType::class),
            Array('name', TextType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['graph_selection'])) {
            return $this->_api_fail(
                'Failed to rename graph selection: no graph_selection given',
                $format);
        }

        if (empty($data['name'])) {
            return $this->_api_fail(
                'Failed to rename graph selection: no name given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository('GizmoCapoBundle:GraphSelection')
                  ->updateGraphSelection($data);

        if ($obj) {
            $em->persist($obj);
            $em->flush();
            
            $this->_log_event(__FUNCTION__,
                              'graph_selection_id:' . $data['graph_selection'] . ', ' . 'name:' . $data['name']);

            return $this->_encoded_response(array('result' => 'OK', 'name' => $obj->getName()), $format);
        } else {
            return $this->_api_fail(
                'No such graph selection',
                $format);
        }
    }

    /**
     * Reposition graph selection item
     *
     * @return Response encoded response
     */
    public function changeGraphSelectionItemItemNrAction(Request $request)
    {
        $form = Array(
            Array('item_id', IntegerType::class),
            Array('new_pos', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        if (empty($data['item_id'])) {
            return $this->_api_fail(
                'Failed to reposition graph selection item: no item_id given',
                $format);
        }

        if (empty($data['new_pos'])) {
            return $this->_api_fail(
                'Failed to reposition graph selection item: no new_pos given',
                $format);
        }

        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository('GizmoCapoBundle:GraphSelectionItem')
                  ->repositionItem($data);

        if ($obj) {
            $em->persist($obj);
            $em->flush();
            
            $this->_log_event(__FUNCTION__,
                              'graph_selection_item_id:' . $data['item_id'] . ', ' . 'new_pos:' . $data['new_pos']);

            return $this->_encoded_response(array('result' => 'OK'), $format);
        } else {
            return $this->_api_fail(
                'No such graph selection item',
                $format);
        }
    }

    /**
     * Get JSON encoded array of Cacti graphs
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of Cacti graphs
     */
    public function getGraphsAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('cacti_instance', IntegerType::class),
            Array('graph_template', TextType::class),
            Array('host', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $graphs = $em
            ->getRepository('GizmoCapoBundle:Graph')
            ->getGraphs($data, true);

        return $this->_encoded_response($graphs, $format);
    }

    /**
     * Get JSON encoded array of Cacti graph templates
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of Cacti graph templates
     */
    public function getGraphTemplatesAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('cacti_instance', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $graph_templates = $em
            ->getRepository('GizmoCapoBundle:GraphTemplate')
            ->getGraphTemplates($data, true);

        return $this->_encoded_response($graph_templates, $format);
    }

    /**
     * Get JSON encoded array of hosts
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of hosts
     */
    public function getHostsAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('cacti_instance', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $hosts = $em->getRepository('GizmoCapoBundle:Host')
            ->getHosts($data, true);

        return $this->_encoded_response($hosts, $format);
    }

    /**
     * Get encoded array of graph titles
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response encoded array of graph titles
     */
    public function getGraphTitlesAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('cacti_instance', IntegerType::class),
            Array('graph_template', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $graph_titles = $em
            ->getRepository('GizmoCapoBundle:Graph')
            ->getGraphTitles($data, true);

        return $this->_encoded_response($graph_titles, $format);
    }

    /**
     * Get JSON encoded array of Cacti weathermaps
     *
     * Results are filtered based on GET or POST input data
     *
     * @return Response JSON encoded array of weathermaps
     */
    public function getWeathermapsAction(Request $request)
    {
        $form = Array(
            Array('q', TextType::class),
            Array('cacti_instance', IntegerType::class),
            Array('page_limit', IntegerType::class),
            Array('page', IntegerType::class),
            Array('format', TextType::class)
        );

        $data = $this->_get_request_data($form, $request);
        $format = $this->_get_supported_format($data['format']);

        $em = $this->getDoctrine()->getManager();
        $weathermaps = $em
            ->getRepository('GizmoCapoBundle:Weathermap')
            ->getWeathermaps($data, true);

        return $this->_encoded_response($weathermaps, $format);
    }

    /**
     * Show specified graph image
     *
     * @param int $graph_id
     * @param int $rra_id
     *
     * @return Response PNG image of graph
     */
    public function showGraphAction(Request $request, $graph_id, $rra_id = 0, $graph_start=0, $graph_end=0)
    {
        $form = Array();
        $rra_id = intval($rra_id);
        $graph_id = intval($graph_id);
        $graph_start = intval($graph_start);
        $graph_end = intval($graph_end);

        $data = $this->_get_request_data($form, $request);
        $data['id'] = $graph_id;

        if ($rra_id > 10) {
            throw $this->createNotFoundException('No such rra');
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $graph = $em->getRepository('GizmoCapoBundle:Graph')->getGraph($data);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('No such graph');
        }

        $img_url = $graph->getCactiInstance()->getBaseUrl() .
            'capo_graph_image.php?action=view&local_graph_id=' .
        $graph->getGraphLocalId() . '&rra_id=' . $rra_id;

        if ($graph_start !== 0 && $graph_end !== 0) {
            $img_url = $graph->getCactiInstance()->getBaseUrl() .
            'capo_graph_image.php?action=zoom&local_graph_id=' .
            $graph->getGraphLocalId() . '&rra_id=' . $rra_id . 
            '&graph_start=' . $graph_start . '&graph_end=' . $graph_end;
        }

        $code = $this->container->getParameter('capo_retrieval_code');
        if (empty($code)) {
            throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException('Please specify capo_retrieval_code in parameters.yml');
        }

        $client = new Client();
        $jar = new CookieJar();

        try {
            $res = $client->get($img_url, ['cookies' => $jar]);
            $body = $res->getBody();

            if (preg_match('/.* csrfMagicToken = "([^"]*)".*/', $body, $matches)) {
                $csrfMagicToken = $matches[1];
            }
            if (preg_match('/.* csrfMagicName = "([^"]*)".*/', $body, $matches)) {
                $csrfMagicName = $matches[1];
            }

            if (isset($csrfMagicName)) {
                $postData = [ 'code' => $code, "$csrfMagicName" => "$csrfMagicToken" ];
            } else {
                $postData = [ 'code' => $code ];
            }

            $res = $client->post($img_url, [ 'form_params' => $postData, 'cookies' => $jar ]);

            $image = $res->getBody();

        } catch (TransferException $e) {
            // could not retrieve image, show error image
            $arrBundle = $this->get('kernel')->getBundles();
            $image = file_get_contents($arrBundle['GizmoCapoBundle']->getPath() .
                    '/Resources/public/images/capo_graph_error.png');
        }

        $this->_log_event(__FUNCTION__,
                          'graph_id:' . $graph_id . ', ' . 'rra_id:' . $rra_id,
                          'showGraphAction: cacti_instance: ' . $graph->getCactiInstance()->getId() . ' (' . $graph->getCactiInstance()->getName() . ')' .
                          ', graph: ' . $graph->getGraphLocalId() . ' (' . $graph->getTitleCache() . ')'
        );

        $response = new Response($image);
        $response->headers->set('Content-type', 'image/png');
        return $response;
    }

    /**
     * Show specified weathermap image
     *
     * @param int $wmap_id
     *
     * @return Response PNG image of weathermap
     */
    public function showWmapAction(Request $request, $wmap_id)
    {
        $form = Array();

        $data = $this->_get_request_data($form, $request);
        $data['id'] = $wmap_id;

        try {
            $em = $this->getDoctrine()->getManager();
            $wmap = $em->getRepository('GizmoCapoBundle:Weathermap')->getWeathermap($data);
            
        } catch (\Exception $e) {
            throw $this->createNotFoundException('No such weathermap');
        }

        $img_url = $wmap->getCactiInstance()->getBaseUrl() .
        'capo_wmap_image.php?id=' . $wmap->getOrigId();

        $code = $this->container->getParameter('capo_retrieval_code');
        if (empty($code)) {
            throw new \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException('Please specify capo_retrieval_code in parameters.yml');
        }

        $client = new Client();
        $jar = new CookieJar();

        try {
            $res = $client->get($img_url, ['cookies' => $jar]);
            $body = $res->getBody();

            if (preg_match('/.* csrfMagicToken = "([^"]*)".*/', $body, $matches)) {
                $csrfMagicToken = $matches[1];
            }
            if (preg_match('/.* csrfMagicName = "([^"]*)".*/', $body, $matches)) {
                $csrfMagicName = $matches[1];
            }

            if (isset($csrfMagicName)) {
                $postData = [ 'code' => $code, "$csrfMagicName" => "$csrfMagicToken" ];
            } else {
                $postData = [ 'code' => $code ];
            }

            $res = $client->post($img_url, [ 'form_params' => $postData, 'cookies' => $jar ]);

            $image = $res->getBody();

        } catch (TransferException $e) {
            // could not retrieve image, show error image
            $arrBundle = $this->get('kernel')->getBundles();
            $image = file_get_contents($arrBundle['GizmoCapoBundle']->getPath() .
                '/Resources/public/images/capo_graph_error.png');
        }

        /**
         * 2013-06-26: logging for weathermaps disabled, autorefresh causes a lot of lines :)
         *
        $this->_log_event(__FUNCTION__,
                          'wmap_id:' . $wmap_id,
                          'showWmapAction: cacti_instance: ' . $wmap->getCactiInstance()->getId() . ' (' . $wmap->getCactiInstance()->getName() . ')' .
                          ', weathermap: ' . $wmap->getId() . ' (' . $wmap->getTitleCache() . ')'
        );
        */

        $response = new Response($image);
        $response->headers->set('Content-type', 'image/png');
        return $response;
    }
}
