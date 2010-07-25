<?php

include_once 'htmllib/lib/displayinclude.php';
clearsession_main();

function clearsession_main()
{
    global $gbl, $sgbl, $login, $ghtml;
    initProgramlib('admin');
    $login->__session_timeout = true;

    $ulist = $login->getList('utmp');
    if(!empty($ulist))
    {
        foreach($ulist as $u) {
            if ($u->timeout < time()) {
                $u->setUpdateSubaction('');
                $u->logouttime = time();
                $u->logoutreason = 'Session Expired';
                $u->write();
            }
        }
    }

    $slist = $login->getList("ssessionlist");
    if(!empty($slist))
    {
        foreach($slist as $s) {
            if ($s->timeout < time()) {
                $s->dbaction = 'delete';
                $s->write();
            }
        }
    }
}
sleep(600);