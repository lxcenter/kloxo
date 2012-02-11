<?php
/**
 * $Horde: framework/Kolab_Filter/lib/Horde/Kolab/Filter/Transport/lda.php,v 1.4.2.2 2010/07/15 21:35:39 wrobel Exp $
 *
 * @package Kolab_Filter
 */

/**
 * Provides DovecotLDA delivery.
 *
 * $Horde: framework/Kolab_Filter/lib/Horde/Kolab/Filter/Transport/lda.php,v 1.4.2.2 2010/07/15 21:35:39 wrobel Exp $
 *
 * Copyright 2008 Intevation GmbH
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html.
 *
 * @author  Sascha Wilde <wilde@intevation.de>
 * @package Kolab_Filter
 */
class Horde_Kolab_Filter_Transport_lda extends Horde_Kolab_Filter_Transport 
{
    /**
     * Create the transport handler.
     *
     * @return DovecotLDA The LDA handler.
     */
    function &_createTransport() 
    {
        require_once dirname(__FILE__) . '/DovecotLDA.php';

        $transport = new Dovecot_LDA();

        return $transport;
    }
}
