<?php
/**
 * Handles the data of a resource.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Resource
 * @author   Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Resource
 */

class Horde_Kolab_Resource_Data
{

    /**
     * Constructor.
     *
     * @param string $sender   The sender address
     * @param string $resource The resource
     */
    public function fetch($sender, $resource)
    {
        require_once 'Horde/Kolab/Server.php';
        $db = Horde_Kolab_Server::singleton();
        if ($db instanceOf PEAR_Error) {
            $db->code = OUT_LOG | EX_SOFTWARE;
            return $db;
        }

        $dn = $db->uidForMail($resource, KOLAB_SERVER_RESULT_MANY);
        if ($dn instanceOf PEAR_Error) {
            $dn->code = OUT_LOG | EX_NOUSER;
            return $dn;
        }
        if (is_array($dn)) {
            if (count($dn) > 1) {
                Horde::logMessage(sprintf("%s objects returned for %s",
                                          $count($dn), $resource),
                                  __FILE__, __LINE__, PEAR_LOG_WARNING);
                return false;
            } else {
                $dn = $dn[0];
            }
        }
        $user = $db->fetch($dn, KOLAB_OBJECT_USER);

        $cn      = $user->get(KOLAB_ATTR_CN);
        $id      = $user->get(KOLAB_ATTR_MAIL);
        $hs      = $user->get(KOLAB_ATTR_HOMESERVER);
        if (is_a($hs, 'PEAR_Error')) {
            return $hs;
        }
        $hs      = strtolower($hs);
        $actions = $user->get(KOLAB_ATTR_IPOLICY);
        if (is_a($actions, 'PEAR_Error')) {
            $actions->code = OUT_LOG | EX_UNAVAILABLE;
            return $actions;
        }
        if ($actions === false) {
            $actions = array(RM_ACT_MANUAL);
        }
        $fbfuture = $user->get(KOLAB_ATTR_FBFUTURE);
        if (is_a($fbfuture, 'PEAR_Error')) {
            $fbfuture = null;
        }

        $policies = array();
        $defaultpolicy = false;
        foreach ($actions as $action) {
            if (preg_match('/(.*):(.*)/', $action, $regs)) {
                $policies[strtolower($regs[1])] = $regs[2];
            } else {
                $defaultpolicy = $action;
            }
        }
        // Find sender's policy
        if (array_key_exists($sender, $policies)) {
            // We have an exact match, stop processing
            $action = $policies[$sender];
        } else {
            $action = false;
            $dn = $db->uidForMailOrAlias($sender);
            if (is_a($dn, 'PEAR_Error')) {
                $dn->code = OUT_LOG | EX_NOUSER;
                return $dn;
            }
            if ($dn) {
                // Sender is local, check for groups
                foreach ($policies as $gid => $policy) {
                    if ($db->memberOfGroupAddress($dn, $gid)) {
                        // User is member of group
                        if (!$action) {
                            $action = $policy;
                        } else {
                            $action = min($action, $policy);
                        }
                    }
                }
            }
            if (!$action && $defaultpolicy) {
                $action = $defaultpolicy;
            }
        }
        return array('cn' => $cn, 'id' => $id,
                     'homeserver' => $hs, 'action' => $action,
                     'fbfuture' => $fbfuture);
    }

}