Structure
=========

.. |br| raw:: html

   <br />

A plug-in consists of a *directory structure*, which must be physically present
on the data medium of the online shop, and an XML file (``info.xml``, see also :doc:`here <infoxml>`), which is responsible for the installation and the updates
of the plug-in. |br|
The ``info.xml`` file defines the plug-in. It defines which files a plug-in uses,
which tasks it should perform, and what the plug-in’s identity is.

The installation file and the directory structure varies depending on the range of tasks of the respective
plug-in. |br|
In the JTL-Shop directory structure there is a defined directory that contains all plug-ins.
From there, the system accesses plug-in resources and installation information.

.. hint::

    A plug-in for automatic creation of JTL-Shop plug-ins can be found in the
    `public Gitlab repository. <https://gitlab.com/jtl-software/jtl-shop/legacy-plugins/plugin-bootstrapper>`_.
    This can simplify the manual creation of the ``info.xml`` and the file structure.

Directory structure
-------------------

A plug-in needs a defined directory structure to be installed. |br|
There are some exceptions where you can omit certain directories, or structure them according to your own preferences.
Each plug-in has its own subdirectory within the plug-in directory.

Always assign meaningful and unique plug-in names to avoid overlaps in plug-in directory
names.
The newer plug-in directory would, therefore, overwrite the older one during the upload and the original plug-in
would no longer work. We recommend extending the plug-in directory name with unique attributes
such as the plug-in author's company name.

For all versions prior to **JTL-Shop 5.x**, the plug-ins directory ``plugins/``, in which all shop plug-ins can be found,
is located in the ``<Shop-Root>/includes/``directory. |br|
Accordingly, a typical plug-in can be found under ``[Shop-Rot]/includes/plugins/[Ihr_Pluginordner]``.

Jedes Plugin in einem Onlineshop der Version 4.x muss mindestens einen *Versionsordner* enthalten. |br|
The versions start with the integer 100 (meaning: version 1.00) and continue with 101, 102, and so on.
The integer version numbers are also the folder names below the *version directory*. |br|
Each plug-in must contain the ``100/`` directory in any case (see versions).

.. code-block:: console
   :emphasize-lines: 2-3

    [Shop-Root]/includes/plugins/[PluginName]/
    ├── version
    │   └── 100
    │       ├── adminmenu
    │       ├── frontend
    │       └── sql
    ├── info.xml
    └── README.md

**As of JTL-Shop 5.x**, the plug-in directory is located directly under the shop root,
like so ``[Shop-Root]/plugins/[Ihr_Pluginordner]``.

.. attention::

    Note that as of JTL-Shop 5.x, the **plug-in directory name** must
    correspond to the **plug-in ID** in the ``info.xml`` file.

.. code-block:: console
   :emphasize-lines: 12

    [Shop-Root]/plugins/[PluginName]/
    ├── adminmenu
    │   └── ...
    ├── frontend
    │   └── ...
    ├── paymentmethod
    │   └── ...
    ├── locale
    │   └── ...
    ├── Migrations
    │   └── ...
    ├── info.xml
    ├── README.md
    └── Bootstrap.php

Possible subdirectories
"""""""""""""""""""""""

+--------------------+-------------------------------------------------------------------------------------------------------------+
| Directory name     | Function                                                                                                    |
+====================+=============================================================================================================+
| ``adminmenu/``     | Shop admin tabs for displaying custom content in the admin area or to implement settings.                   |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``frontend/``      | Front end links to pages in the online shop with custom content.                                            |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``paymentmethod/`` | Implementation of payment methods in the online shop.                                                       |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``sql/``           | For versions older than 5.x; SQL file to make custom database tables to store data in or to modify.         |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``src/``           | As of 5.0.0, plug-in specific helper classes (organised as packages)                                        |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``locale/``        | As of 5.0.0, translation files                                                                              |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``Migrations/``    | As of 5.0.0, SQL migrations                                                                                 |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``Portlets/``      | As of 5.0.0, OPC portlets                                                                                   |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``blueprints/``    | As of 5.0.0, OPC blueprints                                                                                 |
+--------------------+-------------------------------------------------------------------------------------------------------------+

Payment directory structure
"""""""""""""""""""""""""""

A plug-in can implement any number of payment methods in the online shop. |br|
To do this, a subdirectory called ``paymentmethod/`` is needed, which is located in JTL-Shop 5.x, directly below
the plug-in root.

**Beispiel, JTL-Shop 4.x**

.. code-block:: console
   :emphasize-lines: 8-9

    [Shop-Root]/includes/plugins/[PluginName]/
    ├── version
    │   └── 100
    │       ├── adminmenu
    │       │   └── ...
    │       ├── frontend
    │       │   └── ...
    │       ├── paymentmethod
    │       │   └── ...
    │       └── sql
    │           └── ...
    ├── preview.png
    ├── info.xml
    ├── README.md
    └── LICENSE.md

**JTL-Shop 5.x example**

.. code-block:: console
   :emphasize-lines: 6-7

    [Shop-Root]/plugins/[PluginName]/
    ├── adminmenu
    │   └── ...
    ├── frontend
    │   └── ...
    ├── paymentmethod
    │   └── ...
    ├── locale
    │   └── ...
    ├── Migrations
    │   └── ...
    ├── preview.png
    ├── info.xml
    ├── README.md
    ├── LICENSE.md
    └── Bootstrap.php

Under the directory ``paymentmethod/``, it is useful to create at least the ``template/`` directory. Put the templates that display payment type specific content there
accordingly. |br|
Arrange the actual payment method classes directly under ``paymentmethod/``. |br|
Place any "helper" classes below the plug-in-specific ``src/`` folder and organize them
there in packages in a namespace-compliant way. |br|

.. code-block:: console
   :emphasize-lines: 3,9-10,12

    ├── src
    │   ├── Payment
    │   │   └── PaymentHelper.php
    │   └── ...
    └── paymentmethod
        ├── images
        │   ├── de-ppcc-logo-175px.png
        │   └── ...
        ├── template
        │   ├── paypalplus.tpl
        │   └── ...
        └── PayPalPlus.php

See section :ref:`label_infoxml_payment method` for an **example** of how this directory structure is defined in
the ``info.xlm`` file.


.. _label_aufbau_versionierung:

Versioning
----------

You can see what the XML definition of the plug-in version looks like
in the ``info.xml`` section ":ref:`label_infoxml_versioning`".

Before JTL-Shop 5.x
"""""""""""""""""""

Since plug-ins can also continue to develop over time, there is a plug-in versioning. |br|
This provides the possibility to update a plug-in via the plug-in system’s update mechanism,
to introduce new features or to fix bugs.

Each plug-in must contain the ``version/`` directory. |br|
This directory contains all previously released versions of the plug-in. Each plug-in must contain the lowest
version (meaning version 1.00). |br|
These subdirectories (version directories) contain all resources of the plug-in for the respective version.

.. code-block:: console
   :emphasize-lines: 2,3

    [Shop-Root]/includes/plugins/[PluginName]/
    ├── version
    │   └── 100
    │       ├── adminmenu
    │       │   └── ...
    │       ├── frontend
    │       │   └── ...
    │       └── sql
    │           └── ...
    ├── preview.png
    ├── info.xml
    ├── README.md
    └── LICENSE.md

If a new version is developed, the version is incremented by 1. The versioning
is, therefore, continual: 100, 101, 102, 103 and so on. An upper version limit does not exist.

To update a plug-in, transfer the ``info.xml`` to the respective plug-in directory. |br|
Transfer all new version directories to the ``/version`` directory of the respective plug-in directory.
So when a new version of a plug-in is created, paste the ``<pluginname>/info.xml`` file and
all ``<pluginname>/version/*`` version directories into the online shop.
The plug-in administrator in the admin area automatically detects when updates are available for a particular plug-in and displays an
update button.

Example:
Two versions are defined in the info.xml file. Accordingly, the *version* subdirectories would look as follows
: */version/100/* and */version/101/*.

A physical directory must exist for every version defined in the installation file.

As of JTL-Shop 5.x
""""""""""""""""""

.. important::
    As of JTL-Shop 5.0, the ``version/`` subdirectory is no longer necessary and all other directories must be created directly
    under the plug-in directory!

.. code-block:: console

    [Shop-Root]/plugins/[PluginName]/
    ├── adminmenu
    │   └── ...
    ├── frontend
    │   └── ...
    ├── locale
    │   └── ...
    ├── Migrations
    │   └── ...
    ├── preview.png
    ├── info.xml
    ├── README.md
    ├── LICENSE.md
    └── Bootstrap.php

To see how versioning is reflected in ``info.xml``, read
the corresponding section ":ref:`label_infoxml_versioning`".


.. _label_infoxml_sql:

SQL in the plug-in
------------------

Before JTL-Shop 5.x
"""""""""""""""""""

Each version of a plug-in has the ability to specify an SQL file that executes any SQL command. |br|
This SQL file can be used, for example, to create new tables or modify data in the database.
If an SQL file was specified in the ``info.xml`` file, it must also physically exist. |br|
When a new table is created in the SQL file, that is, the SQL command ``CREATE TABLE``
is used, the table name must follow a certain convention.
The name must start with ``xplugin_``, followed by a unique ``[PluginID]_``. It may, however, end with any
name. |br|
This results in: ``xplugin_[PluginID]_[Name]``.

Example: If the plug-in ID is "*jtl_exampleplugin*" and the table is called "*tuser*", the table name
must ultimately read "*xplugin_jtl_exampleplugin_tuser*". |br|
The SQL directory is located in the directory of the corresponding plug-in version.

**Example:**

For a version 102 plug-in, the corresponding section of ``info.xml`` must then look like this:

.. code-block:: xml

    <Version nr ="102">
        <SQL>install.sql</SQL>
        <CreateDate>2016-03-17</CreateDate>
    </Version>

Here, the ``install.sql`` file must be located in the SQL directory named ``sql/`` in version 102. |br|
Therefore, the directory structure in this example appears as such:

.. code-block:: console
    :emphasize-lines: 11

    includes/plugins/[PluginName]/
    ├── info.xml
    └── version
        ├── 100
        │   └── ...
        ├── 101
        │   └── ...
        └── 102
            ├── adminmenu
            ├── sql
            │    └── install-102.sql
            └── frontend

There can only be one SQL file for every plug-in version. If no SQL file was specified in the ``info.xml`` for a given version
, *leave out* the SQL directory in the respective version.

During installation, each SQL file is incrementally run from the smallest to the largest version. |br|
So, if a plug-in is in version 1.23, the SQL files of versions 1.00-1.23 will be run successively
during the installation. |br|
The process is the same when updating. Suppose that version 1.07 of a plug-in is already installed and must
now be updated to version 1.13. During the update, all SQL files from 1.08 to 1.13 will be run.

As of JTL-Shop 5.x
""""""""""""""""""

As of JTL-Shop 5.0.0, the ``sql/`` directory is *no longer supported*. Therefore, no more SQL files
will be run. |br|

.. hint::

    Like the online shop itself, plug-ins can now use *migrations*.

These *no longer* have to be defined in the ``info.xml`` file, but are now located in the ``Migrations/``
subdirectory of the plug-in directory. |br|
The naming scheme of the file and the class names are ``Migration<YYYMMDDhhmmss>.php``
(in PHP this corresponds with: ``date('YmdHis');``).

.. code-block:: console
   :emphasize-lines: 6-8

    plugins/jtl_test/
    ├── adminmenu
    │   └── ...
    ├── frontend
    │   └── ...
    ├── Migrations
    │   ├── Migration20181112155500.php
    │   └── Migration20181127162200.php
    ├── info.xml
    ├── Bootstrap.php
    ├── preview.png
    └── README.md

All plug-in migrations must implement the interface ``JTL\Update\IMigration`` and be located
in the ``Plugin\<PLUGIN-ID>\Migrations`` namespace. |br|
This interface defines two main methods, ``up()`` for running SQL code
and ``down()`` for rolling back those changes.

**Example**:

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Plugin\jtl_test\Migrations;

    use JTL\Plugin\Migration;
    use JTL\Update\IMigration;

    class Migration20190321155500 extends Migration implements IMigration
    {
        public function up()
        {
            $this->execute("CREATE TABLE IF NOT EXISTS `jtl_test_table` (
                          `id` int(10) NOT NULL AUTO_INCREMENT,
                          `test` int(10) unsigned NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB COLLATE utf8_unicode_ci");
        }

        public function down()
        {
            $this->execute("DROP TABLE IF EXISTS `jtl_test_table`");
        }
    }

When installing the plug-in, the ``up()`` methods of all migrations are automatically run, and when uninstalling
, all ``down()`` methods are run accordingly. |br|
In this case, the limitation on the creation of tables with the prefix ``xplugin_<PLUGIN-ID>`` is also no longer applicable.
Additionally, by using :doc:`Bootstrapping <bootstrapping>` with the ``installed()``,
``uninstalled()``, and ``updated()`` methods, this provides more advanced options for installing, uninstalling, and
updating a plug-in.


.. _label_aufbau_locale:

Multilingual settings (as of 5.0.0)
-----------------------------------

As of JTL-Shop 5.0.0, plug-in options can be multilingual. |br|
To this end, a plug-in can use the same mechanism as the back end of
JTL-Shop - `gettext <https://www.gnu.org/software/gettext/>`_.

.. code-block:: console
   :emphasize-lines: 8-14

    [Shop-Root]/plugins/[PluginName]/
    ├── adminmenu
    │   └── ...
    ├── frontend
    │   └── ...
    ├── paymentmethod
    │   └── ...
    ├── locale
    │   ├── de-DE
    │   │   ├── base.mo
    │   │   └── base.po
    │   └── en-US
    │       ├── base.mo
    │       └── base.po
    ├── Migrations
    │   └── ...
    ├── info.xml
    ├── README.md
    └── Bootstrap.php

For an illustrative overview of how to do this with the ``info.xml``file , see ":ref:`label_infoxml_locale`" in section
`info.xml``.

.. _label_adminmenu_structure:

"adminmenu/" structure
----------------------

For online shops version 5.x and higher,
the *admin menu* is located directly in the plug-in root. For earlier versions, it is located in the version directory of each plug-in. |br|
(If no *admin menu* has been defined in the ``info.xml`` file, this directory can also be omitted).

A plug-in can contain any number of custom links (:ref:`label_infoxml_custom_links`) in the admin area. |br|
If you have specified any *custom links* in the ``info.xml`` file, there must be a corresponding PHP file in each ``adminmenu/`` directory for each
*custom link*. |br|

.. code-block:: xml
   :emphasize-lines: 4

    <Adminmenu>
        <Customlink sort="1">
            <Name>Statistics</Name>
            <Filename>stats.php</Filename>
        </Customlink>
    </Adminmenu>

In this example, a *custom link* is created in the back end of JTL-Shop, which appears as a tab called "Statistics"
. This tab runs the ``stats.php`` file in the ``adminmenu/`` directory. This file includes the
Smarty template engine and loads a custom template that you can save in a custom defined directory.

.. code-block:: console
   :emphasize-lines: 3

   plugins/[PluginName]/
   ├── adminmenu
   │   ├── stats.php
   │   ├── radiosource.php
   │   └── selectsource.php
   ├── frontend
   │   └── ...
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...

Any additional directories are left to the discretion of the plug-in developer. |br|
Of course, it is also possible to fill the admin menu with settings only (:ref:`label_infoxml_setting_links`).

"frontend/" Structure
---------------------

In the front end menu you can create your own defined links in the front end of JTL-Shop, so that custom PHP files
are run there. |br|
As of JTL-Shop 5.x, the ``frontend/`` directory is located
directly in the plug-in root. |br|
(If no front end menu has been defined in the ``info.xml`` file, you can also omit this directory). |br|
Any number of *front end links* can be integrated.

More information on how to define *front end links* in the ``infox.xml`` file can be found in section :ref:`label_infoxml_frontendlinks`.

Each *front end link* requires a Smarty template file to display content in the online shop. |br|
This template file is located in the ``template/`` directory of the respective ``frontend/`` directory.
Therefore, the path to the template file for the example below would look like ``/meinplugin/version/102/frontend/template/``.

**An example for JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 12-15

   plugins/[PluginName]/
   ├── adminmenu
   │   └─── ...
   ├── frontend
   │   ├── boxes
   │   │   └── ...
   │   ├── css
   │   │   └── ...
   │   ├── js
   │   │   └── ...
   │   ├── template
   │   │   ├── test_page_fullscreen.tpl
   │   │   └── test_page.tpl
   │   ├── test_page_fullscreen.php
   │   └── test_page.php
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...

.. important::

    Once a plug-in that contains *front end links* is installed, make sure that the
    links have to be assigned to the respective link groups of the online shop by the administrator.

For this purpose, the plug-in manager offers the "link group" column.
If *front end links* are available, a button will be displayed there. The button leads to the link group
management (as of JTL-Shop 5.x: " Display" ->
"Custom content" -> "Pages"). |br|

The installation of the plug-in introduces *front end links* in JTL-Shop 3
into the first CMS link group.

The links of the respective plug-in are highlighted here to make it easier to find the
plug-in’s *front end links*. |br|
You can now move the *front end links* of the plug-in to other link groups via a select box.


.. _label_aufbau_frontend_res:

Front end resources
-------------------

The structure of the ``frontend/`` directory continues to include the additional "*front end resources*".

**Example for versions up to JTL-Shop 4.x:**

.. code-block:: console
   :emphasize-lines: 11-17

   includes/plugins/[PluginName]/
   ├── version
   │    ├── 100
   │    │   └── ...
   │    ├── 101
   │    │   └── ...
   │    └── 102
   │        ├── adminmenu
   │        ├── sql
   │        └── frontend
   │           ├── css
   │           │   ├── bar.css
   │           │   ├── bar_custom.css
   │           │   └── foo.css
   │           ├── js
   │           │   ├── bar.js
   │           │   └── foo.js
   │           ├── template
   │           │   └── ...
   │           └── ...
   ├── info.xml
   ├── README.md
   └── ...

**Example as of JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 7-13

   plugins/[PluginName]/
   ├── adminmenu
   │   └─── ...
   ├── frontend
   │   ├── boxes
   │   │   └── ...
   │   ├── css
   │   │   ├── bar.css
   │   │   ├── bar_custom.css
   │   │   └── foo.css
   │   ├── js
   │   │   ├── bar.js
   │   │   └── foo.js
   │   ├── template
   │   │   └── ...
   │   └── ...
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...

For more information, see the ``info.xml`` section: ":ref:`label_infoxml_frontend_res`".

Template blocks
---------------

Front end template blocks can also be manipulated by plug-ins. |br|
No data in the ``info.xml`` files are necessary for this. Only the layout structure of the template must be reproduced
in the plug-in.

A minimalistic plug-in for JTL-Shop 5 and the NOVA template could then look like this:

**Example:**

.. code-block:: console
   :emphasize-lines: 7,8

   plugins/[PluginID]/
   ├── adminmenu
   │   ├── widget
   │   ├── templates
   │   └── ...
   ├── frontend
   │   └── template
   │       └── layout
   │           └── header.tpl
   └── info.xml

When creating the structure in the plug-in ``frontend/`` directory, make sure that you exactly replicate the template
structure. |br|
The ``adminmenu/`` directory is listed here only to demonstrate the distinction between the directory names
``adminmenu/templates`` and ``frontend/template``. In the case of this example, it does not need to be created.

The ``info.xml`` file used here configures only the body of a plug-in:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <jtlshopplugin>
        <Name>[PluginName]</Name>
        <Description>Displays a clear notice on each page that this is a test shop</Description>
        <Author>JTL</Author>
        <URL>https://www.jtl-software.de</URL>
        <PluginID>[PluginID]</PluginID>
        <XMLVersion>100</XMLVersion>
        <MinShopVersion>5.0.0</MinShopVersion>
        <CreateDate>2019-12-03</CreateDate>
        <Version>1.0.0</Version>
        <Install>
            <FlushTags>CACHING_GROUP_CATEGORY, CACHING_GROUP_ARTICLE</FlushTags>
        </Install>
    </jtlshopplugin>

The ``header.tpl`` file contains everything that should be output in the front end:

.. code-block:: smarty
   :emphasize-lines: 2

    extends file="{$parent_template_path}/layout/header.tpl"}
    {block name='layout-header-content-all-starttags' prepend}
        <script>
            console.log('This output appears in the Javascript console and was generated by the plug-in: [PluginID]');
        </script>
        <div id="testing-purpose-alert" class="alert alert-warning text-center">
            This shop is for demonstrational and testing purposes only.
            No real orders can be carried out.
        </div>
    {/block}

For further explanation on block manipulation, see section ":ref:`label_eigenestemplate_tpldateien`".

.. _label_aufbau_boxen:

Boxes
-----

A plug-in can also provide boxes for the front end of JTL-Shop. |br|
The directory for these display elements are also located in the ``frontend/`` directory.

**Example for versions up to JTL-Shop 3.0:**

.. code-block:: console
   :emphasize-lines: 11,12

   includes/plugins/[PluginName]/
   ├── version
   │    ├── 100
   │    │   └── ...
   │    ├── 101
   │    │   └── ...
   │    └── 102
   │        ├── adminmenu
   │        ├── sql
   │        └── frontend
   │           ├── boxen
   │           │   └── example_box.tpl
   │           ├── css
   │           │   └── ...
   │           ├── js
   │           │   └── ...
   │           ├── template
   │           │   └── ...
   │           └── ...
   ├── info.xml
   ├── README.md
   └── ...

.. hint::

    From previous versions up to JTL-Shop 5.0, the name of this directory has changed from ``boxen/`` to ``boxes/``.

**Example as of JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 5,6

   plugins/[PluginName]/
   ├── adminmenu
   │   └─── ...
   ├── frontend
   │   ├── boxes
   │   │   └── example_box.tpl
   │   ├── css
   │   │   └── ...
   │   ├── js
   │   │   └── ...
   │   ├── template
   │   │   └── ...
   │   └── ...
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...

You can find out how to define these new boxes in the ``info.xml`` file and publish them to JTL-Shop,
in section ":ref:`label_infoxml_boxen`".


.. _label_aufbau_widgets:

Widgets
-------

Also in the back end of JTL-Shop new elements can be inserted via plug-ins, like in the dashboard of the
administration area. |br|
*Widgets* are used for this purpose. You can find out how to introduce them to the online shop's logic in the
`info.xml`` page, section ":ref:`label_infoxml_widgets`".

The related files are placed as follows:

**Example for versions up to JTL-Shop 4.x:**

.. code-block:: console
   :emphasize-lines: 9-11

   includes/plugins/[PluginName]/
   ├── version
   │    ├── 100
   │    │   └── ...
   │    ├── 101
   │    │   └── ...
   │    └── 102
   │        ├── adminmenu
   │        │   └── widget
   │        │       ├── examplewidgettemplate.tpl
   │        │       └── class.WidgetInfo_jtl_test.php
   │        ├── sql
   │        └── frontend
   ├── info.xml
   ├── README.md
   └── ...

**As of JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 6-8

   plugins/[PluginName]/
   ├── adminmenu
   │   ├── ...
   │   ├── templates
   │   │   └── ..
   │   └── widget
   │       ├── examplewidgettemplate.tpl
   │       └── Info.php
   ├── frontend
   │   └── ...
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...


.. _label_aufbau_license:

Licencing
---------

With commercial plug-ins for JTL-Shop, it is possible to let an individual class do the licensing verification. |br|
You can find more detailed information on this in the ``info.xml`` page, under the section ":ref:`label_infoxml_license`".

The licensing verification class is placed here:

**Example for versions up to JTL-Shop 4.x:**

.. code-block:: console
   :emphasize-lines: 11,12

   includes/plugins/[PluginName]/
   ├── version
   │    ├── 100
   │    │   └── ...
   │    ├── 101
   │    │   └── ...
   │    └── 102
   │        ├── adminmenu
   │        ├── frontend
   │        ├── sql
   │        └── licence
   │            └── class.PluginLicence.php
   ├── info.xml
   ├── README.md
   └── ...

**As of JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 6,7

   plugins/[PluginName]/
   ├── adminmenu
   │   └── ...
   ├── frontend
   │   └── ...
   ├── licence
   │   └── PluginLicence.php
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...

The location of the plug-in root directory is the same for earlier versions of JTL-Shop as well as JTL-Shop 5.x. |br|






Export formats
--------------

With a plug-in export format, new export formats can be integrated into the JTL-Shop.
You create a new export format by creating the following new block in the info.xml file:

.. code-block:: xml

    <ExportFormat>
     ...
    </ExportFormat>

This block can contain any number of subelements of the <format> type. This means, that a plug-in is capable of creating any number of export formats.

XML depiction in the info.xml file:

.. code-block:: xml

    <ExportFormat>
        <Format>
            <Name>Google Base (plug-in)</Name>
        <FileName>googlebase.txt</FileName>
        <Header>link    title    description    price    imagelink    producttype    id    availability    status    shipping    mpn    ean</Header>
        <Content><![CDATA[{$Artikel->cDeeplink}    {$Artikel->cName|truncate:70}    {$Artikel->cBeschreibung}    {$Artikel->Preise->fVKBrutto} {$Waehrung->cISO}    {$Artikel->Artikelbild}    {$Artikel->Kategoriepfad}    {$Artikel->cArtNr}    {if $Artikel->cLagerBeachten == 'N' || $Artikel->fLagerbestand > 0}Auf Lager{else}Nicht auf Lager{/if}    ARTIKELZUSTAND_BITTE_EINTRAGEN    DE::Standardversand:{$Artikel->Versandkosten}    {$Artikel->cHAN}    {$Artikel->cBarcode}]]></Content>
        <Footer></Footer>
        <Encoding>ASCII</Encoding>
        <VarCombiOption>0</VarCombiOption>
        <SplitSize></SplitSize>
        <OnlyStockGreaterZero>N</OnlyStockGreaterZero>
        <OnlyPriceGreaterZero>N</OnlyPriceGreaterZero>
        <OnlyProductsWithDescription>N</OnlyProductsWithDescription>
        <ShippingCostsDeliveryCountry>DE</ShippingCostsDeliveryCountry>
        <EncodingQuote>N</EncodingQuote>
        <EncodingDoubleQuote>N</EncodingDoubleQuote>
        <EncodingSemicolon>N</EncodingSemicolon>
        </Format>
    </ExportFormat>

+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| Element name                       | Description                                                                                                 |
+====================================+=============================================================================================================+
| ``<Name>``                         | Export format name                                                                                          |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<FileName>``                     | File name without indication of the path to which the items are to be exported                              |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Header>``                       | Export file header                                                                                          |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Content>``                      | Export format (Smarty)                                                                                      |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<footer>``                       | Export file footer                                                                                          |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Encoding>``                     | ASCII or UTF-8 encoding of the export file                                                                  |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<VarCombiOption>``               | 1 = Export parent and child item / 2 =Export parent item only / 3 = Export child item only                  |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<SplitSize>``                    | Size of the files into which the export is to be split (into megabytes)                                     |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<OnlyStockGreaterZero>``         | Only products with stock greater than 0                                                                     |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<OnlyPriceGreaterZero>``         | Only products with prices greater than 0                                                                    |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<OnlyProductsWithDescription>``  | Only products with descriptions                                                                             |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<ShippingCostsDeliveryCountry>`` | Destination country shipping costs (ISO-Code)                                                               |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<EncodingQuote>``                | Quote encoding                                                                                              |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<EncodingDoubleQuote>``          | Double quote encoding                                                                                       |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<EncodingSemicolon>``            | Semicolon encoding                                                                                          |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+

(*) Mandatory field

The following example demonstrates how a plug-in export format might look:

.. code-block:: xml

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshopplugin>
        <Name>Export format</Name>
        <Description>Export format example</Description>
        <Author>JTL-Software-GmbH</Author>
        <URL>http://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>500</ShopVersion>
        <PluginID>jtl_export</PluginID>
        <Version>1.0.0</Version>
        <Install>
            <ExportFormat>
                <Format>
                    <Name>Google Base (plug-in)</Name>
                    <FileName>googlebase.txt</FileName>
                    <Header>link    title    description    price    imagelink    producttype    id    availability    status    shipping    mpn    ean</Header>
                    <Content><![CDATA[{$Artikel->cUrl}    {$Artikel->cName|truncate:70}    {$Artikel->cBeschreibung}    {$Artikel->Preise->fVKBrutto} {$Waehrung->cISO}    {$Artikel->Artikelbild}    {$Artikel->Kategoriepfad}    {$Artikel->cArtNr}    {if $Artikel->cLagerBeachten == 'N' || $Artikel->fLagerbestand > 0}Auf Lager{else}Nicht auf Lager{/if}    ARTIKELZUSTAND_BITTE_EINTRAGEN    DE::Standardversand:{$Artikel->Versandkosten}    {$Artikel->cHAN}    {$Artikel->cBarcode}]]></Content>
                    <Footer></Footer>
                    <Encoding>ASCII</Encoding>
                    <VarCombiOption>0</VarCombiOption>
                    <SplitSize></SplitSize>
                    <OnlyStockGreaterZero>N</OnlyStockGreaterZero>
                    <OnlyPriceGreaterZero>N</OnlyPriceGreaterZero>
                    <OnlyProductsWithDescription>N</OnlyProductsWithDescription>
                    <ShippingCostsDeliveryCountry>DE</ShippingCostsDeliveryCountry>
                    <EncodingQuote>N</EncodingQuote>
                    <EncodingDoubleQuote>N</EncodingDoubleQuote>
                    <EncodingSemicolon>N</EncodingSemicolon>
                </Format>
            </ExportFormat>
        </Install>
    </jtlshopplugin>


.. _label_aufbau_portlets:

Portlets (as of JTL-Shop 5.0.0)
-------------------------------

Plug-ins can also provide :doc:`Portlets </shop_plugins/portlets>` for the *OnPageComposer*.

**As of JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 6-9

   plugins/[PluginName]/
   ├── adminmenu
   │   └── ...
   ├── frontend
   │   └── ...
   ├── Portlets
   │   └── MyPortlet
   │       ├── MyPortlet.tpl
   │       ├── MyPortlet.php
   │       └── ...
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...

Publishing of the new portlets is carried out via XML in the ``info.xml`` file. |br|
You can find more information on this in the ":ref:`label_infoxml_portlets`" section.

Everything that belongs to a portlet is located in its own directory. |br|
You can read about how such a portlet subdirectory might look in detail
in the section :doc:`Portlets </shop_plugins/portlets>`.

.. _label_aufbau_blueprints:

Blueprints (as of JTL-Shop 5.0.0)
---------------------------------

Likewise, plug-ins can also define blueprints, which are *compositions of individual portlets*. |br|
To see how this is communicated to the online shop via the ``info.xml`` file, see section ":ref:`label_infoxml_blueprints`".  

**As of JTL-Shop 5.x:**

.. code-block:: console
   :emphasize-lines: 6-8

   plugins/[PluginName]/
   ├── adminmenu
   │   └── ...
   ├── frontend
   │   └── ...
   ├── blueprints
   │   ├── image_4_text_8.json
   │   └── text_8_image_4.json
   ├── info.xml
   ├── README.md
   ├── Bootstrap.php
   └── ...


----


Changes from previous versions up to JTL-Shop 5.x
-------------------------------------------------

Here is a brief overview of the changes for plug-ins made since JTL-Shop 5.x:

* New installation directory: ``<SHOP-ROOT>/plugins/<PLUGIN-ID>/``
* No more``version/<VERSION>/`` directory
* XML root ``<jtlshopplugin>``, in place of``<jtlshop3plugin>``
* ``<Version>``nodes are omitted as ``<Install>`` subnodes
* ``<CreateDate>`` and``<Version>`` must now be indicated as ``<jtlshopplugin>``subnodes  and no longer
  as ``<Install><Version>`` subnodes
* Plug-ins will have the ``Plugin\<PLUGIN-ID>`` namespace
* Plug-ins can run migrations but not SQL files
* Widget classes correspond to the class defined in the ``info.xml`` file and do not require any further conventions
* Plug-ins can offer localisations
* Plug-ins can define portlets and blueprints
