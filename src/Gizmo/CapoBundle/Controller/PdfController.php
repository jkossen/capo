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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use \Gizmo\CapoBundle\Services\PDFService;
use \Gizmo\CapoBundle\Model\PredefinedTimespan;

class PdfController extends BaseController
{
    /**
     * Initialize PDF Service
     *
     * @return PDFService $pdf
     */
    protected function _initPdf()
    {
        $pdf = $this->get('pdf_exporter');
        $pdf->AliasNbPages();
        $pdf->setAutoPageBreak(true);
        $pdf->AddPage();
        $pdf->SetFont('Arial','',10);

        return $pdf;
    }

    /**
     * Retrieve graph image from cacti server
     *
     * @return string path to which image data was written
     */
    protected function _retrieve_image($img_url)
    {
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
            throw $this->createNotFoundException('Unable to retrieve graph data');
        }

        $tmpfname = tempnam(sys_get_temp_dir(), 'capo_pdf_img');
        $fh = fopen($tmpfname, 'w');
        if (! fwrite($fh, $image)) {
            fclose($fh);
            unlink($tmpfname);
            throw $this->createNotFoundException('Unable to write graph data');
        }
        fclose($fh);

        return $tmpfname;
    }

    /**
     * PDF content creation for a single graph
     *
     * @param PDFService $pdf
     * @param Array $data
     *
     */
    protected function _create_content_single_graph(PDFService $pdf, Array $data)
    {
        $rra = array(
            '1' => 'Daily (5 minute average)',
            '2' => 'Weekly (30 Minute Average)',
            '3' => 'Monthly (2 Hour Average)',
            '4' => 'Yearly (1 Day Average)'
        );

        $graph = $data['graph'];

        for ($i=1; $i<=4; $i++) {
            if ($i === 3) {
                $pdf->AddPage();
            }
            $img_url = $graph->getCactiInstance()->getBaseUrl() .
            'capo_graph_image.php?action=view&local_graph_id=' .
            $graph->getGraphLocalId() .
            '&rra_id=' . $i;

            try {
                $tmpfname = $this->_retrieve_image($img_url);
                $pdf->Cell(0, 0,
                           $pdf->Cell(0,10,$graph->getTitleCache(),0,1,'C') .
                           $pdf->Image($tmpfname,
                                       20,
                                       null,
                                       -110,
                                       0,
                                       'PNG') .
                           $pdf->Cell(0,5,$rra[$i],0,1,'C'),
                           0, 1, 'C', false);
                unlink($tmpfname);
            } catch (\Exception $e) { }
        }
    }

    /**
     * PDF content creation for multiple graphs
     *
     * @param PDFService $pdf
     * @param Array data
     *
     */
    protected function _create_content_multiple_graphs(PDFService $pdf, Array $data, $rra_id, $timespan_id)
    {
        $rra = array(
            '1' => 'Daily (5 minute average)',
            '2' => 'Weekly (30 Minute Average)',
            '3' => 'Monthly (2 Hour Average)',
            '4' => 'Yearly (1 Day Average)'
        );

        if (null !== $timespan_id) {
            $span = new PredefinedTimespan($timespan_id);
            $suffix = sprintf('&graph_start=%d&graph_end=%d', $span->getStart()->getTimestamp(), $span->getEnd()->getTimestamp());
            $desc = $span->getDescription();
        } else {
            if (null === $rra_id || !array_key_exists($rra_id, $rra)) {
                $rra_id = 1;
            }
            $suffix = sprintf('&rra_id=%d', $rra_id);
            $desc = $rra[$rra_id];
        }

        $i = 0;
        $graph_ids_indexed = array_keys($data['graphs_indexed']);
        foreach (json_decode($data['graphs_selected']) as $selected_graph_id) {
            if (in_array($selected_graph_id, $graph_ids_indexed)) {
                $graph = $data['graphs_indexed'][$selected_graph_id];

                if ($i === 2) {
                    $pdf->AddPage();
                    $i = 0;
                }

                $img_url = $graph['cacti_instance']['base_url'] .
                'capo_graph_image.php?action=view&local_graph_id=' .
                $graph['graph_local_id'] . $suffix;

                try {
                    $tmpfname = $this->_retrieve_image($img_url);
                    $pdf->Cell(0, 0,
                               $pdf->Cell(0,10,$graph['title_cache'],0,1,'C') .
                               $pdf->Image($tmpfname,
                                           20,
                                           null,
                                           -110,
                                           0,
                                           'PNG') .
                               $pdf->Cell(0,5,$desc,0,1,'C'),
                               0, 1, 'C', false);
                    unlink($tmpfname);
                } catch (\Exception $e) { }
                $i++;
            }
        }
    }

    /**
     * Export single graph to PDF with all RRA periods
     *
     * @return Response PDF file
     */
    public function pdfSingleGraphAction(Request $request)
    {
        if (! $request->isMethod('POST')) {
            throw $this->createNotFoundException('Only POST is supported');
        } else {
            $form = Array(
                Array('graph', IntegerType::class)
            );

            $data = $this->_get_request_data($form, $request);

            if (!isset($data['graph'])) {
                throw $this->createNotFoundException('No graph given');
            }

            if ($data['graph'] < 1) {
                throw $this->createNotFoundException('No such graph');
            }

            $data['id'] = $data['graph'];

            $em = $this->getDoctrine()->getManager();

            try {
                $graph = $em->getRepository('GizmoCapoBundle:Graph')
                            ->getGraph($data, false);
            } catch (\Exception $e) {
                throw $this->createNotFoundException('No such graph');
            }

            $pdfdata = Array();
            $pdfdata['graph'] = $graph;
            $pdf = $this->_initPdf();
            $this->_create_content_single_graph($pdf, $pdfdata);
            $output = $pdf->Output('capo.pdf', 'S');

            $this->_log_event(__FUNCTION__, 'graph_id: ' . $data['id'], 'pdfSingleGraphAction: graph: ' . $data['id'] . ' (' . $graph->getCactiInstance()->getName() . ' - ' . $graph->getTitleCache() . ')');

            $response = new Response();
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition',
                                    'attachment; filename="capo_' .
                                    date('YmdHs') . '.pdf"');
            $response->setContent($output);
            return $response;
        }
    }

    /**
     * Export a PDF selection to PDF
     *
     * @return Response PDF file
     */
    public function pdfGraphSelectionAction(Request $request)
    {
        if (! $request->isMethod('POST')) {
            throw $this->createNotFoundException('Only POST is supported');
        } else {
            $form = Array(
                Array('graphs_selected', TextType::class),
                Array('rra_id', IntegerType::class),
                Array('predefined_timespan_id', IntegerType::class),
            );

            $data = $this->_get_request_data($form, $request);
            $graphs_indexed = Array();
            $graphs_validated = Array();
            $graphs_posted = json_decode($data['graphs_selected']);

            if (empty($graphs_posted)) {
                throw $this->createNotFoundException('No graph ids given');
            }

            // make sure we only use positive integers
            foreach ($graphs_posted as $graph_id) {
                $graphs_validated[] = abs(intval($graph_id));
            }

            $data['graph_ids'] = json_encode($graphs_validated);

            $em = $this->getDoctrine()->getManager();
            $res = $em->getRepository('GizmoCapoBundle:Graph')
                      ->getGraphs($data, true);

            $graphs = $res['graphs'];

            foreach ($graphs as $graph) {
                $graphs_indexed[$graph['id']] = $graph;
            }

            $nr_of_exported_graphs = 0;

            foreach ($graphs_validated as $selected_graph_id) {
                if (in_array($selected_graph_id, array_keys($graphs_indexed))) {
                    $nr_of_exported_graphs++;
                }
            }

            if ($nr_of_exported_graphs < 1) {
                throw $this->createNotFoundException('Given graphs not found');
            }

            $data['graphs_indexed'] = $graphs_indexed;

            $pdf = $this->_initPdf();

            $this->_create_content_multiple_graphs($pdf, $data, $data['rra_id'], $data['predefined_timespan_id']);

            $output = $pdf->Output('capo.pdf', 'S');

            $this->_log_event(__FUNCTION__, count($graphs_posted) . ' graphs',
                'pdfGraphSelectionAction: ' . count($graphs_posted) . ' graphs');

            $response = new Response();
            $response->setContent($output);
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition',
                                    'attachment; filename="capo_' .
                                    date('YmdHs') . '.pdf"');

            return $response;
        }
    }
}
