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

namespace Gizmo\CapoBundle\DataFixtures\ORM\test;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Gizmo\CapoBundle\Entity\CactiInstance;
use Gizmo\CapoBundle\Entity\Graph;
use Gizmo\CapoBundle\Entity\GraphTemplate;
use Gizmo\CapoBundle\Entity\Host;
use Gizmo\CapoBundle\Entity\Group;
use Gizmo\CapoBundle\Entity\User;
use Gizmo\CapoBundle\Entity\ApiUser;
use Gizmo\CapoBundle\Entity\GraphSelection;
use Gizmo\CapoBundle\Entity\EventLog;
use Gizmo\CapoBundle\Entity\Weathermap;

class FixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $groups = Array();
        $groups_desc = Array(
            0 => Array('testgroup'),
            1 => Array('testgroup2'),
            2 => Array('testgroup3')
        );

        foreach ($groups_desc as $gd) {
            $g = new Group;
            $g->setName($gd[0]);

            $manager->persist($g);
            $groups[] = $g;
        }

        $users = Array();
        $users_desc = Array(
            0 => Array('testuser', 'test@email.disabled', 'testpass', 0),
            1 => Array('testuser2', 'test2@email.disabled', 'testpass2', 0)
        );
        
        foreach ($users_desc as $ud) {
            $u = new User;
            $u->setUserName($ud[0]);
            $u->setEmail($ud[1]);
            $u->setPassword($ud[2]);
            $u->setLdapGroup('dn: CN=Test f User,OU=OS\, Security & Applications,OU=Users,OU=Company,DC=domain,DC=local');
            $u->setGroup($groups[$ud[3]]);
            $u->setEnabled(true);

            $manager->persist($u);
            $users[] = $u;
        }

        $apiusers = Array();
        $apiusers_desc = Array(
            0 => Array('testapiuser1', 'dhnqwjdjqwiojdioqwjiodjqwiojiodjqwo', true),
            1 => Array('testapiuser2', 'djqwiodjioqwjiodjqwodqojdoijqwiojo', true),
            2 => Array('testapiuser3', 'dqi90iqwdmklfji123jrlkewqjfkl', false),
            3 => Array('testapiuser4', 'vmd,lfvmopkweopfkweopkpwekkwekfkwe;kfewk;fkwekfpwekpkwepokopewk;', true)
        );

        foreach ($apiusers_desc as $ud) {
            $u = new ApiUser;
            $u->setUserName($ud[0]);
            $u->setPassword($ud[1]);
            $u->setActive($ud[2]);

            $manager->persist($u);
            $apiusers[] = $u;
        }

        $cis = Array();
        $cis_desc = Array(
            0 => Array('cacti_instance_test_01', 'http://cacti-instance-test-01.local/', 1),
            1 => Array('cacti_instance_test_02', 'http://cacti-instance-test-02.local/', 1),
            2 => Array('cacti_instance_test_03', 'http://cacti-instance-test-03.local/', 1),
            3 => Array('cacti_instance_test_04', 'http://cacti-instance-test-04.local/', 1),
            4 => Array('cacti_instance_test_05', 'http://cacti-instance-test-05.local/', 1),
            5 => Array('cacti_instance_test_06', 'http://cacti-instance-test-06.local/', 1),
            6 => Array('cacti_instance_test_07', 'http://cacti-instance-test-07.local/', 1),
            7 => Array('cacti_instance_test_08', 'http://cacti-instance-test-08.local/', 1),
            8 => Array('cacti_instance_test_09', 'http://cacti-instance-test-09.local/', 1),
            9 => Array('cacti_instance_test_10', 'http://cacti-instance-test-10.local/', 1),
            10 => Array('cacti_instance_test_11', 'http://cacti-instance-test-11.local/', 1),
            11 => Array('cacti_instance_test_12', 'http://cacti-instance-test-12.local/', 1)
        );

        foreach ($cis_desc as $ci) {
            $c = new CactiInstance;
            $c->setName($ci[0]);
            $c->setBaseUrl($ci[1]);
            $c->setActive(true);
            $c->setQueueImport(false);
            $c->setImportDate(new \DateTime('1970-01-01 00:00:00'));
            $manager->persist($c);
            $cis[] = $c;
        }

        // assign some cacti instances to groups
        $groups[0]->addCactiInstance($cis[2]);
        $groups[0]->addCactiInstance($cis[3]);
        $groups[0]->addCactiInstance($cis[4]);
        $groups[1]->addCactiInstance($cis[3]);
        $groups[1]->addCactiInstance($cis[4]);
        $groups[1]->addCactiInstance($cis[5]);
        $groups[1]->addCactiInstance($cis[6]);

        $manager->persist($groups[0]);
        $manager->persist($groups[1]);

        $apiusers[0]->addCactiInstance($cis[2]);
        $apiusers[0]->addCactiInstance($cis[3]);
        $apiusers[0]->addCactiInstance($cis[4]);
        $apiusers[1]->addCactiInstance($cis[3]);
        $apiusers[1]->addCactiInstance($cis[4]);
        $apiusers[1]->addCactiInstance($cis[5]);
        $apiusers[1]->addCactiInstance($cis[6]);

        $manager->persist($apiusers[0]);
        $manager->persist($apiusers[1]);

        $hosts = Array();
        $hosts_desc = Array(
            0 => Array('test-host-01', 'host01', 4501, 1),
            1 => Array('test-host-02', 'host02', 4502, 2),
            2 => Array('test-host-03', 'host03', 4503, 0),
            3 => Array('test-host-04', 'host04', 4504, 5),
            4 => Array('test-host-05', 'host05', 4505, 3),
            5 => Array('test-host-06', 'host06', 4506, 9),
            6 => Array('test-host-07', 'host07', 4507, 4),
            7 => Array('test-host-08', 'host08', 4508, 11),
            8 => Array('test-host-09', 'host09', 4509, 9),
            9 => Array('test-host-10', 'host10', 4510, 7),
            10 => Array('test-host-11', 'host11', 4511, 8),
            11 => Array('test-host-12', 'host12', 4512, 3),
            12 => Array('test-host-13', 'host13', 4513, 1)
        );

        foreach ($hosts_desc as $host) {
            $h = new Host;
            $h->setHostname($host[0]);
            $h->setDescription($host[1]);
            $h->setOrigId($host[2]);
            $h->setCactiInstance($cis[$host[3]]);
            $manager->persist($h);
            $hosts[] = $h;
        }

        $gts = Array();
        $gts_desc = Array(
            0 => Array(8901, 0, '010b90500e1fc6a05abfd542940584d1', 'Graph Template 01'),
            1 => Array(8902, 1, '010b90500e1fc6a05abfd542940584d2', 'Graph Template 02'),
            2 => Array(8903, 11, '010b90500e1fc6a05abfd542940584d3', 'Graph Template 03'),
            3 => Array(8904, 10, '010b90500e1fc6a05abfd542940584d4', 'Graph Template 04'),
            4 => Array(8905, 4, '010b90500e1fc6a05abfd542940584d5', 'Graph Template 05'),
            5 => Array(8906, 3, '010b90500e1fc6a05abfd542940584d6', 'Graph Template 06'),
            6 => Array(8907, 4, '010b90500e1fc6a05abfd542940584d7', 'Graph Template 07'),
            7 => Array(8908, 1, '010b90500e1fc6a05abfd542940584d8', 'Graph Template 08'),
            8 => Array(8909, 9, '010b90500e1fc6a05abfd542940584d9', 'Graph Template 09'),
            9 => Array(8910, 2, '010b90500e1fc6a05abfd54294058410', 'Graph Template 10'),
            10 => Array(8911, 1, '010b90500e1fc6a05abfd54294058411', 'Graph Template 11')
        );

        foreach ($gts_desc as $gt) {
            $t = new GraphTemplate;
            $t->setOrigId($gt[0]);
            $t->setCactiInstance($cis[$gt[1]]);
            $t->setHash($gt[2]);
            $t->setName($gt[3]);
            $manager->persist($t);
            $gts[] = $t;
        }

        $graphs = Array();
        $graphs_desc = Array(
            0 => Array(0, 0, 5360, 'Graph Title A |graph_title_nr|', 'Graph Title A 1'),
            1 => Array(2, 3, 5361, 'Graph Title A |graph_title_nr|', 'Graph Title A 2'),
            2 => Array(1, 5, 5362, 'Graph Title A |graph_title_nr|', 'Graph Title A 3'),
            3 => Array(4, 2, 5363, 'Graph Title A |graph_title_nr|', 'Graph Title A 4'),
            4 => Array(8, 1, 5364, 'Graph Title B |graph_title_nr|', 'Graph Title B 5'),
            5 => Array(8, 6, 5365, 'Graph Title B |graph_title_nr|', 'Graph Title B 6'),
            6 => Array(8, 3, 5366, 'Graph Title B |graph_title_nr|', 'Graph Title B 7'),
            7 => Array(7, 2, 5367, 'Graph Title B |graph_title_nr|', 'Graph Title B 8'),
            8 => Array(4, 1, 5368, 'Graph Title C |graph_title_nr|', 'Graph Title C 9'),
            9 => Array(5, 6, 5369, 'Graph Title D |graph_title_nr|', 'Graph Title D 10'),
            10 => Array(6, 10, 5370, 'Graph Title E |graph_title_nr|', 'Graph Title E 11'),
            11 => Array(1, 11, 5371, 'Graph Title E |graph_title_nr|', 'Graph Title E 12'),
            12 => Array(1, 12, 5372, 'Graph Title E |graph_title_nr|', 'Graph Title E 13'),
            13 => Array(1, 11, 5373, 'Graph Title E |graph_title_nr|', 'Graph Title E 14'),
            14 => Array(2, 11, 5374, 'Graph Title E |graph_title_nr|', 'Graph Title E 15'),
            15 => Array(2, 11, 5375, 'Graph Title E |graph_title_nr|', 'Graph Title E 15'),
            16 => Array(2, 11, 5376, 'Graph Title E |graph_title_nr|', 'Graph Title E 15'),
            17 => Array(2, 11, 5377, 'Graph Title E |graph_title_nr|', 'Graph Title E 15')
        );

        foreach ($graphs_desc as $gd) {
            $g = new Graph;
            $g->setGraphTemplate($gts[$gd[0]]);
            $g->setCactiInstance($hosts[$gd[1]]->getCactiInstance());
            $g->setHost($hosts[$gd[1]]);
            $g->setGraphLocalId($gd[2]);
            $g->setTitle($gd[3]);
            $g->setTitleCache($gd[4]);
            $manager->persist($g);
            $graphs[] = $g;
        }

        $weathermaps = Array(
            0 => Array(389, 1, 'AV ContentRing', '3ed6963cf3af9c299c5c'),
            1 => Array(42, 3, 'TV over IP Tier1 (de Meern)', 'e2a4561fabec3ac0183b')
        );
        
        foreach ($weathermaps as $wm) {
            $w = new WeatherMap;
            $w->setOrigId($wm[0]);
            $w->setCactiInstance($cis[$wm[1]]);
            $w->setTitleCache($wm[2]);
            $w->setFileHash($wm[3]);

            $manager->persist($w);
        }

        $graph_selections = Array(
            0 => Array(0, 'graph_selection_1', Array(1,2,3)),
            1 => Array(1, 'graph_selection_2', Array(4,5))
        );

        foreach ($graph_selections as $gs) {
            $g = new GraphSelection;
            $g->setUser($users[$gs[0]]);
            $g->setName($gs[1]);
            $g->setCreated(new \DateTime());
            foreach ($gs[2] as $graph) {
                $g->addGraph($graphs[$graph]);
            }

            $manager->persist($g);
        }


        $event_logs = Array(
            0 => Array(new \DateTime(),
                       '192.168.1.142', 9, 'user9', '/api/show_graph/210651/1/',
                       'ApiController', 'showGraphAction', Array(210651,1), 'show graph'),
            1 => Array(new \DateTime(),
                       '192.168.1.143', 2, 'user2', '/api/get_graphs/',
                       'ApiController', 'getGraphsAction', Array(), 'get graphs'),
            2 => Array(new \DateTime(),
                       '192.168.1.140', 4, 'user4', '/api/show_wmap/71/',
                       'ApiController', 'showWmapAction', Array(71), 'show weathermap')

        );

        foreach ($event_logs as $log) {
            $e = new EventLog;
            $e->setEventDate($log[0]);
            $e->setClientIp($log[1]);
            $e->setUserId($log[2]);
            $e->setUserName($log[3]);
            $e->setRequestUri($log[4]);
            $e->setCustomData(Array(
                'controller' => $log[5],
                'function' => $log[6],
                'arguments' => $log[7],
                'message' => $log[8]
            ));

            $manager->persist($e);
        }

        $manager->flush();
    }
}
