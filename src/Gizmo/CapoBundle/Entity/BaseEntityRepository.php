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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

abstract class BaseEntityRepository extends EntityRepository
{
    /**
     * Create QueryBuilder with some standard options
     *
     * This function creates a new QueryBuilder based on the given entity and
     * data array
     *
     * @param Array $data array with desired page nr and items per page limit
     *
     * @return Array querybuilder, first_result and limit
     */
    protected function _getStdQueryBuilder(Array $data, $entity_identifier='e')
    {
        $limit = 25;
        $max_limit = 100;
        $page = 1;

        $q = $this->createQueryBuilder($entity_identifier);

        $q->where('1 = 1');

        if (isset($data['page_limit'])) {
            $limit = intval(abs($data['page_limit']));

            if ($limit > $max_limit) {
                $limit = $max_limit;
            }

            if ($limit < 1) {
                $limit = 1;
            }
        }

        if (isset($data['page'])) {
            $page = intval(abs($data['page']));
        }

        if ($page < 1) {
            $page = 1;
        }

        $first_result = ($page - 1) * $limit;

        return (array(
            'q' => $q,
            'first_result' => $first_result,
            'limit' => $limit
        ));
    }

    /**
     * Count the number of records based on the given QueryBuilder
     *
     * @param QueryBuilder $q
     *
     * @return integer number of records
     */
    protected function _getResultCount(QueryBuilder $q)
    {
        $r = clone($q);
        $query = $r->select('count(e.id)')->getQuery();
        return $query->getSingleScalarResult();
    }

    protected function _access_control($q, $data, $ci_identifier = 'c')
    {
        if (isset($data['api_user']) && $data['api_user'] === true) {
            $q->join($ci_identifier . '.apiusers', 'ci_access_u');
            $q->andWhere('ci_access_u.id = :user_id');
            $q->andWhere('ci_access_u.active = 1');
            $q->setParameter('user_id', $data['capo_user_id']);
        } elseif (! (isset($data['user_is_admin']) && $data['user_is_admin'] === true) ) {
            $q->join($ci_identifier . '.groups', 'ci_access_g');
            $q->andWhere('ci_access_g.id = :group_id');
            $q->andWhere('ci_access_g.active = 1');
            $q->setParameter('group_id', $data['capo_group_id']);
        }
    }
}
