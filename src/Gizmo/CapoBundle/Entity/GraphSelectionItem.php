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

namespace Gizmo\CapoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Gizmo\CapoBundle\Entity\GraphSelectionItem
 *
 * @ORM\Table(name="graph_selection_item", indexes={
 * @ORM\Index(name="idx_itemnr", columns={"itemnr"})
 * },
 * uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_selection_graph", columns={"graph_selection_id","graph_id"})
 *})
 *
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\GraphSelectionItemRepository")
 */
class GraphSelectionItem
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $itemnr
     *
     * @ORM\Column(name="itemnr", type="integer", nullable=false)
     */
    protected $itemnr;

    /**
     * @ORM\ManyToOne(targetEntity="GraphSelection", inversedBy="graph_selection_items")
     * @ORM\JoinColumn(name="graph_selection_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $graph_selection;

    /**
     * @ORM\ManyToOne(targetEntity="Graph", inversedBy="graph_selection_items")
     * @ORM\JoinColumn(name="graph_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $graph;

    /**
     * Set id
     *
     * @param int $id
     * @return GraphSelectionItem
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set itemnr
     *
     * @param int $itemnr
     * @return GraphSelectionItem
     */
    public function setItemNr($itemnr)
    {
        $this->itemnr = $itemnr;

        return $this;
    }

    /**
     * Get itemnr
     *
     * @return integer
     */
    public function getItemNr()
    {
        return $this->itemnr;
    }

    /**
     * Set graph selection
     *
     * @param GraphSelection $graph_selection
     * @return GraphSelectionItem
     */
    public function setGraphSelection($graph_selection)
    {
        $this->graph_selection = $graph_selection;

        return $this;
    }

    /**
     * Get graph selection
     *
     * @return GraphSelection
     */
    public function getGraphSelection()
    {
        return $this->graph_selection;
    }

    /**
     * Set graph
     *
     * @param Graph $graph
     * @return GraphSelectionItem
     */
    public function setGraph($graph)
    {
        $this->graph = $graph;

        return $this;
    }

    /**
     * Get graph
     *
     * @return Graph
     */
    public function getGraph()
    {
        return $this->graph;
    }
}
