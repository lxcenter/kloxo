<?php
/**
 * The free/busy Kolab backend for Horde framework 3.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */

/**
 * The free/busy Kolab backend for Horde framework 3.
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_FreeBusy
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_FreeBusy
 */
class Horde_Kolab_FreeBusy_Resource_Event_Fwthree
extends Horde_Kolab_FreeBusy_Resource_Event_Kolab
{
    /**
     * IMAP handler
     *
     * @var Horde_Kolab_FreeBusy_Imap
     */
    private $_imap;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_FreeBusy_Imap $imap The IMAP connection handler.
     * @param Horde_Kolab_FreeBusy_Owner_Freebusy $owner  The resource owner.
     */
    public function __construct(
        Horde_Kolab_FreeBusy_Imap $imap,
        Horde_Kolab_FreeBusy_Owner_Event $owner
    ) {
        $this->_imap  = $imap;
        $this->_owner = $owner;
    }

    /**
     * Return the name of the resource.
     *
     * @return string The name for the resource.
     */
    public function getName()
    {
        return $this->_imap->getFolder()->getName();
    }

    /**
     * Return the folder represented by this resource.
     *
     * @return Horde_Kolab_Storage_Folder The folder.
     */
    protected function getFolder()
    {
        return $this->_imap->getFolder();
    }

    /**
     * Return the data represented by this resource.
     *
     * @return Horde_Kolab_Storage_Data The data.
     */
    protected function getData()
    {
        return $this->getFolder()->getData();
    }

    /**
     * Return for whom this resource exports relevant data.
     *
     * @return string The user type the exported data of this resource is
     *                relevant for.
     *
     * @throws Horde_Kolab_FreeBusy_Exception If retrieving the relevance
     *                                        information failed.
     */
    public function getRelevance()
    {
        $relevance = $this->_imap->getRelevance();
        if (is_a($relevance, 'PEAR_Error')) {
            throw new Horde_Kolab_FreeBusy_Exception($relevance->getMessage());
        }
        return $relevance;
    }

    /**
     * Fetch the resource ACL.
     *
     * @return array ACL for this resource.
     *
     * @throws Horde_Kolab_FreeBusy_Exception If retrieving the ACL information
     *                                        failed.
     */
    public function getAcl()
    {
        $perm = $this->getFolder()->getPermission();
        $acl = &$perm->acl;
        if (empty($acl)) {
            $acl = array();
        }
        return $acl;
    }

    /**
     * Fetch the access controls on specific attributes of this
     * resource.
     *
     * @return array Attribute ACL for this resource.
     *
     * @throws Horde_Kolab_FreeBusy_Exception If retrieving the attribute ACL
     *                                        information failed.
     */
    public function getAttributeAcl()
    {
        $acl = $this->_imap->getExtendedACL();
        if (is_a($acl, 'PEAR_Error')) {
            throw new Horde_Kolab_FreeBusy_Exception($acl->getMessage());
        }
        return $acl;
    }

    /**
     * Lists all events in the given time range.
     *
     * @param Horde_Date $startDate Start of range date object.
     * @param Horde_Date $endDate   End of range data object.
     *
     * @return array Events in the given time range.
     *
     * @throws Horde_Kolab_FreeBusy_Exception If retrieving the events failed.
     */
    public function listEvents(Horde_Date $startDate, Horde_Date $endDate)
    {
        require_once 'Horde/Kolab/FreeBusy/Object/Event.php';

        $data = $this->getData();
        if (is_a($data, 'PEAR_Error')) {
            throw new Horde_Kolab_FreeBusy_Exception($data->getMessage());
        }
        $objects = $data->getObjects();

        $startts = $startDate->timestamp();
        $endts = $endDate->timestamp();

        $result = array();

        /**
         * PERFORMANCE START
         *
         * The following section has been performance optimized using
         * xdebug and kcachegrind.
         *
         * If there are many events it takes a lot of time and memory to create
         * new objects from the array and use those for time comparison. So the
         * code tries to use the original data array as long as possible and
         * only converts it to an object if really required (e.g. the event
         * actually lies in the time span or it recurs in which case the
         * calculations are more complex).
         */
        foreach($objects as $object) {
            /* check if event period intersects with given period */
            if (!(($object['start-date'] > $endts) ||
                  ($object['end-date'] < $startts))) {
                $result[] = new Horde_Kolab_FreeBusy_Object_Event($object);
                continue;
            }

            /* do recurrence expansion if not keeping anyway */
            if (isset($object['recurrence'])) {
                $event = new Horde_Kolab_FreeBusy_Object_Event($object);
                if ($event->recursIn($startDate, $endDate)) {
                    $result[] = $event;
                }
            }
        }
        /** PERFORMANCE END */

        return $result;
    }
}
