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
 * GraphSelectionItemRepository
 */
class GraphSelectionItemRepository extends BaseEntityRepository
{
    protected function _fixItemNumbering(Array $data)
    {
        $graph_selection_id = abs(intval($data['graph_selection_id']));

        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];
        $q2 = clone($q);

        $item = $this->findOneBy(array('graph_selection' => $graph_selection_id));
        $gs = $item->getGraphSelection();

        // check if current user owns this graph selection
        if ($gs->getUser()->getId() != $data['capo_user_id']) {
            return false;
        }

        $need_renumbering = false;

        $q->select('count(e.id)');
        $q->where('e.graph_selection = ' . $graph_selection_id);
        $query = $q->getQuery();
        $res = $query->getSingleResult();
        $max_itemnr = intval($res[1]);

        // check if there are itemnr's smaller than 1, there should not be
        $q->select('count(e.id)');
        $q->where('e.graph_selection = ' . $graph_selection_id);
        $q->andWhere('e.itemnr < 1');
        $query = $q->getQuery();
        $res = $query->getSingleResult();

        if (intval($res[1]) > 0) {
            $need_renumbering = true;
        }

        // check if max_itemnr differs from distict count of itemnr
        // it should be the same
        if (! $need_renumbering) {
            $q->select('count(distinct e.itemnr)');
            $q->where('e.graph_selection = ' . $graph_selection_id);
            $query = $q->getQuery();
            $res = $query->getSingleResult();
            $itemnr_distinct_count = intval($res[1]);

            if ($max_itemnr != $itemnr_distinct_count) {
                $need_renumbering = true;
            }
        }
        
        // check if highest itemnr is higher than max_itemnr, it should not be
        if (! $need_renumbering) {
            $q->select('max(e.itemnr)');
            $q->where('e.graph_selection = ' . $graph_selection_id);
            $query = $q->getQuery();
            $res = $query->getSingleResult();
            $itemnr_highest = intval($res[1]);

            if ($itemnr_highest > $max_itemnr) {
                $need_renumbering = true;
            }
        }

        if ($need_renumbering) {
            $q->select('e');
            $q->where('e.graph_selection = ' . $graph_selection_id);
            $q->addOrderBy('e.itemnr');
            $q->addOrderBy('e.id');
            $query = $q->getQuery();
            $result = $query->getResult();

            for ($i=1;$i<=$max_itemnr;$i++) {
                $item = $result[$i-1];
                $q2->update('Gizmo\CapoBundle\Entity\GraphSelectionItem', 'e');
                $q2->set('e.itemnr', $i);
                $q2->where('e.id = ' . $item->getId());
                $query = $q2->getQuery();
                $query->execute();
            }
        }
    }

    public function repositionItem(Array $data)
    {
        $item_id = abs(intval($data['item_id']));
        $new_pos = abs(intval($data['new_pos']));

        $item = $this->findOneBy(array('id' => $item_id));
        $gs = $item->getGraphSelection();

        // Check if this user owns the graph selection
        if ($gs->getUser()->getId() != $data['capo_user_id']) {
            return false;
        }

        // Check if the item exists
        if (! $item) {
            return false;
        }

        // Don't do anything if new_pos is not different
        if ($new_pos == $item->getItemNr()) {
            return $item;
        }

        // Check and if needed fix the item numbering of this selection
        $data['graph_selection_id'] = $gs->getId();
        $this->_fixItemNumbering($data);

        $max_itemnr = 1;

        $qb = $this->_getStdQueryBuilder($data);
        $q = $qb['q'];
        $q2 = clone($q);

        $q->select('count(e.id)');
        $q->where('e.graph_selection = ' . $item->getGraphSelection()->getId());
        $query = $q->getQuery();
        $max_itemnr = $query->getSingleResult();

        if (($new_pos < 1) || ($new_pos > intval($max_itemnr[1]))) {
            return false;
        }

        $q2->update('Gizmo\CapoBundle\Entity\GraphSelectionItem', 'e');

        if ($new_pos < $item->getItemNr()) {
            $q2->set('e.itemnr', 'e.itemnr + 1');
            $q2->where('e.graph_selection = ' . $item->getGraphSelection()->getId());
            $q2->andWhere('e.id != ' . $item->getId());
            $q2->andWhere('e.itemnr < ' . $item->getItemNr());
            $q2->andWhere('e.itemnr >= ' . $new_pos);
        } else {
            $q2->set('e.itemnr', 'e.itemnr - 1');
            $q2->where('e.graph_selection = ' . $item->getGraphSelection()->getId());
            $q2->andWhere('e.id != ' . $item->getId());
            $q2->andWhere('e.itemnr > ' . $item->getItemNr());
            $q2->andWhere('e.itemnr <= ' . $new_pos);
        }

        $query = $q2->getQuery();
        $query->execute();

        $item->setItemNr($new_pos);

        return $item;
    }
}