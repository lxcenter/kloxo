<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Driver for determining holidays in the Netherlands.
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
 * @author   Jos van der Woude <jos@veerkade.com>
 * @license  http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version  CVS: $Id: Netherlands.php,v 1.9 2009/03/15 20:17:00 kguest Exp $
 * @link     http://pear.php.net/package/Date_Holidays
 */

require_once 'Date/Holidays/Driver/Christian.php';

/**
 * Driver class that calculates Dutch holidays
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Jos van der Woude <jos@veerkade.com>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version    $Id: Netherlands.php,v 1.9 2009/03/15 20:17:00 kguest Exp $
 * @link       http://pear.php.net/package/Date_Holidays
 */

class Date_Holidays_Driver_Netherlands extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'Netherlands';

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access protected
     */
    function Date_Holidays_Driver_Netherlands()
    {
    }

    /**
     * Build the internal arrays that contain data about the calculated holidays
     *
     * @access protected
     * @return boolean true on success, otherwise a PEAR_ErrorStack object
     * @throws object PEAR_ErrorStack
     */
    function _buildHolidays()
    {
        /**
         * New Year's Day
         */
        $this->_addHoliday('newYearsDay',
                           $this->_year . '-01-01',
                           'New Year\'s Day');
        $this->_addTranslationForHoliday('newYearsDay', 'DU_NL', 'Nieuwjaarsdag');

        /**
         * Epiphanias
         */
        $this->_addHoliday('epiphany', $this->_year . '-01-06', 'Epiphany');
        $this->_addTranslationForHoliday('epiphany', 'DU_NL', 'Drie Koningen');

        /**
         * Valentine's Day
         */
        $this->_addHoliday('valentineDay',
                           $this->_year . '-02-14',
                           'Valentine\'s Day');
        $this->_addTranslationForHoliday('valentineDay', 'DU_NL', 'Valentijnsdag');

        /**
         * Queen's Day
         */
        $this->_addHoliday('queenDay', $this->_year . '-04-30', 'Queen\'s Day');
        $this->_addTranslationForHoliday('queenDay', 'DU_NL', 'Koninginnedag');

        /**
         * Commemoration Day Day
         */
        $this->_addHoliday('commemorationDay',
                           $this->_year . '-05-04',
                           'Commemoration Day');
        $this->_addTranslationForHoliday('commemorationDay',
                                         'DU_NL',
                                         'Dodenherdenking');

        /**
         * Liberation Day
         */
        $this->_addHoliday('liberationDay',
                           $this->_year . '-05-05',
                           'Liberation Day');
        $this->_addTranslationForHoliday('liberationDay', 'DU_NL', 'Bevrijdingsdag');


        /**
         * Easter Sunday
         */
        $easterDate = Date_Holidays_Driver_Christian::calcEaster($this->_year);
        $this->_addHoliday('easter', $easterDate, 'Easter Sunday');
        $this->_addTranslationForHoliday('easter', 'DU_NL', '1e Paasdag');

        /**
         * Easter Monday
         */
        $this->_addHoliday('easterMonday',
                           $easterDate->getNextDay(),
                           'Easter Monday');
        $this->_addTranslationForHoliday('easterMonday', 'DU_NL', '2e Paasdag');

        /**
         * Good Friday / Black Friday
         */
        $goodFridayDate = $this->_addDays($easterDate, 2);
        $this->_addHoliday('goodFriday', $goodFridayDate, 'Good Friday');
        $this->_addTranslationForHoliday('goodFriday', 'DU_NL', 'Goede Vrijdag');

        /**
         * Whitsun (determines Whit Monday, Ascension Day and Feast of
         * Corpus Christi)
         */
        $whitsunDate = $this->_addDays($easterDate, 49);
        $this->_addHoliday('whitsun', $whitsunDate, 'Whitsun');
        $this->_addTranslationForHoliday('whitsun', 'DU_NL', '1e Pinksterdag');

        /**
         * Whit Monday
         */
        $this->_addHoliday('whitMonday', $whitsunDate->getNextDay(), 'Whit Monday');
        $this->_addTranslationForHoliday('whitMonday', 'DU_NL', '2e Pinksterdag');

        /**
         * Ascension Day
         */
        $ascensionDayDate = $this->_addDays($whitsunDate, -10);
        $this->_addHoliday('ascensionDay', $ascensionDayDate, 'Ascension Day');
        $this->_addTranslationForHoliday('ascensionDay', 'DU_NL', 'Hemelvaartsdag');

        /**
         * Christmas day
         */
        $this->_addHoliday('christmasDay', $this->_year . '-12-25', 'Christmas Day');
        $this->_addTranslationForHoliday('christmasDay', 'DU_NL', '1e Kerstdag');

        /**
         * Second Christmas Day
         */
        $this->_addHoliday('secondChristmasDay',
                           $this->_year . '-12-26',
                           'Boxing Day');
        $this->_addTranslationForHoliday('secondChristmasDay',
                                         'DU_NL',
                                         '2e Kerstdag');

        /**
         * New Year's Eve
         */
        $this->_addHoliday('newYearsEve', $this->_year . '-12-31', "New Year's Eve");
        $this->_addTranslationForHoliday('newYearsEve', 'DU_NL', 'Oudjaarsdag');

        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }

        return true;
    }

    /**
     * Method that returns an array containing the ISO3166 codes that may possibly
     * identify a driver.
     *
     * @static
     * @access public
     * @return array possible ISO3166 codes
     */
    function getISO3166Codes()
    {
        return array('NL', 'NLD');
    }
}
?>
