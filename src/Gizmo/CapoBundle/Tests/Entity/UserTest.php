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
use Gizmo\CapoBundle\Entity\User;

class UserTest extends UnitTestCase
{
    public function testId()
    {
        $e = new User();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertSame($expected, $result);
    }

    public function testDn()
    {
        $e = new User();
        $expected = 'at.the.office.com';
        $e->setDn($expected);
        $result = $e->getDn();
        $this->assertSame($expected, $result);
    }

    public function testUsername()
    {
        $e = new User();
        $expected = 'test-user';
        $e->setUsername($expected);
        $result = $e->getUsername();
        $this->assertSame($expected, $result);
    }
}
