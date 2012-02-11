<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * PEAR::Services_Weather_Common
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
 * @version     CVS: $Id: Common.php 277074 2009-03-12 23:16:41Z eru $
 * @link        http://pear.php.net/package/Services_Weather
 * @filesource
 */

require_once "Services/Weather.php";

// {{{ constants
// {{{ natural constants and measures
define("SERVICES_WEATHER_RADIUS_EARTH", 6378.15);
// }}}

// {{{ default values for the sun-functions
define("SERVICES_WEATHER_SUNFUNCS_DEFAULT_LATITUDE",  31.7667);
define("SERVICES_WEATHER_SUNFUNCS_DEFAULT_LONGITUDE", 35.2333);
define("SERVICES_WEATHER_SUNFUNCS_SUNRISE_ZENITH",    90.83);
define("SERVICES_WEATHER_SUNFUNCS_SUNSET_ZENITH",     90.83);
// }}}
// }}}

// {{{ class Services_Weather_Common
/**
 * Parent class for weather-services. Defines common functions for unit
 * conversions, checks for cache enabling and does other miscellaneous
 * things.
 *
 * @category    Web Services
 * @package     Services_Weather
 * @author      Alexander Wirtz <alex@pc4p.net>
 * @copyright   2005-2009 Alexander Wirtz
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     Release: 1.4.5
 * @link        http://pear.php.net/package/Services_Weather
 */
class Services_Weather_Common {

    // {{{ properties
    /**
     * Format of the units provided (standard/metric/custom)
     *
     * @var     string                      $_unitsFormat
     * @access  private
     */
    var $_unitsFormat = "s";

    /**
     * Custom format of the units
     *
     * @var     array                       $_customUnitsFormat
     * @access  private
     */
    var $_customUnitsFormat = array(
        "temp"   => "f",
        "vis"    => "sm",
        "height" => "ft",
        "wind"   => "mph",
        "pres"   => "in",
        "rain"   => "in"
    );

    /**
     * Options for HTTP requests
     *
     * @var     array                       $_httpOptions
     * @access  private
     */
    var $_httpOptions = array();

    /**
     * Format of the used dates
     *
     * @var     string                      $_dateFormat
     * @access  private
     */
    var $_dateFormat = "m/d/y";

    /**
     * Format of the used times
     *
     * @var     string                      $_timeFormat
     * @access  private
     */
    var $_timeFormat = "G:i A";

    /**
     * Object containing the location-data
     *
     * @var     object stdClass             $_location
     * @access  private
     */
    var $_location;

    /**
     * Object containing the weather-data
     *
     * @var     object stdClass             $_weather
     * @access  private
     */
    var $_weather;

    /**
     * Object containing the forecast-data
     *
     * @var     object stdClass             $_forecast
     * @access  private
     */
    var $_forecast;

    /**
     * Cache, containing the data-objects
     *
     * @var     object Cache                $_cache
     * @access  private
     */
    var $_cache;

    /**
     * Provides check for Cache
     *
     * @var     bool                        $_cacheEnabled
     * @access  private
     */
    var $_cacheEnabled = false;
    // }}}

    // {{{ constructor
    /**
     * Constructor
     *
     * @param   array                       $options
     * @param   mixed                       $error
     * @throws  PEAR_Error
     * @access  private
     */
    function Services_Weather_Common($options, &$error)
    {
        // Set some constants for the case when PHP4 is used, as the
        // date_sunset/sunrise functions are not implemented there
        if (!defined("SUNFUNCS_RET_TIMESTAMP")) {
            define("SUNFUNCS_RET_TIMESTAMP", 0);
            define("SUNFUNCS_RET_STRING",    1);
            define("SUNFUNCS_RET_DOUBLE",    2);
        }

        // Set options accordingly
        if (isset($options["cacheType"])) {
            if (isset($options["cacheOptions"])) {
                $status = $this->setCache($options["cacheType"], $options["cacheOptions"]);
            } else {
                $status = $this->setCache($options["cacheType"]);
            }
            if (Services_Weather::isError($status)) {
                $error = $status;
                return;
            }
        }

        if (isset($options["unitsFormat"])) {
            if (isset($options["customUnitsFormat"])) {
                $this->setUnitsFormat($options["unitsFormat"], $options["customUnitsFormat"]);
            } else {
                $this->setUnitsFormat($options["unitsFormat"]);
            }
        }

        if (isset($options["httpTimeout"])) {
            $this->setHttpTimeout($options["httpTimeout"]);
        } else {
            $this->setHttpTimeout(60);
        }
        if (isset($options["httpProxy"])) {
            $status = $this->setHttpProxy($options["httpProxy"]);
            if (Services_Weather::isError($status)) {
                $error = $status;
                return;
            }
        }

        if (isset($options["dateFormat"])) {
            $this->setDateTimeFormat($options["dateFormat"], "");
        }
        if (isset($options["timeFormat"])) {
            $this->setDateTimeFormat("", $options["timeFormat"]);
        }
    }
    // }}}

    // {{{ setCache()
    /**
     * Enables caching the data, usage strongly recommended
     *
     * Requires Cache to be installed
     *
     * @param   string                      $cacheType
     * @param   array                       $cacheOptions
     * @return  PEAR_Error|bool
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_CACHE_INIT_FAILED
     * @access  public
     */
    function setCache($cacheType = "file", $cacheOptions = array())
    {
        // The error handling in Cache is a bit crummy (read: not existent)
        // so we have to do that on our own...
        @include_once "Cache.php";
        @$cache = new Cache($cacheType, $cacheOptions);
        if (is_object($cache) && (strtolower(get_class($cache)) == "cache" || is_subclass_of($cache, "cache"))) {
            $this->_cache        = $cache;
            $this->_cacheEnabled = true;
        } else {
            $this->_cache        = null;
            $this->_cacheEnabled = false;
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_CACHE_INIT_FAILED, __FILE__, __LINE__);
        }

        return true;
    }
    // }}}

    // {{{ setUnitsFormat()
    /**
     * Changes the representation of the units (standard/metric)
     *
     * @param   string                      $unitsFormat
     * @param   array                       $customUnitsFormat
     * @access  public
     */
    function setUnitsFormat($unitsFormat, $customUnitsFormat = array())
    {
        static $acceptedFormats;
        if (!isset($acceptedFormats)) {
            $acceptedFormats = array(
                "temp"   => array("c", "f"),
                "vis"    => array("m", "km", "ft", "sm"),
                "height" => array("m", "ft"),
                "wind"   => array("mph", "kmh", "kt", "mps", "fps", "bft"),
                "pres"   => array("in", "hpa", "mb", "mm", "atm"),
                "rain"   => array("in", "mm")
            );
        }

        if (strlen($unitsFormat) && in_array(strtolower($unitsFormat{0}), array("c", "m", "s"))) {
            $this->_unitsFormat = strtolower($unitsFormat{0});
            if ($this->_unitsFormat == "c" && is_array($customUnitsFormat)) {
                foreach ($customUnitsFormat as $key => $value) {
                    if (array_key_exists($key, $acceptedFormats) && in_array($value, $acceptedFormats[$key])) {
                        $this->_customUnitsFormat[$key] = $value;
                    }
                }
            } elseif ($this->_unitsFormat == "c") {
                $this->_unitsFormat = "s";
            }
        }
    }
    // }}}

    // {{{ setHttpOption()
    /**
     * Sets an option for usage in HTTP_Request objects
     *
     * @param   string                      $varName
     * @param   mixed                       $varValue
     * @access  public
     */
    function setHttpOption($varName, $varValue)
    {
        if (is_string($varName) && $varName != "" && !empty($varValue)) {
            $this->_httpOptions[$varName] = $varValue;
        }
    }
    // }}}

    // {{{ setHttpTimeout()
    /**
     * Sets the timeout in seconds for HTTP requests
     *
     * @param   int                         $httpTimeout
     * @access  public
     */
    function setHttpTimeout($httpTimeout)
    {
        if (is_int($httpTimeout)) {
            $this->_httpOptions["timeout"] = $httpTimeout;
        }
    }
    // }}}

    // {{{ setHttpProxy()
    /**
     * Sets the proxy for HTTP requests
     *
     * @param   string                      $httpProxy
     * @access  public
     */
    function setHttpProxy($httpProxy)
    {
        if (($proxy = parse_url($httpProxy)) !== false && $proxy["scheme"] == "http") {
            if (isset($proxy["user"]) && $proxy["user"] != "") {
                $this->_httpOptions["proxy_user"] = $proxy["user"];
            }
            if (isset($proxy["pass"]) && $proxy["pass"] != "") {
                $this->_httpOptions["proxy_pass"] = $proxy["pass"];
            }
            if (isset($proxy["host"]) && $proxy["host"] != "") {
                $this->_httpOptions["proxy_host"] = $proxy["host"];
            }
            if (isset($proxy["port"]) && $proxy["port"] != "") {
                $this->_httpOptions["proxy_port"] = $proxy["port"];
            }

            return true;
        } else {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_HTTP_PROXY_INVALID, __FILE__, __LINE__);
        }
    }
    // }}}

    // {{{ getUnitsFormat()
    /**
     * Returns the selected units format
     *
     * @param   string                      $unitsFormat
     * @return  array
     * @access  public
     */
    function getUnitsFormat($unitsFormat = "")
    {
        // This is cheap'o stuff
        if (strlen($unitsFormat) && in_array(strtolower($unitsFormat{0}), array("c", "m", "s"))) {
            $unitsFormat = strtolower($unitsFormat{0});
        } else {
            $unitsFormat = $this->_unitsFormat;
        }

        $c = $this->_customUnitsFormat;
        $m = array(
            "temp"   => "c",
            "vis"    => "km",
            "height" => "m",
            "wind"   => "kmh",
            "pres"   => "mb",
            "rain"   => "mm"
        );
        $s = array(
            "temp"   => "f",
            "vis"    => "sm",
            "height" => "ft",
            "wind"   => "mph",
            "pres"   => "in",
            "rain"   => "in"
        );

        return ${$unitsFormat};
    }
    // }}}

    // {{{ setDateTimeFormat()
    /**
     * Changes the representation of time and dates (see http://www.php.net/date)
     *
     * @param   string                      $dateFormat
     * @param   string                      $timeFormat
     * @access  public
     */
    function setDateTimeFormat($dateFormat = "", $timeFormat = "")
    {
        if (strlen($dateFormat)) {
            $this->_dateFormat = $dateFormat;
        }
        if (strlen($timeFormat)) {
            $this->_timeFormat = $timeFormat;
        }
    }
    // }}}

    // {{{ convertTemperature()
    /**
     * Convert temperature between f and c
     *
     * @param   float                       $temperature
     * @param   string                      $from
     * @param   string                      $to
     * @return  float
     * @access  public
     */
    function convertTemperature($temperature, $from, $to)
    {
        if ($temperature == 'N/A') {
            return $temperature;
        }

        $from = strtolower($from{0});
        $to   = strtolower($to{0});

        $result = array(
            "f" => array(
                "f" => $temperature,            "c" => ($temperature - 32) / 1.8
            ),
            "c" => array(
                "f" => 1.8 * $temperature + 32, "c" => $temperature
            )
        );

        return round($result[$from][$to], 2);
    }
    // }}}

    // {{{ convertSpeed()
    /**
     * Convert speed between mph, kmh, kt, mps, fps and bft
     *
     * Function will return "false" when trying to convert from
     * Beaufort, as it is a scale and not a true measurement
     *
     * @param   float                       $speed
     * @param   string                      $from
     * @param   string                      $to
     * @return  float|int|bool
     * @access  public
     * @link    http://www.spc.noaa.gov/faq/tornado/beaufort.html
     */
    function convertSpeed($speed, $from, $to)
    {
        $from = strtolower($from);
        $to   = strtolower($to);

        static $factor;
        static $beaufort;
        if (!isset($factor)) {
            $factor = array(
                "mph" => array(
                    "mph" => 1,         "kmh" => 1.609344, "kt" => 0.8689762, "mps" => 0.44704,   "fps" => 1.4666667
                ),
                "kmh" => array(
                    "mph" => 0.6213712, "kmh" => 1,        "kt" => 0.5399568, "mps" => 0.2777778, "fps" => 0.9113444
                ),
                "kt"  => array(
                    "mph" => 1.1507794, "kmh" => 1.852,    "kt" => 1,         "mps" => 0.5144444, "fps" => 1.6878099
                ),
                "mps" => array(
                    "mph" => 2.2369363, "kmh" => 3.6,      "kt" => 1.9438445, "mps" => 1,         "fps" => 3.2808399
                ),
                "fps" => array(
                    "mph" => 0.6818182, "kmh" => 1.09728,  "kt" => 0.5924838, "mps" => 0.3048,    "fps" => 1
                )
            );

            // Beaufort scale, measurements are in knots
            $beaufort = array(
                  1,   3,   6,  10,
                 16,  21,  27,  33,
                 40,  47,  55,  63
            );
        }

        if ($from == "bft") {
            return false;
        } elseif ($to == "bft") {
            $speed = round($speed * $factor[$from]["kt"], 0);
            for ($i = 0; $i < sizeof($beaufort); $i++) {
                if ($speed <= $beaufort[$i]) {
                    return $i;
                }
            }
            return sizeof($beaufort);
        } else {
            return round($speed * $factor[$from][$to], 2);
        }
    }
    // }}}

    // {{{ convertPressure()
    /**
     * Convert pressure between in, hpa, mb, mm and atm
     *
     * @param   float                       $pressure
     * @param   string                      $from
     * @param   string                      $to
     * @return  float
     * @access  public
     */
    function convertPressure($pressure, $from, $to)
    {
        $from = strtolower($from);
        $to   = strtolower($to);

        static $factor;
        if (!isset($factor)) {
            $factor = array(
                "in"   => array(
                    "in" => 1,         "hpa" => 33.863887, "mb" => 33.863887, "mm" => 25.4,      "atm" => 0.0334213
                ),
                "hpa"  => array(
                    "in" => 0.02953,   "hpa" => 1,         "mb" => 1,         "mm" => 0.7500616, "atm" => 0.0009869
                ),
                "mb"   => array(
                    "in" => 0.02953,   "hpa" => 1,         "mb" => 1,         "mm" => 0.7500616, "atm" => 0.0009869
                ),
                "mm"   => array(
                    "in" => 0.0393701, "hpa" => 1.3332239, "mb" => 1.3332239, "mm" => 1,         "atm" => 0.0013158
                ),
                "atm"  => array(
                    "in" => 29,921258, "hpa" => 1013.2501, "mb" => 1013.2501, "mm" => 759.999952, "atm" => 1
                )
            );
        }

        return round($pressure * $factor[$from][$to], 2);
    }
    // }}}

    // {{{ convertDistance()
    /**
     * Convert distance between km, ft and sm
     *
     * @param   float                       $distance
     * @param   string                      $from
     * @param   string                      $to
     * @return  float
     * @access  public
     */
    function convertDistance($distance, $from, $to)
    {
        $to   = strtolower($to);
        $from = strtolower($from);

        static $factor;
        if (!isset($factor)) {
            $factor = array(
                "m" => array(
                    "m" => 1,            "km" => 1000,      "ft" => 3.280839895, "sm" => 0.0006213699
                ),
                "km" => array(
                    "m" => 0.001,        "km" => 1,         "ft" => 3280.839895, "sm" => 0.6213699
                ),
                "ft" => array(
                    "m" => 0.3048,       "km" => 0.0003048, "ft" => 1,           "sm" => 0.0001894
                ),
                "sm" => array(
                    "m" => 0.0016093472, "km" => 1.6093472, "ft" => 5280.0106,   "sm" => 1
                )
            );
        }

        return round($distance * $factor[$from][$to], 2);
    }
    // }}}

    // {{{ calculateWindChill()
    /**
     * Calculate windchill from temperature and windspeed (enhanced formula)
     *
     * Temperature has to be entered in deg F, speed in mph!
     *
     * @param   float                       $temperature
     * @param   float                       $speed
     * @return  float
     * @access  public
     * @link    http://www.nws.noaa.gov/om/windchill/
     */
    function calculateWindChill($temperature, $speed)
    {
        return round(35.74 + 0.6215 * $temperature - 35.75 * pow($speed, 0.16) + 0.4275 * $temperature * pow($speed, 0.16));
    }
    // }}}

    // {{{ calculateHumidity()
    /**
     * Calculate humidity from temperature and dewpoint
     * This is only an approximation, there is no exact formula, this
     * one here is called Magnus-Formula
     *
     * Temperature and dewpoint have to be entered in deg C!
     *
     * @param   float                       $temperature
     * @param   float                       $dewPoint
     * @return  float
     * @access  public
     * @link    http://www.faqs.org/faqs/meteorology/temp-dewpoint/
     */
    function calculateHumidity($temperature, $dewPoint)
    {
        // First calculate saturation steam pressure for both temperatures
        if ($temperature >= 0) {
            $a = 7.5;
            $b = 237.3;
        } else {
            $a = 7.6;
            $b = 240.7;
        }
        $tempSSP = 6.1078 * pow(10, ($a * $temperature) / ($b + $temperature));

        if ($dewPoint >= 0) {
            $a = 7.5;
            $b = 237.3;
        } else {
            $a = 7.6;
            $b = 240.7;
        }
        $dewSSP  = 6.1078 * pow(10, ($a * $dewPoint) / ($b + $dewPoint));

        return round(100 * $dewSSP / $tempSSP, 1);
    }
    // }}}

    // {{{ calculateDewPoint()
    /**
     * Calculate dewpoint from temperature and humidity
     * This is only an approximation, there is no exact formula, this
     * one here is called Magnus-Formula
     *
     * Temperature has to be entered in deg C!
     *
     * @param   float                       $temperature
     * @param   float                       $humidity
     * @return  float
     * @access  public
     * @link    http://www.faqs.org/faqs/meteorology/temp-dewpoint/
     */
    function calculateDewPoint($temperature, $humidity)
    {
        if ($temperature >= 0) {
            $a = 7.5;
            $b = 237.3;
        } else {
            $a = 7.6;
            $b = 240.7;
        }

        // First calculate saturation steam pressure for temperature
        $SSP = 6.1078 * pow(10, ($a * $temperature) / ($b + $temperature));

        // Steam pressure
        $SP  = $humidity / 100 * $SSP;

        $v   = log($SP / 6.1078, 10);

        return round($b * $v / ($a - $v), 1);
    }
    // }}}

    // {{{ polar2cartesian()
    /**
     * Convert polar coordinates to cartesian coordinates
     *
     * @param   float                       $latitude
     * @param   float                       $longitude
     * @return  array
     * @access  public
     */
    function polar2cartesian($latitude, $longitude)
    {
        $theta = deg2rad($latitude);
        $phi   = deg2rad($longitude);

        $x = SERVICES_WEATHER_RADIUS_EARTH * cos($phi) * cos($theta);
        $y = SERVICES_WEATHER_RADIUS_EARTH * sin($phi) * cos($theta);
        $z = SERVICES_WEATHER_RADIUS_EARTH             * sin($theta);

        return array($x, $y, $z);
    }
    // }}}


    // {{{ calculateSunRiseSet()
    /**
     * Calculates sunrise and sunset for a location
     *
     * The sun position algorithm taken from the 'US Naval Observatory's
     * Almanac for Computers', implemented by Ken Bloom <kekabloom[at]ucdavis[dot]edu>
     * for the zmanim project, converted to C by Moshe Doron <mosdoron[at]netvision[dot]net[dot]il>
     * and finally taken from the PHP5 sources and converted to native PHP as a wrapper.
     *
     * The date has to be entered as a timestamp!
     *
     * @param   int                         $date
     * @param   int                         $retformat
     * @param   float                       $latitude
     * @param   float                       $longitude
     * @param   float                       $zenith
     * @param   float                       $gmt_offset
     * @param   bool                        $sunrise
     * @return  PEAR_Error|mixed
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_SUNFUNCS_DATE_INVALID
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_SUNFUNCS_RETFORM_INVALID
     * @throws  PEAR_Error::SERVICES_WEATHER_ERROR_UNKNOWN_ERROR
     * @access  public
     */
    function calculateSunRiseSet($date, $retformat = null, $latitude = null, $longitude = null, $zenith = null, $gmt_offset = null, $sunrise = true)
    {
        // Date must be timestamp for now
        if (!is_int($date)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_SUNFUNCS_DATE_INVALID, __FILE__, __LINE__);
        }

        // Check for proper return format
        if ($retformat === null) {
            $retformat  = SUNFUNCS_RET_STRING;
        } elseif (!in_array($retformat, array(SUNFUNCS_RET_TIMESTAMP, SUNFUNCS_RET_STRING, SUNFUNCS_RET_DOUBLE)) ) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_SUNFUNCS_RETFORM_INVALID, __FILE__, __LINE__);
        }

        // Set default values for coordinates
        if ($latitude === null) {
            $latitude   = SUNFUNCS_DEFAULT_LATITUDE;
        } else {
            $latitude   = (float) $latitude;
        }
        if ($longitude === null) {
            $longitude  = SUNFUNCS_DEFAULT_LONGITUDE;
        } else {
            $longitude  = (float) $longitude;
        }
        if ($zenith === null) {
            if($sunrise) {
                $zenith = SUNFUNCS_SUNRISE_ZENITH;
            } else {
                $zenith = SUNFUNCS_SUNSET_ZENITH;
            }
        } else {
            $zenith     = (float) $zenith;
        }

        // Default value for GMT offset
        if ($gmt_offset === null) {
            $gmt_offset = date("Z", $date) / 3600;
        } else {
            $gmt_offset = (float) $gmt_offset;
        }

        // If we have PHP5, then act as wrapper for the appropriate functions
        if ($sunrise && function_exists("date_sunrise")) {
            return date_sunrise($date, $retformat, $latitude, $longitude, $zenith, $gmt_offset);
        }
        if (!$sunrise && function_exists("date_sunset")) {
            return date_sunset($date, $retformat, $latitude, $longitude, $zenith, $gmt_offset);
        }

        // Apparently we have PHP4, so calculate the neccessary steps in native PHP
        // Step 1: First calculate the day of the year
        $N = date("z", $date) + 1;

        // Step 2: Convert the longitude to hour value and calculate an approximate time
        $lngHour = $longitude / 15;

        // Use 18 for sunset instead of 6
        if ($sunrise) {
            // Sunrise
            $t = $N + ((6 - $lngHour) / 24);
        } else {
            // Sunset
            $t = $N + ((18 - $lngHour) / 24);
        }

        // Step 3: Calculate the sun's mean anomaly
        $M = (0.9856 * $t) - 3.289;

        // Step 4: Calculate the sun's true longitude
        $L = $M + (1.916 * sin(deg2rad($M))) + (0.020 * sin(deg2rad(2 * $M))) + 282.634;

        while ($L < 0) {
            $Lx = $L + 360;
            assert($Lx != $L); // askingtheguru: really needed?
            $L = $Lx;
        }

        while ($L >= 360) {
            $Lx = $L - 360;
            assert($Lx != $L); // askingtheguru: really needed?
            $L = $Lx;
        }

        // Step 5a: Calculate the sun's right ascension
        $RA = rad2deg(atan(0.91764 * tan(deg2rad($L))));

        while ($RA < 0) {
            $RAx = $RA + 360;
            assert($RAx != $RA); // askingtheguru: really needed?
            $RA = $RAx;
        }

        while ($RA >= 360) {
            $RAx = $RA - 360;
            assert($RAx != $RA); // askingtheguru: really needed?
            $RA = $RAx;
        }

        // Step 5b: Right ascension value needs to be in the same quadrant as L
        $Lquadrant  = floor($L / 90) * 90;
        $RAquadrant = floor($RA / 90) * 90;

        $RA = $RA + ($Lquadrant - $RAquadrant);

        // Step 5c: Right ascension value needs to be converted into hours
        $RA /= 15;

        // Step 6: Calculate the sun's declination
        $sinDec = 0.39782 * sin(deg2rad($L));
        $cosDec = cos(asin($sinDec));

        // Step 7a: Calculate the sun's local hour angle
        $cosH = (cos(deg2rad($zenith)) - ($sinDec * sin(deg2rad($latitude)))) / ($cosDec * cos(deg2rad($latitude)));

        // XXX: What's the use of this block.. ?
        // if (sunrise && cosH > 1 || !sunrise && cosH < -1) {
        //     throw doesnthappen();
        // }

        // Step 7b: Finish calculating H and convert into hours
        if ($sunrise) {
            // Sunrise
            $H = 360 - rad2deg(acos($cosH));
        } else {
            // Sunset
            $H = rad2deg(acos($cosH));
        }
        $H = $H / 15;

        // Step 8: Calculate local mean time
        $T = $H + $RA - (0.06571 * $t) - 6.622;

        // Step 9: Convert to UTC
        $UT = $T - $lngHour;

        while ($UT < 0) {
            $UTx = $UT + 24;
            assert($UTx != $UT); // askingtheguru: really needed?
            $UT = $UTx;
        }

        while ($UT >= 24) {
            $UTx = $UT - 24;
            assert($UTx != $UT); // askingtheguru: really needed?
            $UT = $UTx;
        }

        $UT = $UT + $gmt_offset;

        // Now bring the result into the chosen format and return
        switch ($retformat) {
            case SUNFUNCS_RET_TIMESTAMP:
                return intval($date - $date % (24 * 3600) + 3600 * $UT);
            case SUNFUNCS_RET_STRING:
                $N = floor($UT);
                return sprintf("%02d:%02d", $N, floor(60 * ($UT - $N)));
            case SUNFUNCS_RET_DOUBLE:
                return $UT;
            default:
                return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_UNKNOWN_ERROR, __FILE__, __LINE__);
        }
    }
    // }}}

    // {{{ getWeatherIcon()
    /**
     * Gets a number corresponding to a weather icon.
     *
     * These numbers just happen to correspond with the icons that you get with
     * the weather.com SDK, but open versions of them have been created. Input
     * must be in standard units. For the icons that include day/night, we use
     * the present time and the provided lat/lon to determine if the sun is up.
     * A complete set of icon descriptions can be found here:
     * http://sranshaft.wincustomize.com/Articles.aspx?AID=60165&u=0
     *
     * There are a number of icon sets here:
     * http://www.desktopsidebar.com/forums/index.php?showtopic=2441&st=0
     * http://www.desktopsidebar.com/forums/index.php?showtopic=819
     *
     * @param   string                      $condition      The condition.
     * @param   array                       $clouds         The clouds at various levels.
     * @param   float                       $wind           Wind speed in mph.
     * @param   float                       $temperature    Temperature in deg F.
     * @param   float                       $latitude       Point latitude.
     * @param   float                       $longitude      Point longitude.
     * @author  Seth Price  <seth@pricepages.org>
     * @access  public
     */
    function getWeatherIcon($condition, $clouds = array(), $wind = 5, $temperature = 70, $latitude = -360, $longitude = -360)
    {
        // Search for matches that don't use the time of day
        $hail     = (bool) stristr($condition, "hail");
        $dust     = (bool) stristr($condition, "dust")     || (bool) stristr($condition, "sand");
        $smoke    = (bool) stristr($condition, "smoke")    || (bool) stristr($condition, "volcanic ash");

        // Slightly more complex matches that might or might not use the time of day
        $near     = (bool) stristr($condition, "vicinity") || (bool) stristr($condition, "recent");
        $light    = (bool) stristr($condition, "light");
        $heavy    = (bool) stristr($condition, "heavy");
        $ice      = (bool) stristr($condition, "ice")      || (bool) stristr($condition, "pellets");
        $rain     = (bool) stristr($condition, "rain");
        $snow     = (bool) stristr($condition, "snow");
        $fog      = (bool) stristr($condition, "fog")      || (bool) stristr($condition, "spray")        || (bool) stristr($condition, "mist");
        $haze     = (bool) stristr($condition, "haze");
        $ts       = (bool) stristr($condition, "thunderstorm");
        $freezing = (bool) stristr($condition, "freezing");
        $wind     = (bool) stristr($condition, "squall")   || $wind > 25;
        $nsw      = (bool) stristr($condition, "no significant weather");
        $hot      = $temperature > 95;
        $frigid   = $temperature < 5;


        if ($hail) {
            return 6;  // Hail
        }
        if ($dust) {
            return 19; // Dust
        }
        if ($smoke) {
            return 22; // Smoke
        }

        // Get some of the dangerous conditions fist
        if ($rain && $snow && ($ice || $freezing)) {
            return 7;  // Icy/Clouds Rain-Snow
        }
        if (($ts || $rain) && ($ice || $freezing)) {
            return 10; // Icy/Rain
        }
        if (($fog || $haze) && ($ice || $freezing)) {
            return 8;  // Icy/Haze Rain
        }
        if ($rain && $snow) {
            return 5;  // Cloudy/Snow-Rain Mix
        }
        if ($fog && $rain) {
            return 9;  // Haze/Rain
        }
        if ($wind && $rain) {
            return 1;  // Wind/Rain
        }
        if ($wind && $snow) {
            return 43; // Windy/Snow
        }
        if ($snow && $light) {
            return 13; // Flurries
        }
        if ($light && $rain) {
            return 11; // Light Rain
        }

        // Get the maximum coverage of the clouds at any height. For most
        // people, overcast at 1000ft is the same as overcast at 10000ft.
        //
        // 0 == clear, 1 == hazey, 2 == partly cloudy, 3 == mostly cloudy, 4 == overcast
        $coverage = 0;
        foreach ($clouds as $layer) {
            if ($coverage < 1 && stristr($layer["amount"], "few")) {
                $coverage = 1;
            } elseif ($coverage < 2 && stristr($layer["amount"], "scattered")) {
                $coverage = 2;
            } elseif ($coverage < 3 && (stristr($layer["amount"], "broken") || stristr($layer["amount"], "cumulus"))) {
                $coverage = 3;
            } elseif ($coverage < 4 && stristr($layer["amount"], "overcast")) {
                $coverage = 4;
            }
        }

        // Check if it is day or not. 0 is night, 2 is day, and 1 is unknown
        // or twilight (~(+|-)1 hour of sunrise/sunset). Note that twilight isn't
        // always accurate because of issues wrapping around the 24hr clock. Oh well...
        if ($latitude < 90 && $latitude > -90 && $longitude < 180 && $longitude > -180) {
            // Calculate sunrise/sunset and current time in GMT
            $sunrise   = $this->calculateSunRiseSet(gmmktime(), SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, SERVICES_WEATHER_SUNFUNCS_SUNRISE_ZENITH, 0, true);
            $sunset    = $this->calculateSunRiseSet(gmmktime(), SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, SERVICES_WEATHER_SUNFUNCS_SUNRISE_ZENITH, 0, false);
            $timeOfDay = gmmktime();

            // Now that we have the sunrise/sunset times and the current time,
            // we need to figure out if it is day, night, or twilight. Wrapping
            // these times around the 24hr clock is a pain.
            if ($sunrise < $sunset) {
                if ($timeOfDay > ($sunrise + 3600) && $timeOfDay < ($sunset - 3600)) {
                    $isDay = 2;
                } elseif ($timeOfDay > ($sunrise - 3600) && $timeOfDay < ($sunset + 3600)) {
                    $isDay = 1;
                } else {
                    $isDay = 0;
                }
            } else {
                if ($timeOfDay < ($sunrise - 3600) && $timeOfDay > ($sunset + 3600)) {
                    $isDay = 0;
                } elseif ($timeOfDay < ($sunrise + 3600) && $timeOfDay > ($sunset - 3600)) {
                    $isDay = 1;
                } else {
                    $isDay = 2;
                }
            }
        } else {
            // Default to twilight because it tends to have neutral icons.
            $isDay = 1;
        }

        // General precipitation
        if ($ts && $near) {
            switch ($isDay) {
                case 0:
                case 1:
                    return 38; // Lightning
                case 2:
                    return 37; // Lightning/Day
            }
        }
        if ($ts) {
            switch ($isDay) {
                case 0:
                    return 47; // Thunderstorm/Night
                case 1:
                case 2:
                    return 0;  // Rain/Lightning
            }
        }
        if ($snow) {
            switch ($isDay) {
                case 0:
                    return 46; // Snow/Night
                case 1:
                case 2:
                    return 41; // Snow
            }
        }
        if ($rain) {
            switch ($isDay) {
                case 0:
                    return 45; // Rain/Night
                case 1:
                    return 40; // Rain
                case 2:
                    return 39; // Rain/Day
            }
        }

        // Cloud conditions near the ground
        if ($fog) {
            return 20; // Fog
        }
        if ($haze) {
            return 21; // Haze
        }

        // Cloud conditions
        if ($coverage == 4) {
            return 26; // Mostly Cloudy
        }
        if ($coverage == 3) {
            switch ($isDay) {
                case 0:
                    return 27; // Mostly Cloudy/Night
                case 1:
                    return 26; // Mostly Cloudy
                case 2:
                    return 28; // Mostly Cloudy/Day
            }
        }
        if ($coverage == 2) {
            switch ($isDay) {
                case 0:
                    return 29; // Partly Cloudy/Night
                case 1:
                    return 26; // Mostly Cloudy
                case 2:
                    return 30; // Partly Cloudy/Day
            }
        }
        if ($coverage == 1) {
            switch ($isDay) {
                case 0:
                case 1:
                    return 33; // Hazy/Night
                case 2:
                    return 34; // Hazy/Day
            }
        }

        // Catch-alls
        if ($wind) {
            return 23; // Wind
        }
        if ($hot) {
            return 36; // Hot!
        }
        if ($frigid) {
            return 25; // Frigid
        }

        if ($nsw) {
            switch ($isDay) {
                case 0:
                case 1:
                    // Use night for twilight because the moon is generally
                    // out then, so it will match with most icon sets.
                    return 31; // Clear Night
                case 2:
                    return 32; // Clear Day
            }
        }

        return "na";
    }
    // }}}
}
// }}}
?>
