<?php
/**
 * Caching for the Kolab free/busy data.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache.php,v 1.17.2.14 2011/04/13 17:38:18 wrobel Exp $
 *
 * @package Kolab_FreeBusy
 */

/** Load the cache components that we always need */
require_once 'Horde/Kolab/FreeBusy/Cache/File.php';
require_once 'Horde/Kolab/FreeBusy/Cache/File/Pvcal.php';
require_once 'Horde/Kolab/FreeBusy/Cache/Db.php';
require_once 'Horde/Kolab/FreeBusy/Cache/Db/Base.php';
require_once 'Horde/Kolab/FreeBusy/Cache/Db/Acl.php';
require_once 'Horde/Kolab/FreeBusy/Cache/Db/Xacl.php';

/**
 * The Horde_Kolab_FreeBusy_Cache:: class provides functionality to store
 * prepared free/busy data for quick retrieval.
 *
 * $Horde: framework/Kolab_FreeBusy/lib/Horde/Kolab/FreeBusy/Cache.php,v 1.17.2.14 2011/04/13 17:38:18 wrobel Exp $
 *
 * Copyright 2004-2008 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @author  Gunnar Wrobel <p@rdus.de>
 * @author  Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @package Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Cache
{
    /**
     * The handler for the cache structure.
     *
     * @var Horde_Kolab_FreeBusy_Cache_Structure
     */
    private $_structure;

    /**
     * The owner of the data being accessed.
     *
     * @var Horde_Kolab_FreeBusy_Owner
     */
    private $_owner;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Cache_Structure $structure The cache structure.
     * @param Horde_Kolab_FreeBusy_Owner           $owner     The cache owner.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Cache_Structure $structure,
        Horde_Kolab_FreeBusy_Owner $owner
    ) {
        $this->_structure = $structure;
        $this->_owner = $owner;
    }

    /**
     * Delete the cache information for a calendar.
     *
     * @param Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder The folder to delete.
     *
     * @return NULL
     */
    public function deletePartial(
        Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder
    ) {
        $partial = $this->_structure
            ->getPartialByOwnerAndFolder($folder, $this->_owner);
        $this->_structure
            ->getExtendedAcl()
            ->delete($partial);
        $this->_structure
            ->getAcl()
            ->delete($partial);

        $partial->delete();
    }

    /**
     * Update the cache information for a resource.
     *
     * @param Horde_Kolab_FreeBusy_User                   $user     The user accessing the cache.
     * @param Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder   The folder being accessed.
     * @param Horde_Kolab_FreeBusy_Resource               $resource The resource.
     * @param mixed                                       $data     The data to store.
     *
     * @return NULL
     */
    public function storePartial(
        Horde_Kolab_FreeBusy_User $user,
        Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder,
        Horde_Kolab_FreeBusy_Resource $resource,
        $data
    ) {
        $partial = $this->_structure
            ->getPartialByOwnerAndFolder($folder, $this->_owner);
        $partial->store($data);
        $this->_structure
            ->getExtendedAcl()
            ->store(
                $partial,
                $resource,
                $this->_structure
                ->getAcl()
                ->store($user, $partial, $resource)
            );
    }

    /**
     * Load partial free/busy data.
     *
     * @param Horde_Kolab_FreeBusy_User                   $user     The user accessing the cache.
     * @param Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder   The folder being accessed.
     * @param boolean                                     $extended Should the data hold the extended
     *                                                              free/busy information?
     *
     * @return Horde_iCalendar The free/busy data of a single calendar.
     */
    public function loadPartial(
        Horde_Kolab_FreeBusy_User $user,
        Horde_Kolab_FreeBusy_Params_Freebusy_Folder $folder,
        $extended
    ) {
        $partial = $this->_structure
            ->getPartialByOwnerAndFolder($folder, $this->_owner);
        if ($extended
            && $this->_structure
            ->getExtendedAcl()
            ->allow($user, $partial)) {
            return $partial->load();
        } else {
            return $partial->loadSimple();
        }
    }

    /**
     * Load the complete free/busy data of a user.
     *
     * @param Horde_Kolab_FreeBusy_User $user The user accessing the cache.
     * @param boolean               $extended Should the data hold the extended
     *                                        free/busy information?
     *
     * @return Horde_iCalendar The free/busy data for a user.
     */
    function loadCombined(
        Horde_Kolab_FreeBusy_User $user,
        $extended
    ) {
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Combined.php';
        require_once 'Horde/Kolab/FreeBusy/Export/Freebusy/Combined/Decorator/Cache.php';
        $combined = new Horde_Kolab_FreeBusy_Export_Freebusy_Combined_Decorator_Cache(
            new Horde_Kolab_FreeBusy_Export_Freebusy_Combined(
                $this->_owner,
                $this->_structure->getCombined(
                    $this->_owner,
                    $user
                )
            ),
            $this->_owner,
            $user,
            $this->_structure->getCacheDir()
        );

        return $combined->generate($extended);
    }

    /**
     * Delete the cache information for the current owner.
     *
     * @return NULL
     */
    public function deleteOwner()
    {
        $partial = $this->_structure
            ->deleteOwner($this->_owner);
    }

}