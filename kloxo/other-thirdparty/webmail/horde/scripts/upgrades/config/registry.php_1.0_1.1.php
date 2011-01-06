unset($this->applications['occam'], $this->applications['troll'],
      $this->applications['troll-menu'], $this->applications['hylax'],
      $this->applications['thor'], $this->applications['rakim'],
      $this->applications['hermes-watch'], $this->applications['giapeto']);

$this->applications['dimp'] = array(
    'fileroot' => dirname(__FILE__) . '/../dimp',
    'webroot' => $this->applications['horde']['webroot'] . '/dimp',
    'name' => _("Dynamic Mail"),
    'status' => 'notoolbar',
);

$this->applications['imp']['provides'] = array('mail', 'contacts/favouriteRecipients');

$this->applications['turba']['provides'] = array('contacts', 'clients/getClientSource', 'clients/clientFields', 'clients/getClient', 'clients/getClients', 'clients/addClient', 'clients/updateClient', 'clients/deleteClient', 'clients/searchClients');

$this->applications['nag-alarms'] = array(
    'status' => 'block',
    'app' => 'nag',
    'blockname' => 'tree_alarms',
    'menu_parent' => 'nag',
);

$this->applications['nag-menu'] = array(
    'status' => 'block',
    'app' => 'nag',
    'blockname' => 'tree_menu',
    'menu_parent' => 'nag',
);

$this->applications['mnemo-menu'] = array(
    'status' => 'block',
    'app' => 'mnemo',
    'blockname' => 'tree_menu',
    'menu_parent' => 'mnemo',
);

$this->applications['chora-menu'] = array(
    'status' => 'block',
    'app' => 'chora',
    'blockname' => 'tree_menu',
    'menu_parent' => 'chora',
);

$this->applications['whups-menu'] = array(
    'status' => 'block',
    'app' => 'whups',
    'blockname' => 'tree_menu',
    'menu_parent' => 'whups',
);

$this->applications['hermes-stopwatch'] = array(
    'status' => 'block',
    'app' => 'hermes',
    'blockname' => 'tree_stopwatch',
    'menu_parent' => 'hermes',
);

$this->applications['hermes-menu'] = array(
    'status' => 'block',
    'app' => 'hermes',
    'blockname' => 'tree_menu',
    'menu_parent' => 'hermes',
);

$this->applications['vilma']['menu_parent'] = 'administration';

$this->applications['nic']['menu_parent'] = 'administration';
