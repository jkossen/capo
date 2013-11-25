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
 * Gizmo\CapoBundle\Entity\Weathermap
 *
 * @ORM\Table(name="weathermap", indexes={@ORM\Index(name="idx_weathermap_orig_id", columns={"orig_id"})},
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_ci_weathermap", columns={"cacti_instance_id","orig_id"})
 * })
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\WeathermapRepository")
 */
class Weathermap
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
     * @ORM\ManyToOne(targetEntity="CactiInstance", inversedBy="weathermaps")
     * @ORM\JoinColumn(name="cacti_instance_id", referencedColumnName="id", nullable=false)
     */
    protected $cacti_instance;

    /**
     * @var string $titlecache
     *
     * @ORM\Column(name="titlecache", type="string", length=255)
     */
    protected $titlecache;

    /**
     * @var string $filehash
     *
     * @ORM\Column(name="filehash", type="string", length=255)
     */
    protected $filehash;

    /**
     * Set id
     *
     * @param string $id
     * @return Weathermap
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
     * Set cacti_instance
     *
     * @param CactiInstance $cacti_instance
     * @return Weathermap
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
     * Set filehash
     *
     * @param string $filehash
     * @return Host
     */
    public function setFilehash($filehash)
    {
        $this->filehash = $filehash;

        return $this;
    }

    /**
     * Get filehash
     *
     * @return string
     */
    public function getFilehash()
    {
        return $this->filehash;
    }

    /**
     * Set orig_id
     *
     * @param integer $orig_id
     * @return Weathermap
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
     * Set titlecache
     *
     * @param string $titlecache
     * @return Weathermap
     */
    public function setTitleCache($titlecache)
    {
        $this->titlecache = $titlecache;

        return $this;
    }

    /**
     * Get titlecache
     *
     * @return string
     */
    public function getTitleCache()
    {
        return $this->titlecache;
    }

}
