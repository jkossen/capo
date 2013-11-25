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

namespace Gizmo\CapoBundle\Tests\Entity;

use Gizmo\CapoBundle\Tests\UnitTestCase;
use Gizmo\CapoBundle\Entity\GraphSelection;
use Gizmo\CapoBundle\Entity\Graph;
use Gizmo\CapoBundle\Entity\User;

class GraphSelectionTest extends UnitTestCase
{
    public function testId()
    {
        $e = new GraphSelection();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertSame($expected, $result);
    }

    public function testUser()
    {
        $e = new GraphSelection();
        $expected = new User();
        $e->setUser($expected);
        $result = $e->getUser();
        $this->assertSame($expected, $result);
    }

    public function testCreated()
    {
        $e = new GraphSelection();
        $expected = new \DateTime('2013-06-06 12:23:11');
        $e->setCreated($expected);
        $result = $e->getCreated();
        $this->assertSame($expected, $result);
    }

    public function testActive()
    {
        $e = new GraphSelection();
        $expected = false;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);

        $expected = true;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);
    }

    public function testGraphs()
    {
        $e = new GraphSelection();
        $g1 = new Graph();
        $g1->setTitleCache('graph-1');
        $g2 = new Graph();
        $g2->setTitleCache('graph-2');
        $g3 = new Graph();
        $g3->setTitleCache('graph-3');

        $e->addGraph($g1);
        $e->addGraph($g2);
        $e->addGraph($g3);

        $expected = 3;
        $result = count($e->getGraphs());

        $this->assertSame($expected, $result);

        $e->removeGraph($g2);

        $expected = 2;
        $result = count($e->getGraphs());

        $this->assertEquals($expected, $result);

        $e->removeGraph($g1);

        $expected = $g3->getTitleCache();
        $result = $e->getGraphs();

        $result = $result->current()->getTitleCache();

        $this->assertEquals($expected, $result);
    }    
}

