<?php
if (!function_exists('_prefs_hook_add_source')) {
    function _prefs_hook_add_source($username = null)
    {
        if (!$username) {
            return;
        }

        return $GLOBALS['registry']->call('contacts/getDefaultShare');
    }
}
