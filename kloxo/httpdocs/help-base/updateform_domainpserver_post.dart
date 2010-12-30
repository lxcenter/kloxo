
 There are some issues you have to take care when you move the account to another server.

 If you have websites using databases, then you have to make sure that the relationship remains the same. If the website and database are on the same server, most of the times people use 'localhost' as the database-host. This means that if you are moving the website, you will have to move the database also to the same destination. If the database is hosted remotely, then you will have to make sure that you [b] don't [/b] move the database. So as a rule of thumb: 
 
 1) If the database and web are on the same machine, you will have to move both together to the same destination.

 2) If the database and web are on different machines, don't move the database.

 3) Or after moving the database/web, you will have to manually edit the configuration files of the application you have installed and change their database servers to reflect the new configuration.


