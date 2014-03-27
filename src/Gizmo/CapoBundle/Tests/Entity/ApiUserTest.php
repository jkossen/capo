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
use Gizmo\CapoBundle\Entity\ApiUser;
use Gizmo\CapoBundle\Entity\CactiInstance;

class ApiUserTest extends UnitTestCase
{
    public function testId()
    {
        $e = new ApiUser();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertSame($expected, $result);
    }

    public function testUsername()
    {
        $e = new ApiUser();
        $expected = 'test-user';
        $e->setUsername($expected);
        $result = $e->getUsername();
        $this->assertSame($expected, $result);
    }

    public function testPassword()
    {
        $e = new ApiUser();
        $expected = 'djiqwofjowejfiowejiofweoi';
        $e->setPassword($expected);
        $result = $e->getPassword();
        $this->assertSame($expected, $result);
    }

    public function testActive()
    {
        $e = new ApiUser();
        $expected = true;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);

        $expected = false;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);
    }

    public function testCactiInstances()
    {
        $e = new ApiUser();
        $c1 = new CactiInstance();
        $c1->setName('cacti-instance-1');
        $c2 = new CactiInstance();
        $c2->setName('cacti-instance-2');
        $c3 = new CactiInstance();
        $c3->setName('cacti-instance-3');

        $e->addCactiInstance($c1);
        $e->addCactiInstance($c2);
        $e->addCactiInstance($c3);

        $expected = 3;
        $result = count($e->getCactiInstances());

        $this->assertSame($expected, $result);

        $e->removeCactiInstance($c2);

        $expected = 2;
        $result = count($e->getCactiInstances());

        $this->assertSame($expected, $result);

        $e->removeCactiInstance($c1);

        $expected = $c3->getName();
        $result = $e->getCactiInstances();

        $result = $result->current()->getName();

        $this->assertSame($expected, $result);
    }
}
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
use Gizmo\CapoBundle\Entity\ApiUser;
use Gizmo\CapoBundle\Entity\CactiInstance;

class ApiUserTest extends UnitTestCase
{
    public function testId()
    {
        $e = new ApiUser();
        $expected = 347892;
        $e->setId($expected);
        $result = $e->getId();
        $this->assertSame($expected, $result);
    }

    public function testUsername()
    {
        $e = new ApiUser();
        $expected = 'test-user';
        $e->setUsername($expected);
        $result = $e->getUsername();
        $this->assertSame($expected, $result);
    }

    public function testPassword()
    {
        $e = new ApiUser();
        $expected = 'djiqwofjowejfiowejiofweoi';
        $e->setPassword($expected);
        $result = $e->getPassword();
        $this->assertSame($expected, $result);
    }

    public function testActive()
    {
        $e = new ApiUser();
        $expected = true;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);

        $expected = false;
        $e->setActive($expected);
        $result = $e->getActive();
        $this->assertSame($expected, $result);
    }

    public function testCactiInstances()
    {
        $e = new ApiUser();
        $c1 = new CactiInstance();
        $c1->setName('cacti-instance-1');
        $c2 = new CactiInstance();
        $c2->setName('cacti-instance-2');
        $c3 = new CactiInstance();
        $c3->setName('cacti-instance-3');

        $e->addCactiInstance($c1);
        $e->addCactiInstance($c2);
        $e->addCactiInstance($c3);

        $expected = 3;
        $result = count($e->getCactiInstances());

        $this->assertSame($expected, $result);

        $e->removeCactiInstance($c2);

        $expected = 2;
        $result = count($e->getCactiInstances());

        $this->assertSame($expected, $result);

        $e->removeCactiInstance($c1);

        $expected = $c3->getName();
        $result = $e->getCactiInstances();

        $result = $result->current()->getName();

        $this->assertSame($expected, $result);
    }
}
