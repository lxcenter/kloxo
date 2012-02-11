<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * PEAR::Services_Weather_Globalweather
 *
 * PHP versions 4 and 5
 *
 * <LICENSE>
 * Copyright (c) 2005-2009, Alexander Wirtz
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * o Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * o Neither the name of the software nor the names of its contributors
 *   may be used to endorse or promote products derived from this software
 *   without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * </LICENSE>
 *
 * @category    Web Services
 * @package     Services_Weather
 * @author      Alexander Wirtz <alex@pc4p.net>
 * @copyright   2005-2009 Alexander Wirtz
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     CVS: $Id: Globalweather.php 277074 2009-03-12 23:16:41Z eru $
 * @link        http://pear.php.net/package/Services_Weather
 * @link        http://www.capescience.com/webservices/globalweather/index.shtml
 * @example     examples/globalweather-basic.php    globalweather-basic.php
 * @filesource
 */

require_once "Services/Weather/Common.php";

// {{{ class Services_Weather_Globalweather
/**
 * This class acts as an interface to the soap service of capescience.com.
 * It searches for given locations and retrieves current weather data.
 *
 * GlobalWeather is a SOAP frontend for METAR data, provided by CapeScience.
 * If you want to use METAR, you should try this class first, as it is much
 * more comfortable (and also a bit faster) than the native METAR-class
 * provided by this package. On the other hand, this service won't supply
 * TAFs, the forecast system accompanying METAR, so you have to make
 * the call here...
 *
 * For a working example, please take a look at
 *     docs/Services_Weather/examples/globalweather-basic.php
 *
 *
 * @category    Web Services
 * @package     Services_Weather
 * @author      Alexander Wirtz <alex@pc4p.net>
 * @copyright   2005-2009 Alexander Wirtz
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     Release: 1.4.5
 * @link        http://pear.php.net/package/Services_Weather
 * @link        http://www.capescience.com/webservices/globalweather/index.shtml
 * @example     examples/globalweather-basic.php    globalweather-basic.php
 */
class Services_Weather_Globalweather extends Services_Weather_Common {

    // {{{ properties
    /**
     * WSDL object, provided by CapeScience
     *
     * @var     object                      $_wsdl
     * @access  private
     */
    var $_wsdl;

    /**
     * SOAP object to access station data, provided by CapeScience
     *
     * @var     object                      $_stationSoap
     * @access  private
     */
    var $_stationSoap;

    /**
     * SOAP object to access weather data, provided by CapeScience
     *
     * @var      object                      $_weatherSoap
     * @access   private
     */
    var $_weatherSoap;
    // }}}

    // {{{ constructor
    /**
     * Constructor
     *
     * Requires SOAP to be installed
     *
     * @param   array                       $options
     * @param   mixed                       $error
     * @throws  PEAR_Error
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA
     * @access  private
     */
    function Services_Weather_Globalweather($options, &$error)
    {
        $perror = null;
        $this->Services_Weather_Common($options, $perror);
        if (Services_Weather::isError($perror)) {
            $error = $perror;
        }
    }
    // }}}

    // {{{ _connectServer()
    /**
     * Connects to the SOAP server and retrieves the WSDL data
     *
     * @return  PEAR_Error|bool
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA
     * @access  private
     */
    function _connectServer()
    {
        include_once "SOAP/Client.php";
        $this->_wsdl = new SOAP_WSDL("http://live.capeclear.com/ccx/GlobalWeather?wsdl", $this->_httpOptions);
        if (isset($this->_wsdl->fault) && Services_Weather::isError($this->_wsdl->fault)) {
            $error = Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            return $error;
        }

        eval($this->_wsdl->generateAllProxies());
        if (!class_exists("WebService_GlobalWeather_StationInfo") || !class_exists("WebService_GlobalWeather_GlobalWeather")) {
            $error = Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            return $error;
        }

        $this->_stationSoap = &new WebService_GlobalWeather_StationInfo;
        $this->_weatherSoap = &new WebService_GlobalWeather_GlobalWeather;

        return true;
    }
    // }}}

    // {{{ _checkLocationID()
    /**
     * Checks the id for valid values and thus prevents silly requests to
     * GlobalWeather server
     *
     * @param   string                      $id
     * @return  PEAR_Error|bool
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_NO_LOCATION
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_INVALID_LOCATION
     * @access  private
     */
    function _checkLocationID($id)
    {
        // Check, if the stationSoap-Object is present. If not, connect to the Server and retrieve the WDSL data
        if (!$this->_stationSoap) {
            $status = $this->_connectServer();
            if (Services_Weather::isError($status)) {
                return $status;
            }
        }

        if (is_array($id) || is_object($id) || !strlen($id)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_NO_LOCATION, __FILE__, __LINE__);
        } elseif ($this->_stationSoap->isValidCode($id) === false) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_INVALID_LOCATION, __FILE__, __LINE__);
        }

        return true;
    }
    // }}}

    // {{{ searchLocation()
    /**
     * Searches IDs for given location, returns array of possible locations
     * or single ID
     *
     * @param   string                      $location
     * @param   bool                        $useFirst       If set, first ID of result-array is returned
     * @return  PEAR_Error|array|string
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION
     * @access  public
     */
    function searchLocation($location, $useFirst = false)
    {
        // Check, if the stationSoap-Object is present. If not, connect to the Server and retrieve the WDSL data
        if (!$this->_stationSoap) {
            $status = $this->_connectServer();
            if (Services_Weather::isError($status)) {
                return $status;
            }
        }

        // Get search data from server and unserialize
        $search = $this->_stationSoap->searchByName($location);

        if (Services_Weather::isError($search)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
        } else {
            if (!is_array($search) || !sizeof($search)) {
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION, __FILE__, __LINE__);
            } else {
                if (!$useFirst && (sizeof($search) > 1)) {
                    $searchReturn = array();
                    for ($i = 0; $i < sizeof($search); $i++) {
                        $searchReturn[$search[$i]->icao] = $search[$i]->name.", ".$search[$i]->country;
                    }
                } elseif ($useFirst || (sizeof($search) == 1)) {
                    $searchReturn = $search[0]->icao;
                }
            }
        }

        return $searchReturn;
    }
    // }}}

    // {{{ searchLocationByCountry()
    /**
     * Returns IDs with location-name for a given country or all available
     * countries, if no value was given
     *
     * @param   string                      $country
     * @return  PEAR_Error|array
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION
     * @access  public
     */
    function searchLocationByCountry($country = "")
    {
        // Check, if the stationSoap-Object is present. If not, connect to the Server and retrieve the WDSL data
        if (!$this->_stationSoap) {
            $status = $this->_connectServer();
            if (Services_Weather::isError($status)) {
                return $status;
            }
        }

        // Return the available countries as no country was given
        if (!strlen($country)) {
            $countries = $this->_stationSoap->listCountries("");
            if (Services_Weather::isError($countries)) {
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
            }
            return $countries;
        }

        // Now for the real search
        $countryLocs = $this->_stationSoap->searchByCountry($country);
        // Check result for validity
        if (Services_Weather::isError($countryLocs)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA, __FILE__, __LINE__);
        } elseif (!is_array($countryLocs)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION, __FILE__, __LINE__);
        }

        // Construct the result
        $locations = array();
        foreach ($countryLocs as $location) {
            $locations[$location->icao] = $location->name.", ".$location->country;
        }
        asort($locations);

        return $locations;
    }
    // }}}

    // {{{ getLocation()
    /**
     * Returns the data for the location belonging to the ID
     *
     * @param   string                      $id
     * @return  PEAR_Error|array
     * @throws  PEAR_Error
     * @access  public
     */
    function getLocation($id = "")
    {
        $status = $this->_checkLocationID($id);

        if (Services_Weather::isError($status)) {
            return $status;
        }

        $locationReturn = array();

        if ($this->_cacheEnabled && ($location = $this->_cache->get("GW-".$id, "location"))) {
            // Get data from cache
            $this->_location = $location;
            $locationReturn["cache"] = "HIT";
        } else {
            // Check, if the stationSoap-Object is present. If not, connect to the Server and retrieve the WDSL data
            if (!$this->_stationSoap) {
                $status = $this->_connectServer();
                if (Services_Weather::isError($status)) {
                    return $status;
                }
            }

            $location = $this->_stationSoap->getStation($id);

            if (Services_Weather::isError($location)) {
                return $location;
            }

            $this->_location = $location;

            if ($this->_cacheEnabled) {
                // ...and cache it
                $expire = constant("SERVICES_WEATHER_EXPIRES_LOCATION");
                $this->_cache->extSave("GW-".$id, $this->_location, "", $expire, "location");
            }
            $locationReturn["cache"] = "MISS";
        }
        if (strlen($this->_location->region) && strlen($this->_location->country)) {
            $locname = $this->_location->name.", ".$this->_location->region.", ".$this->_location->country;
        } elseif (strlen($this->_location->country)) {
            $locname = $this->_location->name.", ".$this->_location->country;
        } else {
            $locname = $this->_location->name;
        }
        $locationReturn["name"]      = $locname;
        $locationReturn["latitude"]  = $this->_location->latitude;
        $locationReturn["longitude"] = $this->_location->longitude;
        $locationReturn["sunrise"]   = date($this->_timeFormat, $this->calculateSunRiseSet(gmmktime(), SUNFUNCS_RET_TIMESTAMP, $this->_location->latitude, $this->_location->longitude, SERVICES_WEATHER_SUNFUNCS_SUNRISE_ZENITH, 0, true));
        $locationReturn["sunset"]    = date($this->_timeFormat, $this->calculateSunRiseSet(gmmktime(), SUNFUNCS_RET_TIMESTAMP, $this->_location->latitude, $this->_location->longitude, SERVICES_WEATHER_SUNFUNCS_SUNSET_ZENITH,  0, false));
        $locationReturn["elevation"] = $this->_location->elevation;

        return $locationReturn;
    }
    // }}}

    // {{{ getWeather()
    /**
     * Returns the weather-data for the supplied location
     *
     * @param   string                      $id
     * @param   string                      $unitsFormat
     * @return  PEAR_Error|array
     * @throws  PEAR_Error
     * @access  public
     */
    function getWeather($id = "", $unitsFormat = "")
    {
        static $clouds;
        if (!isset($clouds)) {
            $clouds    = array(
                "sky clear",
                "few",
                "scattered",
                "broken",
                "overcast",
            );
        }

        $status = $this->_checkLocationID($id);

        if (Services_Weather::isError($status)) {
            return $status;
        }

        // Get other data
        $units    = $this->getUnitsFormat($unitsFormat);

        $weatherReturn = array();
        if ($this->_cacheEnabled && ($weather = $this->_cache->get("GW-".$id, "weather"))) {
            // Same procedure...
            $this->_weather = $weather;
            $weatherReturn["cache"] = "HIT";
        } else {
            // Check, if the weatherSoap-Object is present. If not, connect to the Server and retrieve the WDSL data
            if (!$this->_weatherSoap) {
                $status = $this->_connectServer();
                if (Services_Weather::isError($status)) {
                    return $status;
                }
            }

            // ...as last function
            $weather = $this->_weatherSoap->getWeatherReport($id);

            if (Services_Weather::isError($weather)) {
                return $weather;
            }

            $this->_weather = $weather;

            if ($this->_cacheEnabled) {
                // ...and cache it
                $expire = constant("SERVICES_WEATHER_EXPIRES_WEATHER");
                $this->_cache->extSave("GW-".$id, $this->_weather, "", $expire, "weather");
            }
            $weatherReturn["cache"] = "MISS";
        }

        $update = trim(str_replace(array("T", "Z"), " ", $this->_weather->timestamp))." GMT";

        $weatherReturn["update"]            = gmdate(trim($this->_dateFormat." ".$this->_timeFormat), strtotime($update));
        $weatherReturn["updateRaw"]         = $this->_weather->timestamp;
        if (strlen($this->_weather->station->region) && strlen($this->_weather->station->country)) {
            $locname = $this->_weather->station->name.", ".$this->_weather->station->region.", ".$this->_weather->station->country;
        } elseif (strlen($this->_weather->station->country)) {
            $locname = $this->_weather->station->name.", ".$this->_weather->station->country;
        } else {
            $locname = $this->_weather->station->name;
        }
        $weatherReturn["station"]           = $locname;
        $weatherReturn["wind"]              = $this->convertSpeed($this->_weather->wind->prevailing_speed, "mps", $units["wind"]);
        $weatherReturn["windDegrees"]       = $this->_weather->wind->prevailing_direction->degrees;
        $weatherReturn["windDirection"]     = $this->_weather->wind->prevailing_direction->compass;
        if ($this->_weather->wind->prevailing_speed != $this->_weather->wind->gust_speed) {
            $weatherReturn["windGust"]      = $this->convertSpeed($this->_weather->wind->gust_speed, "mps", $units["wind"]);
        }
        if ($this->_weather->wind->varying_from_direction != "" && $this->_weather->wind->varying_to_direction != "") {
            $weatherReturn["windVar"]       = array (
                "from" => $this->_weather->wind->varying_from_direction,
                "to"   => $this->_weather->wind->varying_to_direction
            );
        }

        $weatherReturn["visibility"]        = $this->convertDistance($this->_weather->visibility->distance / 1000, "km", $units["vis"]);
        $weatherReturn["visQualifier"]      = $this->_weather->visibility->qualifier;

        $condition = array();
        for ($i = 0; $i < sizeof($this->_weather->phenomena); $i++) {
            $condition[] = $this->_weather->phenomena[$i]->string;
        }
        $weatherReturn["condition"]         = implode(", ", $condition);

        if (is_array($this->_weather->sky->layers)) {
            $layers = array();
            for ($i = 0; $i < sizeof($this->_weather->sky->layers); $i++) {
                if (strtoupper($this->_weather->sky->layers[$i]->type) != "CLEAR") {
                    $layers[$i]             = array();
                    $layers[$i]["amount"]   = $clouds[$this->_weather->sky->layers[$i]->extent];
                    $layers[$i]["height"]   = $this->convertDistance($this->_weather->sky->layers[$i]->altitude, "m", $units["height"]);
                    if (strtoupper($this->_weather->sky->layers[$i]->type) != "CLOUD") {
                        $layers[$i]["type"] = ucwords(str_replace("_", "", $this->_weather->sky->layers[$i]->type));
                    }
                }
            }
            if (sizeof($layers)) {
                $weatherReturn["clouds"]        = $layers;
            }
        }

        $weatherReturn["temperature"]       = $this->convertTemperature($this->_weather->temperature->ambient, "c", $units["temp"]);
        $feltTemperature = $this->calculateWindChill($this->convertTemperature($weatherReturn["temperature"], $units["temp"], "f"), $this->convertSpeed($weatherReturn["wind"], $units["wind"], "mph"));
        $weatherReturn["feltTemperature"]   = $this->convertTemperature($feltTemperature, "f", $units["temp"]);
        $weatherReturn["dewPoint"]          = $this->convertTemperature($this->_weather->temperature->dewpoint, "c", $units["temp"]);
        $weatherReturn["humidity"]          = $this->_weather->temperature->relative_humidity;
        $weatherReturn["pressure"]          = $this->convertPressure($this->_weather->pressure->altimeter, "hpa", $units["pres"]);

        return $weatherReturn;
    }
    // }}}

    // {{{ getForecast()
    /**
     * GlobalWeather has no forecast per se, so this function is just for
     * compatibility purposes.
     *
     * @param   string                      $int
     * @param   int                         $days
     * @param   string                      $unitsFormat
     * @return  bool
     * @access  public
     * @deprecated
     */
    function getForecast($id = null, $days = null, $unitsFormat = null)
    {
        return false;
    }
    // }}}
}
// }}}
?>
