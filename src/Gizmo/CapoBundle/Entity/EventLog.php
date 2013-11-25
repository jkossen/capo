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

use Doctrine\ORM\Mapping as ORM;

/**
 * Gizmo\CapoBundle\Entity\EventLog
 *
 * @ORM\Table(name="event_log", indexes={@ORM\Index(name="idx_user_id", columns={"user_id"}),
 * @ORM\Index(name="idx_user_name", columns={"user_name"}),
 * @ORM\Index(name="idx_request_uri", columns={"request_uri"})})
 * @ORM\Entity(repositoryClass="Gizmo\CapoBundle\Entity\EventLogRepository")
 */
class EventLog
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */ 
    protected $id;

    /**
     * @var \DateTime $event_date
     *
     * @ORM\Column(name="event_date", type="datetime")
     */
    protected $event_date;

    /**
     * @var String $client_ip
     *
     * @ORM\Column(name="client_ip", type="string", length=255)
     */
    protected $client_ip;

    /**
     * @var integer $user_id
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    protected $user_id;

    /**
     * @var string $user_name
     *
     * @ORM\Column(name="user_name", type="string", length=255)
     */
    protected $user_name;

    /**
     * @var string $request_uri
     *
     * @ORM\Column(name="request_uri", type="string", length=255)
     */
    protected $request_uri;

    /**
     * @var text $request_data
     *
     * @ORM\Column(name="request_data", type="text")
     */
    protected $request_data;

    /**
     * @var text $custom_data
     *
     * @ORM\Column(name="custom_data", type="text")
     */
    protected $custom_data;

    public function __construct()
    {
        $this->event_date = new \DateTime();

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->request_uri = $_SERVER['REQUEST_URI'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->client_ip = $_SERVER['REMOTE_ADDR'];
        }

        $request_data = Array(
            'get' => $_GET,
            'post' => $_POST
        );

        $this->request_data = json_encode($request_data);
    }

    /**
     * Set id
     *
     * @param string $id
     * @return EventLog
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set event_date
     * @param \DateTime $datetime
     *
     * @return EventLog
     */
    public function setEventDate($datetime)
    {
        $this->event_date = $datetime;
        return $this;
    }

    /**
     * Get event_date
     *
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->event_date;
    }

    /**
     * Set client_ip
     *
     * @param String $client_ip
     *
     * @return EventLog
     */
    public function setClientIp($client_ip)
    {
        $this->client_ip = $client_ip;
        return $this;
    }

    /**
     * Get client_ip
     *
     * @return String
     */
    public function getClientIp()
    {
        return $this->client_ip;
    }

    /**
     * Set user_id
     *
     * @param integer $user_id
     * @return EventLog
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set user_name
     *
     * @param integer $user_name
     * @return EventLog
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;

        return $this;
    }

    /**
     * Get user_name
     *
     * @return String
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * Set custom_data
     *
     * @param Array $custom_data
     * @return EventLog
     */
    public function setCustomData(Array $custom_data)
    {
        $this->custom_data = json_encode($custom_data);

        return $this;
    }

    /**
     * Get custom_data
     *
     * @return string
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    /**
     * Get request_data
     *
     * @return string
     */
    public function getRequestData()
    {
        return $this->request_data;
    }

    /**
     * Set request_data
     *
     * @param string $data
     * @return EventLog
     */
    public function setRequestData($data)
    {
        $this->request_data = $data;

        return $this;
    }

    /**
     * Get request_uri
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }

    /**
     * Set request_uri
     *
     * @param string $uri
     * @return EventLog
     */
    public function setRequestUri($uri)
    {
        $this->request_uri = $uri;

        return $this;
    }

}
