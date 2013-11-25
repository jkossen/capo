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

use FR3D\LdapBundle\Model\LdapUserInterface;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements LdapUserInterface
{
    /**
     * @var ArrayCollection $graph_selections
     *
     * @ORM\OneToMany(targetEntity="GraphSelection", mappedBy="user")
     */
    protected $graph_selections;

    /**
     * @var Group $group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="users")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     */
    protected $group;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Ldap Object Distinguished Name
     * @var string $dn
     */
    protected $dn;

    /**
     * @var string $ldap_group
     *
     * @ORM\Column(name="ldap_group", type="string", length=255, unique=False, nullable=True)
     */
    protected $ldap_group;

    public function __construct()
    {
        parent::__construct();
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
     * Get group
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param Group $group
     *
     * @return User
     */
    public function setGroup($group) {
        $this->group = $group;

        return $this;
    }
    /**
     * {@inheritDoc}
     */
    public function getDn() {
        return $this->dn;
    }

    /**
     * {@inheritDoc}
     */
    public function setDn($dn) {
        $this->dn = $dn;

        return $this;
    }

    /**
     * Set Username
     *
     * @param String $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;
        $this->email = $username . '@email.disabled';

        return $this;
    }

    /**
     * Set Group from DN
     *
     * @param String $dn
     * @return User
     */
    public function setLdapGroup($dn) {
        $dn_arr = preg_split('/,OU=/', stripslashes($dn));
        if (count($dn_arr) == 1) {
            $grpname = str_replace('OU=', '', $dn_arr[0]);
        } else {
            $grpname = str_replace('OU=', '', $dn_arr[1]);
        }

        $this->ldap_group = $grpname;

        return $this;
    }

    /**
     * Get LDAP group
     *
     * @return String Ldap group
     */
    public function getLdapGroup()
    {
        return $this->ldap_group;
    }

}
