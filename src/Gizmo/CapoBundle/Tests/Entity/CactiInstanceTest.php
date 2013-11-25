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

use Doctrine\Common\Collections\ArrayCollection;
use Gizmo\CapoBundle\Tests\UnitTestCase;
use Gizmo\CapoBundle\Entity\CactiInstance;
use Gizmo\CapoBundle\Entity\Graph;
use Gizmo\CapoBundle\Entity\GraphTemplate;
use Gizmo\CapoBundle\Entity\Host;

class CactiInstanceTest extends UnitTestCase
{
    public function testId()
    {
        $e = new CactiInstance();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertEquals($expected, $result);
    }

    public function testName()
    {
        $e = new CactiInstance();
        $expected = 'phpUnit';
        $e->setName($expected);
        $result = $e->getName();
        $this->assertEquals($expected, $result);
    }

    public function testBaseUrl()
    {
        $e = new CactiInstance();
        $expected = 'http://cacti-test-1.local/cacti';
        $e->setBaseUrl($expected);
        $result = $e->getBaseUrl();
        $this->assertEquals($expected, $result);
    }

    public function testGraphs()
    {
        $e = new CactiInstance();
        $expected = new ArrayCollection();
        $expected->add(new Graph());
        $expected->add(new Graph());
        $expected->add(new Graph());

        $e->setGraphs($expected);
        $result = $e->getGraphs();
        $this->assertSame($expected, $result);
    }

    public function testGraphTemplates()
    {
        $e = new CactiInstance();
        $expected = new ArrayCollection();
        $expected->add(new GraphTemplate());
        $expected->add(new GraphTemplate());
        $expected->add(new GraphTemplate());

        $e->setGraphTemplates($expected);
        $result = $e->getGraphTemplates();
        $this->assertSame($expected, $result);
    }

    public function testHosts()
    {
        $e = new CactiInstance();
        $expected = new ArrayCollection();
        $expected->add(new Host());
        $expected->add(new Host());
        $expected->add(new Host());

        $e->setHosts($expected);
        $result = $e->getHosts();
        $this->assertSame($expected, $result);
    }

    public function testActive()
    {
        $e = new CactiInstance();
        $expected = false;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);

        $expected = true;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);
    }

}
