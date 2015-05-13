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

class PDFService extends \fpdf\FPDF
{
    protected $_title;

    protected $_logo_path;

    public function setLogoPath($logo_path)
    {
        $this->_logo_path = $logo_path;
    }

    public function setTitle($title, $isUTF8 = false)
    {
        $this->_title = $title;
        parent::SetTitle($title, $isUTF8);
    }

    function Header()
    {
        // Logo
        if ($this->_logo_path != null) {
            $this->Image(__DIR__ . '/../' . $this->_logo_path,10,10,-92,0,'PNG');
        }

        // Arial bold 15
        $this->SetFont('Arial','B',12);
        // Move to the right
        $this->Cell(50);

        // Title
        if ($this->_title != null) {
            $this->Cell(85,10,$this->_title,1,1,'C');
        }
    }

    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}
