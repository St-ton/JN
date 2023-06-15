Shop CLI
========

.. |br| raw:: html

    <br />


The shop CLI is a command line tool which is used together with the PHP
command line interpreter and allows you to carry out administrative tasks without having to run the shop’s
back end. |br|
To be able to use the shop CLI, PHP must be available in the form of a command line interpreter.
(See: PHP server configuration)


Executing the shop CLI
----------------------

The shop CLI will be executed in the main directory (installation directory) of the JTL-Shop:

.. code-block:: text

    $> php cli [befehl:sub-befehl [parameter]]

Executing without a command, as also happens when the ``list`` command is executed, will show
a short list of all available commands.

.. code-block:: text

    $> php cli
    ...
    $> php cli list
    ...

Many commands have only one sub-command. |br|
In this case, a prompt from the shop CLI will ask if the sub-command should be carried out.


Help with commands
------------------

With ``help`` before a command, as is the case with Parameter ``-h`` and ``--help`` after
a command, you will receive help regarding this specific command.

.. code-block:: text

    $> php cli help generate:demodata
    ...
    $> php cli generate:demodata -h
    ...
    $> php cli generate:demodata --help
    ...


Individual commands
-------------------

``migrate``
...........

If the command ``migrate`` is executed without a sub-command, it will run all
migrations that were not previously run.

``migrate:create``
..................

The ``create`` command will create the object of a new migration. |br|
New migrations contain two empty methods (``up()``, ``down()``) that are to be used by the developer.

``migrate:innodbutf8``
......................

The ``innodbutf8`` command converts all database tables to the "InnoDB" *engine* that to date are still running on
the "MyISAM" engine. |br|
Subsequently, all the tables change to ``CHARACTER SET 'utf8'`` and the sorting rule changes to
``COLLATE 'utf8_unicode_ci'``.

``migrate:status``
..................

With the ``status`` command, you can generate a list of all migrations and their respective execution status.


backup
......

The command ``backup`` cannot be independently executed. It can only be executed with a specific
sub-command.

``backup:db``
.............

``db`` creates a backup of the shop’s database. |br|
The backup that is created will then be saved under ``export/backup/[DatumID]_backup.sql``. |br|
With the ``-c`` (or ``--compress``) parameter, the backup file can be compressed with gzip*. The file name will
then change to ``export/backup/[DatumID]_backup.sql.gz``.

``backup:files``
................

``files`` creates a backup of the file and folder structure of the shop. |br|
With the ``--exclude-dir=`` parameter, one or more of the directories can be excluded from the
archiving process. |br|
In the case of multiple directories, the exclude-parameter
will be used several times: ``exclude-dir=pfad_a --exclude-dir=pfad_b`` etc.)

.. danger::

    Should the installation directory involve a git repository, then it is advised
    that ``.git/``-directory always be excluded from archiving with ``--exclude-dir=.git/`!

.. caution::

    Large directories, like picture directories or possibly ``includes/vendor/``, should be, if at all possible,
    left out when archiving. Otherwise, the process could take much longer.

The``.zip`` file created will then be saved under ``export/backup/[DatumID]_file_backup.zip``.


cache
.....

The command ``cache`` cannot be independently executed. It can only be executed with a specific
sub-command.

``cache:dbes:delete``
.....................

The synchronisation between JTL-Wawi and JTL-Shop creates temporary folders that are usually automatically deleted. |br|
In the case that automatic deletion is blocked by the ``KEEP_SYNC_FILES`` constant, these temporary files can be deleted with
this command.

``cache:file:delete``
.....................

As long as the caching method for JTL-Shop is set to "Files" (and "Files (extended)"), these files will be saved
under ``templates_c/filecache/``. JTL-Shop automatically manages the ``filecache/`` directory.

When necessary, with the help of the shop CLI and this command, the directory can be emptied and removed altogether.

``cache:tpl:delete``
....................

For every activated template in JTL-Shop, there is a directory in the ``templates_c/`` folder. Here,
all the template files that are pre-compiled by Smarty will be stored. |br|

With this sub-command, this folder can also be manually emptied and deleted, if necessary.

``cache:clear``
...............

This sub-command clears the corresponding memory of the currently activated object cache method. |br|
(As set in the back end. See: *System -> Cache -> Settings-> Method:*)

``cache:warm``
..............

This command carries out a “cache warm-up”. When this happens, various contents will be
prepared for quicker access.

With the proper parameters, you can specifically establish which areas of the cache are to be
“warmed up”.

.. code-block:: text

    -d, --details         prepare item details
    -l, --list            prepare item lists
    -k, --childproducts   prepare child items
    -g, --linkgroups      prepare link groups
    -c, --categories      prepare categories
    -m, --manufacturers   prepare manufacturers

.. caution::

    Warming up the cache can take a while, depending on the size of your JTL-Shop.

You can force the cache to clear before warm-up with the following parameter:

.. code-block:: text

    -p, --preflush        Clear cache before warm-up

The parameters can be arbitrarily combined.


compile
.......

The command ``compile`` can not be independently executed. It can only be executed with a specific
sub-command.

``compile:less``
................

Alle *Themes* des EVO-Templates enthalten ``.less``-Dateien. |br|
Sollten Sie die ``.less``-Dateien in einem *Theme* an Ihre Bedürfnisse angepaßt haben, können Sie mit diesen Befehl
alle ``.less``-Dateien, aller Themes des EVO-Templates, in ``.css``-Dateien übersetzen.

With the ``--theme=[Theme-Name]`` parameter, you can select a specific *theme*. |br|
With the ``--templateDir=[Template-Name]`` parameter, you can define another template directory.

``compile:sass``
................

All NOVA template *themes* contain ``.scss`` files. |br|
If you have customised the ``.scss`` files in a *theme* according to your needs, you can use this command to convert all
``.scss`` files, of all themes of the NOVA template, to ``.css`` files.

This command also converts the "*critical SCSS*", which is always included in the page header.

With the ``--theme=[Theme-Name]`` parameter, you can select a specific *theme*. |br|
With the ``--templateDir=[Template-Name]`` parameter, you can define another template directory.

generate
........

The ``generate`` command can be independently executed, but it will then prompt and ask if the sub-command
should be executed.

``generate:demodata``
.....................

With this command, you can create simple items and categories in a still empty JTL-Shop, to demonstrate
some of the shop’s basic functions.


mailtemplates
.............

The ``mailtemplates`` command can be independently executed, but it will then prompt and ask if the sub-command
should be executed.

``mailtemplates:reset``
.......................

All JTL-Shop mail templates are fully configurable. They are stored in
the database. |br|
This command can be used to set the mail templates back to the factory default setting.


model
.....

The ``model`` command can be independently executed, but it will then prompt and ask if the sub-command
should be executed.

``model:create``
................

This command can be executed interactively. |br|
It creates a new class derived from ``DataModel`` under the name ``T[Table name]Model.php``,
which then displays the table.

.. caution::

    In order to save the new objects, there must be a folder called ``models/`` in the main directory of the shop that can be writable
    by the PHP CLI.




Extension via plug-in
---------------------

The `jtl_plugin_bootstrapper <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_plugin_bootstrapper>`_
plug-in extends the shop CLI using the command "*create-plugin*". |br|
If this plug-in is installed in JTL-Shop, you can execute the command
``jtl_plugin_bootstrapper:create-plugin`` with the shop CLI in order to create the fundamental structure of a JTL-Shop
plug-in.

The ``jtl_plugin_bootstrapper`` command can be independently executed, but it will then prompt and ask if the
sub-command ``create-plugin`` should be executed. |br|
The ``create-plugin`` sub-command then queries all required parameters and creates the necessary foundational directories
and files in the ``plugins/`` folder.

If execution of the sub-command ``create-plugin`` is required by script, all parameters
 can also be passed in a shell script. |br|

Example:

.. code-block:: sh

    #!/bin/env bash

    PLUGIN_NAME='TestPlugin'                 # Plug-in name
    PLUGIN_VERSION='1.0.0'                   # Plug-in version (SemVer compliant)
    DESCRIPTION='This is a test plug-in'     # Plug-in description
    AUTHOR='John Smith'                      # Author name
    URL='http://example.com'                 # Sample author home page
    ID='test_plugin'                         # Plug-in ID (Plug-in and internal shop ID)
    FLUSH_TAGS='CACHING_GROUP_PRODUCT'       # Caching groups that should be cleared during installation (comma-separated values)
    MINSHOPVERSION='5.0.0'                   # minimal shop version in which the plug-in is still executable (SemVer compliant)
    MAXSHOPVERSION='5.1.3'                   # maximal shop version in which the plug-in is still executable (SemVer compliant)
    CREATE_MIGRATIONS='tplugin_table'        # Generate migrations to create new tables (comma-separated values)
    CREATE_MODELS='Yes'                      # Generate a model to create new tables? (Yes/No)
    HOOKS='61,62'                            # Hooks that should be used (comma-separated and numerical)
    JS='main.js'                             # Javascript files that are to be created (comma-separated values)
    CSS='main.css'                           # CSS files that are to be created (comma-separated values)
    DELETE='Yes'                             # Upon installation, should the plug-in replace an older version? (Yes/No)
    LINKS='test-plugin'                      # Front end plug-in link name (SEO compliant, comma-separated values)
    SETTINGS='Textarea Test,Checkbox Test'   # Back end setting name (comma-separated values, must be congruent with the settings type)
    SETTINGSTYPES='textarea,checkbox'        # Back end setting type (comma-separated values)


    php cli jtl_plugin_bootstrapper:create-plugin  \
      --name="${PLUGIN_NAME}"                      \
      --plugin-version="${PLUGIN_VERSION}"         \
      --description="${DESCRIPTION}"               \
      --author="${AUTHOR}"                         \
      --url="${URL}"                               \
      --id="${ID}"                                 \
      --flush-tags="${FLUSH_TAGS}"                 \
      --minshopversion="${MINSHOPVERSION}"         \
      --maxshopversion="${MAXSHOPVERSION}"         \
      --create-migrations="${CREATE_MIGRATIONS}"   \
      --create-models="${CREATE_MODELS}"           \
      --hooks="${HOOKS}"                           \
      --js="${JS}"                                 \
      --css="${CSS}"                               \
      --delete="${DELETE}"                         \
      --links="${LINKS}"                           \
      --settings="${SETTINGS}"                     \
      --settingstypes="${SETTINGSTYPES}"           \

Not all parameters must be provided. |br|
During interactive execution, only the essential specifications are asked.

For the parameter ``SETTINGSTYPES``, the values that are listed in the``info.xml`` segment
in the ":ref:`Attribut Typ <label_infoxml_settingtypes>`" row are valid. |br|
``SETTINGS`` (setting names) and ``SETTINGSTYPES`` must be two "congruent" arrays in which, for example,
value 1 in the ``SETTINGS`` array corresponds with value 1 in the ``SETTINGSTYPES`` array.

The``--flush-tags`` parameter applies to the cache group constants, which can be found in the ``includes/defines_inc.php``
file.

