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
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\GroupRepository")
 * @ORM\Table(name="usergroup")
 */
class Group
{
    /**
     * @ORM\ManyToMany(targetEntity="CactiInstance", inversedBy="groups")
     * @ORM\JoinTable(name="usergroup_cacti_instance")
     */
    protected $cacti_instances;

    /**
     * @var ArrayCollection $users
     *
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     */
    protected $users;

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
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active = True;

    public function __construct()
    {
        $this->cacti_instances = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * {@inheritDoc}
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Group
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
     * Set active
     *
     * @param $active
     *
     * @return Group
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
     * Add Cacti Instance
     *
     * @param CactiInstance $ci CactiInstance to add
     * @return Group
     */
    public function addCactiInstance(CactiInstance $ci)
    {
        if ($this->cacti_instances->contains($ci)) {
            return $this;
        }

        $this->cacti_instances->add($ci);
        
        return $this;
    }
    /**
     * Remove Cacti Instance
     *
     * @param CactiInstance $ci CactiInstance to remove
     * @return Group
     */
    public function removeCactiInstance(CactiInstance $ci)
    {
        $this->cacti_instances->removeElement($ci);

        return $this;
    }

    /**
     * Get CactiInstances for this group
     *
     * @return ArrayCollection
     */
    public function getCactiInstances()
    {
        return $this->cacti_instances;
    }

    /**
     * Get Users for this group
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
