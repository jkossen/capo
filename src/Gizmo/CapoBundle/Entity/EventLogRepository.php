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
 * EventLogRepository
 */
class EventLogRepository extends BaseEntityRepository
{
    /**
     * Get array of log lines
     *
     * @param Array $data array of filters
     *
     * @return Array array of log lines
     */
    public function getLogLines(Array $data)
    {
        $qb = $this->_getStdQueryBuilder($data);

        $q = $qb['q'];

        $q->select('e');

        if (isset($data['q'])) {
            $q->andWhere('e.user_name LIKE :query OR e.client_ip LIKE :query OR e.request_uri LIKE :query OR e.request_data LIKE :query OR e.custom_data LIKE :query');
            $q->setParameter('query', '%' . $data['q'] . '%');
        }

        $q->addOrderBy('e.event_date', 'DESC');

        $total = $this->_getResultCount($q);

        $q->setFirstResult($qb['first_result']);
        $q->setMaxResults($qb['limit']);

        $query = $q->getQuery();

        $array_result = $query->getArrayResult();

        return array(
            'loglines_total' => $total,
            'loglines' => $array_result
        );
    }

    /**
     * Create new EventLog
     *
     * @param User $user
     * @param String $controller
     * @param String $function
     * @param Array $arguments
     *
     * @return EventLog
     */
    public function createEventLog($user, $controller, $function, $arguments, $message)
    {
        $obj = new EventLog();
        if ($user) {
            $user_id = ($user->getId() === null) ? 0 : $user->getId();
            $obj->setUserId($user_id);
            $obj->setUserName($user->getUserName());
        } else {
            $obj->setUserId(0);
            $obj->setUserName('no user');
        }
        $obj->setCustomData(Array(
            'class' => $controller,
            'function' => $function,
            'arguments' => $arguments,
            'message' => $message
        ));

        return $obj;
    }
}
