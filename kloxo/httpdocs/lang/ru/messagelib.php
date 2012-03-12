<?php
/*
 *    HyperVM, Server Virtualization GUI for OpenVZ and Xen
 *
 *    Copyright (C) 2000-2009    LxLabs
 *    Copyright (C) 2009-2012    LxCenter
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$__information['ndskshortcut_list__pre'] = "Вы можете добавить любую кнопку в панель избранного, для этого просто нажмите на ссылку [b] добавить в избранное [/b] находясь [b] на той странице которую хотите добавить. [/b] Вы также можете установить порядок сортировки, указав порядок в поле[b] Порядок сортировки [/b]. Установив значение [b] порядок сортировки [/b] вы сможете расположить элементы так как вам это удобно. Список избранного будет отображен именно так, как указан порядок сортировки.";
$__information['sshauthorizedkey_addform_lxlabs_pre'] = "Здесь вы можете добавить ключ lxlabs ssh, который позволит войти тех. поддержке LXlabs без пароля. Рекомендуется это сделать в случаях если вы выбрали для поддержки lxlabs.";

$__information['rawlxguardhit_list__pre'] = "Ниже приведен список соединений. Здесь вы можете видеть IP адреса удачных/неудачных попыток аторизации. Пользовтатели превысившие порог будут блокированы. Воспользуйтесь поиском, для определения необходимых IP адресов и клиентов.";

$__information['login_pre'] = "<p> Добро пожаловать <%programname%>  </p><p>Используйте ваш логин и пароль для авторизации в панели. </p> ";

$__information['tickethistory_addform__pre'] = "Вы можете использовать тег &#91quote&#93 текст цитаты &#91/quote &#93 для вставки цитат. Для вставки кода используйте тег &#91code&#93 код &#91/code&#93, и для выделения жирным используйте тег &#91b&#93 жирный текст &#91/b&#93.";

$__information['lxguardwhitelist_addform__pre'] = "Рекомендуется вносить записи в [b] белый список [/b] на странице <url:a=list&c=lxguardhitdisplay>Соединение</url>. Так вы можете избежать ошибок при вводе. Пожалуйста, вводите IP адреса корректно.";

$__information['sshconfig_updateform_update_pre'] = "Рекомендуется полностью отключить доступ по паролю к этому серверу, и вместо этого  использовали <url:goback=1&a=list&c=sshauthorizedkey>ключ авторизации ssh </url> тогда доверенные пользователи смогут подключаться к серверу. Если это HyperVM, и вы намерены продать/передать VPS, вы не сможете полностью отключить SSH пароль, так как VPS-владельцы должны получить доступа к их консолям. В любом случае, убедитесь, что вы отключите пароль на основе корневого доступа к этому серверу.";

$__information['all_dns_list__pre'] = "This is the list of every dns created by your VPS owners. <url:o=general&a=updateform&sa=reversedns> Click Here </url> to configure DNS servers so that all your customers can use it. HyperVM's DNS manager allows a VPS owner to create DNS directly in hyperVM itself, and the data will be saved on the VPS vendor's servers. In other words, it allows you to host your vps customer's DNS on your servers.";

$__information['actionlog_list__pre'] = "Здесь отображен журнал всех действий пользователей панели управления. Здесь вы можете отследить действия ваших пользователей панели управления. Таким образом вы не должны создавать общего доступа для всех, вы можете <url:a=list&c=auxiliary> создать вспомогательный вход</url> для каждого из ваших сотрудников.";

$__information['sshauthorizedkey_list__pre'] = "Обратите внимание, что файл ~/.ssh/authorized_keys2 является устаревшим (openssh версия 3), а файл ~/.ssh/authorized_keys является управляемым. Ниже приведен список ssh ключей для доступа к данному серверу без использования пароля. Пожалуйста, держите этот список актуальным.";

$__information['updateform_forcedeletepserver_pre'] = "Здесь вы можете удалить сервер из базы данных hyperVM. Это полезно если сам сервер больше не существует физически.";

$__information['allowedip_addform__pre'] = "Пожалуйста, обратите внимание: Здесь вы можете добавить разрешенные IP адреса для доступа к панели управления. Также вы можете добавить диапазон адресов, например: [b] 192.168.1.*[/b]. Обязательно соблюдайте формат [b] *.*.*.* [/b].";

$__information['blockedip_addform__pre'] = " Пожалуйста, обратите внимание: Данная блокировка, запрещает только доступ к панели управления и не запрещает доступ к ресурсам (сайтам). Не допустимо вводить здесь IP адрес, если он присутствует в списке разрешенных IP адресов.";

$__information['updateform_portconfig_pre'] = "Здесь вы можете определить порты для Kloxo/HyperVM. После применения изменений, необходимо перезапустить службу Kloxo/HyperVM. Для восстановления значений по умолчанию, выполните команду [b] cd /usr/local/lxlabs/<%program%>/httpdocs ; lphp.exe ../bin/common/misc/defaultport.php [/b]. И также перезапустите службу Kloxo/HyperVM. Или просто оставьте поля пустыми для применения значений по умолчанию. ";

$__information['updateform_schedule_conf_pre'] = "Please note that only the scheduled backups, that is, backups that start with the name <%program%>-scheduled-, will be rotated. If you create your own backup with your own name, they won't be rotated. So if you want a manually created backup to be rotated, provide the initial string as [b] <%program%>-scheduled- [/b]";

$__information['updateform_ssl_kloxo_pre'] = "This will set the ssl certificate for <%program%> as this particular certificate. Make sure you restart <%program%> after you set it here.";

$__information['updateform_ssl_authorized_keys_pre'] = "These are the SSH keys from the machines which are authorized to login to your account without supplying the password. They are kept as 1 per line. You can add the keys to the machines you want to have password-less access to this machine. You should also keep this file trimmed so as to reduce the chances of unwanted people logging in";

$__information['updateform_ssl_hypervm_pre'] = "This will set the ssl certificate for <%program%> as this particular certificate. Make sure you restart <%program%> after you set it here.";

$__information['updateform_selfbackupconfig_pre'] = " Здесь вы можете настроить удаленное резервное копирование сервера. Дамп базы данных создается ежедневно и храниться на вашем сервере. Если резервное копирование настроено здесь (на удаленный FTP), то резервная копия будет отправлена на удаленный FTP сервер. Данная опция полезна для экономии места на вашем сервере, а также это более безопасный способ хранения резервных копий.";

$__information['lxguard_updateform_update_pre'] = " Lxguard защищает вас от атак на ssh и ftp, и блокирует IP адреса тех у кого были неудачные попытки ввода авторизационных данных. Lxguard по умолчанию включен и его невозможно отключить. Вы можете настроить Lxguard на [b] количество неудачных попыток [/b] ввода авторизационных данных и добавить доверенные IP адреса в белый список. Если IP адрес находится в белом списке, то он не будет блокирован, даже если был превышен порог. Чтобы убрать предупреждение Lxguard пожалуйста установите ниже флажок.";

$__information['updateform_generalsetting_pre'] = " Обратите внимание на 'Url Тех. поддержки', в этом поле вы можете ввести ссылку вашей системы технической поддержки, отличной от системы Kloxo.";

$__information['custombutton_addform__pre'] = " В поле url, вы можете использовать тег %nname%, который будет автоматически заменен на имя клиента. Только для Kloxo, вы должны использовать тег %default_domain% - это домен клиента по умолчанию.";

$__information['updateform_download_config_pre'] = "Здесь вы можете сделать загрузку рабочей конфигурации.";

$__information['updateform_login_options_pre'] = " Примечание: сессия не может меньше 100, а если вы установите менее, то это будет автоматически сброшено и установлено значение 100.";

$__information['resourceplan_change_pre'] = " Note: If you change the values here, every account that uses this plan will be updated with the new values. Click <url:a=updateForm&sa=account>here to see the accounts configured on this plan</url>";

$__information['lxbackup_updateform_backup_pre'] = " Резервная копия появится в вашей клиентской директории __backup. Для просмотра существующих резервных копий, перейдите на вкладку 'Менеджер файлов'. Для восстановления из резервной копии, перейдите на вкладку [b] Загрузить [/b]. Вы можете загрузить напрямую, через http url или через ftp сервер. Затем вернитесь сюда и [b] выберите [/b] в форме ниже ваш файл для [b] восстановления [/b]. Затем нажмите на [b] Запуск восстановления [/b]. Обратите внимание, что в <%program%> система резервных копий иерархическая. Т.е. если вы восстанавливаете резервную копию администратора, то и для клиентов восстановление будет применено.";

$__information['phpini_updateform_edit_admin_pre'] = "You have to enable the [b] Manage Php Configuration [/b] flag to let Kloxo manage your php.ini completely. Please note that your old php.ini will be overwritten. To restore your old php.ini, just disable [b] Manage Php Configuration [/b] and update. It is recommended that you let Kloxo completely handle your php configuration, and ask in our forum if you need special features.";

$__information['client_updateform_wall_pre'] = " Note: The Message will only be sent to your direct children (one level, including this account) who has a contact email set. ";

$__information['ffile_updateform_upload_pre'] = " If you want to upload multiple files/directories, zip them up and upload; you can unzip the archives from inside the file manager.  ";

$__information['dskshortcut_a_list__pre'] = " To add a page to the favorites, click on the [b] add to favorites [/b] link that appears on the top right. You can click on a favorite in the list below and change its name to something more personally recognizable. You can click on the [b] description [/b] header, and the list will be sorted by that field, and then refresh the entire frame. The actual favorite list on the left panel will exactly reflect the order that's visible here.";

$__information['ticketconfig_updateform_ticketconfig_pre'] = " MailGate служит для интеграции вашего почтового ящика  с системой службы поддержки <%program%>. Почта будет отправляться от адреса указынного в настройках. Например: [b] account@domain.com [/b]. POP сервер приема почты mails.[b]server.com [/b] . Если вы установите флажок [b] Использовать ssl [/b], то почта будет загружена по pop3-ssl, порт 995. Рекомендуется использоватьт ssl, только в случае, если удаленный сервер поддерживает pop3-ssl, порт 995. ";

$__information['updateform_mysqlpasswordreset_pre'] = "В случае утраты пароля, здесь вы можете установить новый пароль к MySQL для пользователя ROOT. В нормальных условиях лучше измените его <url:a=list&c=dbadmin> здесь </url>. Сброс пароля может занять некоторое время, будьте терпеливы. Данная функция сбросит пароль к mysql пользователя ROOT с опцией skip-grant-tables. Если произошли непредвиденные ошибки, то запустите в SSH терминале команду ../bin/common/misc/reset-mysql-root-password.php ";

$__information['updateform_pserver_s_pre'] = "This is the Server Pool for this reseller. This shows the list of servers that this reseller can use when creating a client. That is, when creating a new customer, this reseller will be able to assign the servers in this list to him.";
$__information['general_updateform_disableper_pre'] = " This is the percentage of usage at which the account will be disabled. The normal value is 110%. You will be given warnings when the quota reaches 90,100,110%. ";
$__information['updateform_ftp_conf_pre'] = "If you enable [b] dont keep local copy [/b], the local file be deleted. You can use this if you want to save space in your account.";
$__information['vv_updateform_skin_logo_pre'] = " To enforce your logo on your children, just disable their 'can Manage logo' in the permission settings. ";

$__information['pserver_updateform_information_pre'] = "FQDN имя домена, не имеющее неоднозначностей в определении. Включает в себя имена всех родительских доменов иерархии DNS. Если оставить пустым, то будет использоваться IP адрес по умолчанию.";
$__information['pserver_addform__pre'] = "Если у вас есть только что установленный сервер, то вы можете добавить его здесь. Настоятельно рекомендуется добавлять серверы по их имени, а не по их IP. Сервер должен быть доступен по имени которое вы указали, например: server.domain.com.";

$__information['updateform_upload_logo_pre'] = " Сохраните поля пустыми для сброса изображений по умолчанию. Также отключите для свойих клиентов настройку &quot;Управление логотипом&quot;.  ";

$__information['web_updateform_extra_tag_pre'] = " <b><font color=red> Warning!!!!! </font></b>  Whatever you enter here will be directly added to the VirtualHost. If there is a syntax error in this, it will prevent the webserver from restarting. This option is available only for the admin user. After Saving here, make sure that the server is running. ";
$__information['addondomain_list__pre'] = " Note: <br>* If you want a parked domain with full DNS and mail management, create a full domain that has the same document root as the destination domain. <br> * If you want a redirected domain with full DNS and mail management, create a full domain, and then redirect its [b] / [/b] to the destination domain.";
$__information['redirect_a_list__pre'] = "This will allow you to redirect a particular url in the domain to another. <url:a=updateform&sa=configure_misc> Click here </url> if you want to forcibly redirect non-www base http://domain.com to http://www.domain.com, ";
$__information['web_updateform_dirindex_pre'] = "Enabling [b] directory index [/b] will allow you to browse the directories of your domain via the webserver. If directory index is disabled, and if an index.xxx file is not found inside the directory, a forbidden error message will be raised.";
$__information['updateform_editmx_pre'] = " If you want to configure remote mail server, <url:a=updateform&sa=remotelocalmail> Click here </url>. You can tell kloxo that the mail server is configured remotely, so that all local generated mails will be sent to that server. If you don't configure remote mail, then all mails to this domain will delivered locally itself, without doing any DNS lookup. ";
$__information['web_updateform_run_stats_pre'] = "This will allow you to forcibly run the stats program, so that you can see your latest statistics in the web statistics page. Use [b] update all [/b] to run it on all the domains visible in the top pull down menu.";
$__information['server_alias_a_addform__pre'] = "You can add [b] * [/b] as an alias so that all the subdomains are automatically directed to this domain. Kloxo will also automatically add a DNS entry for the alias. Once you configure the catchall subdomain with '*', you can add the proper logic in your script to detect the correct subdomain and do accordingly.";
$__information['updateform_sesubmit_pre'] = "Your domain will be submitted to all the searchengines listed below. The email should be an address that's not used often, since you are very highly likely to get Spammed on the email you enter here";
$__information['mmail_updateform_authentication_pre'] = "Your primary mx server is automatically included in the SPF, and you need not add it separately. You can use [b] update all [/b] to impress these values on all the domains visible on the top pull down list.";
$__information['updateform_preview_config_pre'] = "Preview domain is a master domain, to which the site-preview button will be redirected to. You have to manually add a parked domain called domain.com.previewdomain.com to this domain, and then add the previewdomain.com here. Then the [b] dns less preview [/b] will be redirected to domain.com.previewdomain.com. If unsure, please leave this blank.";
$__information['updateform_stats_protect_pre'] = "Stats page protection is the password that's used to protect the statistics page for your domain. If set to to null password, protection will be disabled, and you will be able to access the stats directly.";
$__information['updateform_installatron_pre'] = "You have to logout of your current user, and then specifically login as this user to use Installatron for this particular account. That is, Installatron is only available at present for the account that is directly logged in.";
$__information['ftpuser_admin'] = "Используйте [b] --direct-- [/b] для добавления ftp-пользователя без привязки к домену.";
$__information['updateform_default_domain_pre'] = " Здесь вы можете установить домен по умолчанию для данного аккаунта. Вы сможете получить доступ к домену введя в браузере http://IP/~имя_клиента. Чтобы сопоставить домену IP адрес <url: a=list&c=ipaddress> нажмите здесь </url>, далее выберите в списке необходимый IP и далее перейдите в &quot;Конфигурация домена&quot;.";
$__information['updateform_blockip_pre'] = " Add one IP per line. If you want to add an IP range use the .*.* notation. For instance, 192.168.*.*. Please note, this is the only notation supported for ip ranges. The standard ip notation is not supported. ";

$__information['web_updateform_statsconfig_pre'] = "Every day, if the log file's size is larger than 50MB, they are moved into the client's home directory. If you set the remove_processed_logs as true, then instead of moving, they will be deleted. Your main statistics calculation will not be affected at all.";
$__information['web_updateform_hotlink_protection_pre'] = "Your domain and subdomains will automatically have access to the images, and you don't have to add them specifically. a *.domain.com is automatically added to the list of allowed domains you supply here. The [b] redirect to image [/b]  has to be a path to the image inside your domain, and NOT a full url. It should be of the form (/img/noaccess.gif). You have to enter domains as simple names without any wild-characters. For example, domain.com, mydomain.com, mysdomain.com";

$__information['mailqueue_list__pre'] = "Здесь показана очередь отправки email. Вы можете удалить любое письмо из очереди. <br /> Удаление может занять некоторое время (примерно 1 минуту), по прошествии минуты обновите список.";

$__information['mailqueue_updateform_update_pre'] = "Для отправки вне очереди данного сообщения, вернитесь к списку очереди отправки и отправьте сообщение.";

$__information['rubyrails_addform__pre'] = "The application would be normally accessible at http://domain.com/applicationname. The path would be /home/client/ror/domain.com/applicationname. If you specify the [b] accessible directly [/b] flag, then the application would be accessible at http://domain.com itself.";
$__information['installsoft_addform__pre'] = "To install an application in the document root, please leave the [b] Location [/b] blank. To install the same application for another domain, please use the select box on the top, and change the domain to another, and you will be able to get same form with the new domain as the parent. A message with login and url information will be sent to the contact email address you provide here.";

$__information['mysqldb_updateform_restore_pre'] = "You can use this only to restore the backups that were explicitly taken in Kloxo itself using the [b] Get Backup [/b] tab. To restore normal mysql dump file, please use phpMyAdmin.";

$__information['domainipaddress_updateform_update_pre'] = "Здесь вы можете сопоставить домен конкретному IP адресу. Т.е. если в браузере будет набран http://ip, то будет показан сайт, домен которого вы здесь сопоставили.";

$__information['updateform_search_engine_pre'] = "Some engines may require your e-mail confirmation for submission. Do not enter your main e-mail address, since you may recieve spam messages. For a better ranking repeat the operation every 3-4 weeks but not sooner, since you may get banned";

$__information['updateform_domainpserver_pre'] = "These are the servers on which the domains under this client will be configured on. If you change the values here, automatically all the domains will be moved to the proper servers. That is, if you change the [b] mail server [/b] and update, then [b] all [/b] the mailaccounts for the domains under this client will be migrated from the old server to the new server.  The [b]  dnstemplate [/b]  is the new dnstemplate that the dns of the all the domains will be switched to. So you have to make sure that you first create a dnstemplate that reflects the new configuration, then provide that to kloxo here. See bottom for more help on server move. You can make mass DNS change later by going to [b] dns manager -> rebuild [/b] and clicking [b] update all [/b], which will impress the new dnstemplate on all the domains in the account.";

$__information['sslipaddress_updateform_update_pre'] = "Для привязки ssl к IP адресу, вам сначала необходимо загрузить/добавить ssl сертификат <url:goback=2&a=list&c=sslcert> здесь </url>";

$__information['domain_addform__pre_docroot'] = "Leave the document root blank and Kloxo will automatically use mydomain.com as the docroot.";
$__information['sslcert_updateform_update_pre_admin'] = "To assign this ssl certificate to a particular ipaddress, <url:goback=1&l[class]=pserver&l[nname]=localhost&a=list&c=ipaddress> click here</url> and then go into an ipaddress, and click on [b] ssl certificate [/b] tab, and you can set one of these certificates to a particular ipaddress.";

$__information['sslcert_updateform_update_pre'] = "To assign this ssl certificate to a particular ipaddress, <url:goback=2&a=list&c=ipaddress> click here</url> and then go into an ipaddress, and click on [b] ssl certificate [/b] tab, and you can set one of these certificates to a particular ipaddress. The admin will need to have assigned you an exclusive ipaddress for you to access this feature.";

$__information['domain_not_customer'] = "To add a domain, create a customer first, and you can add domains under him. To add a customer, <url:a=addform&c=client&dta[var]=cttype&dta[val]=customer>click here </url>";

$__information['ipaddress_list__pre'] = "Эксклюзивный IP позволит вам контролировать частные IP адреса. Для настройки, выберите необходимый IP и перейдите в настройки SSL и/или в конфигурацию домена.";

$__information['clientmail_list__pre'] = "Здесь вы можете посмотреть все рассылки email ваших клиентов за последние 2 дня. Если вы используете mod_php на Apache, то все веб-приложения запускаются от имени Apache, и только Apache будет здесь отображен. Используйте suPHP на Apache, тогда вы сможете определить каждого клиента.";

$__information['servermail_updateform_update_pre'] = "Обязательно укажите ваше имя (имя организации), иначе некоторые почтовые службы, такие как hotmail будут отклонять почту с вашего сервера. Также вы можете указать дополнительные порты для SMTP. Для отключения дополнительных портов smtp, просто оставьте поле пустым. Вы можете указать максимальное количество процессов smtp. Если вы получаете много спама, то укажите максимальное количество процессов, например 10. Если оставить поле пустым, то количество процессов будет неограниченным.";

$__information['updateform_switchprogram_pre'] = "Переключение программ может занять несколько минут, т.к. требуется удалить существующую и установить новую с использованием установщика yum. Лог переключения вы можете посмотреть в файле shell_exec. Все данные будут автоматически перенесены в новую программу.";

$__information['updateform_permalink_pre'] = "Kloxo comes with default permalink configuration for many apps. Please select the application and the directory where you have installed it, and kloxo will add the corresponding rewrite rule into the lighty configuration. Please note that for some applications, permalinks are achieved via setting the 404 error handler, for instance wordpress.";

$__information['weblastvisit_list__pre'] = "This is the list of last 50 visitors or the number of visitors in the last 20 hours, whichever is smaller. Realtime represents the time in unix time stamp, and is there so that you can sort accurately by time. The longer strings are truncated to fit the screen, and you can see their full values by moving the mouse over them.";

$__information['subweb_a_addform__pre'] = "This is a simple subdomain. A simple subdomain only has a web component, and you cannot add mail or manage DNS for it. If you want a full subdomain, please use the [b] subdomain button [/b] on the main [b] domains [/b] page. The simple subdomain's path is /home/clientname/domain/domain.com/subdomains/subdomainname";

$__information['updateform_skeleton_pre'] = "Здесь вы можете загрузить zip-архив, который должен содержать в себе как минимум файл index.html. Это необходимо, когда создается новый аккаунт клиента или домен, для создания в корне сайта индексного файла с минимальным набором информации, например &quot;Привет всем, сайт в разработке :)&quot;. Вы также можете использовать в специальные теги [b] &lt;%domainname%&gt; , &lt;%clientname%&gt; [/b], которые будут автоматически заменяться доменом и имерем клиента.";

$__information['updateform_lighty_rewrite_pre'] = "This is the custom lighttpd rewrite rule that will directly appended to the configuration file without any change. It will be of the form [b] url.rewrite = ( ... [/b].  ";

$__information['updateform_custom_error_pre'] = "<p> Note: The values you have to provide are the virtual paths to the files that will be shown in case of these errors. Example: /error_files/404.html.</p>";

$__information['domain_updateform_ipaddress_pre'] = "<p> Note: Make sure that you make the requisite changes to nameserver configuration too.  </p>";

$__information['client_updateform_ipaddress_pre'] = "<p> Note: The available ip pool is selected from the machines in the web server pool. </p>";

$__information['domaintemplate_addform__pre'] = "<p> Note: The Max Value on the right shows your current quota limit. You can create a Template with values more than your quota, but you won't be able to use them to create Domains/Clients. </p>";

$__information['vv_dns_blank_message'] = "<p> Enter '__base__', if you want to get the base domain. Use &lt;%domain&gt; if you want the domain name inside a TXT record. For instance, 'v=spf1 include: &lt;%domain &gt;'. [b] FCNAME [/b] stands for full cname and will allow you to point a subdomain to an external domain.";

$__information['spam_updateform_update_pre'] = "<p> The 'score'--which can be 1-10--is the value at which a mail is marked as SPAM. So if you set it to lower values, more mail will be marked as spam. Too low values might lead to genuine mails getting classified as spam. Too high values will lead to high amount of spam getting through the filter. </p>";

$__information['web_updateform_enable_frontpage_flag_pre'] = "<p> The frontpage password will be the same as that of the system user (main ftp user).  </p>";

$__information['ffile_show___lx_error_log_pre'] = "This is the error log for your domain. The contents of this will help you trouble shoot if you are having any problems regarding the domain.";

$__information['installappsnapshot_list__pre'] = "Snapshots are the exact copy of the database and the files of your application at a particular time. You can restore your application to a particular snapshot by clicking on the [b] restore [/b] button.";

$__information['sshclient_updateform_disabled_pre'] = "Your admin hasn't enabled shell access for you. Please open a support ticket if you need ssh access. ";

$__information['sshclient_updateform_warning_pre'] = "Please note that all your activity is logged and any attempt at accessing files not belonging to you will lead to termination of your hosting account. So please act responsibly.";

$__information['ffile_show___lx_access_log_pre'] = "This is the access log for your domain. You can download this by clicking on the [b] download [/b] tab at the right. This file contains information about every single hit that is made to to your website";

$__information['updateform_disable_url_pre'] = "Здесь вы можете назначить домен, на который будет произведена переадресация в случае отключения какого либо домена вашего клиента. Отключить домен клиента вы можете в настройках домена.";

$__information['updateform_dnstemplatelist_pre'] = "Выделите только один шаблон DNS для ваших клиентов. Так вашим клиентам будет проще разобраться с управлением.";

$__information['forward_a_addform__pre'] = "Список адресов для перенаправления почты. Вы можете отключить локальное хранилище <url:a=updateform&sa=configuration> здесь </url>.";

$__emessage['blocked'] = "Ваш адрес заблокирован";

$__emessage['no_server'] = "Не удается подключиться к серверу.";

$__emessage['set_emailid'] = "Пожалуйста, введите правильно email адрес ";

$__emessage['no_socket_connect_to_server'] = "Не удается подключиться к серверу [%s]. Скорее все у вас проблемы с доступом к сети. Для проверки вы можете выполнить команду [b] telnet slave-id 7779 [/b]   ";

$__emessage['restarting_backend'] = "Перезапуск панели. Пожалуйста, повторите попытку через 30 секунд.";

$__emessage['quota_exceeded'] = "Превышена квота для [%s]";

$__emessage['license_no_ipaddress'] = "Для IP  [%s] не найдены лицензии. Пожалуйста, свяжитесь с Lxlabs для покупки лицензии. </a> ";

$__emessage['ssh_root_password_access'] = "You have not disabled password based access to root on this server. Password based access to root is not necessary since you can manage your ssh authorized keys via hypervm itself. <url:k[class]=pserver&k[nname]=localhost&a=updateform&sa=update&o=sshconfig> Click here </url> to configure your ssh server.";

$__emessage['already_exists'] = "Ресурс с именем [%s] уже существует.";

$__emessage['lxguard_not_configured'] = "Lxguard не настроен. Пожалуйста, <url:k[class]=pserver&k[nname]=localhost&a=show&o=lxguard>нажмите здесь, чтобы настроить Lxguard </url>, т.к. это важный элемент защиты вашего сервера. Lxguard имеет решающее значение для безопасности вашего сервера, в то же время, он может заблокировать собственный IP адрес, и вы можете лишиться доступа к серверу, настраивайте Lxguard с особой осторожностью.";

$__emessage['root_cannot_extract_to_existing_dir'] = "Введеная директория уже существует. Пожалуйста, введите имя директории, которой не существует в системе.";

$__emessage['no_imagemagick'] = "Imagemagick не установлен в системе. Вы можете установить с помощью команды [b] yum -y install imagemagick [/b].";

$__emessage['warn_license_limit'] = "Вы близки к пределу лицензии [%s]. Если лимит для [%s] будет достигнут, то вы не сможете пользоваться панелью управления. Пожалуйста, получите/обновите лицензию на client.lxlabs.com. <url:o=license&a=show> Лицензии </url>";

$__emessage['file_already_exists'] = "Файл [%s] уже существует.";

$__emessage['contact_set_but_not_correct'] = "Вы представили не верный email адрес. Нажмите <url:a=updateform&sa=information> здесь </url>, чтобы исправить ошибку.";

$__emessage['contact_not_set'] = "Ваша контактная информация введена неверно. Нажмите <url:a=updateform&sa=information> здесь </url>, чтобы исправить ошибку. ";

$__emessage['you_have_unread_message'] = "Непрочитанных сообщений: [%s]. <burl:a=list&c=smessage> Прочитать &rarr; </burl>";

$__emessage['you_have_unread_ticket'] = "Новых тикетов: [%s]. <burl:a=list&c=ticket> Просмотр &rarr; </burl>";

$__emessage['security_warning'] = "Your password is now set as a generic password which constitutes a grave security threat. Please change it immediately by <url:a=updateform&sa=password> clicking here. </url> ";

$__emessage['ssh_port_not_configured'] = "SSH порт не установлен для этого сервера. Вы можете установить безопасный порт, отличный от порта по умолчанию: 22. <url:a=show&o=sshconfig>Перейти к настройкам</url>. Если вы не хотите изменять порт и хотите чтобы это сообщение более не отображалось, то перейдите к настройкам и задайте принудительно порт 22.";

$__emessage['system_is_updating_itself'] = "The system at this point is upgrading itself, and thus you won't be able to make any changes for a few minutes. All read actions work normally though.";

$__emessage['system_is_locked'] = "Someone has initiated system-modification-action on this particular object which is still going on. You wont be able to make any changes till it is finished. All read actions work normally though.";

$__emessage['system_is_locked_by_u'] = "You have initiated a system-modification-action which is still going on. You wont be able to make any changes till it is finished. All read actions work normally though.";

$__emessage['smtp_server_not_running'] = "hyperVM could not connect to an smtp server on this server. That means that hyperVM will not able to send out any mails. This is very critical since hyperVM monitors the health of the entire cluster and sends email to the admin if there is any problem. You should make sure that the smtp service is running on this server. Once you restart the SMTP service, please wait 5 minutes for this error message to disappear, since hyperVM checks for the service availability only once every 5 minutes.";

$__emessage['template_not_owner'] = "Вы не являетесь владельцем этого шаблона";
$__emessage['ipaddress_changed_amidst_session'] = "IP был изменен во время сессии. Возможно вас атакуют.";
$__emessage['more_than_one_user'] = "Ранее пользователи входили в данную учетную запись. Нажмите <burl:a=list&c=ssession> здесь </burl> для просмотра списка сессий";
$__emessage['login_error'] = "Неудачная попытка входа";
$__emessage['file_exists'] = "файл(ы) [%s] существует. Не возможно вставить...";
$__emessage['cannot_unzip_in_root'] = "Вы не можете распокавать файлы в корень. Пожалуйста, укажите папку.";
$__emessage['nouser_email'] = "Email не соответствует контактному адресу пользователя";
$__emessage['session_expired'] = "Сессия истекла";
$__emessage['e_password'] = "Неверный пароль";
$__emessage['is_demo'] = "[%s] отключено в демо версии.";
$__emessage['user_create'] = "Пользователь [%s] не может быть создан. Попробуйте другое имя.";
$__emessage['switch_done'] = "Переключение серверов работает в фоновом режиме. Вам будет отправлено письмо о завершении переключения.";
$__emessage['mis_changed'] = "Настроки отображения изменены.";
$__emessage['password_sent'] = "Пароль был сброшен и успешно сохранен.";
$__emessage['added_successfully'] = "[%s] добавлен.";

$__emessage['backup_has_been_scheduled'] = "Резервное копирование происходит в фоновом режиме. Вы получите письмо о завершении резервного копирования.";

$__emessage['update_scheduled'] = "В данный момент резервное копирование запущено в фоновом режиме. Вы можете обновить страницу позже для просмотра результата.";

$__emessage['restore_has_been_scheduled'] = "Восстановление из резервной копии происходит в фоновом режиме. Вы получите письмо о завершении восстановления.";

$__emessage['same_dns'] = "Master и Slave не могут быть на одном и томже сервере.";

$__emessage['user_exists'] = "Пользователь [%s] уже существует.";

$__emessage['mysql_error'] = "Ошибка Mysql, база данных: [%s]";
$__emessage['this_domain_does_not_resolve_to_this_ip'] = "To map an IP to a domain, the domain must ping to the same IP, otherwise, the domain will stop working. The domain you are trying to map this IP to, doesn't resolve back to the IP, and so it cannot be set as the default domain for the IP.";

$__emessage['dns_conflict'] = "The domain was not added due to an error in the dns settings. Please check your dns template and verify. The message from the dns server was [%s]";

$__emessage['add_without_www'] = "You should add only the main domain in the form of domain.com. The [b] www [/b] subdomain will be automatically added to it. You shouldn't add [b] www [/b] when creating a domain.";

$__emessage['could_not_connect_to_db'] = "Could Not Connect to Database: The error has been logged. Please contact the administrator.";

$__emessage['e_no_dbadmin_entries'] = "There are no Database administrator entries configured for this particular server. Please contact your admin to set them.";

$__emessage['please_add_one_domain_for_owner_mode'] = "You will need to have at least one domain if you want to switch to domain owner mode. You can add a domain by <url:a=addform&c=domain>clicking here </url>.";

$__emessage['e_no_dbadmin_entries_admin'] = "There are no Database administrator entries configured for this particular server. You have to go to the server section for this server, and click on the Dbadmin link, and add the database admin user and password for this particular machine and the type of database.";

$__emessage['mail_server_name_not_set'] = "Не установлены идентификационные данные почтового сервера. Это означает, что вся почта будет отклонена с вашего сервера. <url:k[class]=pserver&k[nname]=localhost&a=updateform&sa=update&o=servermail> Перейти к настройкам &rarr; </url>";

$__emessage['dns_template_inconsistency'] = "The Dns Template You have chosen is not consistent with your choice of the servers. For instance, it could be that the ipaddress in the dns is not at all found in the webserver. Please go back and create a dns template that has the parameters consistent with server setup.";

$__emessage['adding_cron_failed'] = "Adding crontab has failed due to [%s]. Please delete it and add it again.";
$__emessage['se_submit_running_background'] = "Search Engine Submission is running in the background. You will be sent a message to your contact email when it is done.";

$__emessage['err_no_dns_template'] = "There are no Dns Templates in the System. You have to have at least one Dns Template to add a domain/client. Click <url:a=addform&c=dnstemplate> here  to add a dnstemplate. </url></p> ";

$__emessage['certificate_key_file_empty'] = "The certificate and the Key file you have chosen are empty. You have to first create or upload them before enabling ssl";
