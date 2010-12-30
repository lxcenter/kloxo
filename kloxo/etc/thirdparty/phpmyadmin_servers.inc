<?php

$i = 0;

$i++;
$cfg['Servers'][$i]['host']            = 'localhost';
$cfg['Servers'][$i]['port']            = '';
$cfg['Servers'][$i]['socket']          = '';
$cfg['Servers'][$i]['connect_type']    = 'tcp';
$cfg['Servers'][$i]['extension']       = 'mysql';
$cfg['Servers'][$i]['compress']        = FALSE;
$cfg['Servers'][$i]['controluser']     = '';
$cfg['Servers'][$i]['controlpass']     = '';
$cfg['Servers'][$i]['auth_type']       = 'cookie';
$cfg['Servers'][$i]['user']            = '';
$cfg['Servers'][$i]['password']        = '';
$cfg['Servers'][$i]['only_db']         = '';
$cfg['Servers'][$i]['verbose']         = '';
$cfg['Servers'][$i]['pmadb']           = ''; // 'phpmyadmin' - see scripts/create_tables.sql
$cfg['Servers'][$i]['bookmarktable']   = ''; // 'pma_bookmark'
$cfg['Servers'][$i]['relation']        = ''; // 'pma_relation'
$cfg['Servers'][$i]['table_info']      = ''; // 'pma_table_info'
$cfg['Servers'][$i]['table_coords']    = ''; // 'pma_table_coords'
$cfg['Servers'][$i]['pdf_pages']       = ''; // 'pma_pdf_pages'
$cfg['Servers'][$i]['column_info']     = ''; // 'pma_column_info'
$cfg['Servers'][$i]['history']         = ''; // 'pma_history'
$cfg['Servers'][$i]['verbose_check']   = TRUE;
$cfg['Servers'][$i]['AllowRoot']       = TRUE;

$cfg['Servers'][$i]['AllowDeny']['order'] = '';
$cfg['Servers'][$i]['AllowDeny']['rules'] = array();

$i++;
$cfg['Servers'][$i]['host']            = 'polo';
$cfg['Servers'][$i]['port']            = '';
$cfg['Servers'][$i]['socket']          = '';
$cfg['Servers'][$i]['connect_type']    = 'tcp';
$cfg['Servers'][$i]['extension']       = 'mysql';
$cfg['Servers'][$i]['compress']        = FALSE;
$cfg['Servers'][$i]['controluser']     = '';
$cfg['Servers'][$i]['controlpass']     = '';
$cfg['Servers'][$i]['auth_type']       = 'cookie';
$cfg['Servers'][$i]['user']            = '';
$cfg['Servers'][$i]['password']        = '';
$cfg['Servers'][$i]['only_db']         = '';
$cfg['Servers'][$i]['verbose']         = '';
$cfg['Servers'][$i]['pmadb']           = ''; // 'phpmyadmin' - see scripts/create_tables.sql
$cfg['Servers'][$i]['bookmarktable']   = ''; // 'pma_bookmark'
$cfg['Servers'][$i]['relation']        = ''; // 'pma_relation'
$cfg['Servers'][$i]['table_info']      = ''; // 'pma_table_info'
$cfg['Servers'][$i]['table_coords']    = ''; // 'pma_table_coords'
$cfg['Servers'][$i]['pdf_pages']       = ''; // 'pma_pdf_pages'
$cfg['Servers'][$i]['column_info']     = ''; // 'pma_column_info'
$cfg['Servers'][$i]['history']         = ''; // 'pma_history'
$cfg['Servers'][$i]['verbose_check']   = TRUE;
$cfg['Servers'][$i]['AllowRoot']       = TRUE;

$cfg['Servers'][$i]['AllowDeny']['order'] = '';
$cfg['Servers'][$i]['AllowDeny']['rules'] = array();

$i++;
$cfg['Servers'][$i]['host']            = 'root.lxlabs.com';
$cfg['Servers'][$i]['port']            = '';
$cfg['Servers'][$i]['socket']          = '';
$cfg['Servers'][$i]['connect_type']    = 'tcp';
$cfg['Servers'][$i]['extension']       = 'mysql';
$cfg['Servers'][$i]['compress']        = FALSE;
$cfg['Servers'][$i]['controluser']     = '';
$cfg['Servers'][$i]['controlpass']     = '';
$cfg['Servers'][$i]['auth_type']       = 'cookie';
$cfg['Servers'][$i]['user']            = '';
$cfg['Servers'][$i]['password']        = '';
$cfg['Servers'][$i]['only_db']         = '';
$cfg['Servers'][$i]['verbose']         = '';
$cfg['Servers'][$i]['pmadb']           = ''; // 'phpmyadmin' - see scripts/create_tables.sql
$cfg['Servers'][$i]['bookmarktable']   = ''; // 'pma_bookmark'
$cfg['Servers'][$i]['relation']        = ''; // 'pma_relation'
$cfg['Servers'][$i]['table_info']      = ''; // 'pma_table_info'
$cfg['Servers'][$i]['table_coords']    = ''; // 'pma_table_coords'
$cfg['Servers'][$i]['pdf_pages']       = ''; // 'pma_pdf_pages'
$cfg['Servers'][$i]['column_info']     = ''; // 'pma_column_info'
$cfg['Servers'][$i]['history']         = ''; // 'pma_history'
$cfg['Servers'][$i]['verbose_check']   = TRUE;
$cfg['Servers'][$i]['AllowRoot']       = TRUE;

$cfg['Servers'][$i]['AllowDeny']['order'] = '';
$cfg['Servers'][$i]['AllowDeny']['rules'] = array();

	$cfg['ServerDefault'] = 1;              // Default server (0 = no default server)
	$cfg['Server']        = '';
	unset($cfg['Servers'][0]);


