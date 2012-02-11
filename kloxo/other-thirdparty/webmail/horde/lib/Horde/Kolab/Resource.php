<?php
/**
 * Resource management for the Kolab server.
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

/** Load the iCal handling */
require_once 'Horde/iCalendar.php';

/** Load MIME handlers */
require_once 'Horde/MIME.php';
require_once 'Horde/MIME/Message.php';
require_once 'Horde/MIME/Headers.php';
require_once 'Horde/MIME/Part.php';
require_once 'Horde/MIME/Structure.php';

/** Load Kolab_Resource elements */
require_once 'Horde/Kolab/Resource/Availability.php';
require_once 'Horde/Kolab/Resource/Data.php';
require_once 'Horde/Kolab/Resource/Epoch.php';
require_once 'Horde/Kolab/Resource/Exception.php';
require_once 'Horde/Kolab/Resource/Exception/NotBookable.php';
require_once 'Horde/Kolab/Resource/Itip/Exception.php';
require_once 'Horde/Kolab/Resource/Itip/Response.php';
require_once 'Horde/Kolab/Resource/Itip/Response/Type.php';
require_once 'Horde/Kolab/Resource/Itip/Response/Type/Base.php';
require_once 'Horde/Kolab/Resource/Itip/Response/Type/Accept.php';
require_once 'Horde/Kolab/Resource/Itip/Response/Type/Decline.php';
require_once 'Horde/Kolab/Resource/Itip/Response/Type/Tentative.php';
require_once 'Horde/Kolab/Resource/Itip/Resource.php';
require_once 'Horde/Kolab/Resource/Itip/Resource/Base.php';
require_once 'Horde/Kolab/Resource/Itip/Event.php';
require_once 'Horde/Kolab/Resource/Itip/Event/Vevent.php';
require_once 'Horde/Kolab/Resource/Lock.php';
require_once 'Horde/Kolab/Resource/Reply.php';
require_once 'Horde/Kolab/Resource/Request.php';
require_once 'Horde/Kolab/Resource/Storage.php';
require_once 'Horde/Kolab/Resource/Freebusy.php';

require_once 'Horde/String.php';
String::setDefaultCharset('utf-8');

// What actions we can take when receiving an event request
define('RM_ACT_ALWAYS_ACCEPT',              'ACT_ALWAYS_ACCEPT');
define('RM_ACT_REJECT_IF_CONFLICTS',        'ACT_REJECT_IF_CONFLICTS');
define('RM_ACT_MANUAL_IF_CONFLICTS',        'ACT_MANUAL_IF_CONFLICTS');
define('RM_ACT_MANUAL',                     'ACT_MANUAL');
define('RM_ACT_ALWAYS_REJECT',              'ACT_ALWAYS_REJECT');

// What possible ITIP notification we can send
define('RM_ITIP_DECLINE',                   1);
define('RM_ITIP_ACCEPT',                    2);
define('RM_ITIP_TENTATIVE',                 3);

/**
 * Provides Kolab resource handling
 *
 * Copyright 2004-2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @package Kolab_Filter
 * @author  Steffen Hansen <steffen@klaralvdalens-datakonsult.se>
 * @author  Gunnar Wrobel <wrobel@pardus.de>
 */
class Kolab_Resource
{
    function handleMessage($fqhostname, $sender, $resource, $tmpfname)
    {
        global $conf;

        $data = new Horde_Kolab_Resource_Data();
        $rdata = $data->fetch($sender, $resource);
        if (is_a($rdata, 'PEAR_Error')) {
            return $rdata;
        } else if ($rdata === false) {
            /* No data, probably not a local user */
            return true;
        } else if ($rdata['homeserver'] && $rdata['homeserver'] != $fqhostname) {
            /* Not the users homeserver, ignore */
            return true;
        }

        $cn = $rdata['cn'];
        $id = $rdata['id'];
        if (isset($rdata['action'])) {
            $action = $rdata['action'];
        } else {
            // Manual is the only safe default!
            $action = RM_ACT_MANUAL;
        }
        Horde::logMessage(sprintf('Action for %s is %s',
                                  $sender, $action),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);

        // Get out as early as possible if manual
        if ($action == RM_ACT_MANUAL) {
            Horde::logMessage(sprintf('Passing through message to %s', $id),
                              __FILE__, __LINE__, PEAR_LOG_INFO);
            return true;
        }

        /* Get the iCalendar data (i.e. the iTip request) */
        $request = new Horde_Kolab_Resource_Request();
        $iCalendar = $request->getICalendar($tmpfname);
        if ($iCalendar === false) {
            // No iCal in mail
            Horde::logMessage(sprintf('Could not parse iCalendar data, passing through to %s', $id),
                              __FILE__, __LINE__, PEAR_LOG_INFO);
            return true;
        }
        // Get the event details out of the iTip request
        $itip = &$iCalendar->findComponent('VEVENT');
        if ($itip === false) {
            Horde::logMessage(sprintf('No VEVENT found in iCalendar data, passing through to %s', $id),
                              __FILE__, __LINE__, PEAR_LOG_INFO);
            return true;
        }
        $itip = new Horde_Kolab_Resource_Itip_Event_Vevent($itip);

        // What is the request's method? i.e. should we create a new event/cancel an
        // existing event, etc.
        $method = strtoupper(
            $iCalendar->getAttributeDefault(
                'METHOD',
                $itip->getMethod()
            )
        );

        // What resource are we managing?
        Horde::logMessage(sprintf('Processing %s method for %s', $method, $id),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);

        // This is assumed to be constant across event creation/modification/deletipn
        $uid = $itip->getUid();
        Horde::logMessage(sprintf('Event has UID %s', $uid),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);

        // Who is the organiser?
        $organiser = $itip->getOrganizer();
        Horde::logMessage(sprintf('Request made by %s', $organiser),
                      __FILE__, __LINE__, PEAR_LOG_DEBUG);

        // What is the events summary?
        $summary = $itip->getSummary();

        $estart = new Horde_Kolab_Resource_Epoch($itip->getStart());
        $dtstart = $estart->getEpoch();
        $eend = new Horde_Kolab_Resource_Epoch($itip->getEnd());
        $dtend = $eend->getEpoch();

        Horde::logMessage(sprintf('Event starts on <%s> %s and ends on <%s> %s.',
                                  $dtstart, $estart->iCalDate2Kolab(), $dtend, $eend->iCalDate2Kolab()),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);

        if ($action == RM_ACT_ALWAYS_REJECT) {
            if ($method == 'REQUEST') {
                Horde::logMessage(sprintf('Rejecting %s method', $method),
                                  __FILE__, __LINE__, PEAR_LOG_INFO);
                return $this->sendITipReply($cn, $resource, $itip, RM_ITIP_DECLINE,
                                            $organiser, $uid, $is_update);
            } else {
                Horde::logMessage(sprintf('Passing through %s method for ACT_ALWAYS_REJECT policy', $method),
                                  __FILE__, __LINE__, PEAR_LOG_INFO);
                return true;
            }
        }

        $is_update  = false;
        $ignore     = array();

        $storage = new Horde_Kolab_Resource_Storage($id);
        $storage->getFolder();

        if ($storage->failed() && $action == RM_ACT_MANUAL_IF_CONFLICTS) {
            Horde::logMessage(sprintf('Failed accessing IMAP calendar: %s',
                                      $storage->failed()),
                              __FILE__, __LINE__, PEAR_LOG_ERR);
            return true;
        }

        switch ($method) {
        case 'REQUEST':
            if ($action == RM_ACT_MANUAL) {
                Horde::logMessage(sprintf('Passing through %s method', $method),
                                  __FILE__, __LINE__, PEAR_LOG_INFO);
                break;
            }

            if (!$storage->objectUidExists($uid)) {
                $old_uid = null;
            } else {
                $old_uid = $uid;
                $ignore[] = $uid;
                $is_update = true;
            }

            /** Generate the Kolab object */
            $object = $itip->getKolabObject();

            Horde::logMessage(sprintf('Assembled event object: %s',
                                      print_r($object, true)),
                              __FILE__, __LINE__, PEAR_LOG_DEBUG);

            // Don't even bother checking free/busy info if RM_ACT_ALWAYS_ACCEPT
            // is specified
            if ($action != RM_ACT_ALWAYS_ACCEPT) {

                $availability = new Horde_Kolab_Resource_Availability();
                try {
                    if (isset($rdata['fbfuture']) && $rdata['fbfuture'] !== null) {
                        $fbfuture = $rdata['fbfuture'];
                    } else {
                        $fbfuture = $conf['kolab']['freebusy']['future_days'];
                    }
                    if (!$availability->isFree($resource, $object, $dtstart, $dtend, $storage, $ignore, $fbfuture)) {
                        if ($action == RM_ACT_MANUAL_IF_CONFLICTS) {
                            Horde::logMessage('Conflict detected; Passing mail through',
                                              __FILE__, __LINE__, PEAR_LOG_INFO);
                            return true;
                        } else if ($action == RM_ACT_REJECT_IF_CONFLICTS) {
                            Horde::logMessage('Conflict detected; rejecting',
                                              __FILE__, __LINE__, PEAR_LOG_INFO);
                            return $this->sendITipReply($cn, $id, $itip, RM_ITIP_DECLINE,
                                                        $organiser, $uid, $is_update);
                        }
                    }
                } catch (Horde_Kolab_Resource_Exception_NotBookable $e) {
                    if ($action == RM_ACT_MANUAL_IF_CONFLICTS) {
                        Horde::logMessage('Invitation outside bookable period; Passing mail through',
                                          __FILE__, __LINE__, PEAR_LOG_INFO);
                        return true;
                    }
                    return $this->sendITipReply(
                        $cn, $resource, $itip, RM_ITIP_DECLINE,
                        $organiser, $uid, $is_update, _("outside bookable period")
                    );
                /* } catch (Exception $e) { */
                /*     return PEAR::raiseError($e->getMessage(), */
                /*                             OUT_LOG | EX_UNAVAILABLE); */
                }
            }

            if ($storage->failed()) {
                Horde::logMessage('Could not access users calendar; rejecting',
                                  __FILE__, __LINE__, PEAR_LOG_INFO);
                return $this->sendITipReply($cn, $id, $itip, RM_ITIP_DECLINE,
                                            $organiser, $uid, $is_update);
            }

            // At this point there was either no conflict or RM_ACT_ALWAYS_ACCEPT
            // was specified; either way we add the new event & send an 'ACCEPT'
            // iTip reply

            Horde::logMessage(sprintf('Adding event %s', $uid),
                              __FILE__, __LINE__, PEAR_LOG_INFO);

            $this->locking = new Horde_Kolab_Resource_Lock();
            if ($this->locking->isLocked($resource)) {
                return $this->sendITipReply($cn, $id, $itip, RM_ITIP_DECLINE,
                                            $organiser, $uid, $is_update);
            }

            $itip->setAccepted($resource);

            $result = $storage->save($itip->getKolabObject(), $old_uid);
            if (is_a($result, 'PEAR_Error')) {
                $result->code = OUT_LOG | EX_UNAVAILABLE;
                return $result;
            }

            return $this->sendITipReply(
                $cn, $resource, $itip, RM_ITIP_ACCEPT,
                $organiser, $uid, $is_update
            );

        case 'CANCEL':
            Horde::logMessage(sprintf('Removing event %s', $uid),
                              __FILE__, __LINE__, PEAR_LOG_INFO);

            if ($storage->failed()) {
                $body = sprintf(_("Unable to access %s's calendar:"), $resource) . "\n\n" . $summary;
                $subject = sprintf(_("Error processing \"%s\""), $summary);
            } else if (!$storage->objectUidExists($uid)) {
                Horde::logMessage(sprintf('Canceled event %s is not present in %s\'s calendar',
                                          $uid, $resource),
                                  __FILE__, __LINE__, PEAR_LOG_WARNING);
                $body = sprintf(_("The following event that was canceled is not present in %s's calendar:"), $resource) . "\n\n" . $summary;
                $subject = sprintf(_("Error processing \"%s\""), $summary);
            } else {
                /**
                 * Delete the messages from IMAP
                 * Delete any old events that we updated
                 */
                Horde::logMessage(sprintf('Deleting %s because of cancel',
                                          $uid),
                                  __FILE__, __LINE__, PEAR_LOG_DEBUG);

                $result = $storage->delete($uid);
                if (is_a($result, 'PEAR_Error')) {
                    Horde::logMessage(sprintf('Deleting %s failed with %s',
                                              $uid, $result->getMessage()),
                                      __FILE__, __LINE__, PEAR_LOG_DEBUG);
                }

                $body = _("The following event has been successfully removed:") . "\n\n" . $summary;
                $subject = sprintf(_("%s has been cancelled"), $summary);
            }

            Horde::logMessage(sprintf('Sending confirmation of cancelation to %s', $organiser),
                              __FILE__, __LINE__, PEAR_LOG_WARNING);

            $body = new MIME_Part('text/plain', String::wrap($body, 76, "\n", 'utf-8'), 'utf-8');
            $mime = &MIME_Message::convertMimePart($body);
            $mime->setTransferEncoding('quoted-printable');
            $mime->transferEncodeContents();

            // Build the reply headers.
            $msg_headers = new MIME_Headers();
            $msg_headers->addHeader('Date', date('r'));
            $msg_headers->addHeader('From', $resource);
            $msg_headers->addHeader('To', $organiser);
            $msg_headers->addHeader('Subject', $subject);
            $msg_headers->addMIMEHeaders($mime);

            $reply = new Horde_Kolab_Resource_Reply(
                $resource, $organiser, $msg_headers, $mime
            );
            Horde::logMessage('Successfully prepared cancellation iTip reply',
                              __FILE__, __LINE__, PEAR_LOG_DEBUG);
            return $reply;

        default:
            // We either don't currently handle these iTip methods, or they do not
            // apply to what we're trying to accomplish here
            Horde::logMessage(sprintf('Ignoring %s method and passing message through to %s',
                                      $method, $resource),
                              __FILE__, __LINE__, PEAR_LOG_INFO);
            return true;
        }
    }

    /**
     * Helper function to clean up after handling an invitation
     *
     * @return NULL
     */
    function cleanup()
    {
        if (!empty($this->locking)) {
            $this->locking->cleanup();
        }
    }

    /**
     * Send an automated reply.
     *
     * @param string  $cn                     Common name to be used in the iTip
     *                                        response.
     * @param string  $resource               Resource we send the reply for.
     * @param string  $Horde_iCalendar_vevent The iTip information.
     * @param int     $type                   Type of response.
     * @param string  $organiser              The event organiser.
     * @param string  $uid                    The UID of the event.
     * @param boolean $is_update              Is this an event update?
     */
    function sendITipReply(
        $cn, $resource, $itip, $type, $organiser, $uid, $is_update, $comment = null
    ) {
        Horde::logMessage(sprintf('sendITipReply(%s, %s, %s, %s)',
                                  $cn, $resource, get_class($itip), $type),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);

        $itip_reply = new Horde_Kolab_Resource_Itip_Response(
            $itip,
            new Horde_Kolab_Resource_Itip_Resource_Base(
                $resource, $cn
            )
        );
        switch($type) {
        case RM_ITIP_DECLINE:
            $type = new Horde_Kolab_Resource_Itip_Response_Type_Decline(
                $resource, $itip
            );
            break;
        case RM_ITIP_ACCEPT:
            $type = new Horde_Kolab_Resource_Itip_Response_Type_Accept(
                $resource, $itip
            );
            break;
        case RM_ITIP_TENTATIVE:
            $type = new Horde_Kolab_Resource_Itip_Response_Type_Tentative(
                $resource, $itip
            );
            break;
        }
        list($headers, $message) = $itip_reply->getMessage(
            $type,
            '-//kolab.org//NONSGML Kolab Server 2//EN',
            $comment
        );

        Horde::logMessage(sprintf('Sending %s iTip reply to %s',
                                  $type->getStatus(),
                                  $organiser),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);

        $reply = new Horde_Kolab_Resource_Reply(
            $resource, $organiser, $headers, $message
        );
        Horde::logMessage('Successfully prepared iTip reply',
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);
        return $reply;
    }
}
