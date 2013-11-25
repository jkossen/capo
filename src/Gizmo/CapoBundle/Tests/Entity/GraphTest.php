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
use Gizmo\CapoBundle\Entity\Graph;

class GraphTest extends UnitTestCase
{
    public function testId()
    {
        $e = new Graph();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertEquals($expected, $result);
    }

    public function testGraphLocalId()
    {
        $e = new Graph();
        $expected = 'phpUnit';
        $e->setGraphLocalId($expected);
        $result = $e->getGraphLocalId();
        $this->assertEquals($expected, $result);
    }

    public function testGraphTemplate()
    {
        $e = new Graph();
        $expected = $this->getMock('\\Gizmo\CapoBundle\Entity\GraphTemplate');
        $e->setGraphTemplate($expected);
        $result = $e->getGraphTemplate();
        $this->assertSame($expected, $result);
        $this->assertInstanceOf('\\Gizmo\CapoBundle\Entity\GraphTemplate', $result);
    }

    public function testCactiInstance()
    {
        $e = new Graph();
        $expected = $this->getMock('\\Gizmo\CapoBundle\Entity\CactiInstance');
        $e->setCactiInstance($expected);
        $result = $e->getCactiInstance();
        $this->assertSame($expected, $result);
        $this->assertInstanceOf('\\Gizmo\CapoBundle\Entity\CactiInstance', $result);
    }

    public function testHost()
    {
        $e = new Graph();
        $expected = $this->getMock('\\Gizmo\CapoBundle\Entity\Host');
        $e->setHost($expected);
        $result = $e->getHost();
        $this->assertSame($expected, $result);
        $this->assertInstanceOf('\\Gizmo\CapoBundle\Entity\Host', $result);
    }

    public function testTitleCache()
    {
        $e = new Graph();
        $expected = 'phpUnit';
        $e->setTitleCache($expected);
        $result = $e->getTitleCache();
        $this->assertEquals($expected, $result);
    }

    public function testTitle()
    {
        $e = new Graph();
        $expected = 'phpUnit';
        $e->setTitle($expected);
        $result = $e->getTitle();
        $this->assertEquals($expected, $result);
    }
}
