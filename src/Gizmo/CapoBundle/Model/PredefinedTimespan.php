<?php
/*
    Capo, a web interface for querying multiple Cacti instances
    Copyright (C) 2004-2017 The Cacti Group 
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

namespace Gizmo\CapoBundle\Model;

class PredefinedTimespan
{
    const LAST_HALF_HOUR = 1;
    const LAST_HOUR = 2;
    const LAST_2_HOURS = 3;
    const LAST_4_HOURS = 4;
    const LAST_6_HOURS = 5;
    const LAST_12_HOURS = 6;
    const LAST_DAY = 7;
    const LAST_2_DAYS = 8;
    const LAST_3_DAYS = 9;
    const LAST_4_DAYS = 10;
    const LAST_WEEK = 11;
    const LAST_2_WEEKS = 12;
    const LAST_MONTH = 13;
    const LAST_2_MONTHS = 14;
    const LAST_3_MONTHS = 15;
    const LAST_4_MONTHS = 16;
    const LAST_6_MONTHS = 17;
    const LAST_YEAR = 18;
    const LAST_2_YEARS = 19;
    const DAY_SHIFT = 20;
    const THIS_DAY = 21;
    const THIS_WEEK = 22;
    const THIS_MONTH = 23;
    const THIS_YEAR = 24; 
    const PREV_DAY = 25;
    const PREV_WEEK = 26;
    const PREV_MONTH = 27;
    const PREV_YEAR = 28;

    private $timespan;
    private $end;
    private $start;

    public function __construct($timespan, \DateTimeImmutable $end = null)
    {
        $this->timespan = $timespan;

        switch ($timespan) {
            case static::LAST_HALF_HOUR:
            case static::LAST_HOUR:
            case static::LAST_2_HOURS:
            case static::LAST_4_HOURS:
            case static::LAST_6_HOURS:
            case static::LAST_12_HOURS:
            case static::LAST_DAY:
            case static::LAST_2_DAYS:
            case static::LAST_3_DAYS:
            case static::LAST_4_DAYS:
            case static::LAST_WEEK:
            case static::LAST_2_WEEKS:
            case static::LAST_MONTH:
            case static::LAST_2_MONTHS:
            case static::LAST_3_MONTHS:
            case static::LAST_4_MONTHS:
            case static::LAST_6_MONTHS:
            case static::LAST_YEAR:
            case static::LAST_2_YEARS:
                break;
            case static::DAY_SHIFT:
                throw new \UnexpectedValueException("Unsupported Value");
            case static::THIS_DAY:
                break;
            case static::THIS_WEEK:
                throw new \UnexpectedValueException("Unsupported Value");
            case static::THIS_MONTH:
            case static::THIS_YEAR:
            case static::PREV_DAY:
                break;
            case static::PREV_WEEK:
                throw new \UnexpectedValueException("Unsupported Value");
            case static::PREV_MONTH:
            case static::PREV_YEAR:
                break;
            default:
                throw new \UnexpectedValueException("Unsupported Value");
        }

        $this->setEnd($end);
    }

    public function getTimespan()
    {
        return $this->timespan;
    }

    private function setEnd(\DateTimeImmutable $end = null)
    {
        $this->end = is_null($end) ? new \DateTimeImmutable() : $end;

        switch ($this->timespan) {
            case static::LAST_HALF_HOUR:
                $this->start = $this->end->sub(new \DateInterval('P30M'));
                break;
            case static::LAST_HOUR:
                $this->start = $this->end->sub(new \DateInterval('P1H'));
                break;
            case static::LAST_2_HOURS:
                $this->start = $this->end->sub(new \DateInterval('P2H'));
                break;
            case static::LAST_4_HOURS:
                $this->start = $this->end->sub(new \DateInterval('P4H'));
                break;
            case static::LAST_6_HOURS:
                $this->start = $this->end->sub(new \DateInterval('P6H'));
                break;
            case static::LAST_12_HOURS:
                $this->start = $this->end->sub(new \DateInterval('P12H'));
                break;
            case static::LAST_DAY:
                $this->start = $this->end->sub(new \DateInterval('P1D'));
                break;
            case static::LAST_2_DAYS:
                $this->start = $this->end->sub(new \DateInterval('P2D'));
                break;
            case static::LAST_3_DAYS:
                $this->start = $this->end->sub(new \DateInterval('P3D'));
                break;
            case static::LAST_4_DAYS:
                $this->start = $this->end->sub(new \DateInterval('P4D'));
                break;
            case static::LAST_WEEK:
                $this->start = $this->end->sub(new \DateInterval('P1W'));
                break;
            case static::LAST_2_WEEKS:
                $this->start = $this->end->sub(new \DateInterval('P2W'));
                break;
            case static::LAST_MONTH:
                $this->start = $this->end->sub(new \DateInterval('P1M'));
                break;
            case static::LAST_2_MONTHS:
                $this->start = $this->end->sub(new \DateInterval('P2M'));
                break;
            case static::LAST_3_MONTHS:
                $this->start = $this->end->sub(new \DateInterval('P3M'));
                break;
            case static::LAST_4_MONTHS:
                $this->start = $this->end->sub(new \DateInterval('P4M'));
                break;
            case static::LAST_6_MONTHS:
                $this->start = $this->end->sub(new \DateInterval('P6M'));
                break;
            case static::LAST_YEAR:
                $this->start = $this->end->sub(new \DateInterval('P1Y'));
                break;
            case static::LAST_2_YEARS:
                $this->start = $this->end->sub(new \DateInterval('P2Y'));
                break;
            case static::DAY_SHIFT:
                throw new \UnexpectedValueException("Unsupported Value");
            case static::THIS_DAY:
                $start = new \DateTimeImmutable($this->end->format('Y-m-d\T00:00:00P'));
                $this->start = $start;
                $this->end = $start->add(new \DateInterval('P1D'));
                break;
            case static::THIS_WEEK:
                throw new \UnexpectedValueException("Unsupported Value");
            case static::THIS_MONTH:
                $start = new \DateTimeImmutable($this->end->format('Y-m-01\T00:00:00P'));
                $this->start = $start;
                $this->end = $start->add(new \DateInterval('P1M'));
                break;
            case static::THIS_YEAR:
                $start = new \DateTimeImmutable($this->end->format('Y-01-01\T00:00:00P'));
                $this->start = $start;
                $this->end = $start->add(new \DateInterval('P1Y'));
                break;
            case static::PREV_DAY:
                $end = new \DateTimeImmutable($this->end->format('Y-m-d\T00:00:00P'));
                $this->start = $end->sub(new \DateInterval('P1D'));
                $this->end = $end;
                break;
            case static::PREV_WEEK:
                throw new \UnexpectedValueException("Unsupported Value");
            case static::PREV_MONTH:
                $end = new \DateTimeImmutable($this->end->format('Y-m-01\T00:00:00P'));
                $this->start = $end->sub(new \DateInterval('P1M'));
                $this->end = $end;
                break;
            case static::PREV_YEAR:
                $end = new \DateTimeImmutable($this->end->format('Y-01-01\T00:00:00P'));
                $this->start = $end->sub(new \DateInterval('P1Y'));
                $this->end = $end;
                break;
            default:
                throw new \UnexpectedValueException("Unsupported Value");
        }
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getDescription()
    {
        return static::getTimespanDescription($this->getTimespan());
    }

    public static function getTimespanDescription($timespan)
    {
        switch ($timespan) {
            case static::LAST_HALF_HOUR:
                return 'Last Half Hour';
            case static::LAST_HOUR:
                return 'Last Hour';
            case static::LAST_2_HOURS:
                return 'Last 2 Hours';
            case static::LAST_4_HOURS:
                return 'Last 4 Hours';
            case static::LAST_6_HOURS:
                return 'Last 6 Hours';
            case static::LAST_12_HOURS:
                return 'Last 12 Hours';
            case static::LAST_DAY:
                return 'Last Day';
            case static::LAST_2_DAYS:
                return 'Last 2 Days';
            case static::LAST_3_DAYS:
                return 'Last 3 Days';
            case static::LAST_4_DAYS:
                return 'Last 4 Days';
            case static::LAST_WEEK:
                return 'Last Week';
            case static::LAST_2_WEEKS:
                return 'Last 2 Weeks';
            case static::LAST_MONTH:
                return 'Last Month';
            case static::LAST_2_MONTHS:
                return 'Last 2 Months';
            case static::LAST_3_MONTHS:
                return 'Last 3 Months';
            case static::LAST_4_MONTHS:
                return 'Last 4 Months';
            case static::LAST_6_MONTHS:
                return 'Last 6 Months';
            case static::LAST_YEAR:
                return 'Last Year';
            case static::LAST_2_YEARS:
                return 'Last 2 Years';
            case static::DAY_SHIFT:
                return 'Day Shift';
            case static::THIS_DAY:
                return 'This Day';
            case static::THIS_WEEK:
                return 'This Week';
            case static::THIS_MONTH:
                return 'This Month';
            case static::THIS_YEAR:
                return 'This Year';
            case static::PREV_DAY:
                return 'Previous Day';
            case static::PREV_WEEK:
                return 'Previous Day';
            case static::PREV_MONTH:
                return 'Previous Month';
            case static::PREV_YEAR:
                return 'Previous Year';
        }
    }
}
