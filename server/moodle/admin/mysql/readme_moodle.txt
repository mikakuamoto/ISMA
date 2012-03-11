Description of phpmyadmin 2.11.11.1 import into Moodle

Change:
 * replaced libraries/session.inc.php - totally hacked to play nice with our session code
 * patched libraries/common.inc.php - cookies not cleared during install/upgrade anymore
 * removed contrib/*.*
 * removed scripts/*.*
 * removed test/*.*
 * removed config.sample.inc.php
 * added scripts/create_tables_moodle.sql - our db tables
 * added scripts/create_tables_moodle_designer.sql - our upgrade of db tables
 * added config.inc.php - takes configuration from our config.php
 * added frame.php - access control checks, moodle frame integration

Notes:
 * now supported in 1.6.x - 1.9.x
 * please use contrib/plugins/admin/mysql for 2.0
 * only utf-8 languages
