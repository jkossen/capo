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

use Doctrine\ORM\Query\SqlWalker;

class UseIndexWalker extends SqlWalker
{
    const HINT_USE_INDEX = 'UseIndexWalker.UseIndex';

    public function walkFromClause($fromClause)
    {
        $sql = parent::walkFromClause($fromClause);
        $index = $this->getQuery()->getHint(self::HINT_USE_INDEX);

        return preg_replace('/( INNER JOIN| LEFT JOIN|$)/', sprintf(' USE INDEX(%s)\1', $index), $sql, 1);
    }
}
