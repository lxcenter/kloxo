<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Composite driver.
 *
 * PHP Version 4
 *
 * Copyright (c) 1997-2008 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available at through the world-wide-web at
 * http://www.php.net/license/3_01.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category Date
 * @package  Date_Holidays
 * @author   Carsten Lucke <luckec@tool-garage.de>
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version  CVS: $Id: Composite.php 277166 2009-03-14 21:31:51Z kguest $
 * @link     http://pear.php.net/package/Date_Holidays
 */

/**
 * driver not found
 *
 * @access  public
 */
define('DATE_HOLIDAYS_DRIVER_NOT_FOUND', 100);

/**
 * Composite driver - you can use this one to combine two or more drivers
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Carsten Lucke <luckec@tool-garage.de>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version    CVS: $Id: Composite.php 277166 2009-03-14 21:31:51Z kguest $
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_Composite extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'Composite';

    /**
     * compound of drivers
     *
     * @access   private
     * @var      array
     */
    var $_drivers = array();

    /**
     * Driver-ids ordered by importance
     *
     * @access   private
     * @var      array
     */
    var $_driverIds = array();

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access   protected
     */
    function Date_Holidays_Driver_Composite()
    {
    }

    /**
     * Build the internal arrays that contain data about the calculated holidays
     *
     * @access   private
     * @return   boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
    }

    /**
     * Add a driver component
     *
     * @param object $driver Date_Holidays_Driver driver-object
     *
     * @access   public
     * @return   boolean true on success, false otherwise
     */
    function addDriver($driver)
    {
        if (! is_a($driver, 'Date_Holidays_Driver')) {
            return false;
        }

        $id                  = md5(serialize($driver));
        $this->_drivers[$id] = $driver;
        array_push($this->_driverIds, $id);

        $this->_internalNames = array_merge($driver->getInternalHolidayNames(),
                                            $this->_internalNames);
        return true;
    }

    /**
     * Remove a driver component
     *
     * @param object $driver Date_Holidays_Driver driver-object
     *
     * @access   public
     * @return   boolean true on success, otherwise a PEAR_Error object
     * @throws   object PEAR_Error   DATE_HOLIDAYS_DRIVER_NOT_FOUND
     */
    function removeDriver($driver)
    {
        if (! is_a($driver, 'Date_Holidays_Driver')) {
            return false;
        }

        $id = md5(serialize($driver));
        // unset driver object
        if (! isset($this->_drivers[$id])) {
            return Date_Holidays::raiseError(DATE_HOLIDAYS_DRIVER_NOT_FOUND,
                                             'Driver not found');
        }
        unset($this->_drivers[$id]);

        // unset driver's prio
        $index = array_search($id, $this->_driverIds);
        unset($this->_driverIds[$index]);

        // rebuild the internal-names array
        $this->_internalNames = array();
        foreach ($this->_driverIds as $id) {
            $this->_internalNames =
                    array_merge($this->_drivers[$id]->_internalNames,
                                $this->_internalNames);
        }

        return true;
    }

    /**
     * Returns the specified holiday
     *
     * Return format:
     * <pre>
     *   array(
     *       'title' =>  'Easter Sunday'
     *       'date'  =>  '2004-04-11'
     *   )
     * </pre>
     *
     * @param string $internalName internal name of the holiday
     * @param string $locale       locale setting that shall be used by this
     *                              method
     *
     * @access   public
     * @return   object Date_Holidays_Holiday holiday's information on
     *                                        success, otherwise a PEAR_Error
     *                                        object
     * @throws   object PEAR_Error       DATE_HOLIDAYS_INVALID_INTERNAL_NAME
     */
    function getHoliday($internalName, $locale = null)
    {
        foreach ($this->_driverIds as $id) {
            $holiday = $this->_drivers[$id]->getHoliday($internalName, $locale);
            if (Date_Holidays::isError($holiday)) {
                /**
                 * lets skip this error, perhaps another driver knows this
                 * internal-name
                 */
                continue;
            }
            return $holiday;
        }

        return Date_Holidays::raiseError(DATE_HOLIDAYS_INVALID_INTERNAL_NAME,
                                    'Invalid internal name: ' . $internalName);
    }

    /**
     * Returns date of a holiday
     *
     * @param string $internalName internal name for holiday
     *
     * @access   public
     * @return   object Date       date of the holiday as PEAR::Date
     *                              object on success, otherwise a PEAR_Error
     *                              object
     * @throws   object PEAR_Error DATE_HOLIDAYS_INVALID_INTERNAL_NAME
     */
    function getHolidayDate($internalName)
    {
        foreach ($this->_driverIds as $id) {
            $date = $this->_drivers[$id]->getHolidayDate($internalName);
            if (Date_Holidays::isError($date)) {
                /**
                 * lets skip this error, perhaps another driver knows this
                 * internal-name
                 */
                continue;
            }
            return $date;
        }

        return Date_Holidays::raiseError(DATE_HOLIDAYS_INVALID_INTERNAL_NAME,
                                    'Invalid internal name: ' . $internalName);
    }

    /**
     * Returns dates of all holidays or those accepted by the specified filter.
     *
     * @param Date_Holidays_Filter $filter filter-object
     *                                       (or an array !DEPRECATED!)
     *
     * @access   public
     * @return   array   array with holidays' dates on success, otherwise a
     *                       PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack   DATE_HOLIDAYS_INVALID_INTERNAL_NAME
     */
    function getHolidayDates($filter = null)
    {
        if (is_null($filter)) {
            $filter = new Date_Holidays_Filter_Blacklist(array());
        } elseif (is_array($filter)) {
            $filter = new Date_Holidays_Filter_Whitelist($filter);
        }

        $errorStack = &Date_Holidays::getErrorStack();
        $dates      = array();
        $notFound   = array();

        foreach ($this->_internalNames as $internalName) {
            // check if the filter permits further processing
            if (! $filter->accept($internalName)) {
                continue;
            }

            foreach ($this->_driverIds as $id) {
                $date = $this->_drivers[$id]->getHolidayDate($internalName);
                if (Date_Holidays::isError($date)) {
                    if ($date->getCode() == DATE_HOLIDAYS_DATE_UNAVAILABLE) {
                        /**
                         * this means a fatal error (would be the right place
                         * for sth. like an assert, as this should normally
                         * never happen)
                         */

                        $message = 'No date found for holiday with internal ' .
                                   'name: ' . $internalName;
                        $errorStack->push(DATE_HOLIDAYS_DATE_UNAVAILABLE,
                                          'error',
                                          array(),
                                          $message,
                                          false,
                                          debug_backtrace());
                        continue;
                    }

                    /**
                     * current driver doesn't have this internalName, trying
                     * next driver
                     */
                    array_push($notFound, $internalName);
                    continue;
                }
                /**
                 * internal name found in highest priorized driver, stepping
                 * to next internal name
                 * checks if internal name is existent in $notFound array and
                 * unsets this entry as it has been found now
                 */
                $notFound = array_unique($notFound);
                if (in_array($internalName, $notFound)) {
                    unset($notFound[array_search($internalName, $notFound)]);
                }
                $dates[$internalName] = $date;
                continue 2;
            }
        }

        if (! empty($notFound)) {
            foreach ($notFound as $internalName) {
                $errorStack->push(DATE_HOLIDAYS_INVALID_INTERNAL_NAME,
                                  'error',
                                  array(),
                                  'Invalid internal name: ' . $internalName,
                                  false,
                                  debug_backtrace());
            }
        }

        if ($errorStack->hasErrors() && ! empty($notFound)) {
            return $errorStack;
        }
        return $dates;
    }

    /**
     * Returns the title of the holiday, if any was found, matching the
     * specified date.
     *
     * Normally the method will return the title/data for the first holiday
     * matching the date.
     * If you want the mthod to continue searching holidays for the specified
     * date, set the 4th param to true
     * If multiple holidays match your date, the return value will be an array
     * of the titles/data.
     * <pre>
     * array(
     *   array(
     *       'title' => 'New Year',
     *       'date'  => Object of type Date
     *   ),
     *   array(
     *       'title' => 'Circumcision of Jesus',
     *       'date'  => Object of type Date
     *   )
     * )
     * </pre>
     *
     * @param mixed   $date     date (timestamp | string | PEAR::Date object)
     * @param string  $locale   locale setting that shall be used by this method
     * @param boolean $multiple true if multiple search is required.
     *
     * @access   public
     * @return   object  object of type Date_Holidays_Holiday on success
     *                      (numeric array of those on multiple search); if no
     *                      holiday was found, matching this date, null is returned
     * @uses     getHoliday()
     * @uses     getHolidayTitle()
     * @see      getHoliday()
     */
    function getHolidayForDate($date, $locale = null, $multiple = false)
    {
        $holidays = array();
        foreach ($this->_driverIds as $id) {
            $holiday = $this->_drivers[$id]->getHolidayForDate($date,
                                                               $locale,
                                                               $multiple);
            if (is_null($holiday)) {
                /**
                 * No holiday found for this date in the current driver, trying
                 * next one
                 */
                continue;
            }

            if (is_array($holiday)) {
                for ($i = 0; $i < count($holiday); ++$i) {
                    $holidays[] = $holiday[$i];
                }
            } else {
                $holidays[] = $holiday;
            }

            if (! $multiple) {
                return $holiday;
            }
        }

        if (empty($holidays)) {
            return null;
        }
        return $holidays;
    }

    /**
     * Returns all holidays that were found
     *
     * Return format:
     * <pre>
     *   array(
     *       'easter' =>  array(
     *           'title' =>  'Easter Sunday'
     *           'date'  =>  '2004-04-11'
     *       ),
     *       'eastermonday'  =>  array(
     *           'title' =>  'Easter Monday'
     *           'date'  =>  '2004-04-12'
     *       ),
     *       ...
     *   )
     * </pre>
     *
     * @param Date_Holidays_Filter $filter filter-object
     *                                       (or an array !DEPRECATED!)
     *
     * @access   public
     * @return   array   numeric array containing objects of Date_Holidays_Holiday
     *                       on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack   DATE_HOLIDAYS_INVALID_INTERNAL_NAME
     */
    function getHolidays($filter = null)
    {
        if (is_null($filter)) {
            $filter = new Date_Holidays_Filter_Blacklist(array());
        } elseif (is_array($filter)) {
            $filter = new Date_Holidays_Filter_Whitelist($filter);
        }

        $errorStack = &Date_Holidays::getErrorStack();
        $holidays   = array();
        $notFound   = array();

        foreach ($this->_internalNames as $internalName) {
            // check if the filter permits further processing
            if (! $filter->accept($internalName)) {
                continue;
            }

            foreach ($this->_driverIds as $id) {
                $holiday = $this->_drivers[$id]->getHoliday($internalName);
                if (Date_Holidays::isError($holiday)) {
                    /**
                     * current driver doesn't have this internalName, trying
                     * next driver
                     */
                    array_push($notFound, $internalName);
                    continue;
                }
                /**
                 * internal name found in highest priorized driver, stepping to
                 * next internal name checks if internal name is existent in
                 * $notFound array and unsets this entry as it has been found now
                 */
                $notFound = array_unique($notFound);
                if (in_array($internalName, $notFound)) {
                    unset($notFound[array_search($internalName, $notFound)]);
                }
                $holidays[$internalName] = $holiday;
                continue 2;
            }
        }

        if (! empty($notFound)) {
            foreach ($notFound as $internalName) {
                $errorStack->push(DATE_HOLIDAYS_INVALID_INTERNAL_NAME,
                                  'error',
                                  array(),
                                  'Invalid internal name: ' . $internalName,
                                  false,
                                  debug_backtrace());
            }
        }

        if ($errorStack->hasErrors() && ! empty($notFound)) {
            return $errorStack;
        }
        return $holidays;
    }

    /**
     * Returns localized title for a holiday
     *
     * @param string $internalName internal name for holiday
     * @param string $locale       locale setting that shall be used by this method
     *
     * @access   public
     * @return   string  title on success, otherwise a PEAR_Error object
     * @throws   object PEAR_Error   DATE_HOLIDAYS_INVALID_INTERNAL_NAME
     * @throws   object PEAR_Error   DATE_HOLIDAYS_TITLE_UNAVAILABLE
     */
    function getHolidayTitle($internalName, $locale = null)
    {
        foreach ($this->_driverIds as $id) {
            $title = $this->_drivers[$id]->getHolidayTitle($internalName, $locale);
            if (Date_Holidays::isError($title)) {
                /**
                 * lets skip this error, perhaps another driver knows this
                 * internal-name
                 */
                continue;
            }
            return $title;
        }

        return Date_Holidays::raiseError(DATE_HOLIDAYS_INVALID_INTERNAL_NAME,
                                         'Invalid internal name: ' . $internalName);
    }

    /**
     * Returns localized titles of all holidays or those specififed in
     * $restrict array
     *
     * @param Date_Holidays_Filter $filter filter-object
     *                                      (or an array !DEPRECATED!)
     * @param string               $locale locale setting that shall be used by
     *                                      this method
     *
     * @access   public
     * @return   array   array with localized holiday titles on success,
     *                      otherwise a PEAR_Error object
     * @throws   object PEAR_Error   DATE_HOLIDAYS_INVALID_INTERNAL_NAME
     */
    function getHolidayTitles($filter = null, $locale = null)
    {
        if (is_null($filter)) {
            $filter = new Date_Holidays_Filter_Blacklist(array());
        } elseif (is_array($filter)) {
            $filter = new Date_Holidays_Filter_Whitelist($filter);
        }

        $errorStack = &Date_Holidays::getErrorStack();
        $titles     = array();
        $notFound   = array();

        foreach ($this->_internalNames as $internalName) {
            // check if the filter permits further processing
            if (! $filter->accept($internalName)) {
                continue;
            }

            foreach ($this->_driverIds as $id) {
                $title = $this->_drivers[$id]->getHolidayTitle($internalName,
                                                               $locale);
                if (Date_Holidays::isError($title)) {
                    /**
                     * current driver doesn't have this internalName, trying next
                     * driver
                     */
                    array_push($notFound, $internalName);
                    continue;
                }
                /**
                 * internal name found in highest priorized driver, stepping to
                 * next internal name checks if internal name is existent in
                 * $notFound array and unsets this entry as it has been found now
                 */
                $notFound = array_unique($notFound);
                if (in_array($internalName, $notFound)) {
                    unset($notFound[array_search($internalName, $notFound)]);
                }
                $titles[$internalName] = $title;
                continue 2;
            }
        }

        if (! empty($notFound)) {
            foreach ($notFound as $internalName) {
                $errorStack->push(DATE_HOLIDAYS_INVALID_INTERNAL_NAME,
                                  'error',
                                  array(),
                                  'Invalid internal name: ' . $internalName,
                                  false,
                                  debug_backtrace());
            }
        }

        if ($errorStack->hasErrors() && ! empty($notFound)) {
            return $errorStack;
        }
        return $titles;
    }

    /**
     * Using this method doesn't affect anything. If you have been able to add
     * your driver to this compound, you should also be able to directly
     * execute this action.
     * This method is only available to keep abstraction working.
     *
     * @access   public
     * @return   void
     */
    function getYear()
    {
    }

    /**
     * This (re)sets the year of every driver-object in the compound.
     *
     * Note that this will cause every attached driver to recalculate the holidays!
     *
     * @param int $year year
     *
     * @access   public
     * @return   boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack
     */
    function setYear($year)
    {
        $errors = false;

        foreach ($this->_driverIds as $id) {
            if ($this->_drivers[$id]->setYear($year) != true) {
                $errors = true;
            }
        }

        if ($errors) {
            return Date_Holidays::getErrorStack();
        }
        return true;
    }

    /**
     * Determines whether a date represents a holiday or not.
     *
     * The method searches all added drivers for this date, to determine
     * whether it's a holiday.
     *
     * @param mixed                $date   date (can be a timestamp, string or
     *                                      PEAR::Date object)
     * @param Date_Holidays_Filter $filter filter-object (or an array !DEPRECATED!)
     *
     * @access   public
     * @return   boolean true if date represents a holiday, otherwise false
     */
    function isHoliday($date, $filter = null)
    {
        foreach ($this->_driverIds as $id) {
            if ($this->_drivers[$id]->isHoliday($date, $filter)) {
                return true;
            }
            continue;
        }

        return false;
    }

    /**
     * Using this method doesn't affect anything. If you have bben able to add
     * your driver to this compound you should also be able to directly execute
     * this action.
     * This method is only available to keep abstraction working.
     *
     * @param string $locale locale
     *
     * @access public
     * @return void
     */
    function setLocale($locale)
    {
    }
}
?>
