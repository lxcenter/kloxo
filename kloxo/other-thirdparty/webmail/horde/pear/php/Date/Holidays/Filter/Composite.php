<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Holiday.php
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
 * @version  CVS: $Id: Composite.php 251355 2008-01-26 00:14:33Z kguest $
 * @link     http://pear.php.net/package/Date_Holidays
 */

/**
 * Filter not found
 *
 * @access  public
 */
define('DATE_HOLIDAYS_FILTER_NOT_FOUND', 200);

/**
 * Class that acts like a single filter but actually is a compound of
 * an arbitrary number of filters.
 *
 * @category   Date
 * @package    Date_Holidays
 * @subpackage Filter
 * @author     Carsten Lucke <luckec@tool-garage.de>
 * @license    http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version    CVS: $Id: Composite.php 251355 2008-01-26 00:14:33Z kguest $
 * @link       http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Filter_Composite extends Date_Holidays_Filter
{
    /**
     * List of filters.
     *
     * @access   private
     * @var      array
     */
    var $_filters = array();

    /**
     * Constructor.
     */
    function __construct()
    {
        parent::__construct(array());
    }

    /**
     * Constructor.
     */
    function Date_Holidays_Filter_Composite()
    {
        $this->__construct();
    }

    /**
     * Lets the filter decide whether a holiday shall be processed or not.
     *
     * @param string $holiday a holidays' internal name
     *
     * @return   boolean true, if a holidays shall be processed, false otherwise
     */
    function accept($holiday)
    {
        foreach (array_keys($this->_filters) as $fId) {
            if ($this->_filters[$fId]->accept($holiday)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a filter to the compound.
     *
     * @param Date_Holidays_Filter $filter filter object
     *
     * @access   public
     * @return   boolean true on success, false otherwise
     */
    function addFilter($filter)
    {
        if (! is_a($filter, 'Date_Holidays_Filter')) {
            return false;
        }

        $id                  = md5(serialize($filter));
        $this->_filters[$id] = $filter;
        return true;
    }

    /**
     * Remove a filter from the compound.
     *
     * @param Date_Holidays_Filter $filter filter object
     *
     * @access   public
     * @return   boolean     true on success, otherwise a PEAR_Error object
     * @throws   PEAR_Error  DATE_HOLIDAYS_FILTER_NOT_FOUND
     */
    function removeFilter($filter)
    {
        if (! is_a($filter, 'Date_Holidays_Filter')) {
            return false;
        }

        $id = md5(serialize($filter));
        // unset filter object
        if (! isset($this->_filters[$id])) {
            return Date_Holidays::raiseError(DATE_HOLIDAYS_FILTER_NOT_FOUND,
                                             'Filter not found');
        }
        unset($this->_drivers[$id]);
        return true;
    }
}
?>
