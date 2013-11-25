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

class UserRepository extends BaseEntityRepository
{
    /**
     * Get User
     *
     * @param int $id
     * @param bool $as_array return Array or object
     *
     * @return User|Array
     */
    public function getUser($id, $as_array = false)
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

    /**
     * Get array of users
     *
     * @param Array $data array of filters
     *
     * @return Array array of users
     */
    public function getUsers(Array $data)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];
        $q->join('e.group', 'g');

        $q->where('1 = 1');

        if (!(isset($data['active_users_only']) &&
              intval($data['active_users_only']) === 0)) {
            $q->andWhere('e.enabled = 1');
        }

        if (isset($data['group_id'])) {
            $q->andWhere('e.group_id = :group_id');
            $q->setParameter('group_id', intval($data['group_id']));
        }

        if (isset($data['q'])) {
            $q->andWhere('e.username LIKE :query OR e.usernameCanonical LIKE :query OR g.name LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->select(array('e', 'g'));
        $q->addOrderBy('e.lastLogin', 'DESC');
        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();

        $array_result = $query->getArrayResult();

        return array(
            'users_total' => $total,
            'users' => $array_result
        );
    }


    public function updateUser($id, $params)
    {
        $obj = $this->getUser($id);

        if ($obj === null) {
            return null;
        }

        if (array_key_exists('enabled', $params)) {
            $obj->setEnabled($params['enabled'] === 1);
        }

        return $obj;
    }
}
