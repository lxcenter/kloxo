<?php
/**
 * Handles the cached partial free/busy lists.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * Handles the cached partial free/busy lists.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Cache_Combined_Freebusy
{
    /**
     * The owner of the data.
     *
     * @var Horde_Kolab_FreeBusy_Owner
     */
    private $_owner;

    /**
     * The user accessing the data.
     *
     * @var Horde_Kolab_FreeBusy_User
     */
    private $_user;

    /**
     * The cache structure.
     *
     * @var Horde_Kolab_FreeBusy_Cache_Structure
     */
    private $_structure;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Owner      $owner       The owner of the data.
     * @param Horde_Kolab_FreeBusy_User       $user        The user accessing the cache.
     * @param Horde_Kolab_FreeBusy_Cache_Structure $structure Cache structure     */
    public function __construct(
        Horde_Kolab_FreeBusy_Owner $owner,
        Horde_Kolab_FreeBusy_User $user,
        Horde_Kolab_FreeBusy_Cache_Structure $structure
    ) {
        $this->_owner = $owner;
        $this->_user  = $user;
        $this->_structure = $structure;
    }

    public function combineResult($vFb, $extended)
    {
        $mtimes = array();
        $extended_access = $this->getExtendedAccess($extended);
        foreach ($this->getPartials() as $partial) {
            $this->merge(
                $vFb, $partial, in_array($partial->getId(), $extended_access)
            );
            /* Store last modification time */
            $mtimes[$partial->getId()] = $partial->getMtime();
        }
        return $mtimes;
    }

    /**
     * Return the IDs of the partials that allow extended access to the data.
     *
     * @param boolean $extended Should extended partials be considered or not?
     *
     * @return array The IDs.
     */
    public function getExtendedAccess($extended)
    {
        $extended_access = array();
        if ($extended) {
            foreach ($this->getPartials() as $partial) {
                if ($this->_structure->getExtendedAcl()->allow($this->_user, $partial)) {
                    $extended_access[] = $partial->getId();
                }
            }
        }
        return $extended_access;
    }

    /**
     * Return the IDs of the partials that are relevant to this combined result.
     *
     * @return array The IDs.
     */
    public function getPartialIds()
    {
        return $this->_structure->getAcl()->getPartialIds($this->_owner);
    }

    /**
     * Return the partial representations that are relevant to this combined
     * result.
     *
     * @return array The partials.
     */
    private function getPartials()
    {
        $partials = array();
        foreach ($this->getPartialIds() as $id) {
            $partials[] = $this->_structure->getPartialById($id);
        }
        return $partials;
    }

    private function merge($vfb, $partial, $extended)
    {
        try {
            //@todo: ensure that merging only selects the overlapping time period
            $vfb->merge($this->getFreeBusy($partial, $extended));
        } catch (Horde_Kolab_FreeBusy_Exception $e) {
        }
        return $vfb;
    }

    public function getFreeBusy($partial, $extended)
    {
        if ($extended) {
            $vcal = $partial->load();
        } else {
            $vcal = $partial->loadSimple();
        }
        $freebusy = $vcal->findComponent('vfreebusy');
        if (is_a($freebusy, 'PEAR_Error')) {
            throw new Horde_Kolab_FreeBusy_Exception(
                $freebusy->getMessage()
            );
        }
        return $freebusy;
    }
}