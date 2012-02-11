<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * This file contains only the Driver class for determining holidays in Western
 * Australia.
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

/**
 * This Driver class calculates holidays in Western Australia.  It should be used in
 * conjunction with the Australia driver.
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Sam Wilson <sam@archives.org.au>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_AustraliaWA extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'AustraliaWA';

    /**
     * Constructor
     *
     * @access   protected
     */
    function Date_Holidays_Driver_AustraliaWA()
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
         * Labour Day.
         */
        $labourDay = Date_Calc::nWeekdayOfMonth(1, 1, 3, $this->_year);
        $this->_addHoliday('labourDay', $labourDay, "Labour Day");

        /*
         * Foundation Day (Queen's Birthday in other states).
         * See http://en.wikipedia.org/wiki/Queen%27s_Official_Birthday#Australia
         */
        $foundationDay = Date_Calc::nWeekdayOfMonth(1, 1, 6, $this->_year);
        $this->_addHoliday('foundationDay', $foundationDay, "Foundation Day");

        /*
         * The Queen's Birthday.  There is no firm rule to determine this date before
         * it is proclaimed, but it is usually the last Monday of September or the
         * first Monday of October, to fit in with school terms and the Perth Royal
         * Show.  Here we assume that whichever of the two is closest to the start of
         * Octber will be the holiday; this has been verified to be correct up to and
         * including 2013, but dates beyond then should be verified once they've been
         * proclaimed (and something added here to account for historical and future
         * irregularities).
         */
        $y = $this->_year;
        $lastMonSept = new Date(Date_Calc::nWeekdayOfMonth('last', 1, 9, $y));
        $firstMonOct = new Date(Date_Calc::nWeekdayOfMonth(1, 1, 10, $y));
        $daysToEnd = 30 - $lastMonSept->getDay();
        $daysToStart = $firstMonOct->getDay();
        if ($daysToEnd < $daysToStart) {
            $queensBirthday = $lastMonSept;
        } else {
            $queensBirthday = $firstMonOct;
        }
        $this->_addHoliday('queensBirthday', $queensBirthday, "Queen's Birthday");

    } // _buildHolidays()

} // Date_Holidays_Driver_AustraliaWA
