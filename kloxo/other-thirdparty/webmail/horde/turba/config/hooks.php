<?php
/**
 * Turba Hooks configuration file.
 *
 * THE HOOKS PROVIDED IN THIS FILE ARE EXAMPLES ONLY.  DO NOT ENABLE THEM
 * BLINDLY IF YOU DO NOT KNOW WHAT YOU ARE DOING.  YOU HAVE TO CUSTOMIZE THEM
 * TO MATCH YOUR SPECIFIC NEEDS AND SYSTEM ENVIRONMENT.
 *
 * For more information please see the horde/config/hooks.php.dist file.
 *
 * $Horde: turba/config/hooks.php.dist,v 1.1.2.3 2009/12/30 14:02:07 jan Exp $
 */

// Example default_dir hook. This function sets the user's personal address
// book as the default address book. While this is not necessary for most
// features, some might rely on a default to be set.

if (!function_exists('_prefs_hook_default_dir')) {
    function _prefs_hook_default_dir($username = null)
    {
        if (!$username || empty($_SESSION['turba']['has_share'])) {
            return;
        }

        require TURBA_BASE . '/config/sources.php';
        $shares = Turba::listShares(true);
        if (is_a($shares, 'PEAR_Error')) {
            return;
        }

        foreach ($shares as $uid => $share) {
            $params = @unserialize($share->get('params'));
            if (empty($params['source'])) {
                continue;
            }
            $driver = &Turba_Driver::factory($params['source'], $cfgSources[$params['source']]);
            if (is_a($driver, 'PEAR_Error')) {
                continue;
            }
            if ($driver->checkDefaultShare($share, $cfgSources[$params['source']])) {
                return $uid;
            }
        }
    }
}
