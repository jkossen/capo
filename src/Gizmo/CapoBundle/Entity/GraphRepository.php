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

use Doctrine\ORM\Query;
use Gizmo\CapoBundle\Entity\UseIndexWalker;

class GraphRepository extends BaseEntityRepository
{
    /**
     * Get single Cacti graph
     *
     * @param int $id
     * @param bool $as_array return Array or object
     *
     * @return Graph|Array
     */
    public function getGraph($data, $as_array = false)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->join('e.cacti_instance', 'c');
        $q->join('e.graph_template', 'g');

        $q->select('e', 'c', 'g');

        $q->where('1 = 1');
        $this->_access_control($q, $data);

        $q->andWhere('e.id = :id');
        $q->setParameter('id', $data['id']);

        $query = $q->getQuery();
        $result = ($as_array) ? $query->getArrayResult() : $query->getSingleResult();

        return $result;
    }

    /**
     * Get array of graphs
     *
     * @param Array $data array of filters
     *
     * @return Array array of graphs
     */
    public function getGraphs(Array $data, $as_array = false)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->select(array('e', 'c', 'g'));
        $q->join('e.cacti_instance', 'c');
        $q->join('e.graph_template', 'g');
        $q->where('1 = 1');

        $this->_access_control($q, $data);

        if (isset($data['graph_ids'])) {
            $graph_ids = json_decode($data['graph_ids']);
            $q->andWhere($q->expr()->in('e.id', $graph_ids));
        }

        if (isset($data['cacti_instance'])) {
            $q->andWhere('e.cacti_instance = :cacti_instance');
            $q->setParameter('cacti_instance', $data['cacti_instance']);
        } else {
            if (! (isset($data['active_cacti_only']) && intval($data['active_cacti_only']) === 0) ) {
                $q->andWhere('c.active = 1');
            }
        }

        if (isset($data['graph_template'])) {
            $q->andWhere('g.name = :graph_template');
            $q->setParameter('graph_template', $data['graph_template']);
        }

        if (isset($data['host'])) {
            $q->andWhere('e.host = :host');
            $q->setParameter('host', $data['host']);
        }

        if (isset($data['q'])) {
            $q->andWhere('e.title_cache LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $q->addOrderBy('e.title_cache', 'ASC');

        $query = $q->getQuery();

        if ($this->getEntityManager()->getConnection()->getDriver()->getName() === 'pdo_mysql') {
            $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, '\Gizmo\CapoBundle\Entity\UseIndexWalker');
            $query->setHint(UseIndexWalker::HINT_USE_INDEX, 'idx_title_cache');
        }

        $array_result = ($as_array) ? $query->getArrayResult() : $query->getResult();

        return array(
            'graphs_total' => $total,
            'graphs' => $array_result
        );
    }

    /**
     * Get array of graph titles
     *
     * @param Array $data array of filters
     *
     * @return Array array of graph titles
     */
    public function getGraphTitles(Array $data, $as_array = false)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->select('DISTINCT e.title_cache as title');
        $q->join('e.cacti_instance', 'c');
        $q->where('1 = 1');

        $this->_access_control($q, $data);

        if (isset($data['cacti_instance'])) {
            $q->andWhere('e.cacti_instance = :cacti_instance');
            $q->setParameter('cacti_instance', $data['cacti_instance']);
        } else {
            if (! (isset($data['active_cacti_only']) && intval($data['active_cacti_only']) === 0) ) {
                $q->andWhere('c.active = 1');
            }
        }

        if (isset($data['graph_template'])) {
            $q->andWhere('e.graph_template = :graph_template');
            $q->setParameter('graph_template', $data['graph_template']);
        }

        if (isset($data['q'])) {
            $q->andWhere('e.title LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();

        $array_result = ($as_array) ? $query->getArrayResult() : $query->getResult();

        return array(
            'graph_titles_total' => $total,
            'graph_titles' => $array_result
        );
    }
}
