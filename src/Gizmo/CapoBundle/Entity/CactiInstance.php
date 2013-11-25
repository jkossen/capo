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
 * Gizmo\CapoBundle\Entity\CactiInstance
 *
 * @ORM\Table(name="cacti_instance")
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\CactiInstanceRepository")
 */
class CactiInstance
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, unique=True)
     */
    protected $name;

    /**
     * @var string $base_url
     *
     * @ORM\Column(name="base_url", type="string", length=255)
     */
    protected $base_url;

    /**
     * @var ArrayCollection $graphs
     *
     * @ORM\OneToMany(targetEntity="Graph", mappedBy="cacti_instance")
     */
    protected $graphs;

    /**
     * @var ArrayCollection $graph_templates
     *
     * @ORM\OneToMany(targetEntity="GraphTemplate", mappedBy="cacti_instance")
     */
    protected $graph_templates;

    /**
     * @var ArrayCollection $hosts
     *
     * @ORM\OneToMany(targetEntity="Host", mappedBy="cacti_instance")
     */
    protected $hosts;

    /**
     * @var ArrayCollection $weathermaps
     *
     * @ORM\OneToMany(targetEntity="Weathermap", mappedBy="cacti_instance")
     */
    protected $weathermaps;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active = True;

    /**
     * @var boolean $queue_import
     *
     * @ORM\Column(name="queue_import", type="boolean")
     */
    protected $queue_import = False;

    /**
     * @var \DateTime $import_date
     *
     * @ORM\Column(name="import_date", type="datetime")
     */
    protected $import_date;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="cacti_instances")
     * @ORM\JoinTable(name="usergroup_cacti_instances")
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="ApiUser", mappedBy="cacti_instances")
     * @ORM\JoinTable(name="apiuser_cacti_instance")
     */
    protected $apiusers;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->apiusers = new ArrayCollection();
        $this->graphs = new ArrayCollection();
        $this->graph_templates = new ArrayCollection();
        $this->hosts = new ArrayCollection();
        $this->weathermaps = new ArrayCollection();
        $this->import_date = new \DateTime('1970-01-01 00:00:00');
    }

    /**
     * Set id
     *
     * @param string $id
     * @return CactiInstance
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
     * Set name
     *
     * @param string $name
     * @return CactiInstance
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set base_url
     *
     * @param string $base_url
     * @return CactiInstance
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;

        return $this;
    }

    /**
     * Get base_url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Set graphs
     *
     * @param ArrayCollection $graphs
     *
     * @return CactiInstance
     */
    public function setGraphs(ArrayCollection $graphs)
    {
        $this->graphs = $graphs;

        return $this;
    }

    /**
     * Get graphs
     *
     * @return ArrayCollection
     */
    public function getGraphs()
    {
        return $this->graphs;
    }

    /**
     * Set graph_templates
     *
     * @param ArrayCollection $graph_templates
     *
     * @return CactiInstance
     */
    public function setGraphTemplates(ArrayCollection $graph_templates)
    {
        $this->graph_templates = $graph_templates;

        return $this;
    }

    /**
     * Get graph_templates
     *
     * @return ArrayCollection
     */
    public function getGraphTemplates()
    {
        return $this->graph_templates;
    }

    /**
     * Set hosts
     *
     * @param ArrayCollection $hosts
     *
     * @return CactiInstance
     */
    public function setHosts(ArrayCollection $hosts)
    {
        $this->hosts = $hosts;

        return $this;
    }

    /**
     * Get hosts
     *
     * @return ArrayCollection
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Set active
     *
     * @param $active
     *
     * @return CactiInstance
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set queue_import
     *
     * @param $queue_import
     *
     * @return CactiInstance
     */
    public function setQueueImport($queue_import)
    {
        $this->queue_import = $queue_import;

        return $this;
    }

    /**
     * Get queue_import
     *
     * @return boolean
     */
    public function getQueueImport()
    {
        return $this->queue_import;
    }

    /**
     * Set import_date
     *
     * @param \DateTime $import_date
     * @return CactiInstance
     */
    public function setImportDate($import_date)
    {
        $this->import_date = $import_date;

        return $this;
    }

    /**
     * Get import_date
     *
     * @return \DateTime
     */
    public function getImportDate()
    {
        return $this->import_date;
    }
}
