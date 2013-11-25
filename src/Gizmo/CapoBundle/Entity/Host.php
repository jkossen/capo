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
 * Gizmo\CapoBundle\Entity\Host
 *
 * @ORM\Table(name="host", indexes={@ORM\Index(name="idx_host_orig_id", columns={"orig_id"})},
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_ci_host", columns={"cacti_instance_id","orig_id"})
 * })
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\HostRepository")
 */
class Host
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
     * @var CactiInstance $cacti_instance
     *
     * @ORM\ManyToOne(targetEntity="CactiInstance", inversedBy="hosts")
     * @ORM\JoinColumn(name="cacti_instance_id", referencedColumnName="id", nullable=false)
     */
    protected $cacti_instance;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=150)
     */
    protected $description;

    /**
     * @var string $hostname
     *
     * @ORM\Column(name="hostname", type="string", length=250)
     */
    protected $hostname;

    /**
     * Set id
     *
     * @param string $id
     * @return Host
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
     * @return Host
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
     * @return Host
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
     * Set description
     *
     * @param string $description
     * @return Host
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     * @return Host
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }
}
