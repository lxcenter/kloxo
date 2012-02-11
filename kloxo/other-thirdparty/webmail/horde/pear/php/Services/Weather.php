<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

/**
 * PEAR::Services_Weather
 *
 * Services_Weather searches for given locations and retrieves current
 * weather data and, dependant on the used service, also forecasts. Up to
 * now, SOAP services from CapeScience and EJSE, XML from weather.com and
 * METAR/TAF from noaa.gov are supported, further services will get
 * included, if they become available and are properly documented.
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
 * @version     CVS: $Id: Weather.php 277074 2009-03-12 23:16:41Z eru $
 * @link        http://pear.php.net/package/Services_Weather
 * @filesource
 */

// {{{ constants
// {{{ cache times
define("SERVICES_WEATHER_EXPIRES_UNITS",        900); // 15M
define("SERVICES_WEATHER_EXPIRES_LOCATION",   43200); // 12H
define("SERVICES_WEATHER_EXPIRES_WEATHER",     1800); // 30M
define("SERVICES_WEATHER_EXPIRES_FORECAST",    7200); //  2H
define("SERVICES_WEATHER_EXPIRES_LINKS",      43200); // 12H
define("SERVICES_WEATHER_EXPIRES_SEARCH",   2419200); // 28D
// }}}

// {{{ error codes
define("SERVICES_WEATHER_ERROR_SERVICE_NOT_FOUND",        10);
define("SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION",         11);
define("SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA",        12);
define("SERVICES_WEATHER_ERROR_CACHE_INIT_FAILED",        13);
define("SERVICES_WEATHER_ERROR_DB_NOT_CONNECTED",         14);
define("SERVICES_WEATHER_ERROR_HTTP_PROXY_INVALID",       15);
define("SERVICES_WEATHER_ERROR_SUNFUNCS_DATE_INVALID",    16);
define("SERVICES_WEATHER_ERROR_SUNFUNCS_RETFORM_INVALID", 17);
define("SERVICES_WEATHER_ERROR_METAR_SOURCE_INVALID",     18);
// }}}

// {{{ error codes defined by weather.com
define("SERVICES_WEATHER_ERROR_UNKNOWN_ERROR",            0);
define("SERVICES_WEATHER_ERROR_NO_LOCATION",              1);
define("SERVICES_WEATHER_ERROR_INVALID_LOCATION",         2);
define("SERVICES_WEATHER_ERROR_INVALID_PARTNER_ID",     100);
define("SERVICES_WEATHER_ERROR_INVALID_PRODUCT_CODE",   101);
define("SERVICES_WEATHER_ERROR_INVALID_LICENSE_KEY",    102);
// }}}
// }}}

// {{{ class Services_Weather
/**
 * This class acts as an interface to various online weather-services.
 *
 * @category    Web Services
 * @package     Services_Weather
 * @author      Alexander Wirtz <alex@pc4p.net>
 * @copyright   2005-2009 Alexander Wirtz
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     Release: 1.4.5
 * @link        http://pear.php.net/package/Services_Weather
 */
class Services_Weather {

    // {{{ &service()
    /**
     * Factory for creating the services-objects
     *
     * Usable keys for the options array are:
     * o debug              enables debugging output
     * --- Common Options
     * o cacheType          defines what type of cache to use
     * o cacheOptions       passes cache options
     * o unitsFormat        use (US)-standard, metric or custom units
     * o customUnitsFormat  defines the customized units format
     * o httpTimeout        sets timeout for HTTP requests
     * o httpProxy          sets proxy for HTTP requests, please use the
     *                      notation http://[user[:pass]@]host[:port]
     * o dateFormat         string to use for date output
     * o timeFormat         string to use for time output
     * --- EJSE Options
     * o none
     * --- GlobalWeather Options
     * o none
     * --- METAR/TAF Options
     * o dsn                String for defining the DB connection
     * o dbOptions          passes DB options
     * o sourceMetar        http, ftp or file - type of data-source for METAR
     * o sourcePathMetar    where to look for the source, URI or filepath,
     *                      of METAR information
     * o sourceTaf          http, ftp or file - type of data-source for TAF
     * o sourcePathTaf      where to look for the source, URI or filepath,
     *                      of TAF information
     * --- weather.com Options
     * o partnerID          You'll receive these keys after registering
     * o licenseKey         with the weather.com XML-service
     * o preFetch           Enables pre-fetching of data in one single request
     *
     * @param    string                     $service
     * @param    array                      $options
     * @return   PEAR_Error|object
     * @throws   PEAR_Error
     * @throws   PEAR_Error::SERVICES_WEATHER_ERROR_SERVICE_NOT_FOUND
     * @access   public
     */
    function &service($service, $options = null)
    {
        $service = ucfirst(strtolower($service));
        $classname = "Services_Weather_".$service;

        // Check for debugging-mode and set stuff accordingly
        if (is_array($options) && isset($options["debug"]) && $options["debug"] >= 2) {
            if (!defined("SERVICES_WEATHER_DEBUG")) {
                define("SERVICES_WEATHER_DEBUG", true);
            }
            include_once("Services/Weather/".$service.".php");
        } else {
            if (!defined("SERVICES_WEATHER_DEBUG")) {
                define("SERVICES_WEATHER_DEBUG", false);
            }
            @include_once("Services/Weather/".$service.".php");
        }

        // No such service... bail out
        if (!class_exists($classname)) {
            return Services_Weather::raiseError(SERVICES_WEATHER_ERROR_SERVICE_NOT_FOUND, __FILE__, __LINE__);
        }

        // Create service and return
        $error = null;
        @$obj = &new $classname($options, $error);

        if (Services_Weather::isError($error)) {
            return $error;
        } else {
            return $obj;
        }
    }
    // }}}

    // {{{ apiVersion()
    /**
     * For your convenience, when I come up with changes in the API...
     *
     * @return  string
     * @access  public
     */
    function apiVersion()
    {
        return "1.4";
    }
    // }}}

    // {{{ _errorMessage()
    /**
     * Returns the message for a certain error code
     *
     * @param   PEAR_Error|int              $value
     * @return  string
     * @access  private
     */
    function _errorMessage($value)
    {
        static $errorMessages;
        if (!isset($errorMessages)) {
            $errorMessages = array(
                SERVICES_WEATHER_ERROR_SERVICE_NOT_FOUND         => "Requested service could not be found.",
                SERVICES_WEATHER_ERROR_UNKNOWN_LOCATION          => "Unknown location provided.",
                SERVICES_WEATHER_ERROR_WRONG_SERVER_DATA         => "Server data wrong or not available.",
                SERVICES_WEATHER_ERROR_CACHE_INIT_FAILED         => "Cache init was not completed.",
                SERVICES_WEATHER_ERROR_DB_NOT_CONNECTED          => "MetarDB is not connected.",
                SERVICES_WEATHER_ERROR_HTTP_PROXY_INVALID        => "The given proxy is not valid, please use the notation http://[user[:pass]@]host[:port].",
                SERVICES_WEATHER_ERROR_SUNFUNCS_DATE_INVALID     => "The date you've provided for calculation of sunrise/sunset is not a timestamp.",
                SERVICES_WEATHER_ERROR_SUNFUNCS_RETFORM_INVALID  => "The return format you've provided for calculation of sunrise/sunset is not valid.",
                SERVICES_WEATHER_ERROR_METAR_SOURCE_INVALID      => "The METAR/TAF source you've provided has an invalid type or path.",
                SERVICES_WEATHER_ERROR_UNKNOWN_ERROR             => "An unknown error has occured.",
                SERVICES_WEATHER_ERROR_NO_LOCATION               => "No location provided.",
                SERVICES_WEATHER_ERROR_INVALID_LOCATION          => "Invalid location provided.",
                SERVICES_WEATHER_ERROR_INVALID_PARTNER_ID        => "Invalid partner id.",
                SERVICES_WEATHER_ERROR_INVALID_PRODUCT_CODE      => "Invalid product code.",
                SERVICES_WEATHER_ERROR_INVALID_LICENSE_KEY       => "Invalid license key."
            );
        }

        if (Services_Weather::isError($value)) {
            $value = $value->getCode();
        }

        return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[SERVICES_WEATHER_ERROR_UNKNOWN_ERROR];
    }
    // }}}

    // {{{ isError()
    /**
     * Checks for an error object, same as in PEAR
     *
     * @param   PEAR_Error|mixed            $value
     * @return  bool
     * @access  public
     */
    function isError($value)
    {
        return (is_object($value) && (strtolower(get_class($value)) == "pear_error" || is_subclass_of($value, "pear_error")));
    }
    // }}}

    // {{{ &raiseError()
    /**
     * Creates error, same as in PEAR with a customized flavor
     *
     * @param   int                         $code
     * @param   string                      $file
     * @param   int                         $line
     * @return  PEAR_Error
     * @access  private
     */
    function &raiseError($code = SERVICES_WEATHER_ERROR_UNKNOWN_ERROR, $file = "", $line = 0)
    {
        // This should improve the performance of the script, as PEAR is only included, when
        // really needed.
        include_once "PEAR.php";

        $message = "Services_Weather";
        if ($file != "" && $line > 0) {
            $message .= " (".basename($file).":".$line.")";
        }
        $message .= ": ".Services_Weather::_errorMessage($code);

        $error = PEAR::raiseError($message, $code, PEAR_ERROR_RETURN, E_USER_NOTICE, "Services_Weather_Error", null, false);
        return $error;
    }
    // }}}
}
// }}}
?>
