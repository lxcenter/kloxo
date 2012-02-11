<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * This file contains only the Driver class for determining holidays in Australia.
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
 * @author   Sam Wilson <sam@archives.org.au>
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link     http://pear.php.net/package/Date_Holidays
 */

require_once 'Date/Calc.php';
require_once 'Date/Holidays/Driver/Christian.php';

/**
 * This is a Driver class that calculates holidays in Australia.  Individual states
 * generally have other holidays as well (ones that sometimes override those defined
 * herein) and so if one is available you should combine it with this one.
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Sam Wilson <sam@archives.org.au>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_Australia extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'Australia';

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access   protected
     */
    function Date_Holidays_Driver_Australia()
    {
    }

    /**
     * Build the internal arrays that contain data about holidays.
     *
     * @access   protected
     * @return   boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
        parent::_buildHolidays();

        /*
         * New Year's Day
         */
        $newYearsDay = new Date($this->_year . '-01-01');
        if ($newYearsDay->getDayOfWeek() == 0) { // 0 = Sunday
            $newYearsDay = $this->_year . '-01-02';
        } elseif ($newYearsDay->getDayOfWeek() == 6) { // 6 = Saturday
            $newYearsDay = $this->_year . '-01-03';
        }
        $this->_addHoliday('newYearsDay', $newYearsDay, 'New Year\'s Day');

        /*
         * Australia Day
         */
        $australiaDay = new Date($this->_year . '-01-26');
        if ($australiaDay->getDayOfWeek() == 0) { // 0 = Sunday
            $australiaDay = $this->_year . '-01-27';
        } elseif ($australiaDay->getDayOfWeek() == 6) { // 6 = Saturday
            $australiaDay = $this->_year . '-01-28';
        }
        $this->_addHoliday('australiaDay', $australiaDay, 'Australia Day');

        /*
         * Easter
         */
        $easter = Date_Holidays_Driver_Christian::calcEaster($this->_year);
        $goodFridayDate = new Date($easter);
        $goodFridayDate = $this->_addDays($easter, -2);
        $this->_addHoliday('goodFriday', $goodFridayDate, 'Good Friday');
        $this->_addHoliday('easterMonday', $easter->getNextDay(), 'Easter Monday');

        /*
         * Anzac Day
         */
        $anzacDay = new Date($this->_year . '-04-25');
        $this->_addHoliday('anzacDay', $anzacDay, 'Anzac Day');
        if ($anzacDay->getDayOfWeek() == 0) { // 0 = Sunday
            $anzacDayHol = $this->_year . '-04-26';
            $this->_addHoliday('anzacDayHoliday', $anzacDayHol, 'Anzac Day Holiday');
        } elseif ($anzacDay->getDayOfWeek() == 6) { // 6 = Saturday
            $anzacDayHol = $this->_year . '-04-27';
            $this->_addHoliday('anzacDayHoliday', $anzacDayHol, 'Anzac Day Holiday');
        }

        /*
         * The Queen's Birthday.
         * See http://en.wikipedia.org/wiki/Queen%27s_Official_Birthday#Australia
         */
        $queensBirthday = Date_Calc::nWeekdayOfMonth(1, 1, 6, $this->_year);
        $this->_addHoliday('queensBirthday', $queensBirthday, "Queen's Birthday");

        /*
         * Christmas and Boxing days
         */
        $christmasDay = new Date($this->_year.'-12-25');
        $boxingDay = new Date($this->_year.'-12-26');
        $this->_addHoliday('christmasDay', $christmasDay, 'Christmas Day');
        $this->_addHoliday('boxingDay', $boxingDay, 'Boxing Day');
        if ($christmasDay->getDayName() == 'Sunday') {
            $this->_addHoliday(
                'boxingDayHoliday', $this->_year.'-12-27', 'Boxing Day Holiday'
            );
        } elseif ($christmasDay->getDayName() == 'Friday') {
            $this->_addHoliday(
                'boxingDayHoliday', $this->_year.'-12-28', 'Boxing Day Holiday'
            );
        } elseif ($christmasDay->getDayName() == 'Saturday') {
            $this->_addHoliday(
                'christmasDayHoliday', $this->_year.'-12-27', 'Christmas Day Holiday'
            );
            $this->_addHoliday(
                'boxingDayHoliday', $this->_year.'-12-28', 'Boxing Day Holiday'
            );
        }

        /*
         * Check for errors, and return.
         */
        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }
        return true;

    }

    /**
     * Method that returns an array containing the ISO3166 codes ('au' and 'aus')
     * that identify this driver.
     *
     * @static
     * @access public
     * @return array possible ISO3166 codes
     */
    function getISO3166Codes()
    {
        return array('au', 'aus');
    }

}
