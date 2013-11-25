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

class XmlConversionService
{
    protected $_xml;

    /**
     * Convert $array_input to XML format
     *
     * @param Array $array_input
     * @param SimpleXMLElement &$xml
     *
     * @return SimpleXMLElement XML output
     */
    protected function _array_to_xml(Array $array_input, &$xml)
    {
        foreach($array_input as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml->addChild("$key");
                    $this->_array_to_xml($value, $subnode);
                }
                else{
                    $this->_array_to_xml($value, $xml);
                }
            } else {
                if (is_object($value) && get_class($value) === 'DateTime') {
                    $xml->addChild(strval($key), $value->format('Y-m-d H:i:s'));
                } else {
                    $xml->addChild(strval($key), strval($value));
                }
            }
        }

        return $xml;
    }

    /**
     * Read Array $array_input and parse it into XML format
     *
     * @param Array $array_input
     *
     * @return SimpleXMLElement XML output
     */
    public function parseArray(Array $array_input)
    {
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
        $this->_array_to_xml($array_input, $xml);
        $this->_xml = $xml;

        return $this->_xml;
    }

    /**
     * Get XML as String
     *
     * @return String textual XML output
     */
    public function toString()
    {
        return $this->_xml->asXML();
    }
}
