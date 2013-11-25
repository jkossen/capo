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

namespace Gizmo\CapoBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction()
    {
        $form = Array(
            Array('q', 'text')
        );
        $privileges = $this->_get_privileges();
        $data = $this->_get_request_data($form);
        $data['active_page'] = 'graphs';
        return $this->render('GizmoCapoBundle:Default:index.html.twig',
                             array('data' => $data,
                             'privileges' => $privileges));
    }

    public function weathermapsAction()
    {
        $form = Array(
            Array('q', 'text')
        );
        $privileges = $this->_get_privileges();
        $data = $this->_get_request_data($form);
        $data['active_page'] = 'weathermaps';
        return $this->render('GizmoCapoBundle:Default:weathermaps.html.twig',
                             array('data' => $data,
                                   'privileges' => $privileges
                             ));
    }
}
