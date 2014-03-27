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
 * ApiUserRepository
 */
class ApiUserRepository extends BaseEntityRepository
{
    /**
     * Get ApiUser
     *
     * @param int $id
     * @param bool $as_array return Array or object
     *
     * @return ApiUser|Array
     */
    public function getApiUser($id, $as_array = false)
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
        $qb = $this->_getStdQueryBuilder($data, 'ci_u');
        $q = $qb['q'];
        $q->leftJoin('ci_u.cacti_instances', 'c');
        $q->where('ci_u.id = :id');
        $q->setParameter('id', $data['id']);

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
     * Get array of API users
     *
     * @param Array $data array of filters
     *
     * @return Array array of groups
     */
    public function getApiUsers(Array $data)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->where('1 = 1');

        if (!(isset($data['active_accounts_only']) &&
              intval($data['active_accounts_only']) === 0)) {
            $q->andWhere('e.active = 1');
        }

        if (isset($data['id'])) {
            $q->andWhere('e.id = :id');
            $q->setParameter('id', intval($data['id']));
        }

        if (isset($data['q'])) {
            $q->andWhere('e.username LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->select(array('e'));
        $q->addOrderBy('e.username', 'asc');
        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();

        $array_result = $query->getArrayResult();

        return array(
            'api_accounts_total' => $total,
            'api_accounts' => $array_result
        );
    }

    public function genPassword()
    {
        $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
        return hash('sha256', mcrypt_create_iv($size, MCRYPT_DEV_URANDOM));
    }

    public function createApiUser($username)
    {
        $obj = new ApiUser();
        $obj->setUsername($username);
        $obj->setPassword($this->genPassword());

        return $obj;
    }

    public function updateApiUser($id, $params)
    {
        $obj = $this->getApiUser($id);

        if ($obj === null) {
            return null;
        }

        if (array_key_exists('username', $params) && !empty($params['username'])) {
            $obj->setUsername($params['username']);
        }

        if (array_key_exists('password', $params) && !empty($params['password'])) {
            $obj->setPassword($params['password']);
        }

        if (array_key_exists('active', $params)) {
            $obj->setActive($params['active'] === 1);
        }

        return $obj;
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

/**
 * ApiUserRepository
 */
class ApiUserRepository extends BaseEntityRepository
{
    /**
     * Get ApiUser
     *
     * @param int $id
     * @param bool $as_array return Array or object
     *
     * @return ApiUser|Array
     */
    public function getApiUser($id, $as_array = false)
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
        $qb = $this->_getStdQueryBuilder($data, 'ci_u');
        $q = $qb['q'];
        $q->leftJoin('ci_u.cacti_instances', 'c');
        $q->where('ci_u.id = :id');
        $q->setParameter('id', $data['id']);

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
     * Get array of API users
     *
     * @param Array $data array of filters
     *
     * @return Array array of groups
     */
    public function getApiUsers(Array $data)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->where('1 = 1');

        if (!(isset($data['active_accounts_only']) &&
              intval($data['active_accounts_only']) === 0)) {
            $q->andWhere('e.active = 1');
        }

        if (isset($data['id'])) {
            $q->andWhere('e.id = :id');
            $q->setParameter('id', intval($data['id']));
        }

        if (isset($data['q'])) {
            $q->andWhere('e.username LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $total = $this->_getResultCount($q);

        $q->select(array('e'));
        $q->addOrderBy('e.username', 'asc');
        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();

        $array_result = $query->getArrayResult();

        return array(
            'api_accounts_total' => $total,
            'api_accounts' => $array_result
        );
    }

    public function genPassword()
    {
        $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
        return hash('sha256', mcrypt_create_iv($size, MCRYPT_DEV_URANDOM));
    }

    public function createApiUser($username)
    {
        $obj = new ApiUser();
        $obj->setUsername($username);
        $obj->setPassword($this->genPassword());

        return $obj;
    }

    public function updateApiUser($id, $params)
    {
        $obj = $this->getApiUser($id);

        if ($obj === null) {
            return null;
        }

        if (array_key_exists('username', $params) && !empty($params['username'])) {
            $obj->setUsername($params['username']);
        }

        if (array_key_exists('password', $params) && !empty($params['password'])) {
            $obj->setPassword($params['password']);
        }

        if (array_key_exists('active', $params)) {
            $obj->setActive($params['active'] === 1);
        }

        return $obj;
    }
}
