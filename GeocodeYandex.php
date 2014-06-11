<?php
/**
 * @author calmsen
 */
class GeocodeYandex {
    private $GeocodeMapsYandexUrl = "http://geocode-maps.yandex.ru/1.x/";
    private $_xslxPath;
    private $_start = 1;
    private $_end = 50;
    
    public function __construct($_xslxPath) {
        $this->_xslxPath = $_xslxPath;
    }
    
    public function setGeocode() {
        $dom = new DOMDocument("1.0", "utf-8");
        $dom->load($this->_xslxPath);
        $cellEls = $dom->getElementsByTagName("Cell");
        $curPlaceData = array();
        for ($i = 0; $i < $cellEls->length; $i++) {
            if ($cellEls->item($i)->getAttribute("Row") == 0) {
                continue;
            }
            if ($cellEls->item($i)->getAttribute("Col") <= 3) {
                $curPlaceData[$cellEls->item($i)->getAttribute("Col")] = $cellEls->item($i)->nodeValue;
            }
            if ($cellEls->item($i)->getAttribute("Col") >= 4) {
                continue;
            }
            if (($i == $cellEls->length - 1) 
                    || $cellEls->item($i + 1)->getAttribute("Row") != $cellEls->item($i)->getAttribute("Row")) {
                $refNode = $i == $cellEls->length - 1 ? null : $cellEls->item($i + 1);
                if ($cellEls->item($i)->getAttribute("Col") == 2) {
                    $emptyNode = $cellEls->item($i)->cloneNode();
                    $emptyNode->setAttribute("Col", 3);
                    $emptyNode->setAttribute("ValueType", 60);
                    $emptyNode->nodeValue = "";
                    $cellEls->item($i)->parentNode->insertBefore($emptyNode, $refNode);
                    $cellEls->item($i)->parentNode->insertBefore(new DOMText("\n"), $refNode);
                }
                $json = json_decode(file_get_contents($this->GeocodeMapsYandexUrl . "?geocode=" . $curPlaceData[0] . ",+" . $curPlaceData[1] . ",+дом+" . $curPlaceData[2] . (isset($curPlaceData[3]) ? $curPlaceData[3] : "") . "&format=json"));
                $pos = $json->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
                $coords = preg_split("/\s/", $pos);
                $longitudeNode = $cellEls->item($i)->cloneNode();
                $longitudeNode->setAttribute("Col", 4);
                $longitudeNode->setAttribute("ValueType", 60);
                $longitudeNode->nodeValue = $coords[0];
                $cellEls->item($i)->parentNode->insertBefore($longitudeNode, $refNode);
                $cellEls->item($i)->parentNode->insertBefore(new DOMText("\n"), $refNode);
                $latitudeNode = $cellEls->item($i)->cloneNode();
                $latitudeNode->setAttribute("Col", 5);
                $latitudeNode->setAttribute("ValueType", 60);
                $latitudeNode->nodeValue = $coords[1];
                $cellEls->item($i)->parentNode->insertBefore($latitudeNode, $refNode);
                $cellEls->item($i)->parentNode->insertBefore(new DOMText("\n"), $refNode);
                if (++$this->_start >= $this->_end) {
                    break;
                }
            }
            
        }
        $dom->save($this->_xslxPath);
    }

}
