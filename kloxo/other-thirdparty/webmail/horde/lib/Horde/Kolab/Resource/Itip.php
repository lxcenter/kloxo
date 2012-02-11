<?php
/**
 * Handles iTip invitation requests/responses.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  Kolab_Resource
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Resource
 */

/**
 * Handles iTip invitation requests/responses.
 *
 * Copyright 2010 Klarälvdalens Datakonsult AB
 *
 * See the enclosed file COPYING for license information (LGPL). If you did not
 * receive this file, see
 * http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @category Kolab
 * @package  Kolab_Resource
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Resource
 */
class Horde_Kolab_Resource_Itip
{
    /**
     * The iTip response.
     *
     * @var Horde_Kolab_Resource_Itip_Response
     */
    private $_response;

    /**
     * Constructor.
     *
     * @param Horde_Kolab_Resource_Itip_Response $response The iTip response.
     */
    public function __construct(
        Horde_Kolab_Resource_Itip_Response $response
    ) {
        $this->_response = $response;
    }

    /**
     * Return the response as an iCalendar vEvent object.
     *
     * @param Horde_Kolab_Resource_Itip_Response_Type $type The response type.
     *
     * @return Horde_iCalendar_vevent The response object.
     */
    public function getVeventResponse(
        Horde_Kolab_Resource_Itip_Response_Type $type
    ) {
        return $this->_response->getVevent(
            $type, false
        );
    }

    /**
     * Return the response as an iCalendar object.
     *
     * @param Horde_Kolab_Resource_Itip_Response_Type $type       The response
     *                                                            type.
     * @param string                                  $product_id The ID that
     *                                                            should be set
     *                                                            as the iCalendar
     *                                                            product id.
     *
     * @return Horde_iCalendar The response object.
     */
    public function getIcalendarResponse(
        Horde_Kolab_Resource_Itip_Response_Type $type,
        $product_id
    ) {
        return $this->_response->getIcalendar(
            $type, $product_id
        );
    }

    /**
     * Return the response as a MIME message.
     *
     * @param Horde_Kolab_Resource_Itip_Response_Type $type       The response
     *                                                            type.
     * @param string                                  $product_id The ID that
     *                                                            should be set
     *                                                            as the iCalendar
     *                                                            product id.
     * @param string $subject_comment An optional comment on the subject line.
     *
     * @return array A list of two object: The mime headers and the mime
     *               message.
     */
    public function getMessageResponse(
        Horde_Kolab_Resource_Itip_Response_Type $type,
        $product_id,
        $subject_comment = null
    ) {
        return $this->_response->getMessage(
            $type, $product_id, $subject_comment
        );
    }

    /**
     * Factory for generating a response object for an iCalendar invitation.
     *
     * @param Horde_iCalendar_vevent             $vevent   The iCalendar request.
     * @param Horde_Kolab_Resource_Itip_Resource $resource The invited resource.
     *
     * @return Horde_Kolab_Resource_Itip_Response The prepared response.
     */
    static public function prepareResponse(
        Horde_iCalendar_vevent $vevent,
        Horde_Kolab_Resource_Itip_Resource $resource
    ) {
        return new Horde_Kolab_Resource_Itip_Response(
            new Horde_Kolab_Resource_Itip_Event_Vevent(
                $vevent
            ),
            $resource
        );
    }

    /**
     * Factory for generating an iTip handler for an iCalendar invitation.
     *
     * @param Horde_iCalendar_vevent             $vevent   The iCalendar request.
     * @param Horde_Kolab_Resource_Itip_Resource $resource The invited resource.
     *
     * @return Horde_Kolab_Resource_Itip The iTip handler.
     */
    static public function factory(
        Horde_iCalendar_vevent $vevent,
        Horde_Kolab_Resource_Itip_Resource $resource
    ) {
        return new Horde_Kolab_Resource_Itip(
            self::prepareResponse($vevent, $resource)
        );
    }
}