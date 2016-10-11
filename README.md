# ueppycms

Per configurare un nuovo cms, clonare il repository in locale, creare un file di configurazione in /public_html/conf/config.php

define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'sssshsecretpass');
define('DB_NAME', 'mydbname');
define('REL_ROOT', '/');
define('HOST', 'http://mysupercoolhostname');
define('ADMIN_EMAIL', 'admin@me.com');

Una volta creato il database e il sopracitato file richiamare:

http://mysupercoolhostname/utility/db/dbimport.php

La directory utility dovrebbe essere protetta da un file.htaccess per evitare l'accesso ai non autorizzati.
