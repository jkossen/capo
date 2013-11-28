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

namespace Gizmo\CapoBundle\Services;

use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class EventLogService
{
	/** @var \Symfony\Component\Security\Core\SecurityContext */
	private $securityContext;

	/** @var \Doctrine\ORM\EntityManager */
	private $em;

    /** @var bool */
    private $enabled;

	/**
	 * Constructor
	 * 
	 * @param Doctrine        $doctrine
     * @param bool            $enabled
	 */
	public function __construct(SecurityContext $securityContext, Doctrine $doctrine, $enabled = false)
	{
		$this->securityContext = $securityContext;
        $this->em = $doctrine->getEntityManager();
        $this->enabled = $enabled;
	}

    /**
     * Create an event log message
     */
    public function log($str_class, $str_function, $str_args, $message=null)
    {
        if (! $this->enabled) {
            return;
        }

        $er = $this->em->getRepository('GizmoCapoBundle:EventLog');
        $msg = $str_class . ':' . $str_function . ':' . $str_args;

        if ($message !== null) {
            $msg = $message;
        }

        $log_line = $er->createEventLog(
            $this->securityContext->getToken()->getUser(),
            $str_class,
            $str_function,
            $str_args,
            $msg
        );

        try {
            $this->em->persist($log_line);
            $this->em->flush();
        } catch (\Exception $e) { } // ignore errors with logging
    }
}