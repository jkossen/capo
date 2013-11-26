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
 * GroupRepository
 */
class GroupRepository extends BaseEntityRepository
{
    /**
     * Get Group
     *
     * @param int $id
     * @param bool $as_array return Array or object
     *
     * @return Group|Array
     */
    public function getGroup($id, $as_array = false)
    {
        $qb = $this->_getStdQueryBuilder(Array());
        $q = $qb['q'];

        $q->select('e');

        $q->where('e.id = :id');
        $q->setParameter('id', $id);

        $query = $q->getQuery();
        try {
            $result = ($as_array) ? $query->getArrayResult() : $query->getSingleResult();
        } catch(\Doctrine\ORM\NoResultException $e) {
            return null;
        }

        return $result;
    }

    public function getCactiInstanceIdsQuery(Array $data) {
        $qb = $this->_getStdQueryBuilder($data, 'ci_g');
        $q = $qb['q'];
        $q->leftJoin('ci_g.cacti_instances', 'c');
        $q->where('ci_g.id = :group_id');
        $q->setParameter('group_id', $data['group_id']);

        $q2 = clone($q);
        $q2->select('count(c.id)');
        $query2 = $q2->getQuery();
        $result2 = $query2->getSingleScalarResult();

        if ($result2 === '0') {
            return false;
        }

        $q->select(array('c.id'));
        return $q;
    }

    /**
     * Get array of groups
     *
     * @param Array $data array of filters
     *
     * @return Array array of groups
     */
    public function getGroups(Array $data)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->where('1 = 1');

        if (!(isset($data['active_groups_only']) &&
              intval($data['active_groups_only']) === 0)) {
            $q->andWhere('e.active = 1');
        }

        if (isset($data['id'])) {
            $q->andWhere('e.id = :id');
            $q->setParameter('id', intval($data['id']));
        }

        if (isset($data['q'])) {
            $q->andWhere('e.name LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->select(array('e', 'c'));
        $q->leftJoin('e.cacti_instances', 'c');
        $q->addOrderBy('e.name', 'asc');
        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();

        $array_result = $query->getArrayResult();

        return array(
            'groups_total' => $total,
            'groups' => $array_result
        );
    }

    public function createGroup($name)
    {
        $obj = new Group();
        $obj->setName($name);

        return $obj;
    }

    public function updateGroup($id, $params)
    {
        $obj = $this->getGroup($id);

        if ($obj === null) {
            return null;
        }

        if (array_key_exists('name', $params) && !empty($params['name'])) {
            $obj->setName($params['name']);
        }

        if (array_key_exists('active', $params)) {
            $obj->setActive($params['active'] === 1);
        }

        return $obj;
    }
}
