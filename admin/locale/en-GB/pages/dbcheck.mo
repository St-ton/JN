��    @        Y         �     �     �     �  
   �     �     �     �  
   �     �               !     8     O     d     }     �     �     �     �     �     �     �          '     :     I     Y     k  	   }     �     �     �     �     �     �     �                8     W     g     w     �     �  	   �     �     �     �     �     	     +	     H	     e	     v	     �	     �	     �	     �	     �	     �	  	   
     
  (   (
     Q
     _
     o
     �
     �
     �
  T   �
          (     A  %   W     }     �     �  �   �     u     �  B   �  �   �     �     �  L   �     J  #   `  &   �  $   �     �     �     �          '     8  /   N  3   ~  &   �  �   �  �   �  x  \  �   �  `  �  @   �    /    M  �   R  }   �  
   m     x  $   �     �      �     �  �        �     �  Y   �  j   P  �   �     b  �   |  R     P   j  6   �  @   �         -             6      /   0      '   <   "      %          8             3          +   
            ;       #      ,            :   9   @      	      >                 )         ?   *                     $       1                  (   7   &           4           5      2   !                             .   =            buttonCreateScript buttonMigrationStart cancelMigration clearCache countTables dbcheck dbcheckDesc dbcheckURL errorDatatTypeInRow errorDoAction errorEmptyCache errorMigrationTableOne errorMigrationTableTwo errorNoInnoDBSupport errorNoInnoDBSupportDesc errorNoInnoTable errorNoTable errorNoUTF8Support errorNoUTF8SupportDesc errorReadStructureFile errorRowMissing errorTableInUse errorWrongCollation errorWrongCollationRow fullTextDeactivate fullTextDelete ifNecessaryUpTo lessThanOneMinute maintenanceActive migrateOf migrationCancel migrationOf noMaintenance notApproveMaintenance notEnoughTableSpace notEnoughTableSpaceLong noteMigrationScript noteMigrationScriptClick noteMigrationScriptDesc noteMigrationScriptMaintenance notePatienceOne notePatienceTwo noteRecommendMigration noteSoloMigration noteSoloMigrationClick oneMinute showModifiedTables soloStructureTable startAutomaticMigration structureMigration structureMigrationNeeded structureMigrationNeededLong sureCancelStructureMigration viaScriptConsole warningDoBackup warningDoBackupScript warningDoBackupSingle warningOldDBVersion warningOldDBVersionLong warningUseConsoleScript warningUseThisShopScript yesBackup yesEnoughSpace Content-Type: text/plain; charset=UTF-8
 Create script Start migration Cancelling migration… Clearing cache… Number of tables Database review With the database review you can check the consistency of your online shop database. https://jtl-url.de/nv3x4 Data type text in column Could not run action. Error emptying the object cache. (%s) Table   could not be migrated! InnoDB is not supported! Your current database version %s does not support InnoDB tables—a structure migration is not possible.<br/> Please contact the database administrator or host to activate the InnoDB support. is no InnoDB table Missing table UTF-8 collation <strong>utf8_unicode_ci</strong> is not supported! Your current database version {$DB_Version-&gt;server} does not support collation "utf8_unicode_ci"—a structure migration is not possible.<br/> Please contact the database administrator or host to activate collation "utf8_unicode_ci". Could not read structure file. Missing column %s in %s  is currently being used and cannot be migrated! Would you like to continue? has a wrong collation Inconsistent collation in column %s The full-text search will be disabled. Full text %s for %s will be deleted. if necessary up to less than one minute Maintenance mode active. Running migration of  Cancel migration Running migration of  I do not want to activate the maintenance mode. Please confirm the maintenance mode and the backup. Not enough space in InnoDB tablespace. In the InnoDB tablespace of your database, only %s are available for storing data. This might not be enough for the amount of data to be migrated. Please make sure that enough space is available in the InnoDB tablespace. The migration via script using the MySQL Console is recommended if you have administrative access to your database server and want to migrate a great amount of data. By clicking on "Create script" you can generate a script for carrying out the required migration. You can then run this script entirely or partly on the console of your database server. For this you require administrative access (e.g. via SSH) to your database server (e.g. via SSH). A web interface such as phpMyAdmin is <strong>not</strong> suitable for running this script. The script is based on the current situation and contains only changes that are necessary for this JTL-Shop. You cannot use the script to perform a migration for a different JTL-Shop. Please note that it might take some time to perform a complete run of the script. During that time, important tables in the shop remain inaccessible. We therefore recommend activating the <a title="Global settings - Maintenance mode" href="%s/%s/einstellungen.php?kSektion=1#wartungsmodus_aktiviert">Maintenance mode</a> while performing the migration. Please wait. %s tables and a data volume of approx. %s are being  migrated. During the migration process, important tables of the shop will be locked. This might lead to significant limitations in the front end. We therefore recommend activating the <a title="Global settings - Maintenance mode" href="%s/%s/einstellungen.php?kSektion=1#wartungsmodus_aktiviert">Maintenance mode</a> while performing the migration.<br/> Every table is migrated in two individual steps. The first step consists of moving the InnoDB tablespace. The second step consists of the conversion of data into the character set UTF-8. Automatic migration is recommended for cases in which you want to completely remodel the online shop database and the data is within the <a title="Software constraints and limits of JTL products" href="https://jtl-url.de/9thc8">Specifications</a> for JTL-Shop. Individual migration is recommended if only few tables must be edited or if some tables cannot be changed via automatic migration or migration via a script. With a click on the <i class="fa fa-cogs"></i>symbol, you can perform the migration individually for every table in the list. one minute Number of modified tables Individually via the structure table Starting automatic migration… Structure migration of %s tables Structure migration required! %s tables require a migration to the InnoDB tablespace and, if applicable, a conversion to a UTF-8 character set. Approx. %s of data are affected by this migration. Cancel structure migration? Via a script on the DB console <strong>BEFORE</strong> the migration, a backup of the entire database must be performed. <strong>BEFORE</strong> running the script, you absolutely need to create a backup of the entire database. <strong>BEFORE</strong> conducting the migration, we highly recommend performing a backup of the entire database or at least a backup of every table you wish to edit. Outdated database version The used database version %s does not support all possibilities of this shop version. Therefore, some functions will not be available after the migration. Use a server console and do <strong>NOT</strong> use phpMyAdmin to run the script. Only use the script for running the migration of <strong>THIS</strong> JTL-Shop. I created a backup of the entire online shop database. I made sure that there is enough space in the InnoDB tablespace. 