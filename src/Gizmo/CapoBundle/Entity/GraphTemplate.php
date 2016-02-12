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

/**
 * Gizmo\CapoBundle\Entity\GraphTemplate
 *
 * @ORM\Table(name="graph_template", indexes={@ORM\Index(name="idx_graph_template_orig_id", columns={"orig_id"})},
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_ci_graph_template", columns={"cacti_instance_id","orig_id"})
 * })
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\GraphTemplateRepository")
 */
class GraphTemplate
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
     * @var integer $orig_id
     *
     * @ORM\Column(name="orig_id", type="integer", length=3)
     */
    protected $orig_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="CactiInstance", inversedBy="graph_templates")
     * @ORM\JoinColumn(name="cacti_instance_id", referencedColumnName="id", nullable=false)
     */
    protected $cacti_instance;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", length=32)
     */
    protected $hash;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var ArrayCollection $graphs
     *
     * @ORM\OneToMany(targetEntity="Graph", mappedBy="graph_template")
     */
    protected $graphs;

    /**
     * Set id
     *
     * @param string $id
     * @return GraphTemplate
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
     * Set orig_id
     *
     * @param integer $orig_id
     * @return GraphTemplate
     */
    public function setOrigId($orig_id)
    {
        $this->orig_id = $orig_id;

        return $this;
    }

    /**
     * Get orig_id
     *
     * @return integer
     */
    public function getOrigId()
    {
        return $this->orig_id;
    }

    /**
     * Set cacti_instance
     *
     * @param CactiInstance $cacti_instance
     * @return GraphTemplate
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
     * Set hash
     *
     * @param string $hash
     * @return GraphTemplate
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return GraphTemplate
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
}
