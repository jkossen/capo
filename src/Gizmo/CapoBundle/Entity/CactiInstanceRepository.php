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

class CactiInstanceRepository extends BaseEntityRepository
{
    /**
     * Get Cacti instance
     *
     * @param int $id
     * @param bool $as_array return Array or object
     *
     * @return CactiInstance|Array
     */
    public function getCactiInstance($id, $as_array = false)
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

    public function updateCactiInstance($id, $params)
    {
        $obj = $this->getCactiInstance($id);

        if ($obj === null) {
            return null;
        }

        if (array_key_exists('name', $params) && !empty($params['name'])) {
            $obj->setName($params['name']);
        }

        if (array_key_exists('base_url', $params) && !empty($params['base_url'])) {
            $obj->setBaseUrl($params['base_url']);
        }

        if (array_key_exists('import_date', $params) && !empty($params['import_date'])) {
            $obj->setImportDate($params['import_date']);
        }

        if (array_key_exists('active', $params)) {
            $obj->setActive($params['active'] === 1);
        }

        if (array_key_exists('queue_import', $params)) {
            $obj->setQueueImport($params['queue_import'] === 1);
        }

        return $obj;
    }

    public function createCactiInstance($name, $base_url)
    {
        $obj = new CactiInstance();
        $obj->setName($name);
        $obj->setBaseUrl($base_url);
        $obj->setImportDate(new \DateTime('1970-01-01 00:00:00'));

        return $obj;
    }

    /**
     * Get Cacti instances
     *
     * @param Array $data array of filters
     * @param boolean $active_only only show active Cacti instances
     *
     * @return Array array of Cacti instances
     */
    public function getCactiInstances(Array $data, $exclude_query = false, $user_is_admin = false)
    {
        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];

        $q->where('1 = 1');

        $this->_access_control($q, $data, 'e');

        if (isset($data['q'])) {
            $q->andWhere('e.name LIKE :query OR e.base_url LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        if (! (isset($data['active_only']) && intval($data['active_only']) === 0)) {
            $q->andWhere('e.active = 1');
        }

        if ($exclude_query) {
            $q->andWhere($q->expr()->notIn('e.id', $exclude_query->getDQL()));
            $q->setParameter('group_id', $data['exclude_group_id']);
        }

        $total = $this->_getResultCount($q);

        $q->select(array('e', 'LENGTH(e.name) as HIDDEN namel'));

        $q->addOrderBy('namel', 'ASC');
        $q->addOrderBy('e.name', 'ASC');

        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();
        $array_result = $query->getArrayResult();

        return array(
            'cacti_instances_total' => $total,
            'cacti_instances' => $array_result
        );
    }
}
