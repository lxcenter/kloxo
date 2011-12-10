<?php
/**
 * Determines if a resource is available at a given timepoint.
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

class Horde_Kolab_Resource_Availability
{
    public function isFree($resource, $object, $dtstart, $dtend, $storage, $ignore, $freebusyfuture)
    {
        $freebusyfuture = $freebusyfuture * 60 * 60 * 24;
        //@ŧodo additional class
        list($vfb, $vfbstart, $vfbend, $evfbstart, $evfbend) = $this->getFreeBusyData($resource);
        if ($evfbend->getEpoch() < $dtend
            && $dtend < time() + $freebusyfuture
            && $evfbend->getEpoch() < time() + $freebusyfuture - 24 * 60 * 60) {
                Horde::logMessage('Triggering resource to generate updated freebusy information',
                                  __FILE__, __LINE__, PEAR_LOG_NOTICE);
                $storage->trigger();
                list($vfb, $vfbstart, $vfbend, $evfbstart, $evfbend) = $this->getFreeBusyData($resource);
        }
        if ($vfbstart && $dtend > $evfbend->getEpoch()) {
            Horde::logMessage('No freebusy information available',
                              __FILE__, __LINE__, PEAR_LOG_NOTICE);
            throw new Horde_Kolab_Resource_Exception_NotBookable();
        }

        // Check whether we are busy or not
        $busyperiods = $vfb->getBusyPeriods();
        Horde::logMessage(sprintf('Busyperiods: %s',
                                  print_r($busyperiods, true)),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);
        $extraparams = $vfb->getExtraParams();
        Horde::logMessage(sprintf('Extraparams: %s',
                                  print_r($extraparams, true)),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);
        $conflict = false;
        if (!empty($object['recurrence'])) {
            $recurrence = new Horde_Date_Recurrence($dtstart);
            $recurrence->fromHash($object['recurrence']);
            $duration = $dtend - $dtstart;
            $events = array();
            $next_start = $vfbstart;
            $next = $recurrence->nextActiveRecurrence($vfbstart);
            while ($next !== false && $next->compareDate($vfbend) <= 0) {
                $next_ts = $next->timestamp();
                $events[$next_ts] = $next_ts + $duration;
                $next = $recurrence->nextActiveRecurrence(array('year' => $next->year,
                                                                'month' => $next->month,
                                                                'mday' => $next->mday + 1,
                                                                'hour' => $next->hour,
                                                                'min' => $next->min,
                                                                'sec' => $next->sec));
            }
        } else {
            $events = array($dtstart => $dtend);
        }

        foreach ($events as $dtstart => $dtend) {
            Horde::logMessage(sprintf('Requested event from %s to %s',
                                      strftime('%a, %d %b %Y %H:%M:%S %z', $dtstart),
                                      strftime('%a, %d %b %Y %H:%M:%S %z', $dtend)
                              ),
                              __FILE__, __LINE__, PEAR_LOG_DEBUG);
            foreach ($busyperiods as $busyfrom => $busyto) {
                if (empty($busyfrom) && empty($busyto)) {
                    continue;
                }
                Horde::logMessage(sprintf('Busy period from %s to %s',
                                          strftime('%a, %d %b %Y %H:%M:%S %z', $busyfrom),
                                          strftime('%a, %d %b %Y %H:%M:%S %z', $busyto)
                                  ),
                                  __FILE__, __LINE__, PEAR_LOG_DEBUG);
                if ((isset($extraparams[$busyfrom]['X-UID'])
                     && in_array(base64_decode($extraparams[$busyfrom]['X-UID']), $ignore))
                    || (isset($extraparams[$busyfrom]['X-SID'])
                        && in_array(base64_decode($extraparams[$busyfrom]['X-SID']), $ignore))) {
                    // Ignore
                    continue;
                }
                if (($busyfrom >= $dtstart && $busyfrom < $dtend) || ($dtstart >= $busyfrom && $dtstart < $busyto)) {
                    Horde::logMessage('Request overlaps',
                                      __FILE__, __LINE__, PEAR_LOG_DEBUG);
                    return false;
                }
            }
        }
        return true;
    }

    public function getFreeBusyData($resource)
    {
        require_once 'Horde/Kolab/Resource/Freebusy.php';
        $fb  = Horde_Kolab_Resource_Freebusy::singleton();
        $vfb = $fb->get($resource);

        $vfbstart = $vfb->getAttributeDefault('DTSTART', 0);
        $evfbstart = new Horde_Kolab_Resource_Epoch($vfbstart);
        $vfbend = $vfb->getAttributeDefault('DTEND', 0);
        $evfbend = new Horde_Kolab_Resource_Epoch($vfbend);
        Horde::logMessage(sprintf('Free/busy info starts on <%s> %s and ends on <%s> %s',
                                  $vfbstart, $evfbstart->iCalDate2Kolab(), $vfbend, $evfbend->iCalDate2Kolab()),
                          __FILE__, __LINE__, PEAR_LOG_DEBUG);
        return array($vfb, $vfbstart, $vfbend, $evfbstart, $evfbend);
    }
}