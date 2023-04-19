The ``info.xml`` file
=====================

.. |br| raw:: html

   <br />

In the root directory of a plug-in is the xml file ``info.xml``. |br|
All plug-in information is stored in this file.

The file contains, apart from the plug-in name, a description, and the
author’s name, also **all** technical information. This consists of paths to resources, used hooks, language variables, settings elements,
and more. |br|

.. hint::

    This file is the most important element of a plug-in, as it is responsible for both installation and updates.

A plug-in can be divided into the following main components that are defined via ``info.xml``:

* General plug-in information
* Versions
* Admin menu with *custom* and *setting links*
* Payment methods
* Front end links
* Language variables
* Email templates
* Plug-in boxes
* Plug-in licencing
* Static resources

If areas in the plug-in are not needed, you should omit the entire block. |br|
Any global information cannot be omitted.

The body
--------

The main element constitutes the body of the XML file. **For both JTL-Shop 3 and JTL-Shop 4** it
is called ``<jtlshop3plugin>``. |br|
**As of JTL-Shop 5.x** it is called ``<jtlshopplugin>``.

**JTL-Shop 4.x and earlier:**

.. code-block:: xml

  <jtlshop3plugin>
    ...
  </jtlshop3plugin>

**As of JTL-Shop 5.x:**

.. code-block:: xml

  <jtlshopplugin>
    ...
  </jtlshopplugin>

General plug-in information
---------------------------

After the body of the XML file, comes all general information that are suffixed as child elements.

.. code-block:: xml

 <jtlshop3plugin>
    <Name></Name>
    <Description></Description>
    <Author></Author>
    <URL></URL>
    <XMLVersion></XMLVersion>
    <ShopVersion></ShopVersion>
    <PluginID></PluginID>
 </jtlshop3plugin>


+----------------------+-----------------------------------------------------+
| Element name         | Function                                            |
+======================+=====================================================+
| ``<Name>`` *         | plug-in name (``[a-zA-Z0-9_]``)                     |
+----------------------+-----------------------------------------------------+
| ``<Description>``    | plug-in description                                 |
+----------------------+-----------------------------------------------------+
| ``<Author>``         | plug-in author                                      |
+----------------------+-----------------------------------------------------+
| ``<URL>``            | link to plug-in author                              |
+----------------------+-----------------------------------------------------+
| ``<XMLVersion>`` *   | ``info.xml`` version (``[0-9]{3}``)                 |
+----------------------+-----------------------------------------------------+
| ``<ShopVersion>``    | minimum JTL-Shop version |br|                       |
|                      | (>= 300, < 400, >= 500 or even *5.0.0-beta.3*)      |
+----------------------+-----------------------------------------------------+
| ``<MinShopVersion>`` | as of 5.0.0 - minimum version of JTL-Shop 5         |
+----------------------+-----------------------------------------------------+
| ``<MaxShopVersion>`` | as of 5.0.0 - maximum version of JTL-Shop 5         |
+----------------------+-----------------------------------------------------+
| ``<Shop4Version>``   | Mindestversion von JTL-Shop 4 (>= 400)              |
+----------------------+-----------------------------------------------------+
| ``<PluginID>`` *     | plug-in identifier (``[a-zA-Z0-9_]``)               |
+----------------------+-----------------------------------------------------+
| ``<Icon>``           | icon file name                                      |
+----------------------+-----------------------------------------------------+
| ``<Version>``        | as of JTL-Shop 5.0.0 - plug-in version (``[0-9]+``) |
+----------------------+-----------------------------------------------------+
| ``<CreateDate>``     | as of 5.0.0 - date created (YYYY-MM-DD)             |
+----------------------+-----------------------------------------------------+
| ``<ExsID>``          | as of 5.0.0 - ExtensionStore ID                     |
+----------------------+-----------------------------------------------------+

(*)Mandatory field

Name
""""

The plug-in name is displayed in the plug-in manager and in the automatically generated menus in the back end. This is
used to identify the plug-in.

Description
"""""""""""

The description is displayed in the plug-in manager under the "Available" tab located below the plug-in name. It contains
a short description of the plug-in’s function.

Author
""""""

The author’s name will be displayed in the admin menu of the plug-in. This can either be the name of a business or a
private person.

URL
"""

The URL should include a link to the developer or a dedicated plug-in page so that the customer can quickly
and easily access further information or support.

XMLVersion
""""""""""

Since the requirements for the plug-in system can change over time, the XML installation file itself
can also change. Therefore, indicating the XML version is very important, in order to have the right parameters
available for your own plug-in.

ShopVersion
"""""""""""

*ShopVersion* indicates the minimum JTL-Shop version required. In the case that it is higher than
the current installed version of the online shop, an error message will pop up in the back end and the
plug-in will not be able to be installed. Falls nur dieser Wert, nicht aber ``Shop4Version``, konfiguriert wurde, erscheint in JTL-Shop 4.00+
der Hinweis, dass das Plugin möglicherweise in dieser Version nicht funktioniert. Es kann jedoch trotzdem installiert
werden. |br|
The explicit specification of a single version number is possible, but only makes sense temporarily for
developer purposes (see e.g.: *5.0.0-beta.3*)
**As of JTL-Shop 5.0.0 this tag will be replaced with <MinShopVersion> **

MinShopVersion
""""""""""""""

As of Shop 5.0.0 *MinShopVersion* corresponds to the old *ShopVersion* tag.

MaxShopVersion
""""""""""""""

*MaxShopVersion* indicates the maximum JTL-Shop version that is supported. If the current version
is higher than this, a warning will pop up in the back end.

Shop4Version
""""""""""""

*Shop4Version* gibt die Mindest-Version für JTL-Shop 4 an. Wurde nur dieser Wert und nicht ``ShopVersion`` konfiguriert,
ist eine Installation nur in JTL-Shop 4.x möglich. |br|
**Ab JTL-Shop 5.0.0 wird dieser Tag nicht mehr unterstützt!**

Plug-in ID
"""""""""

The plug-in ID gives a plug-in in the online shop a unique identity.  |br|
Be sure to give a meaningful and unique ID for your own plug-ins,
so that similar-sounding plug-ins of other developers do not clash.

Sample ID for a plug-in: "*SoftwareCompany_PluginName*"

**Naming convention:**
Only characters ``a-z`` or ``A-Z`` and ``0-9``, as well as underscores, are permitted. |br|
Periods and hyphens are not permitted.

As of JTL-Shop 5.0.0, the plug-in ID also corresponds to the automatically assigned PSR-4 namespace (led by the prefix ``Plugin\``) for the entire plug-in.
 |br|
Make sure that the plug-in folder name matches the plug-in ID. A plug-in with the plug-in ID "*mycompany_someplugin*" therefore gets the namespace
``plugin\mycompany_someplugin``.

Icon
""""

Not yet implemented, but planned in the future to provide a better overview.

Version
"""""""

As of JTL-Shop 5.x, this will be a requirement to define the plug-in version.

CreateDate
""""""""""

As of JTL-Shop 5.x, this will be a requirement to define the date of creation of the respective plug-in version. |br|
The date must be indicated in ``YYYY-MM-DD`` format, as in "*2019-03-21*” for March 21st, 2019.

ExsID
"""""

As of JTL-Shop 5.0.0, the ``ExsID`` must be indicated for all plug-ins that are to be distributed via JTL-Store.
 |br|
You can find the ``ExsID`` in the customer centre after you have created an extension for the marketplace there.

Install-Block
"""""""""""""

After the general plug-in information, the installation block follows. This looks as follows:

.. code-block:: xml

    <Install>

    </Install>

All information related to the plug-in is introduced in this block as a child element.


.. _label_infoxml_versionierung:

Versioning
----------

You can find out what the directory structure looks like, according to the definition,
in the "Structure" section under, ":ref:`label_aufbau_versionierung`".

Bis JTL-Shop 4.x
""""""""""""""""

Ein Plugin kann beliebig viele Versionen beinhalten. Die Versionierung fängt bei Version 100 an und wird dann
mit 101, 102 usw. weitergeführt. Es muss mindestens ein Block mit der Version 100 vorhanden sein.

.. code-block:: xml

    <Version nr="100">
        <CreateDate>2015-05-17</CreateDate>
    </Version>

Es besteht zu jeder Version die Möglichkeit, eine SQL-Datei anzugeben, die bei der Installation bzw. Aktualisierung
ausgeführt wird. Hierbei gilt es, die Pluginverzeichnisstruktur für SQL-Dateien zu beachten.

.. code-block:: xml

    <Version nr="100">
        <SQL>install.sql</SQL>
        <CreateDate>2016-05-17</CreateDate>
    </Version>

+-------------+-----------------------------------------------+
| Elementname | Funktion                                      |
+=============+===============================================+
| nr*         | Versionsnummer des Plugins (``[0-9]+``)       |
+-------------+-----------------------------------------------+
| SQL         | SQL-Datei                                     |
+-------------+-----------------------------------------------+
| CreateDate  | Erstellungsdatum der Version (``YYYY-MM-DD``) |
+-------------+-----------------------------------------------+

(*)Mandatory field

Lesen Sie hierzu unter Aufbau auch den Abschnitt ":ref:`label_infoxml_sql`".

Falls weitere Versionen zu einem Plugin existieren, werden diese untereinander aufgeführt.

.. code-block:: xml

    <Version nr="100">
        <CreateDate>2015-03-25</CreateDate>
    </Version>
    <Version nr="101">
        <CreateDate>2015-04-15</CreateDate>
    </Version>

As of JTL-Shop 5.x
""""""""""""""""""

**As of JTL-Shop 5.0.0, this block is no longer necessary!**

In the ``info.xml`` files, now only the following considerably simplified structure exists:

.. code-block:: xml

    <jtlshopplugin>
        ...
        <CreateDate>2018-11-13</CreateDate>
        <Version>1.0.0</Version>
        ...
    </jtlshopplugin>


.. _label_infoxml_hooks:

Plug-in hooks
-------------

After the versioning comes the ``<Hooks>`` element. This element defines the places in the online shop where the plug-in
has to execute code.

The *front end link* and the *payment methods* do not require explicit hook data
as they are linked to a specific hook by the system.

**Example:**

.. code-block:: xml

    <Hooks>
        <Hook id="129">onlineuser.php</Hook>
        <Hook id="130">managemenet.php</Hook>
    </Hooks>

The *ID* uniquely identifies a specific location in the code of JTL-Shop. The specified PHP file will then be executed
in the *ID* hook. |br|
If, for example, you wish to change some members after the creation of an item object in the object,
you can then use the corresponding hook to do this.

+----------------+----------------------------------------------------------------------------+
| Element name    | Function                                                                  |
+================+============================================================================+
| ``<id>`` *     | unambiguous hook ID (``[0-9]+``)                                           |
+----------------+----------------------------------------------------------------------------+
| ``<priority>`` | priority (``[0-9]+``)                                                      |
+----------------+----------------------------------------------------------------------------+
| ``<Hook>``     | PHP file in the ``frontend/`` folder, that is executed at ID               |
+----------------+----------------------------------------------------------------------------+

(*) Mandatory field

If no hooks are needed in the plug-in, you can omit the entire hook container.

You can find a list of the hook IDs here: “:doc:`Hook List </shop_plugins/hook_list>`". |br|
You can find further information on the hook system of the online shop at ":doc:`/shop_plugins/hooks`".

As of JTL-Shop 5.x, there is a new alternative to the familiar hooks in JTL-Shop - the *EventDispatcher*. |br|
For more information on how to make use of this new feature, see
the "Bootstrapping" section under ":ref:`label_bootstrapping_eventdispatcher`".

.. _label_infoxml_license:

Licencing
---------

When creating commercial plug-ins for JTL-Shop, another issue that arises is how to secure one's own plug-in against unauthorized sharing and use.


A plug-in can tell the online shop via ``info.xml`` that it is under a certain licence and that this
must be checked. |br|
The online shop provides an interface class for this purpose, that can use the plug-in to overwrite a
certain licence method. This method is then always checked when the plug-in is called up.

You must implement how and by what means the plug-in checks its licence. |br|
At the end of the method, the system must be informed of whether the check was successful or not.

To tell the online shop that a licence check is required, add the following elements to
the ``info.xml``:

.. code-block:: xml

    <LicenceClass>PluginLicence</LicenceClass>
    <LicenceClassFile>PluginLicence.php</LicenceClassFile>

+------------------------+------------------------------------------------------------------------------------------------------+
| Element name           | Description                                                                                          |
+========================+======================================================================================================+
| ``<LicenceClass>``     | plug-in’s licence-checking class, which comes from the shop’s ``PluginLizenz`` interface class       |
+------------------------+------------------------------------------------------------------------------------------------------+
| ``<LicenceClassFile>`` | file name of the plug-in’s licence-checking class                                                    |
+------------------------+------------------------------------------------------------------------------------------------------+

(*) Mandatory field

For information on where to store the necessary files, see the "Structure" section,
under “:ref:`label_aufbau_license`”.

**Up to JTL-Shop 4.x**

**Example:**

.. code-block:: xml
   :emphasize-lines: 9,10

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshop3plugin>
        <Name>Lizenz-Beispiel</Name>
        <Description>Ein einfaches Beispiel</Description>
        <Author>JTL-Software-GmbH</Author>
        <URL>https://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>300</ShopVersion>
        <PluginID>jtl_license_example</PluginID>
        <LicenceClass>jtl_license_examplePluginLicence</LicenceClass>
        <LicenceClassFile>class.PluginLicence.php</LicenceClassFile>
        <Install>
            ...
        </Install>
    </jtlshop3plugin>

Die Lizenzprüfungsklasse muss im Ordner ``licence/`` liegen, der sich wiederum im Ordner der jeweiligen Pluginversion
befindet, beispielsweise: ``[pluginname]/version/100/licence/``.

In unserem Beispiel heißt die Lizenzprüfungsklasse des Plugins ``jtl_license_examplePluginLicence`` und befindet sich
in der Datei ``class.PluginLicence.php``.

**Example:**

.. code-block:: php

    <?php

    class jtl_license_exmplePluginLicence implements PluginLizenz
    {
        /**
        * @param string $cLicence
        * @return bool - true if successfully validated
        */
        public function checkLicence($cLicence)
        {
            return $cLicence === '123';
        }
    }

Wie im Beispiel zu erkennen ist, erbt die in der ``info.xml`` angegebene Lizenzprüfungsklasse
``jtl_license_exmplePluginLicence`` vom Interface ``PluginLizenz``. Dieses Interface schreibt die Implementierung der
Methode ``checkLicence()`` vor. |br|
In unserem Beispiel fragt diese Methode den Parameter ``$cLicence`` ab. Die Methode muss den boolschen Wert
*true* zurückgeben, damit das System dieses Plugin ausführt.

**As of JTL-Shop 5.x**

In JTL-Shop version 5.x, the methodology of interface preset has remained the same compared to
previous versions. However, support for *namespaces* has been added. |br|

**Example**:

.. code-block:: xml
   :emphasize-lines: 11,12

    <?xml version='1.0' encoding="UTF-8"?>
    <jtlshopplugin>
        <Name>SimpleExample</Name>
        <Description>A simple example</Description>
        <Author>JTL-Software-GmbH</Author>
        <URL>https://www.jtl-software.de</URL>
        <XMLVersion>102</XMLVersion>
        <ShopVersion>500</ShopVersion>
        <PluginID>jtl_demo_plugin</PluginID>
        <Version>1.0.0</Version>
        <CreateDate>2019-02-26</CreateDate>
        <LicenceClass>PluginLicence</LicenceClass>
        <LicenceClassFile>PluginLicence.php</LicenceClassFile>
        <Install>
            ...
        </Install>
    </jtlshopplugin>

The corresponding licence-checking class with *namespace* will look as follows:

.. code-block:: php
   :emphasize-lines: 3

    <?php

    namespace Plugin\[PluginID]\licence;

    use JTL\Plugin\LicenseInterface;

    class PluginLicence implements LicenseInterface
    {
        /**
         + @param string $cLicence
         + @return mixed
         */
        public function checkLicence($cLicence)
        {
            // ...
            return (bool)$isValid;
        }
    }

It is still possible to encode the plug-in licence class with "*ionCube*" to prevent
manipulation.

.. important::
    Since version 4.00, JTL-Shop no longer requires *Ioncube*. |br|
    Subsequently, you cannot guarantee that potential customers have *Ioncube* installed on their servers.


.. _label_infoxml_frontend_res:

Front end resources
-------------------

By using the XML tags ``<CSS>`` and ``<JS>``, plug-in developers have the option to include their own resources
in the plug-in, which are integrated into all pages on the front end. |br|
This has the advantage that they do not have to be individually integrated via the template
or ``pq()`` (“phpQuery”). Furthermore, they can be directly minified.

.. code-block:: xml
   :emphasize-lines: 3,5,13,15

    <Install>
        ...
        <CSS>
            <file>
                <name>foo.css</name>
                <priority>4</priority>
            </file>
            <file>
                <name>bar.css</name>
                <priority>9</priority>
            </file>
        </CSS>
        <JS>
            <file>
                <name>foo.js</name>
                <priority>8</priority>
                <position>body</position>
            </file>
            <file>
                <name>bar.js</name>
            </file>
        </JS>
    </Install>

*CSS* file:

+----------------+-----------------------------------------------------------------------------------------------+
| Element name   | Description                                                                                   |
+================+===============================================================================================+
| ``<name>`` *   | file name in ``css/`` subfolder (see also: :ref:`Structure <label_aufbau_frontend_res>`)      |
+----------------+-----------------------------------------------------------------------------------------------+
| ``<priority>`` | priority from 0\-10, meaning the higher the number, the later the file will be integrated     |
+----------------+-----------------------------------------------------------------------------------------------+

*JS* file:

+----------------+----------------------------------------------------------------------------------------------+
| Element name   | Description                                                                                  |
+================+==============================================================================================+
| ``<name>`` *   | file name in the subfolder ``js/`` (see also: :ref:`Structure <label_aufbau_frontend_res>`)  |
+----------------+----------------------------------------------------------------------------------------------+
| ``<priority>`` | priority from 0\-10, meaning the higher the number, the later the file will be integrated    |
+----------------+----------------------------------------------------------------------------------------------+
| ``<position>`` | the position in the DOM to which the file is integrated, "body" or "head”                    |
+----------------+----------------------------------------------------------------------------------------------+

(*) Mandatory field

All files specified here must be located in the ``frontend/css/`` or ``frontend/js/`` subfolder.
You can find an example of this in the "Structure" section, under ":ref:`label_aufbau_frontend_res`". |br|
JavaScript files can be inserted optionally into the header or body via the "*position*" attribute.
They can be modified in the sequence via "*priority*" (0 = highest, 5 = default).

If there is a ``_custom.css`` counterpart to a CSS file included using this method in the same folder,
this will be included **additionally** after the actual CSS file. |br|
Following the example above, this would be ``foo_custom.css`` or ``bar_custom.css``. |br|

.. attention::

    This procedure is not supported for JavaScript files.

Minify
""""""

These files are also minified if the theme function is activated accordingly. |br|
In the theme, the Smarty variables ``$cPluginJsHeadd_arr``, ``$cPluginCss_arr`` and ``$cPluginJsBody_arr``
must be checked or issued for this purpose.

**Example:**

.. code-block:: html+smarty

    {*
        with activated minify, header.tpl
    *}
    {if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0}
        <link type="text/css" href="{$PFAD_MINIFY}/g=plugin_css" rel="stylesheet" media="screen" />
    {/if}
    {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
        <script type="text/javascript" src="{$PFAD_MINIFY}/g=plugin_js_head"></script>
    {/if}

    {*
        footer.tpl:
    *}
    {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
        <script type="text/javascript" src="{$PFAD_MINIFY}/g=plugin_js_body"></script>
    {/if}


    {*
        without minify, header.tpl
    *}
    {foreach from=$cJS_arr item="cJS"}
        <script type="text/javascript" src="{$cJS}"></script>
    {/foreach}
    {if isset($cPluginJsHead_arr)}
        {foreach from=$cPluginJsHead_arr item="cJS"}
            <script type="text/javascript" src="{$cJS}"></script>
        {/foreach}
    {/if}

    {*
        footer.tpl
    *}
    {if isset($cPluginJsHead_arr)}
        {foreach from=$cPluginJsBody_arr item="cJS"}
            <script type="text/javascript" src="{$cJS}"></script>
        {/foreach}
    {/if}

Object cache
------------

In the event that certain contents of the object cache are to be deleted during installation (for example, if the plug-in is
supposed to modify item data), a list of *tags* that
represent the individual caches which are to be reset after installation, can be specified in the
element ``<FlushTags>``.

.. code-block:: xml

    <FlushTags>CACHING_GROUP_CATEGORY, CACHING_GROUP_ARTICLE</FlushTags>

You can find further information on caching and the available *tags* in section ":doc:`Cache </shop_plugins/cache>`".

.. _label_infoxml_boxen:

Boxes
-----

With the JTL *box manager*, shop operators can simply and quickly add,
delete, or move boxes in the online shop.

A plug-in is also capable of creating a new box type. Where the templates for these boxes in the plug-ins are to be placed,
you can find in the "Structure" section, under ":ref:`label_aufbau_boxen`". |br|
This new box can be selected in the box manager and be assigned to a location in the JTL-Shop. The content of the box will be controlled
via the template that is assigned to the box. There, any amount of content can be displayed.

You can create a new box type by creating a new XML node in the ``info.xml`` file.

.. code-block:: xml
   :emphasize-lines: 3-5

   <Install>
       ...
       <Boxes>
            ...
       </Boxes>
       ...
   </Install>

Within this node, there can be any number of subelements of type ``<Box>``. |br|
This means, that a plug-in is capable of creating any number of boxes.

Always assign unique box names so that they do not overlap with other plug-ins.

**Example:**

.. code-block:: xml

    <Boxes>
        <Box>
            <Name>ExampleBoxFromExamplePlugin</Name>
            <Available>0</Available>
            <TemplateFile>example_box.tpl</TemplateFile>
        </Box>
    </Boxes>

+--------------------+------------------------------------------------------------------+
| Element name       | Description                                                      |
+====================+==================================================================+
| ``<Name>``         | name of box type                                                 |
+--------------------+------------------------------------------------------------------+
| ``<Available>``    | page type in which the box will be displayed |br|                |
|                    | (e.g.: 0= each page, 1= item details, 2= item list, etc.)        |
+--------------------+------------------------------------------------------------------+
| ``<TemplateFile>`` | template file with box content                                   |
+--------------------+------------------------------------------------------------------+

Soll beispielsweise eine Box auf der Artikeldetailseite und in der Artikelliste des EVO-Templates angezeigt werden,
würden Sie diese Box in der ``info.xml`` zweimal definieren - für jeden dieser Seitentypen:

.. code-block:: xml
   :emphasize-lines: 4,9

    <Boxes>
        <Box>
            <Name>MyBox 1</Name>
            <Available>1</Available>
            <TemplateFile>box_1.tpl</TemplateFile>
        </Box>
        <Box>
            <Name>MyBox 1</Name>
            <Available>2</Available>
            <TemplateFile>box_1.tpl</TemplateFile>
        </Box>
    </Boxes>

``Available`` gibt dabei den Seitentyp an, auf dem die Box dargestellt werden soll. Die entsprechenden Seitentypen
finden Sie in der ``includes/defines_inc.php``.


.. _label_infoxml_widgets:

Widgets
-------

Plug-in widgets enable you to implement your own widgets in the back end dashboard of the JTL-Shop easily and quickly.

A plug-in is capable of creating an *AdminWidget*.
The content of this widget is controlled via a class and a template. Therefore, any content can be displayed.
 For information on where to place the files,
see the "Structure" section, under ":ref:`label_aufbau_widgets`".

You can create a new *AdminWidget* by inserting the following new XML nodes in the ``<Install>`` XML container into
your ``info.xml`` file:

.. code-block:: xml
   :emphasize-lines: 3-5

   <Install>
       ...
       <AdminWidget>
           ...
       </AdminWidget>
       ...
   </Install>

Any number of subelements from type ``<Widget>`` could be in this XML container.
This means that a plug-in is capable of creating any number of *AdminWidgets*.

**Example:**

.. code-block:: xml

    <AdminWidget>
        <Widget>
            <Title>Serverinfo (Plugin)</Title>
            <Class>Info</Class>
            <Container>center</Container>
            <Description>plug-in example</Description>
            <Pos>1</Pos>
            <Expanded>1</Expanded>
            <Active>1</Active>
        </Widget>
    </AdminWidget>

+-------------------+-----------------------------------------------------------------------+
| Element name       | Description                                                          |
+===================+=======================================================================+
| ``<Title>`` *     | adminWidget title heading                                             |
+-------------------+-----------------------------------------------------------------------+
| ``<Class>`` *     | class name of the PHP class that provides widget content              |
+-------------------+-----------------------------------------------------------------------+
| ``<Container>`` * | position of dashboard container. Values: center, left, right          |
+-------------------+-----------------------------------------------------------------------+
| ``<Description>`` | description of admin widget                                           |
+-------------------+-----------------------------------------------------------------------+
| ``<Pos>`` *       | vertical position in container. Integer (1 = above)                   |
+-------------------+-----------------------------------------------------------------------+
| ``<Expanded>`` *  | AdminWidget should be expanded or minimized. Integer, 0 or 1.         |
+-------------------+-----------------------------------------------------------------------+
| ``<Active>`` *    | AdminWidget directly viewable in the dashboard. Integer, 0 or 1.      |
+-------------------+-----------------------------------------------------------------------+

(*) Mandatory field

Widgets bis JTL-Shop 4.x
""""""""""""""""""""""""

Der Klassenname wird bis einschließlich JTL-Shop 4.x wie folgt generiert:

* Annahme: Das XML schreibt vor, die Klasse heißt `"<Class>Info</Class>"`
  und die Plugin-ID lautet ``<PluginID>jtl_test</PluginID>``.

* Dann muss im Verzeichnis ``version/[Versionsnummer]/adminmenu/widget/`` des Plugins die folgende Klasse
  mit Namen ``class.WidgetInfo_jtl_test.php`` liegen |br|
  (Regel: ``class.Widget + <Class> + _ + <PluginID> + .php``, siehe auch:
  Abschnitt ":ref:`Aufbau / Widgets<label_aufbau_widgets>`")

* Die Klasse in der Datei muss den Namen ``Widget + <Class> +_ + <PluginID>`` tragen
  und muss von der Basisklasse ``WidgetBase`` abgeleitet sein. |br|

**Example:**

.. code-block:: php

   <?php

   class WidgetInfo_jtl_test extends WidgetBase
   {
   }

Widgets as of JTL-Shop 5.x
""""""""""""""""""""""""""

As of JTL-Shop 5.0.0, classes will be generated as follows:

* Scenario: The XML specifies that the class be named ``<Class>Info</Class>``
   and the plug-in be called ``<PluginID>jtl_test</PluginID>``.

* So, the ``Info.php`` file must be located in the ``/adminmenu/widget/`` directory of the plug-in
  (see also: Section ":ref:`Aufbau / Widgets <label_aufbau_widgets>`")

The class in the file must contain the name "*Info*" and be derived from the "*AbstractWidget*" base class.

* The class has to be located in the ``<PluginID>`` namespace.

**Example:**

.. code-block:: php

    <?php

    namespace jtl_test;

    use JTL\Widgets\AbstractWidget;

    class Info extends AbstractWidget
    {
    }

.. _label_infoxml_portlets:

Portlets (as of 5.0.0)
----------------------

As of Shop 5.0.0, plug-ins can also define :doc:`Portlets </shop_plugins/portlets>` for the *OnPageComposer*. |br|
This happens via the XML nodes ``<Portlets>``, which can contain an unlimited number of sub-nodes of the
type ``<Portlet>``.

.. code-block:: xml

    <Install>
        ...
        <Portlets>
            <Portlet>
                <Title>MyTitle</Title>
                <Class>MyClass</Class>
                <Group>content</Group>
                <Active>1</Active>
            </Portlet>
            <Portlet>
                <Title>MyOtherTitle</Title>
                <Class>MyOtherClass</Class>
                <Group>content</Group>
                <Active>1</Active>
            </Portlet>
        </Portlets>
        ...
    </Install>

``<Portlet>``:

+----------------+-------------------------------------------------------------------------------------+
| Element name    | Description                                                                        |
+================+=====================================================================================+
| ``<Title>`` *  | portlet title (localisable with portable object file),                              |
|                | as displayed in the "*OPC-Editor*" (front end) and in the "*OnPage Composer*" in    |
|                | the back end                                                                        |
+----------------+-------------------------------------------------------------------------------------+
| ``<Class>`` *  | portlet class name                                                                  |
+----------------+-------------------------------------------------------------------------------------+
| ``<Group>`` *  | name of the group to which the portlet in the portlet palette is assigned           |
+----------------+-------------------------------------------------------------------------------------+
| ``<Active>`` * | status (1 = activated, 0 = deactivated)                                             |
+----------------+-------------------------------------------------------------------------------------+

(*) Mandatory field

Portlets are always comprised of a PHP file with the name ``<Portlet-Class-Name>.php``, which defines a single class
with the name ``<Portlet-Class-Name>``,
that must be located in the namespace ``Plugin\[Plugin-ID]\Portlets\[Portlet-Class-Name]``. |br|
This new portlet class should always be inherited from the OPC portlet class ``JTL\OPC\Portlet`` of the online shop. |br|

**Example:**

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Plugin\jtl_test\Portlets;

    use JTL\OPC\Portlet;

    class MyPortlet extends Portlet
    {
        // ...
    }

You can find information about the correct placement of the relevant file in your plug-in in
the "Structure" section, under ":ref:`label_aufbau_portlets`". |br|
For information on how to further proceed with your new portlets,
see :doc:`Portlets </shop_plugins/portlets>`.

.. _label_infoxml_blueprints:

Blueprints (as of 5.0.0)
------------------------

As of JTL-Shop 5.0.0, plug-ins can also define blueprints, also known as *compositions of individual portlets*.

For this purpose, another node named ``Blueprints`` is defined in the ``Install`` container, which can, in turn, contain
an unlimited number of sub-nodes.

.. code-block:: xml

    <Install>
        ...
        <Blueprints>
           <Blueprint>
               <Name>Image left text right</Name>
               <JSONFile>image_4_text_8.json</JSONFile>
           </Blueprint>
           <Blueprint>
               <Name>Text left image right</Name>
               <JSONFile>text_8_image_4.json</JSONFile>
           </Blueprint>
        </Blueprints>
        ...
    </Install>


Blueprint:

+------------------+----------------------------------------------------------------+
| Element name     | Description                                                    |
+==================+================================================================+
| ``<Name>`` *     | name shown in the OPC control centre                           |
+------------------+----------------------------------------------------------------+
| ``<JSONFile>`` * | JSON file name in the plug-in subdirectory ``blueprints/``     |
+------------------+----------------------------------------------------------------+

(*) Mandatory field

JSON files can be created via the export in the *OPC Editor*. |br|
You can find out more about what this structure below your plug-in looks like,
in the "Structure" section, under ":ref:`label_aufbau_blueprints`".

Consent manager (as of 5.0.0)
-----------------------------

As of JTL-Shop 5.0.0, plug-ins will be able to define entries in the Consent Manager. |br|
To do this, insert the XML nodes ``<ServicesRequiringConsent>`` in the ``info.xml`` file of your
plug-in. This XML node can contain any number of sub-nodes of type ``<Vendor>``.

**Example:**

.. code-block:: xml

    <Install>
        ...
        <ServicesRequiringConsent>
            <Vendor>
                <ID>myItemID</ID>
                <Company>Meine kleine Firma GmbH</Company>
                <Localization iso="GER">
                    <Name>Name meines Eintrags</Name>
                    <Purpose>Tut etwas Tollen</Purpose>
                    <Description>Dies ist die Beschreibung einer Funktionalität, welche Consent erfordert.
                    </Description>
                    <PrivacyPolicy>https://www.example.com/privacy?hl=de</PrivacyPolicy>
                </Localization>
                <Localization iso="ENG">
                    <Name>Name of my item</Name>
                    <Purpose>Does something great</Purpose>
                    <Description>This is a longer description.
                    </Description>
                    <PrivacyPolicy>https://www.example.com/privacy</PrivacyPolicy>
                </Localization>
            </Vendor>
        </ServicesRequiringConsent>
        ...
    </Install>


``<Vendor>``:

+-----------------------+-------------------------------------------------------------------------------------+
| Element name          | Description                                                                         |
+=======================+=====================================================================================+
| ``<ID>`` *            | ID of an element (``[a-zA-Z0-9_]``)                                                 |
+-----------------------+-------------------------------------------------------------------------------------+
| ``<Company>`` *       | company name                                                                        |
+-----------------------+-------------------------------------------------------------------------------------+
| ``<Localization>`` *  | group of translations                                                               |
+-----------------------+-------------------------------------------------------------------------------------+


``<Localization>``:

+------------------------+-------------------------------------------------------------------------------------+
| Element name           | Description                                                                         |
+========================+=====================================================================================+
| ``<Name>`` *           | feature name                                                                        |
+------------------------+-------------------------------------------------------------------------------------+
| ``<Purpose>`` *        | cookies purpose                                                                     |
+------------------------+-------------------------------------------------------------------------------------+
| ``<Description>`` *    | in-depth description of the purpose and function                                    |
+------------------------+-------------------------------------------------------------------------------------+
| ``<PrivacyPolicy>`` *  | external link to the privacy policy                                                 |
+------------------------+-------------------------------------------------------------------------------------+


(*) Mandatory field


Admin menu
----------

All plug-ins that are either not installed (available), faulty, or installed are displayed in the menu item **Plug-in Manager** in
the admin area of the JTL-Shop.
If the option of an admin menu is not desired, then simply omit the entire ``<Adminmenu>`` container.

Faulty plug-ins will be displayed with the relevant error code. |br|
You can find a table with all possible error codes in :doc:`Fehlercodes </shop_plugins/fehlercodes>`.

.. code-block:: xml

    <Adminmenu>
        ...
    </Adminmenu>

If necessary, the child element ``<Customlink>`` (":ref:`label_infoxml_custom_links`") and
``<Settinglink>`` (":ref:`label_infoxml_setting_links`”) will follow in this element. |br|
If there is no ``<Customlink>`` and no ``<Settinglink>``, the ``<Adminmenu>`` container will be ignored.

.. _label_infoxml_custom_links:

Custom links
------------

*Custom links* are displayed in the admin area under the respective plug-in. |br|
By using these links, a plug-in can create pages in the back end with its own content. These pages serve to provide information for the operator
of the online shop. |br|
*Custom links* are shown as tabs in the back end.

.. code-block:: xml

    <Customlink sort="1">
        <Name>Statistics</Name>
        <Filename>stats.php</Filename>
    </Customlink>


+-----------------------+-------------------------------------+
| Element name          | Function                            |
+=======================+=====================================+
| attribute ``sort=`` * | tab sorting number                  |
+-----------------------+-------------------------------------+
| ``<Name>`` *          | tab name (``[a-zA-Z0-9_\-]+``)      |
+-----------------------+-------------------------------------+
| ``<Filename>`` *      | executable PHP file                 |
+-----------------------+-------------------------------------+

(*)Mandatory field

.. _label_infoxml_setting_links:

Setting links
-------------

*Setting links* are tabs that query plug-in settings. |br|
Any number of settings can be created here. Settings can query various values
(e.g. text, numbers, selection from a select box). These settings can be configured by the shop operator in the back end
and then queried in their own plug-in code.

.. code-block:: xml

    <Settingslink sort="2">
        <Name>Settings</Name>
        <Setting type="text" initialValue="Y" sort="4" conf="N">
            <Name>Online Watcher</Name>
            <Description>Online Watcher</Description>
            <ValueName>onlinewatcher</ValueName>
        </Setting>
    </Settingslink>

``<Settinglink>``:

+-----------------------+----------------------------+
| Element name          | Function                   |
+=======================+============================+
| attribute ``sort=`` * | tab sorting number         |
+-----------------------+----------------------------+
| ``<Name>`` *          | tab name                   |
+-----------------------+----------------------------+
| ``<Setting>`` *       | setting element            |
+-----------------------+----------------------------+

(*)Mandatory field

.. _label_infoxml_settingtypes:

``<Setting>``:

+-------------------------------+-------------------------------------------------------------------------------------------+
| Element name                  | Function                                                                                  |
+===============================+===========================================================================================+
| attribute ``type=`` *         | setting type (``text``, ``textarea``, ``selectbox``, ``checkbox``, ``radio``, |br|        |
|                               | ``colorpicker``, ``email``, ``date``, ``time``, ``tel``, ``url`` [as of Shop 5.0:         |
|                               | ``none``])                                                                                |
+-------------------------------+-------------------------------------------------------------------------------------------+
| attribute ``initialValue=`` * | pre-selected setting                                                                      |
+-------------------------------+-------------------------------------------------------------------------------------------+
| attribute ``sort=``           | setting sorting (higher = further down)                                                   |
+-------------------------------+-------------------------------------------------------------------------------------------+
| attribute ``conf=`` *         | Y = true setting, N= caption                                                              |
+-------------------------------+-------------------------------------------------------------------------------------------+
| ``<Name>`` *                  | setting name (``[a-zA-Z0-9_\-]+``)                                                        |
+-------------------------------+-------------------------------------------------------------------------------------------+
| ``<Description>``             | setting description                                                                       |
+-------------------------------+-------------------------------------------------------------------------------------------+
| ``<ValueName>`` *             | name of the setting variable that is used in the PHP code                                 |
+-------------------------------+-------------------------------------------------------------------------------------------+
| ``<SelectboxOptions>``        | optional child element for type = select box                                              |
+-------------------------------+-------------------------------------------------------------------------------------------+
| ``<RadioOptions>``            | optional child element for type = radio                                                   |
+-------------------------------+-------------------------------------------------------------------------------------------+
| ``<OptionsSource>``           | dynamic source for options in check or select boxes                                       |
+-------------------------------+-------------------------------------------------------------------------------------------+

(*)Mandatory field

**As of JTL-Shop 5.0.0** type can also be selected as "``type=none``". Such options are not shown in the
settings tab. |br|
This is useful if a separate display in another tab is to be selected for this option.
The value will be stored in the plug-in instance anyway, so that no circumvention over an individual SQL logic
is necessary. However, you may have to manually invalidate the object cache.

If the type of setting is ``type=selectbox``, specify the child element as ``<SelectboxOptions>``.

.. code-block:: xml

    <SelectboxOptions>
        <Option value="Y" sort=”1”>Yes</Option>
        <Option value="N" sort=”2”>No</Option>
    </SelectboxOptions>

+------------------------+----------------------------------------------+
| Element name           | Function                                     |
+========================+==============================================+
| ``<Option>`` *         | displayed value in the select box option     |
+------------------------+----------------------------------------------+
| attribute ``value=`` * | value of the select box option               |
+------------------------+----------------------------------------------+
| attribute ``sort=``    | sorting the option (higher = further down)   |
+------------------------+----------------------------------------------+

(*)Mandatory field

If the type of setting is ``type=radio``, specify the child element as ``<RadioOptions>``.

.. code-block:: xml

    </RadioOptions>
        <Option value="Y" sort=”1”>Yes</Option>
        <Option value="N" sort=”2”>No</Option>
        <Option value="V" sort=”3”>Maybe</Option>
    </RadioOptions>

+------------------------+----------------------------------------------+
| Element name           | Function                                     |
+========================+==============================================+
| ``<Option>`` *         | displayed value in the radio option          |
+------------------------+----------------------------------------------+
| attribute ``value=`` * | value of the radio option                    |
+------------------------+----------------------------------------------+
| attribute ``sort=``    | sorting the option (higher = further down)   |
+------------------------+----------------------------------------------+

(*)Mandatory field

As of JTL-Shop 4.05, in lieu of or in addition to *RadioOptions* or *SelectboxOptions* you can add the
element ``<OptionsSource>``. Once it is there, the RadioOptions or SelectboxOptions element
will be ignored.

+--------------+---------------------------------+
| Element name | Function                        |
+==============+=================================+
| ``<File>`` * | file name based on admin menu   |
+--------------+---------------------------------+

(*)Mandatory field

This allows dynamic option values to be defined in a PHP file. |br|
This is especially useful in the event that item/category/page/any shop-specific values are to be displayed, instead of static
selection options, like “yes/no” options, for example. |br|
The file specified must deliver an object array, where "*cValue*" and "*cName*", and
optionally "*nSort*", must be present as object members.

The relevant file must be located in the plug-in folder ``adminmenu/``.
(See also: Section :ref:`label_adminmenu_structure`)

**Example of a dynamic option**:

.. code-block:: php

    <?php
        $options = [];
        $option  = new stdClass();

        $option->cValue = 123;
        $option->cName = ‘Value A';
        $option->nSort = 1;
        $options[]     = $option;

        $option        = new stdClass();
        $option->cValue = 456;
        $option->cName = ‘Value B';
        $option->nSort = 2;
        $options[]     = $option;

        $option        = new stdClass();
        $option->cValue = 789;
        $option->cName = ‘Value C';
        $option->nSort = 2;
        $options[]     = $option;

        return $options;

In this example, the 3 selection options "*Value A*", "*Value B*" and "*Value C*" would be available
for selection.


.. _label_infoxml_locale:

Translation of settings
-----------------------

As of JTL-Shop 5.0.0, plug-in options can be multilingual. |br|
This affects the ``<Name>`` and ``<Description>`` nodes in each ``<Setting>`` element, as well as the values of
`<SelectboxOptions>`` and ``<RadioOptions>``.
The respective values can be specified and translated as *msgid* keys in the plug-in's ``base.po``.

In general, you need to create a subfolder
with an associated IETF language tag in the ``locale/`` subfolder of the plug-in for each language you want to translate and create the ``base.po`` file in it. |br|
You can find the corresponding directory structure for this
in the "Structure" section, under ":ref:`label_aufbau_locale`".

**Example:**

Suppose you would like to display the following option in English and German:

.. code-block:: xml

    <Setting type="selectbox" initialValue="Y" sort="1" conf="Y">
        <Name>Do you find this helpful?</Name>
        <Description>Asks a simple yes/no question</Description>
        <ValueName>myplugin_is_helpful</ValueName>
        <SelectboxOptions>
            <Option value="Y" sort=”0”>Yes</Option>
            <Option value="N" sort="1”>No</Option>
            <Option value=”M" sort=”2”>Maybe</Option>
        </SelectboxOptions>
    </Setting>

In this example, we will only ask simple "yes/no" questions in our plug-in settings.

Add the following files to the plug-in root:

* ``locale/de-DE/base.po``
* ``locale/en-US/base.po``

You can find more information on this in the "Structure" section under ":ref:`label_aufbau_locale`".

The contents for *German* could look like this (``de-DE/base.po``):

.. code-block:: pot

    msgid “Yes"
    msgstr "Ja"

    msgid “No"
    msgstr "Nein"

    msgid “Do you find this helpful?"
    msgstr "Finden Sie das hier hilfreich?"

    msgid “Asks a simple yes/no question"
    msgstr "Stellt eine simple Ja/Nein-Frage"


and that for *English* is created accordingly (``en-US/base.po``):

.. code-block:: pot

    msgid “Yes"
    msgstr "Yes"

    msgid “No"
    msgstr "No"

    msgid “Do you find this helpful?"
    msgstr "Do you find this helpful?"

    msgid “Asks a simple yes/no question"
    msgstr "Asks a simple yes/no question"

In our example, “*Maybe*” was intentionally not translated or addressed. |br|
This would then mean that “*Maybe*” is displayed *unaltered* in all languages.

Finally, you just have to compile the .po file with `Poedit <https://poedit.net/>`_ to
the ``base.mo``.

.. note:

    Check box special functions
    ---------------------------

    The plug-in interface can also be used to register check box functions, which are then available as special functions in the check box control.
    

    **Example:**

    .. code-block:: xml

        <CheckBoxFunction>
            <Function>
                <Name>Name of the special function</Name>
                <ID>myspecialfunction</ID>
            </Function>
        </CheckBoxFunction>

    This will write a new entry in the ``tcheckbox function`` table upon plug-in installation.

    If the check box for "*Plug-in special features*" is checked, the respective php file of the
    plug-in will be included.


.. _label_infoxml_frontendlinks:

Front end links
---------------

Via *front end links*, a plug-in can create a link in the JTL-Shop to manage
content. |br|
You can create any number of ``<Link>`` elements. If no *front end link* is specified, then completely omit
the ``<FrontendLink>`` block. |br|

In versions up to JTL-Shop 4.x, links are created in the *Link groups manager* under CMS ("Pages -> Custom pages").
Here, links created by plug-ins can be managed in retrospect. |br|
As of JTL-Shop 5.x, new *front end links*, under "Custom content" -> "Pages", are assigned to the "Hidden" link group.

In order to, for example, make the front end link "JTL Test Page" of the JTL plug-in
"`Demo Plugin <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_test>`_" visible in your online shop, you can move it from
the link group "Hidden" to the link group "Mega menu".
In the mega menu of your online shop, this new front end link will then be displayed as the last entry.

Every link can be *localised* in any language, as needed. |br|
To do this, the ``<LinkLanguage>`` element is used with its ``iso`` attribute. Its contents are written in capital letters
(e.g.: For German = GER).
However, only the languages that the online shop includes will be installed. |br|
If a plug-in has stored fewer languages than those installed in the online shop, all other shop languages are automatically substituted with the default language.


Each front end link requires a Smary template file. |br|
There are two ways to display these contents. |br|
The first option is to display the content in a defined area (*content area*) of the online shop.
 This is done via the ``<Template>``element. |br|
The second option would be to display the contents on a completely new page. For this, you need
the ``<FullscreenTemplate>`` element. |br|

.. important::

    One of two of these variants must be set. |br|
    It is **not** possible to define both display options in the ``info.xml`` **simultaneously**.

In the following example, the Smarty template file ``test_page.tpl``,
which is located in the ``template/`` folder, is loaded in the defined content area of the online shop.

.. code-block:: xml

    <FrontendLink>
        <Link>
            <Filename>test_page.php</Filename>
            <Name>JTL Test Page</Name>
            <Template>test_page.tpl</Template>
            <VisibleAfterLogin>N</VisibleAfterLogin>
            <PrintButton>N</PrintButton>
            <Identifier>jtlTestUniqueIdentifier</Identifier><!-- seit Shop 5.1.0 -->
            <SSL>2</SSL>
            <LinkLanguage iso="GER">
                <Seo>jtl-test-page</Seo>
                <Name>TestPage</Name>
                <Title>TestPage</Title>
                <MetaTitle>TestPage Meta Title</MetaTitle>
                <MetaKeywords>Test,Page,Meta,Keyword</MetaKeywords>
                <MetaDescription>TestPage Meta Description</MetaDescription>
            </LinkLanguage>
        </Link>
    </FrontendLink>

A front end link does not require a specific hook, since the system connects the link automatically to a defined hook.


Link:

+----------------------------+---------------------------------------------------------+
| Element name               | Function                                                |
+============================+=========================================================+
| ``<Filename>`` *           | file to be exported with link                           |
+----------------------------+---------------------------------------------------------+
| ``<Name>`` *               | link name (``[a-zA-Z0-9 ]+``)                           |
+----------------------------+---------------------------------------------------------+
| ``<Template>`` *           | Smarty template file that displays link content         |
+----------------------------+---------------------------------------------------------+
| ``<FullscreenTemplate>`` * | Smarty template file that displays link content         |
+----------------------------+---------------------------------------------------------+
| ``<VisibleAfterLogin>`` *  | display only if user in logged in ([NY]{1,1})           |
+----------------------------+---------------------------------------------------------+
| ``<PrintButton>`` *        | display push button ([NY]{1,1})                         |
+----------------------------+---------------------------------------------------------+
| ``<NoFollow>`` *           | add no follow attribute in the HTML code ([NY]{1,1})    |
+----------------------------+---------------------------------------------------------+
| ``<LinkLanguage>`` *       |                                                         |
+----------------------------+---------------------------------------------------------+
| ``<Identifier>``           | unmodifiable ID, as of 5.1.0 (``[a-zA-Z0-9 ]+``)        |
+----------------------------+---------------------------------------------------------+
| ``<SSL>``                  | 0 or 1 for default, 2 for forced SSL                    |
+----------------------------+---------------------------------------------------------+

LinkLanguage

+-----------------------+--------------------------------------------------+
| Element name          | Function                                         |
+=======================+==================================================+
| ``<iso>`` *           | ISO (``[A-Z]{3}``)                               |
+-----------------------+--------------------------------------------------+
| ``<Seo>`` *           | SEO link name (``[a-zA-Z0-9 ]+``)                |
+-----------------------+--------------------------------------------------+
| ``<Name>`` *          | link name (``[a-zA-Z0-9 ]+``)                    |
+-----------------------+--------------------------------------------------+
| ``<Title>`` *         | link title (``[a-zA-Z0-9 ]+``)                   |
+-----------------------+--------------------------------------------------+
| ``<MetaTitle>`` *     | link meta title (``[a-zA-Z0-9,. ]+``)            |
+-----------------------+--------------------------------------------------+
| ``<MetaKeywords>`` *  | link meta keywords (``[a-zA-Z0-9, ]+``)          |
+-----------------------+--------------------------------------------------+
| ``<MetaDescription>`` | link meta description (``[a-zA-Z0-9,. ]+``)      |
+-----------------------+--------------------------------------------------+

(*) Mandatory field


.. _label_infoxml_paymentmethode:

Payment methods
---------------

The JTL-Shop plug-in system has the ability to implement several payment methods
at the same time. This does not interfere with the JTL-Shop code. |br|
The main element, ``<PaymentMethod>``, will be added under the ``<Install>`` element. This can contain any number
of payment methods (``<Method>``). |br|
In the case that the plug-in should not implement any payment method, the ``<PaymentMethod>`` block will be omitted entirely.

.. code-block:: xml

    <Install>
        ...
        <PaymentMethod>
            ...
        </PaymentMethod>
        ...
    </Install>

+----------------+-----------------+
| Element name   | Function        |
+================+=================+
| ``<Method>`` * | payment method  |
+----------------+-----------------+

(*) Mandatory field

**Beispiel, JTL-Shop 4.x:** |br|

.. code-block:: xml
   :emphasize-lines: 12,13

    <Method>
        <Name>PayPal Plus</Name>
        <PictureURL>images/de-ppcc-logo-175px.png</PictureURL>
        <Sort>1</Sort>
        <SendMail>1</SendMail>
        <Provider>PayPal</Provider>
        <TSCode>PAYPAL</TSCode>
        <PreOrder>1</PreOrder>
        <Soap>0</Soap>
        <Curl>1</Curl>
        <Sockets>0</Sockets>
        <ClassFile>class/PayPalPlus.class.php</ClassFile>
        <ClassName>PayPalPlus</ClassName>
        <TemplateFile>template/paypalplus.tpl</TemplateFile>
        <MethodLanguage iso="GER">
            <Name>PayPal, Lastschrift, Kreditkarte oder Rechnung</Name>
            <ChargeName>PayPal PLUS</ChargeName>
            <InfoText>PayPal, Lastschrift, Kreditkarte oder Rechnung</InfoText>
        </MethodLanguage>
        <Setting type="text" initialValue="" sort="1" conf="Y">
            <Name>Anzeigename für PayPal Login</Name>
            <Description>Verwendeter Name auf der PayPal-Seite</Description>
            <ValueName>brand</ValueName>
        </Setting>
    </Method>

**JTL-Shop 5.x:**

For JTL-Shop 5, the structure will look like this:

.. code-block:: xml

    <Method>
        ...
        <ClassFile>PayPalPlus.php</ClassFile>
        <ClassName>PayPalPlus</ClassName>
        ...
    </Method>

+------------------------------+-----------------------------------------------------------------+
| Element name                 | Function                                                        |
+==============================+=================================================================+
| ``<Name>`` *                 | name of payment method                                          |
+------------------------------+-----------------------------------------------------------------+
| ``<PictureURL>`` *           | link to logo                                                    |
+------------------------------+-----------------------------------------------------------------+
| ``<Sort>`` *                 | payment method sorting number (``[0-9]+``)                      |
+------------------------------+-----------------------------------------------------------------+
| ``<SendMail>`` *             | send an email upon receipt of payment (1 = “Yes", 0 = “No”)     |
+------------------------------+-----------------------------------------------------------------+
| ``<Provider>``               | payment provider                                                |
+------------------------------+-----------------------------------------------------------------+
| ``<TSCode>`` *               | trusted shops TSCode(``[A-Z_]+``)                               |
+------------------------------+-----------------------------------------------------------------+
| ``<PreOrder>`` *             | pre(1)- or post(0)-order (``[0-1]{1}``)                         |
+------------------------------+-----------------------------------------------------------------+
| ``<Soap>`` *                 | transmission protocol flag (``[0-1]{1}``)                       |
+------------------------------+-----------------------------------------------------------------+
| ``<Curl>`` *                 | transmission protocol flag (``[0-1]{1}``)                       |
+------------------------------+-----------------------------------------------------------------+
| ``<Sockets>`` *              | transmission protocol flag (``[0-1]{1}``)                       |
+------------------------------+-----------------------------------------------------------------+
| ``<ClassFile>`` *            | name of PHP file class (``[a-zA-Z0-9\/_\-.]+.php``)             |
+------------------------------+-----------------------------------------------------------------+
| ``<ClassName>`` *            | class name                                                      |
+------------------------------+-----------------------------------------------------------------+
| ``<TemplateFile>``           | template file name (``[a-zA-Z0-9\/_\-.]+.tpl``)                 |
+------------------------------+-----------------------------------------------------------------+
| ``<AdditionalTemplateFile>`` | template file for additional step                               |
+------------------------------+-----------------------------------------------------------------+
| ``<MethodLanguage>`` *       | payment method localisation                                     |
+------------------------------+-----------------------------------------------------------------+
| ``<Setting>``                | payment method settings                                         |
+------------------------------+-----------------------------------------------------------------+

(*) Mandatory field

The ``<Soap>``, ``<Curl>`` and ``<Sockets>`` elements designate the required communication paths to the server
of the payment provider to be used by this payment method plug-in. |br|
These elements are then checked by the shop's plug-in system when the plug-in is installed and synchronized with the options available
on the current shop server (e.g. the availability of relevant PHP modules). This check
is performed in an **OR** statement. |br|
In other words, as soon as one of the required transfer methods to the payment provider's server is available, the
payment method plug-in will also be marked as operational after installation. |br|
However, in certain cases, multiple transfer paths are needed, for example SOAP for user data transfer
and CURL for liquidity checks. By default, the plug-in system does not check if all necessary transfer
methods are available or not. Therefore, you have to ensure that the plug-in communicates to the plug-in system
when all necessary transfer methods are not available, by using
the ":ref:`isValidIntern() <label_public-function-method-isValidIntern>`" method, for example. |br|
If, on the other hand, the payment plug-in is built on a POST-form, you can assign a ``0`` to each element
here.

In the ``<TemplateFile>`` element, the name or path to a Smarty template file can be specified.
POST forms can then be returned there, for example. |br|

.. _label_AdditionalTemplateFile:

In the ``<AdditionalTemplateFile>`` element, you can also specify a Smarty template file for an additional
payment step. This is where credit card information can be requested.

The ``<TSCode>`` element can contain the following values: "*DIRECT_DEBIT*", "*CREDIT_CARD*", "*INVOICE*",
"*CASH_ON_DELIVERY*", "*PREPAYMENT*", "*CHEQUE*", "*PAYBOX*", "*PAYPAL*", "*CASH_ON_PICKUP*", "*FINANCING*",
"*LEASING*", "*T_PAY*", "*CLICKANDBUY*", "*GIROPAY*", "*GOOGLE_CHECKOUT*", "*SHOP_CARD*", "*DIRECT_E_BANKING*",
"*OTHER*".

The ``<MethodLanguage>`` XML node provides multilingual options for payment methods. |br|
You can implement any number of languages for a payment method. It must contain at least one
language.

+--------------------+------------------------------------------------------------------+
| Element name       | Function                                                         |
+====================+==================================================================+
| ``<iso>`` *        | language code of respective language                             |
+--------------------+------------------------------------------------------------------+
| ``<Name>`` *       | name of payment method                                           |
+--------------------+------------------------------------------------------------------+
| ``<ChargeName>`` * | payment method sorting number (``[0-9]+``)                       |
+--------------------+------------------------------------------------------------------+
| ``<InfoText>`` *   | short description of payment method, as displayed in front end   |
+--------------------+------------------------------------------------------------------+

(*) Mandatory field

The XML node ``<Setting>`` allows the plug-in to receive specific settings from the online shop operator
. |br|
Each payment method can contain any number of settings, like the login details of a specific
shop operator, for example. These settings are displayed in the back end for the respective payment method and can be edited there
.

+------------------------+---------------------------------------------------+
| Element name           | Function                                          |
+========================+===================================================+
| ``<type>`` *           | settings type (text, number, select box)          |
+------------------------+---------------------------------------------------+
| ``<initValue>`` *      | pre-selected setting                              |
+------------------------+---------------------------------------------------+
| ``<sort>`` *           | setting sorting (higher = further down)           |
+------------------------+---------------------------------------------------+
| ``<conf>``  *          | Y = true setting, N = caption                     |
+------------------------+---------------------------------------------------+
| ``<Name>`` *           | setting name                                      |
+------------------------+---------------------------------------------------+
| ``<Description>`` *    | setting variable description                      |
+------------------------+---------------------------------------------------+
| ``<ValueName>`` *      | setting variable name                             |
+------------------------+---------------------------------------------------+
| ``<SelectboxOptions>`` | optional element for type = selectbox             |
+------------------------+---------------------------------------------------+

(*) Mandatory field

For further information about "payment types in plug-ins", please see ":doc:`payment_plugins`".

Language variables
------------------

Language variables are localised variables, which can be stored and retrieved for different languages. |br|
As long as the languages in the JTL-Shop match those in the plug-in, the language variables automatically localise for each
set language in the online shop. |br|
If the plug-in provides *front end links*, then any textual output should be generated using this
language variable.

.. note::

    *Language variables* are not to be confused with ":ref:`label_infoxml_locale`” in the back end of an online shop.

Adapting the languages variables in the admin area’s plug-in settings
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Language variables can be adapted by a shop owner after installation of a plug-in. |br|
For this reason, the plug-in manager provides a column for “*language variables*” in which
the "*Edit*" button can be found as soon as a plug-in provides language variables.

A plug-in can define any number of language variables. |br|
The main element of the language variables is called ``<Locales>``
and every language element will be defined in ``<Variable>``. |br|
``<Locales>`` is a sub-node of ``<Install>``. |br|
In the XML-Container ``<Variable>`` any number of ``<VariableLocalized>`` nodes can be integrated.

.. code-block:: xml

    <Locales>
        <Variable>
            <Name>xmlp_lang_var_1</Name>
            <Description>An example variable.</Description>
            <VariableLocalized iso="GER">PI ist %s und Parameter 2 lautet: %s.</VariableLocalized>
            <VariableLocalized iso="ENG">PI is %s and parameter 2 has the value: %s.</VariableLocalized>
        </Variable>
        <Variable>
            <Description>Another example variable.</Description>
            <Name>xmlp_lang_var_2</Name>
            <VariableLocalized iso="GER">Ich bin variabel!</VariableLocalized>
            <VariableLocalized iso="ENG">I'm variable!</VariableLocalized>
            <Type>textarea</Type>
        </Variable>
    </Locales>

+---------------------------+----------------------------------+
| Element name              | Function                         |
+===========================+==================================+
| ``<Name>`` *              | language variable name           |
+---------------------------+----------------------------------+
| ``<Description>`` *       | language variable description    |
+---------------------------+----------------------------------+
| ``<VariableLocalized>`` * | localised name                   |
+---------------------------+----------------------------------+
| ``<Type>``                | type of input field (as of 5.0.0 |
+---------------------------+----------------------------------+

(*) Mandatory field

.. hint::

    Any changes to the ``info.xml`` file, in this regard, are only visible after reinstalling the plug-in, as the
    variables are written to the database **during installation**.

As of Shop 5.0.0 type specification is possible but remains optional. By default, type will be set to “text”, which corresponds as
a simple text input field in the back end. For longer texts, the type "textarea” is recommended.
Basically, all types defined in JTL\Plugin\Admin\InputType can be used here.

Language variables can always be reset to their original value. |br|
In case of a plug-in update or deactivation of a plug-in, the language variables that were modified by the shop owner, will be kept.
 Only once the plug-in is uninstalled, will the language variables be permanently deleted.


Application of the plug-in
""""""""""""""""""""""""""

Consider the following XML example:

.. code-block:: xml

    <jtlshopplugin>
        ...
        <PluginID>jtl_example_plugin</PluginID>
    </jtlshopplugin>
    <Install>
        <Locales>
            <Variable>
                <Name>lang_var_one</Name>
                <VariableLocalized iso="GER">Ich bin variabel!</VariableLocalized>
                <VariableLocalized iso="ENG">I'm variable!</VariableLocalized>
                <Description>An example variable.</Description>
            </Variable>
            <Variable>
                <Name>lang_var_two</Name>
                <Description>An example variable with placeholder.</Description>
                <VariableLocalized iso="GER">Hallo, mein Name ist %s.</VariableLocalized>
                <VariableLocalized iso="ENG">Hello, my name is %s.</VariableLocalized>
            </Variable>
        </Locales>
        ...
    </Install>

The value of the language variable can be returned via PHP in the following way:

JTL-Shop 4.x
""""""""""""

.. code-block:: php

    $test1 = $oPlugin->oPluginSprachvariableAssoc_arr['lang_var_one']; // hat Wert "Ich bin variabel!"
    $test2 = sprintf($oPlugin->oPluginSprachvariableAssoc_arr['lang_var_two'], "Peter"); // hat Wert "Hallo, mein Name ist Peter."

JTL-Shop 5.x
""""""""""""

.. code-block:: php

    // i.e. within Bootstrap.php in the boot method:
    $plugin = $this->getPlugin();
    $test1  = $plugin->getLocalization()->getTranslation('lang_var_one');
    $test2  = \sprintf($plugin->getLocalization()->getTranslation('lang_var_two'), 'Peter');


As of Shop 5.1.0, language variables can be used within the template file.
To do this, use the ``{lang key='variable-name' section='my-plug-in-id’}`` syntax - as in the example below

.. code-block:: php

    {lang key='lang_var_one' section='jtl_example_plugin'}
    {lang key='lang_var_two' section='jtl_example_plugin' printf='Peter'}


.. _label_infoxml_email:

Email templates
----------------

A plug-in can also define new email types, that can be sent as emails. The email content of a template
can be predefined for all languages available in the online shop. The predefined texts can still be edited
in the email template manager in the back end by the online shop owner.

The main node ``<Emailtemplate>``, which is located in the ``<Install>`` container, defines a new email template.

.. code-block:: xml

    <Emailtemplate>
        </Template>
            <Name>Payment reminder email</Name>
            <Description></Description>
            <Type>text/html</Type>
            <ModulId>payment reminder</ModulId>
            <Active>Y</Active>
            <AKZ>0</AKZ>
            <AGB>0</AGB>
            <WRB>0</WRB>
            <TemplateLanguage iso="GER">
                <Subject>Zahlungserinnerung</Subject>
                <ContentHtml></ContentHtml>
                <ContentText></ContentText>
            </TemplateLanguage>
            <TemplateLanguage iso="ENG">
                <Subject>Payment reminder</Subject>
                <ContentHtml></ContentHtml>
                <ContentText></ContentText>
            </TemplateLanguage>
        </Template>
    </Emailtemplate>

+------------------------+--------------------------------------------------------------------------------------------+
| Element                | Function                                                                                   |
+========================+============================================================================================+
| ``<Template>``         | main container element (for every email template, there must be a ``<Template>`` element   |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Name>``             | email template name                                                                        |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Description>``      | email template description                                                                 |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Type>``             | email template format (html/text or text)                                                  |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<ModulId>``          | email template unique key                                                                  |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Active>``           | email template activation flag (Y/N)                                                       |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<AKZ>``              | add provider information to email template (1/0)                                           |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<AGB>``              | add general terms and conditions to email template (1/0)                                   |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<WRB>``              | add cancellation policy to email template (1/0)                                            |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<TemplateLanguage>`` | localised content per language (min. one language must be available) (Key = SprachISO)     |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Subject>``          | email template subject in the respective language                                          |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<ContentHtml>``      | content as HTML                                                                            |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<ContentText>``      | content as text                                                                            |
+------------------------+--------------------------------------------------------------------------------------------+

(*) Mandatory field

For further information on the topic of "email templates in the plug-in", see ":doc:`mailing`”.
