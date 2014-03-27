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

/**
 * GraphSelectionRepository
 */
class GraphSelectionRepository extends BaseEntityRepository
{
    /**
     * Get graph selection
     *
     * @param Array $data array of filters
     *
     * @return Array array of graph selections
     */
    public function getGraphSelection(Array $data, $as_array = false)
    {
        $graph_selection = $this->findOneBy(
            array(
            'id' => $data['graph_selection'],
            'user' => $data['capo_user_id'],
            'active' => 1));

        if ($graph_selection) {
            return $graph_selection;
        } else {
            return false;
        }
    }

    /**
     * Disable graph selection
     *
     * @param Array $data array of filters
     *
     * @return Array array of graph selections
     */
    public function disableGraphSelection(Array $data, $as_array = false)
    {
        $graph_selection = $this->getGraphSelection($data);

        if ($graph_selection) {
            $graph_selection->setActive(false);
            return $graph_selection;
        }

        return false;
    }

    /**
     * Get array of graph selections
     *
     * @param Array $data array of filters
     *
     * @return Array array of graph selections
     */
    public function getGraphSelections(Array $data, $as_array = false)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->select(array('e'));

        if (isset($data['capo_user_id'])) {
            $q->where('e.user = :user');
            $q->andWhere('e.active = 1');
            $q->setParameter('user', $data['capo_user_id']);
        }

        if (isset($data['q'])) {
            $q->andWhere('e.name LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $q->addOrderBy('e.created', 'DESC');

        $query = $q->getQuery();

        $array_result = ($as_array) ? $query->getArrayResult() : $query->getResult();

        return array(
            'graph_selections_total' => $total,
            'graph_selections' => $array_result
        );
    }

    /**
     * Get selection items from graph selection
     *
     * @param Array $data array of filters
     *
     * @return Array array of graph selection items
     */
    public function getItems(Array $data, $as_array = false)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->select(array('e', 'i', 'g', 'c'));
        $q->join('e.graph_selection_items', 'i');
        $q->join('i.graph', 'g');
        $q->join('g.cacti_instance', 'c');
        $q->where('1 = 1');
        
        $this->_access_control($q, $data);

        $q->andWhere('e.id = :graph_selection_id');
        $q->setParameter('graph_selection_id', $data['graph_selection_id']);

        $total = $this->_getResultCount($q);

        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $q->addOrderBy('i.itemnr', 'ASC');

        $query = $q->getQuery();

        $array_result = ($as_array) ? $query->getArrayResult() : $query->getResult();

        return array(
            'graph_selection_items_total' => $total,
            'graph_selection_items' => $array_result
        );
    }

    /**
     * Create new GraphSelection
     *
     * @param string $name
     * @param int $user
     * @param Array graphs
     * @param Array order_array order of graphs
     *
     * @return GraphSelection
     */
    public function createGraphSelection($name, $user, $graphs, $order_array)
    {
        $obj = new GraphSelection();
        $obj->setName($name);
        $obj->setUser($user);
        $obj->setCreated(new \DateTime(date('r')));

        foreach ($order_array as $graph_id) {
            foreach ($graphs as $graph) {
                if ($graph->getId() == $graph_id) {
                    $obj->addGraph($graph);
                }
            }
        }

        return $obj;
    }

    /**
     * Modify GraphSelection
     *
     * @param Array $data
     *
     * @return GraphSelection
     */
    public function updateGraphSelection($data)
    {
        $obj = $this->getGraphSelection($data);

        if (!is_object($obj)) {
            return null;
        }

        if (array_key_exists('name', $data) && !empty($data['name'])) {
            $obj->setName($data['name']);
        }

        if (array_key_exists('active', $data)) {
            $obj->setActive($data['active'] === 1);
        }

        return $obj;
    }
}
