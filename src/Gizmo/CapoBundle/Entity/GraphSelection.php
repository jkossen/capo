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
 * Gizmo\CapoBundle\Entity\GraphSelection
 *
 * @ORM\Table(name="graph_selections")
 *
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\GraphSelectionRepository")
 */
class GraphSelection
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
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="graph_selections")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="GraphSelectionItem", mappedBy="graph_selection", cascade={"persist"})
     */
    protected $graph_selection_items;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime")
     */
    protected $created;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active = True;
    
    public function __construct()
    {
        $this->graph_selection_items = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param int $id
     * @return GraphSelection
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return GraphSelection
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param int $user
     * @return GraphSelection
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return int $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return GraphSelection
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
     * Get graph selection items
     *
     * @return ArrayCollection
     */
    public function getGraphSelectionItems()
    {
        return $this->graph_selection_items;
    }

    /**
     * Add GraphSelectionItem
     *
     * @param $graph_selection_item GraphSelectionItem to add
     * @return GraphSelection
     */
    public function addGraphSelectionItem($graph_selection_item)
    {
        $this->graph_selection_items[] = $graph_selection_item;

        return $this;
    }

    /**
     * Remove GraphSelectionItem
     *
     * @param $graph_selection_item GraphSelectionItem to remove
     * @return GraphSelection
     */
    public function removeGraphSelectionItem($graph_selection_item)
    {
        $this->graph_selection_items->removeElement($graph_selection_item);

        return $this;
    }

    /**
     * Add Graph to selection
     *
     * @param $graph Graph to add
     * @return GraphSelection
     */
    public function addGraph($graph)
    {
        // only add graph if it's not in the selection already
        foreach ($this->graph_selection_items as $item) {
            if ($item->getGraph()->getId() === $graph->getId()) {
                return $this;
            }
        }

        $item = new GraphSelectionItem();
        $item->setItemNr($this->graph_selection_items->count());
        $item->setGraphSelection($this);
        $item->setGraph($graph);
        
        $this->graph_selection_items[] = $item;

        return $this;
    }

    /**
     * Remove Graph from selection
     *
     * @param $graph Graph to remove
     * @return GraphSelection
     */
    public function removeGraph($graph)
    {
        foreach ($this->graph_selection_items as $item) {
            if ($item->getGraph()->getId() === $graph->getId()) {
                $this->graph_selection_items->removeElement($item);
                return $this;
            }
        }

        return $this;
    }
}
