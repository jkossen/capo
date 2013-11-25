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

use Symfony\Component\HttpFoundation\Response;
use \Gizmo\CapoBundle\Services\PDFService;

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

        $ch = curl_init($img_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'code=' . $code);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);

        $image_data = curl_exec($ch);

        if (! $image_data) {
            throw $this->createNotFoundException('Unable to retrieve graph data');
        }

        $tmpfname = tempnam(sys_get_temp_dir(), 'capo_pdf_img');
        $fh = fopen($tmpfname, 'w');
        if (! fwrite($fh, $image_data)) {
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
    protected function _create_content_multiple_graphs(PDFService $pdf, Array $data, $rra_id)
    {
        $rra = array(
            '1' => 'Daily (5 minute average)',
            '2' => 'Weekly (30 Minute Average)',
            '3' => 'Monthly (2 Hour Average)',
            '4' => 'Yearly (1 Day Average)'
        );

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
                $graph['graph_local_id'] . '&rra_id=' . $rra_id;

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
                               $pdf->Cell(0,5,$rra[$rra_id],0,1,'C'),                           
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
    public function pdfSingleGraphAction()
    {
        $request = $this->getRequest();

        if (! $request->isMethod('POST')) {
            throw $this->createNotFoundException('Only POST is supported');
        } else {
            $form = Array(
                Array('graph', 'integer')
            );

            $data = $this->_get_request_data($form);

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
    public function pdfGraphSelectionAction()
    {
        $request = $this->getRequest();

        if (! $request->isMethod('POST')) {
            throw $this->createNotFoundException('Only POST is supported');
        } else {
            $form = Array(
                Array('graphs_selected', 'text')
            );

            $data = $this->_get_request_data($form);
            $graphs_indexed = Array();
            $graphs_validated = Array();
            $graphs_posted = json_decode($data['graphs_selected']);
            $rra_id = intval($request->request->get('rra_id'));

            if ($rra_id > 10 || $rra_id < 1) {
                $rra_id = 1;
            }

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
            $this->_create_content_multiple_graphs($pdf, $data, $rra_id);
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
