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
