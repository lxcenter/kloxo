<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * UNO
 *
 * PHP Version 4
 *
 * Authors:
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
 * @version  CVS: $Id: UNO.php,v 1.9 2009/03/15 20:17:00 kguest Exp $
 * @link     http://pear.php.net/package/Date_Holidays
 */

/**
 * Driver-class that calculates UNO (United Nations Organization) holidays
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Driver
 * @author     Carsten Lucke <luckec@tool-garage.de>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version    CVS: $Id: UNO.php,v 1.9 2009/03/15 20:17:00 kguest Exp $
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_UNO extends Date_Holidays_Driver
{
    /**
     * this driver's name
     *
     * @access   protected
     * @var      string
     */
    var $_driverName = 'UNO';

    /**
     * Constructor
     *
     * Use the Date_Holidays::factory() method to construct an object of a
     * certain driver
     *
     * @access   protected
     */
    function Date_Holidays_Driver_UNO()
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
         * World's leprosy day
         */
        $this->_addHoliday('leprosyDay',
                           $this->_year . '-01-28',
                           'World\'s leprosy day');

        /**
         * International day of the native tongue
         */
        $this->_addHoliday('nativeTongueDay',
                           $this->_year . '-02-21',
                           'International Day of the native tongue');

        /**
         * International Women's Day
         */
        $this->_addHoliday('womensDay',
                           $this->_year . '-03-08',
                           'International Women\'s Day');

        /**
         * International World Consumers' Day
         */
        $this->_addHoliday('worldConsumersDay',
                           $this->_year . '-03-15',
                           'International World Consumers\' Day');

        /**
         * International day of the forest
         */
        $this->_addHoliday('intForestDay',
                           $this->_year . '-03-21',
                           'International day of the forest');

        /**
         * International day of beating racism
         */
        $this->_addHoliday('intDayBeatingRacism',
                           $this->_year . '-03-21',
                           'International day of beating racism');

        /**
         * Day of poesy
         */
        $this->_addHoliday('dayOfPoesy',
                           $this->_year . '-03-21',
                           'Day of poesy');

        /**
         * Day of water
         */
        $this->_addHoliday('dayOfWater',
                           $this->_year . '-03-22',
                           'Day of water');

        /**
         * World's meteorology day
         */
        $this->_addHoliday('meteorologyDay',
                           $this->_year . '-03-23',
                           'World\'s meteorology day');

        /**
         * World's tuberculosis day
         */
        $this->_addHoliday('tuberculosisDay',
                           $this->_year . '-03-24',
                           'World\'s tuberculosis day');

        /**
         * World's Health Day
         */
        $this->_addHoliday('worldsHealthDay',
                           $this->_year . '-04-07',
                           'World\'s Health Day');

        /**
         * Book and Copyright's Day
         */
        $this->_addHoliday('bookAndCopyrightDay',
                           $this->_year . '-04-23',
                           'Book and Copyright\'s Day');

        /**
         * Tree's Day
         */
        $this->_addHoliday('treesDay',
                           $this->_year . '-04-25',
                           'Tree\'s Day');

        /**
         * World's day of intellectual property
         */
        $this->_addHoliday('intellectualPropertyDay',
                           $this->_year . '-04-26',
                           'World\'s day of intellectual property');

        /**
         * International day of work
         */
        $this->_addHoliday('intDayOfWork',
                           $this->_year . '-05-01',
                           'International day of work');

        /**
         * International day for freedom of the press
         */
        $this->_addHoliday('freedomOfPressDay',
                           $this->_year . '-05-03',
                           'International day for freedom of the press');

        /**
         * Day of the sun
         */
        $this->_addHoliday('dayOfTheSun',
                           $this->_year . '-05-03',
                           'Day of the sun');

        /**
         * International Family's Day
         */
        $this->_addHoliday('intFamilyDay',
                           $this->_year . '-05-15',
                           'International Family\'s Day');

        /**
         * World's Telecommunications Day
         */
        $this->_addHoliday('telecommunicationsDay',
                           $this->_year . '-05-17',
                           'World\'s Telecommunications Day');

        /**
         * International day of cultural development
         */
        $this->_addHoliday('culturalDevelopmentDay',
                           $this->_year . '-05-21',
                           'International day of cultural development');

        /**
         * International day of biological diversity
         */
        if ($this->_year >= 2001) {
            $this->_addHoliday('biologicalDiversityDay',
                               $this->_year . '-05-22',
                               'International day of biological diversity');
        } else {
            $this->_addHoliday('biologicalDiversityDay',
                               $this->_year . '-12-29',
                               'International day of biological diversity');
        }

        /**
         * African Liberation Day
         */
        $this->_addHoliday('africanLiberationDay',
                           $this->_year . '-05-25',
                           'African Liberation Day');

        /**
         * International UN Peace Squads' Day
         */
        $this->_addHoliday('unPeaceSquadsDay',
                           $this->_year . '-05-29',
                           'International UN Peace Squads\' Day');

        /**
         * World's Nonsmokers' Day
         */
        $this->_addHoliday('nonsmokersDay',
                           $this->_year . '-05-31',
                           'World\'s Nonsmokers\' Day');

        /**
         * World's Agriculturalists' Day
         */
        $this->_addHoliday('farmersDay',
                           $this->_year . '-06-01',
                           'World\'s Agriculturalists\' Day');

        /**
         * World's Environment Day
         */
        $this->_addHoliday('environmentDay',
                           $this->_year . '-06-05',
                           'World\'s Environment Day');

        /**
         * African Children's Day
         */
        $this->_addHoliday('africanChildrenDay',
                           $this->_year . '-06-16',
                           'African Children\'s Day');

        /**
         * World's Desert's Day
         */
        $this->_addHoliday('desertDay',
                           $this->_year . '-06-17',
                           'World\'s Desert\'s Day');

        /**
         * African Fugitives' Day
         */
        $this->_addHoliday('africanFugitiveDay',
                           $this->_year . '-06-20',
                           'African Fugitives\' Day');

        /**
         * International day against drugs
         */
        $this->_addHoliday('antiDrugsDay',
                           $this->_year . '-06-26',
                           'International day against drugs');

        /**
         * International Cooperative Societies' Day
         */
        $coopDayDate = new Date($this->_year . '-07-01');
        while ($coopDayDate->getDayOfWeek() != 6) {
            $coopDayDate = $coopDayDate->getNextDay();
        }
        $this->_addHoliday('intCoopDay',
                           $coopDayDate,
                           'International Cooperative Societies\' Day');

        /**
         * World's Population Day
         */
        $this->_addHoliday('populationDay',
                           $this->_year . '-07-11',
                           'World\'s Population Day');

        /**
         * International day of indigenous people
         */
        $this->_addHoliday('indigenousPeopleDay',
                           $this->_year . '-08-09',
                           'International day of indigenous people');

        /**
         * International Youth' Day
         */
        $this->_addHoliday('intYouthDay',
                           $this->_year . '-08-12',
                           'International Youth\' Day');

        /**
         * International day of slave trade's abolishment
         */
        $this->_addHoliday('slaveTradeDay',
                           $this->_year . '-08-23',
                           'International day of slave trade\'s abolishment');

        /**
         * World's Alphabetization Day
         */
        $this->_addHoliday('alphabetizationDay',
                           $this->_year . '-09-08',
                           'World\'s Alphabetization Day');

        /**
         * Ozone Layer's Protection Day
         */
        $this->_addHoliday('ozoneLayerProtectionDay',
                           $this->_year . '-09-16',
                           'Ozone Layer\'s Protection Day');

        /**
         * International day of peace
         */
        $peaceDayDate = new Date($this->_year . '-09-01');
        while ($peaceDayDate->getDayOfWeek() != 2) {
            $peaceDayDate = $peaceDayDate->getNextDay();
        }
        $peaceDayDate = $this->_addDays($peaceDayDate, 14);

        $this->_addHoliday('intPeaceDay',
                           $peaceDayDate,
                           'International day of peace');

        /**
         * World's day of tourism
         */
        $this->_addHoliday('tourismDay',
                           $this->_year . '-09-27',
                           'World\'s day of tourism');

        /**
         * International fugitives' day
         */
        $this->_addHoliday('intFugitiveDay',
                           $this->_year . '-09-28',
                           'International fugitives\' day');

        /**
         * International aged people's day
         */
        $this->_addHoliday('agedPeopleDay',
                           $this->_year . '-10-01',
                           'International aged people\'s day');

        /**
         * World's day for prevention of cruelty to animals
         */
        $this->_addHoliday('animalsDay',
                           $this->_year . '-10-04',
                           'World\'s day for prevention of cruelty to animals');

        /**
         * Beginning of the International Outer Space Week
         */
        $this->_addHoliday('outerSpaceWeek',
                           $this->_year . '-10-04',
                           'Beginning of the International Outer Space Week');

        /**
         * World's Habitat Day
         */
        $habitatDayDate = new Date($this->_year . '-10-01');
        while ($habitatDayDate->getDayOfWeek() != 1) {
            $habitatDayDate = $habitatDayDate->getNextDay();
        }
        $this->_addHoliday('habitatDay', $coopDayDate, 'World\'s Habitat Day');

        /**
         * International Teachers' Day
         */
        $this->_addHoliday('teachersDay',
                           $this->_year . '-10-05',
                           'International Teachers\' Day');

        /**
         * World Post Association's Day
         */
        $this->_addHoliday('postAssociationDay',
                           $this->_year . '-10-09',
                           'World Post Association\'s Day');

        /**
         * World's Sanity Day
         */
        $this->_addHoliday('sanityDay',
                           $this->_year . '-10-10',
                           'World\'s Sanity Day');

        /**
         * World's Nourishment Day
         */
        $this->_addHoliday('nourishmentDay',
                           $this->_year . '-10-16',
                           'World\'s Nourishment Day');

        /**
         * International day for removal of poverty
         */
        $this->_addHoliday('povertyRemovalDay',
                           $this->_year . '-10-17',
                           'International day for removal of poverty');

        /**
         * United Nations' Day
         */
        $this->_addHoliday('unitedNationsDay',
                           $this->_year . '-10-24',
                           'United Nations\' Day');

        /**
         * World's day of information about evolvement
         */
        $this->_addHoliday('evolvementInfoDay',
                           $this->_year . '-10-24',
                           'World\'s day of information about evolvement');

        /**
         * Beginning of the Disarmament Week
         */
        $this->_addHoliday('evolvementInfoDay',
                           $this->_year . '-10-24',
                           'Beginning of the Disarmament Week');

        /**
         * International day against environmental exploitation in wartime
         */
        $this->_addHoliday('environmentalExploitationDay',
                           $this->_year . '-11-06',
            'International day against environmental exploitation in wartime');

        /**
         * International day of tolerance
         */
        $this->_addHoliday('toleranceDay',
                           $this->_year . '-11-16',
                           'International day of tolerance');

        /**
         * African Industrialization Day
         */
        $this->_addHoliday('africanIndustrializationDay',
                           $this->_year . '-11-20',
                           'African Industrialization Day');

        /**
         * World's Children's Day
         */
        $this->_addHoliday('worldChildrenDay',
                           $this->_year . '-11-20',
                           'World\'s Children\'s Day');

        /**
         * World's Television Day
         */
        $this->_addHoliday('televisionDay',
                           $this->_year . '-11-21',
                           'World\'s Television Day');

        /**
         * International day for removal of violence against women
         */
        $this->_addHoliday('noViolenceAgainstWomen',
                          $this->_year . '-11-25',
                          'International day for removal of violence against women');

        /**
         * International day of solidarity with Palestinian people
         */
        $this->_addHoliday('palestinianSolidarity',
                          $this->_year . '-11-29',
                          'International day of solidarity with Palestinian people');

        /**
         * World AIDS Day
         */
        $this->_addHoliday('worldAidsDay',
                           $this->_year . '-12-01',
                           'World AIDS Day');

        /**
         * International day for abolishment of slavery
         */
        $this->_addHoliday('againstSlaveryDay',
                           $this->_year . '-12-01',
                           'International day for abolishment of slavery');

        /**
         * International day for disabled people
         */
        $this->_addHoliday('disabledPeopleDay',
                           $this->_year . '-12-03',
                           'International day for disabled people');

        /**
         * International evolvement helpers' day
         */
        $this->_addHoliday('evolvementHelperDay',
                           $this->_year . '-12-05',
                           'International evolvement helpers\' day');

        /**
         * International day of civil aeronautics
         */
        $this->_addHoliday('civilAeronauticsDay',
                           $this->_year . '-12-07',
                           'International day of civil aeronautics');

        /**
         * International day of human rights
         */
        $this->_addHoliday('humanRightsDay',
                           $this->_year . '-12-10',
                           'International day of human rights');

        /**
         * UNICEF Day
         */
        $this->_addHoliday('unicefDay',
                           $this->_year . '-12-11',
                           'UNICEF Day');

        /**
         * International migrants' day
         */
        $this->_addHoliday('migrantsDay',
                           $this->_year . '-12-18',
                           'International migrants\' day');

        if (Date_Holidays::errorsOccurred()) {
            return Date_Holidays::getErrorStack();
        }
        return true;
    }
}
?>
