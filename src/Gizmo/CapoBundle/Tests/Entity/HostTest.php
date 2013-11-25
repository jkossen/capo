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
use Gizmo\CapoBundle\Entity\Host;

class HostTest extends UnitTestCase
{
    public function testId()
    {
        $e = new Host();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertEquals($expected, $result);
    }

    public function testOrigId()
    {
        $e = new Host();
        $expected = 'phpUnit';
        $e->setOrigId($expected);
        $result = $e->getOrigId();
        $this->assertEquals($expected, $result);
    }

    public function testCactiInstance()
    {
        $e = new Host();
        $expected = $this->getMock('\\Gizmo\CapoBundle\Entity\CactiInstance');
        $e->setCactiInstance($expected);
        $result = $e->getCactiInstance();
        $this->assertSame($expected, $result);
        $this->assertInstanceOf('\\Gizmo\CapoBundle\Entity\CactiInstance', $result);
    }

    public function testHostname()
    {
        $e = new Host();
        $expected = 'phpUnit';
        $e->setHostname($expected);
        $result = $e->getHostname();
        $this->assertEquals($expected, $result);
    }

    public function testDescription()
    {
        $e = new Host();
        $expected = 'phpUnit';
        $e->setDescription($expected);
        $result = $e->getDescription();
        $this->assertEquals($expected, $result);
    }
}
