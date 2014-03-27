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

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\ApiUserRepository")
 * @ORM\Table(name="api_user")
 */
class ApiUser implements UserInterface
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
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255, unique=True)
     */
    protected $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255, unique=False)
     */
    protected $password;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active = True;

    /**
     * @ORM\ManyToMany(targetEntity="CactiInstance", inversedBy="groups")
     * @ORM\JoinTable(name="apiuser_cacti_instance")
     */
    protected $cacti_instances;

    public function __construct()
    {
        $this->cacti_instances = new ArrayCollection();
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

    public function getRoles() {
        return Array('ROLE_USER');
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Group
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return ApiUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set active
     *
     * @param $active
     *
     * @return ApiUser
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
     * @return ApiUser
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
     * @return ApiUser
     */
    public function removeCactiInstance(CactiInstance $ci)
    {
        $this->cacti_instances->removeElement($ci);

        return $this;
    }

    /**
     * Get CactiInstances for this ApiUser
     *
     * @return ArrayCollection
     */
    public function getCactiInstances()
    {
        return $this->cacti_instances;
    }

    public function getSalt() {

    }

    public function eraseCredentials() {
        $this->password = null;
    }
}
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

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\ApiUserRepository")
 * @ORM\Table(name="api_user")
 */
class ApiUser implements UserInterface
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
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255, unique=True)
     */
    protected $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255, unique=False)
     */
    protected $password;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active = True;

    /**
     * @ORM\ManyToMany(targetEntity="CactiInstance", inversedBy="groups")
     * @ORM\JoinTable(name="apiuser_cacti_instance")
     */
    protected $cacti_instances;

    public function __construct()
    {
        $this->cacti_instances = new ArrayCollection();
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

    public function getRoles() {
        return Array('ROLE_USER');
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Group
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return ApiUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set active
     *
     * @param $active
     *
     * @return ApiUser
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
     * @return ApiUser
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
     * @return ApiUser
     */
    public function removeCactiInstance(CactiInstance $ci)
    {
        $this->cacti_instances->removeElement($ci);

        return $this;
    }

    /**
     * Get CactiInstances for this ApiUser
     *
     * @return ArrayCollection
     */
    public function getCactiInstances()
    {
        return $this->cacti_instances;
    }

    public function getSalt() {

    }

    public function eraseCredentials() {
        $this->password = null;
    }
}
