<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Driver for Discordian holidays
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
 * @author   Stephan 'Da:Sourcerer' Hohmann <webmaster@dasourcerer.net>
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version  CVS: $Id: Discordian.php,v 1.6 2008/01/26 00:08:34 kguest Exp $
 * @link     http://pear.php.net/package/Date_Holidays
 */

/**
 * A driver-class calculating discordian Holidays. See the 'Principia Discordia'
 * or http://en.wikipedia.org/wiki/Discordian_calendar for details.
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Stephan 'Da:Sourcerer' Hohmann <webmaster@dasourcerer.net>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version    CVS: $Id: Discordian.php,v 1.6 2008/01/26 00:08:34 kguest Exp $
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_Discordian extends Date_Holidays_Driver
{
    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access   protected
     */
    function Date_Holidays_Driver_Discordian()
    {
    }

    /**
     * Build the internal arrays that contain data about the calculated holidays
     *
     * @access   protected
     * @return   boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws   object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
        /**
         * St. Tib's day. Occurs every leap year.
         */
        $this->_addHoliday('stTibsDay', $this->_year . '-02-29', 'St. Tib\'s Day');

        /**
         * Holidays assigned to apostles. Apostles' holidays are on
         * the fifth day of each season
         */
        $this->_addHoliday('mungday', $this->_year . '-01-05', 'Mungday');
        $this->_addHoliday('mojoday', $this->_year . '-03-19', 'Mojoday');
        $this->_addHoliday('syaday', $this->_year . '-05-31', 'Syaday');
        $this->_addHoliday('zaraday', $this->_year . '-08-12', 'Zaraday');
        $this->_addHoliday('maladay', $this->_year . '-10-24', 'Maladay');

        /**
         * Holidays assigned to seasons. Seasonal holidays are
         * assigned to the fiftieth day of the corresponding season
         */
        $this->_addHoliday('chaoflux', $this->_year . '-02-19', 'Chaoflux');
        $this->_addHoliday('discoflux', $this->_year . '-05-03', 'Discoflux');
        $this->_addHoliday('confuflux', $this->_year . '-07-15', 'Confuflux');
        $this->_addHoliday('bureflux', $this->_year . '-09-26', 'Bureflux');
        $this->_addHoliday('afflux', $this->_year . '-12-08', 'Afflux');

        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }
        return true;
    }
}
?>
