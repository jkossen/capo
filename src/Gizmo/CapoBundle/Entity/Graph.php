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
 * Gizmo\CapoBundle\Entity\Graph
 *
 * @ORM\Table(name="graph", indexes={
 *      @ORM\Index(name="idx_graph_local_id", columns={"graph_local_id"}),
 *      @ORM\Index(name="idx_title", columns={"title"}),
 *      @ORM\Index(name="idx_title_cache", columns={"title_cache"})
 * },
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_ci_graph", columns={"cacti_instance_id","graph_local_id"})
 * })
 *
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\GraphRepository")
 */
class Graph
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
     * @var GraphTemplate $graph_template
     *
     * @ORM\ManyToOne(targetEntity="GraphTemplate", inversedBy="graphs")
     * @ORM\JoinColumn(name="graph_template_id", referencedColumnName="id", nullable=false)
     */
    protected $graph_template;

    /**
     * @var CactiInstance $cacti_instance
     *
     * @ORM\ManyToOne(targetEntity="CactiInstance", inversedBy="graphs")
     * @ORM\JoinColumn(name="cacti_instance_id", referencedColumnName="id", nullable=false)
     */
    protected $cacti_instance;

    /**
     * @var Host $host
     *
     * @ORM\ManyToOne(targetEntity="Host", inversedBy="graphs")
     * @ORM\JoinColumn(name="host_id", referencedColumnName="id", nullable=false)
     */
    protected $host;

    /**
     * @var integer $graph_local_id
     *
     * @ORM\Column(name="graph_local_id", type="integer", length=3)
     */
    protected $graph_local_id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string $title_cache
     *
     * @ORM\Column(name="title_cache", type="string", length=255)
     */
    protected $title_cache;

    /**
     * @ORM\ManyToMany(targetEntity="GraphSelection", inversedBy="graphs")
     * @ORM\JoinTable(name="graph_selections_graphs")
     */
    protected $graph_selections;

    public function __construct()
    {
        $this->graph_selections = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param int $id
     * @return Graph
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
     * Set graph_local_id
     *
     * @param integer $graph_local_id
     * @return Graph
     */
    public function setGraphLocalId($graph_local_id)
    {
        $this->graph_local_id = $graph_local_id;

        return $this;
    }

    /**
     * Get graph_local_id
     *
     * @return integer
     */
    public function getGraphLocalId()
    {
        return $this->graph_local_id;
    }

    /**
     * Set graph_template
     *
     * @param GraphTemplate $graph_template
     * @return Graph
     */
    public function setGraphTemplate($graph_template)
    {
        $this->graph_template = $graph_template;

        return $this;
    }

    /**
     * Get graph_template
     *
     * @return GraphTemplate
     */
    public function getGraphTemplate()
    {
        return $this->graph_template;
    }

    /**
     * Set cacti_instance
     *
     * @param CactiInstance $cacti_instance
     * @return Graph
     */
    public function setCactiInstance($cacti_instance)
    {
        $this->cacti_instance = $cacti_instance;

        return $this;
    }

    /**
     * Get cacti_instance
     *
     * @return CactiInstance
     */
    public function getCactiInstance()
    {
        return $this->cacti_instance;
    }

    /**
     * Set host
     *
     * @param Host $host
     * @return Graph
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return Host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Graph
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title_cache
     *
     * @param string $title_cache
     * @return Graph
     */
    public function setTitleCache($title_cache)
    {
        $this->title_cache = $title_cache;

        return $this;
    }

    /**
     * Get title_cache
     *
     * @return string
     */
    public function getTitleCache()
    {
        return $this->title_cache;
    }

    /**
     * Add Graph Selection to graph
     *
     * @param GraphSelection $graph_selection
     *
     * @return Graph
     */
    public function addGraphSelection(GraphSelection $graph_selection)
    {
        $this->graph_selections[] = $graph_selection;

        return $this;
    }
}
