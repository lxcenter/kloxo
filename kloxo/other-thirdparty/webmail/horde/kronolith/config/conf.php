<?php
/* CONFIG START. DO NOT CHANGE ANYTHING IN OR AFTER THIS LINE. */
// $Horde: kronolith/config/conf.xml,v 1.14.10.5 2007/12/20 14:12:23 jan Exp $
$conf['calendar']['params']['table'] = 'kronolith_events';
$conf['calendar']['params']['driverconfig'] = 'horde';
$conf['calendar']['driver'] = 'sql';
$conf['storage']['params']['table'] = 'kronolith_storage';
$conf['storage']['params']['driverconfig'] = 'horde';
$conf['storage']['driver'] = 'sql';
$conf['metadata']['keywords'] = false;
$conf['autoshare']['shareperms'] = 'none';
$conf['holidays']['enable'] = true;
$conf['menu']['print'] = true;
$conf['menu']['import_export'] = true;
$conf['menu']['apps'] = array();
/* CONFIG END. DO NOT CHANGE ANYTHING IN OR BEFORE THIS LINE. */
