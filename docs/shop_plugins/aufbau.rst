Aufbau
======

.. |br| raw:: html

   <br />

Ein Plugin besteht aus einer *Verzeichnisstruktur*, die physikalisch auf dem Datenträger des Onlineshops vorhanden sein
muss, und einer XML-Datei (``info.xml``, siehe auch :doc:`hier <infoxml>`), die für die Installation und die Updates
des Plugins zuständig ist. |br|
Die ``info.xml`` beschreibt das Plugin. Dort wird definiert, welche Dateien ein Plugin nutzt,
welche Aufgaben es übernehmen soll und welche Identität das Plugin hat.

Die Installationsdatei und damit auch die Verzeichnisstruktur variiert je nach Aufgabenbereich des jeweiligen
Plugins. |br|
In der JTL-Shop-Ordnerstruktur existiert ein fest definierter Ordner, der alle Plugins beinhaltet.
Von dort aus greift das System auf Pluginressourcen und Installationsinformationen zu.

.. hint::

    Ein Plugin zur automatischen Erstellung von JTL-Shop-Plugins findet sich im
    `öffentlichen Gitlab-Repository <https://gitlab.com/jtl-software/jtl-shop/legacy-plugins/plugin-bootstrapper>`_.
    Dadurch kann das manuelle Erstellen der ``info.xml`` und der Dateistruktur vereinfacht werden.

Verzeichnisstruktur
-------------------

Ein Plugin benötigt eine fest definierte Verzeichnisstruktur, damit es installiert werden kann. |br|
Es gibt einige Ausnahmen, wobei man gewisse Verzeichnisse weglassen oder nach eigenen Vorlieben strukturieren kann.
Jedes Plugin hat sein eigenes Unterverzeichnis innerhalb des Pluginverzeichnisses.

Vergeben Sie stets aussagekräftige und eindeutige Pluginnamen, um Überschneidungen in Pluginverzeichnisnamen zu
vermeiden.
Das neuere Pluginverzeichnis würde demnach beim Upload das ältere überschreiben und das ursprüngliche Plugin
würde nicht mehr funktionieren. Wir empfehlen, den Pluginverzeichnisnamen um eindeutige Merkmale
wie z. B. den Firmennamen des Autors zu erweitern.

**Bis JTL-Shop 4.x** liegt das Pluginverzeichnis ``plugins/``, in dem alle Plugins des Shops zu finden sind,
im Ordner ``<Shop-Root>/includes/``. |br|
Demnach könnte ein typisches Plugin unter ``[Shop-Rot]/includes/plugins/[Ihr_Pluginordner]`` zu finden sein.

Jedes Plugin in einem Onlineshop der Version 4.x muss mindestens einen *Versionsordner* enthalten. |br|
Die Versionen fangen bei der Ganzzahl 100 an (Bedeutung: Version 1.00) und werden mit 101, 102 usw. weitergeführt.
Die ganzzahligen Versionsnummern sind gleichzeitig die Ordnernamen unterhalb des *Versionsordners*. |br|
Jedes Plugin muss auf jeden Fall den Ordner ``100/`` enthalten (siehe Versionen).

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

**Ab JTL-Shop 5.x** befindet sich der Pluginordner direkt unterhalb der Shop-Root,
also ``[Shop-Root]/plugins/[Ihr_Pluginordner]``.

.. attention::

    Beachten Sie, dass ab JTL-Shop 5.x der **Plugin-Ordnername** zwingend
    der **Plugin-ID** in der ``info.xml`` entsprechen muss.

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

Mögliche Unterverzeichnisse
"""""""""""""""""""""""""""

+--------------------+-------------------------------------------------------------------------------------------------------------+
| Ordnername         | Funktion                                                                                                    |
+====================+=============================================================================================================+
| ``adminmenu/``     | Onlineshopadmin-Tabs, um eigenen Inhalt im Adminbereich auszugeben bzw. um Einstellungen zu implementieren. |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``frontend/``      | Frontend Links zu Seiten im Onlineshop mit eigenem Inhalt                                                   |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``paymentmethod/`` | Implementierung von Zahlungsmethoden im Onlineshop.                                                         |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``sql/``           | Nur bis 4.x; SQL-Datei, um eigene Datenbanktabellen anzulegen, Daten dort abzulegen oder zu verändern.      |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``src/``           | Ab 5.0.0, pluginspezifische Helper-Klassen (organisiert als Packages)                                       |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``locale/``        | Ab 5.0.0, Übersetzungsdateien                                                                               |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``Migrations/``    | Ab 5.0.0, SQL-Migrationen                                                                                   |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``Portlets/``      | Ab 5.0.0, OPC-Portlets                                                                                      |
+--------------------+-------------------------------------------------------------------------------------------------------------+
| ``blueprints/``    | Ab 5.0.0, OPC-Blueprints                                                                                    |
+--------------------+-------------------------------------------------------------------------------------------------------------+

Verzeichnisstruktur Payment
"""""""""""""""""""""""""""

Ein Plugin kann beliebig viele Zahlungsmethoden im Onlineshop implementieren. |br|
Hierfür wird ein Unterordner namens ``paymentmethod/`` benötigt, der in JTL-Shop 4.x unterhalb der jeweiligen
Pluginversion und ab JTL-Shop 5.x direkt unterhalb der Plugin-Root liegt.

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

**Beispiel, JTL-Shop 5.x**

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

Unterhalb des Ordners ``paymentmethod/`` ist es sinnvoll, mindestens den Ordner ``template/`` anzulegen. Legen Sie dort
entsprechend die Templates ab, die zahlungsartspezifische Inhalte anzeigen. |br|
Ordnen Sie die eigentlichen Zahlart-Klassen direkt unterhalb von ``paymentmethod/`` an. |br|
Platzieren Sie eventuelle "Helper"-Klassen unterhalb des pluginspezifischen ``src/``-Ordners und organisieren Sie sie
dort namespacekonform in Packages. |br|

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

Im Abschnitt :ref:`label_infoxml_paymentmethode` finden Sie ein **Beispiel**, wie diese Verzeichnisstruktur in
der ``info.xlm`` definiert wird.


.. _label_aufbau_versionierung:

Versionierung
-------------

Wie die XML-Definition der Plugin-Version aussieht, finden Sie
im ``info.xml``-Abschnitt ":ref:`label_infoxml_versionierung`".

Bis JTL-Shop 4.x
""""""""""""""""

Da sich Plugins mit der Zeit auch weiterentwickeln können, gibt es eine Versionierung der Plugins. |br|
Damit besteht die Möglichkeit, ein Plugin über den Updatemechanismus des Pluginsystems zu aktualisieren,
um neue Funktionalität einzuführen oder Fehler zu beheben.

Jedes Plugin muss den Ordner ``version/`` enthalten. |br|
Dieser Ordner enthält alle bisher erschienenen Versionen des Plugins. Jedes Plugin muss die niedrigste Version
100 (Bedeutung Version 1.00) enthalten. |br|
In diesen Unterordnern (Versionsordnern) befinden sich alle Ressourcen des Plugins für die jeweilige Version.

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

Wird eine neue Version entwickelt, wird die Version um 1 hochgezählt. Die Versionierung
ist also fortlaufend: 100, 101, 102, 103 und so weiter. Eine Versionsgrenze nach oben existiert nicht.

Um ein Plugin zu aktualisieren, übertragen Sie die ``info.xml`` in das jeweilige Pluginverzeichnis. |br|
Alle neuen Versionsverzeichnisse übertragen Sie in das Verzeichnis ``/version`` des jeweiligen Pluginverzeichnisses.
Wenn also eine neue Version eines Plugins erstellt wurde, kopieren Sie die Datei ``<pluginname>/info.xml`` sowie
alle ``<pluginname>/version/*``-Versionsverzeichnisse in den Onlineshop.
Die Pluginverwaltung im Adminbereich erkennt dabei automatisch, ob Updates zu einem Plugin vorliegen und bietet
einen entsprechenden Updatebutton an.

Beispiel:
In der info.xml wurden zwei Versionen definiert. Demnach würden die Unterordner von *version* wie folgt
aussehen: */version/100/* und */version/101/*.

Für jede Version, die in der Installationsdatei definiert wurde, muss auch ein physischer Ordner existieren.

Ab JTL-Shop 5.x
"""""""""""""""

.. important::
    Ab JTL-Shop 5.0 entfällt der Unterordner ``version/`` und alle anderen Ordner müssen direkt unterhalb
    des Pluginordners angelegt werden!

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

Wie sich die Versionierung in der ``info.xml`` widerspiegelt, lesen Sie
im entsprechenden Abschnitt ":ref:`label_infoxml_versionierung`".


.. _label_infoxml_sql:

SQL im Plugin
-------------

Bis JTL-Shop 4.x
""""""""""""""""

Jede Version eines Plugins hat die Möglichkeit, eine SQL-Datei anzugeben, welche beliebige SQL-Befehle ausführt. |br|
Diese SQL-Datei kann z. B. zum Erstellen neuer Tabellen oder zum Verändern von Daten in der Datenbank genutzt werden.
Falls in der ``info.xml`` eine SQL-Datei angegeben wurde, muss diese auch physikalisch vorhanden sein. |br|
Wenn eine neue Tabelle in der SQL-Datei angelegt wird, also der SQL Befehl ``CREATE TABLE``
genutzt wird, muss der Tabellenname eine bestimmte Konvention einhalten.
Der Name muss mit ``xplugin_`` beginnen, gefolgt von der eindeutigen ``[PluginID]_``. Er kann mit einem
beliebigen Namen enden. |br|
Daraus ergibt sich dann: ``xplugin_[PluginID]_[Name]``.

Beispiel: Lautet die Plugin-ID "*jtl_exampleplugin*" und die Tabelle soll "*tuser*" heißen, so muss der Tabellenname
letztlich "*xplugin_jtl_exampleplugin_tuser*" lauten. |br|
Der SQL-Ordner liegt im Ordner der jeweiligen Pluginversion.

**Beispiel:**

Bei einem Plugin in der Version 102 muss der entsprechende Abschnitt der ``info.xml`` dann wie folgt aussehen:

.. code-block:: xml

    <Version nr ="102">
        <SQL>install.sql</SQL>
        <CreateDate>2016-03-17</CreateDate>
    </Version>

Hier muss die Datei ``install.sql`` im SQL-Ordner namens ``sql/`` der Version 102 liegen. |br|
Die Verzeichnisstruktur sieht daher in diesem Beispiel wie folgt aus:

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

Pro Pluginversion kann es immer nur eine SQL-Datei geben. Falls in der ``info.xml`` keine SQL-Datei für eine Version
angegeben wurde, sollte man das SQL-Verzeichnis in der jeweiligen Version *weglassen*.

Bei der Installation wird jede SQL-Datei von der kleinsten zur größten Version inkrementell abgearbeitet. |br|
Wenn also ein Plugin in der Version 1.23 vorliegt, so werden bei der Installation die SQL-Dateien der Versionen
Version 1.00-1.23 nacheinander ausgeführt. |br|
Analog verhält es sich bei einem Update. Sie haben Version 1.07 eines Plugin installiert und möchten nun
auf Version 1.13 updaten. Beim Update werden alle SQL-Dateien ab 1.08 bis 1.13 ausgeführt.

ab JTL-Shop 5.x
"""""""""""""""

Ab JTL-Shop 5.0.0 wird der Unterordner ``sql/`` *nicht mehr unterstützt*. Somit werden auch keine SQL-Dateien mehr
ausgeführt. |br|

.. hint::

    Plugins können nun, wie der Onlineshop selbst, *Migrationen* nutzen.

Diese müssen *nicht mehr* in der ``info.xml`` definiert werden, sondern liegen im Unterordner ``Migrations/``
des Plugin-Verzeichnisses. |br|
Das Namensschema der Datei- und somit auch Klassennamen lautet ``Migration<YYYYMMDDhhmmss>.php``
(entspricht in PHP: ``date('YmdHis');``).

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

Alle Pluginmigrationen müssen das Interface ``JTL\Update\IMigration`` implementieren
und im Namespace ``Plugin\<PLUGIN-ID>\Migrations`` liegen. |br|
Dieses Interface definiert die zwei wichtigsten Methoden ``up()`` zur Ausführung von SQL-Code
und ``down()`` zum Zurücknehmen dieser Änderungen.

**Beispiel**:

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

Bei der Installation des Plugins werden automatisch die ``up()``-Methoden aller Migrationen ausgeführt, bei der
Deinstallation entsprechend alle ``down()``-Methoden. |br|
Hier entfällt auch die Beschränkung auf die Erstellung von Tabellen mit dem Präfix ``xplugin_<PLUGIN-ID>``.
Zusätzlich bietet die Verwendung von :doc:`Bootstrapping <bootstrapping>` mit den Methoden ``installed()``,
``uninstalled()`` und ``updated()`` erweiterte Möglichkeiten für die Installation, Deinstallation und das
Update eines Plugins.


.. _label_aufbau_locale:

Mehrsprachige Settings (ab 5.0.0)
---------------------------------

Ab JTL-Shop 5.0.0 können Plugin-Optionen mehrsprachig gestaltet werden. |br|
Zu diesem Zweck kann ein Plugin vom gleichen Mechanismus Gebrauch machen wie das Backend
von JTL-Shop - `gettext <https://www.gnu.org/software/gettext/>`_.

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

Einen exemplarischen Überblick, wie Sie dies mit Hilfe der ``info.xml`` bewerkstelligen können, finden Sie im Kapitel
``info.xml`` im Abschnitt ":ref:`label_infoxml_locale`".

.. _label_adminmenu_structure:

"adminmenu/" Struktur
---------------------

Das *Adminmenu* befindet sich bei Onlineshops einer Version bis 4.x in jedem Versionsordner des Plugins und
bei Onlineshops ab Version 5.x direkt in der Plugin-Root. |br|
(Falls kein *Adminmenu* in der ``info.xml`` definiert wurde, können Sie diesen Ordner auch weglassen.)

Ein Plugin kann beliebig viele eigene Links (:ref:`label_infoxml_custom_links`) im Adminbereich enthalten. |br|
Falls Sie *Custom Links* in der ``info.xml`` angegeben haben, muss in jedem ``adminmenu/``-Ordner für jeden
*Custom Link* eine entsprechende PHP-Datei enthalten sein. |br|

.. code-block:: xml
   :emphasize-lines: 4

    <Adminmenu>
        <Customlink sort="1">
            <Name>Statistik</Name>
            <Filename>stats.php</Filename>
        </Customlink>
    </Adminmenu>

In diesem Beispiel wird im Backend von JTL-Shop ein *Custom Link* erstellt, der als Tab mit dem Namen "Statistik"
erscheint. Dieser Tab führt die Datei ``stats.php``, im Ordner ``adminmenu/``, aus. Diese Datei inkludiert die
Smarty-Templateengine und lädt ein eigenes Template, das Sie in einem selbst definierten Ordner ablegen können.

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

Weitere Verzeichnisse sind dem Pluginentwickler selbst überlassen. |br|
Es ist natürlich auch möglich, das Adminmenü nur mit Einstellungen (:ref:`label_infoxml_setting_links`) zu füllen.

"frontend/" Struktur
--------------------

Im Frontendmenü können Sie selbst definierte Links im Frontend von JTL-Shop erstellen, so dass dort eigene PHP-Dateien
ausgeführt werden. |br|
Der Ordner ``frontend/`` befindet sich bei JTL-Shop 4.x im jeweiligen Versionsordner des Plugins und ab
JTL-Shop 5.x direkt in der Plugin-Root. |br|
(Falls kein Frontendmenü in der ``info.xml`` definiert wurde, können Sie diesen Ordner auch weglassen. |br|
Es können beliebig viele *Frontend Links* eingebunden werden.

Wie *Frontend Links* in der ``infox.xml`` definiert werden, finden Sie im Abschnitt :ref:`label_infoxml_frontendlinks`.

Jeder *Frontend Link* benötigt eine Smarty-Templatedatei, um Inhalt im Onlineshop anzuzeigen. |br|
Diese Templatedatei liegt im ``template/``-Ordner des jeweiligen Ordners ``frontend/``.
Der Pfad zur Templatedatei für das untere Beispiel würde also ``/meinplugin/version/102/frontend/template/`` lauten.

**Beispiel für JTL-Shop 5.x:**

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

    Sobald man ein Plugin installiert hat, welches *Frontend Links* beinhaltet, sollte man darauf achten, dass die
    Links den jeweiligen Linkgruppen des Shops, vom Administrator, zugewiesen werden müssen.

Hierfür bietet die Pluginverwaltung die Spalte "Linkgruppe".
Falls *Frontend Links* vorhanden sind, wird dort eine Schaltfläche angezeigt. Die Schaltfläche führt zur Verwaltung der
Linkgruppen (in JTL-Shop 4.x: "Seiten" -> "Eigene Seiten",
ab JTL-Shop 5.x: "Darstellung" -> "Eigene Inhalte" -> "Seiten"). |br|

Die Installation des Plugins stellt *Frontend Links*  ab JTL-Shop 4 in die Linkgruppe "*hidden*" ein
(in JTL-Shop 3 in die erste CMS Linkgruppe).

Die Links des jeweiligen Plugins werden hier farblich hervorgehoben, um das Auffinden der *Frontend Links*  des Plugins
zu erleichtern. |br|
Sie können nun die *Frontend Links* des Plugins  über eine Selectbox in andere Linkgruppen verschieben.


.. _label_aufbau_frontend_res:

Frontend-Ressourcen
-------------------

Weiterhin gehören zur Struktur des Verzeichnisses ``frontend/`` die zusätzlichen "*Frontend-Ressourcen*".

**Beispiel bis JTL-Shop 4.x:**

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

**Beispiel ab JTL-Shop 5.x:**

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

Weitere Informationen finden Sie im ``info.xml``-Abschnitt ":ref:`label_infoxml_frontend_res`".

Template-Blöcke
---------------

Auch Template-Blöcke des Frontends lassen sich durch Plugins manipulieren. |br|
Hierfür sind keine Einträge in der ``info.xml`` nötig. Lediglich die Layoutstruktur des Templates muss im Plugin
nachgebildet werden.

Ein minimalistisches Plugin, für JTL-Shop 5 und das NOVA-Template, könnte dann so aussehen:

**Beispiel:**

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

Achten Sie Beim Anlegen der Struktur im Plugin-Verzeichnis ``frontend/`` darauf, dass Sie die Templatestruktur genau
nachgebildet. |br|
Das Verzeichnis ``adminmenu/`` ist hier nur zur Veranschaulichung des Unterschiedes der Verzeichnisnamen
``adminmenu/templates`` und ``frontend/template`` aufgelistet. Es muß für dieses Beispiel nicht angelegt werden.

Die hier verwendete ``info.xml`` konfiguriert nur den Rumpf eines Plugins:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <jtlshopplugin>
        <Name>[PluginName]</Name>
        <Description>Blendet einen deutlichen Hinweis auf jeder Seite ein, dass es sich um einen Testshop handelt</Description>
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

Die Datei ``header.tpl`` enthält alles, was im Frontend ausgegeben werden soll:

.. code-block:: smarty
   :emphasize-lines: 2

    extends file="{$parent_template_path}/layout/header.tpl"}
    {block name='layout-header-content-all-starttags' prepend}
        <script>
            console.log('Diese Ausgabe erscheint in der Javascript-console und wurde erzeugt vom plugin: [PluginID]');
        </script>
        <div id="testing-purpose-alert" class="alert alert-warning text-center">
            Dieser Shop dient ausschlie&szlig;lich Demonstrations- und Testzwecken.
            Es k&ouml;nnen keine realen Bestellungen ausgef&uuml;hrt werden.
        </div>
    {/block}

Weiter Erläuterungen zur Manipulation von Blöcken finden Sie im Abschnitt ":ref:`label_eigenestemplate_tpldateien`".

.. _label_aufbau_boxen:

Boxen
-----

Ein Plugin kann ebenso Boxen für das Frontend von JTL-Shop mitbringen. |br|
Das Verzeichnis für diese Darstellungselemente befinden sich ebenfalls im Ordner ``frontend/``.

**Beispiel bis JTL-Shop 4.x:**

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

    Von JTL-Shop 4.x zu JTL-Shop 5.0 hat sich der Name dieses Verzeichnisses von ``boxen/`` zu ``boxes/`` geändert.

**Beispiel ab JTL-Shop 5.x:**

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

Wie Sie diese neuen Boxen in der ``info.xml`` definieren und JTL-Shop bekannt machen,
finden Sie im Abschnitt ":ref:`label_infoxml_boxen`".


.. _label_aufbau_widgets:

Widgets
-------

Auch im Backend von JTL-Shop lassen sich neue Elemente über Plugins einfügen, z.B. im Dashboard des
Administrationsbereiches. |br|
Hierfür werden *Widgets* eingesetzt. Wie sie der Logik des Shops bekannt gemacht werden, erfahren Sie im
``info.xml``-Abschnitt ":ref:`label_infoxml_widgets`".

Platziert werden die zugehörigen Dateien wie folgt:

**Bis JTL-Shop 4.x:**

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

**Ab JTL-Shop 5.x:**

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

Lizenzierung
------------

Bei kommerziellen Plugins für JTL-Shop ist es möglich, eine eigene Klasse die Lizenzprüfung erledigen zu lassen. |br|
Nähere Informationen hierzu finden Sie im Kapitel ``info.xml`` unter dem Abschnitt ":ref:`label_infoxml_license`".

Ihre Klasse zur Lizenzprüfung erhält hier ihren Platz:

**Bis JTL-Shop 4.x:**

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

**Ab JTL-Shop 5.x:**

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

Die Stelle im Root-Verzeichnis des Plugins ist für JTL-Shop 4.x sowie JTL-Shop 5.x der gleiche. |br|






Exportformate
-------------

Mit einem Plugin-Exportformat lassen sich neue Exportformate in den JTL-Shop integrieren.
Sie erstellen ein neues Exportformat, indem Sie folgenden neuen Block in der info.xml anlegen:

.. code-block:: xml

    <ExportFormat>
     ...
    </ExportFormat>

In diesem Block können beliebig viele Unterelemente vom Typ <Format> liegen. Das heißt, ein Plugin kann beliebig viele Exportformate anlegen.

XML-Darstellung in der info.xml:

.. code-block:: xml

    <ExportFormat>
        <Format>
            <Name>Google Base (Plugin)</Name>
        <FileName>googlebase.txt</FileName>
        <Header>link    titel    beschreibung    preis    bildlink    produkttyp    id    verfügbarkeit    zustand    versand    mpn    ean</Header>
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
| Elementname                        | Beschreibung                                                                                                |
+====================================+=============================================================================================================+
| ``<Name>``                         | Name des Exportformats                                                                                      |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<FileName>``                     | Dateiname ohne Angabe des Pfades, in welchen die Artikel exportiert werden sollen                           |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Header>``                       | Kopfzeile der Exportdatei                                                                                   |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Content>``                      | Exportformat (Smarty)                                                                                       |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<footer>``                       | Fußzeile der Exportdatei                                                                                    |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Encoding>``                     | ASCII oder UTF-8-Kodierung der Exportdatei                                                                  |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<VarCombiOption>``               | 1 = Vater- und Kindartikel exportieren / 2 = Nur Vaterartikel exportieren / 3 = Nur Kindartikel exportieren |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<SplitSize>``                    | Größe der Dateien, in die der Export zerlegt werden soll (in Megabyte)                                      |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<OnlyStockGreaterZero>``         | Nur Produkte mit Lagerbestand über 0                                                                        |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<OnlyPriceGreaterZero>``         | Nur Produkte mit Preis über 0                                                                               |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<OnlyProductsWithDescription>``  | Nur Produkte mit Beschreibung                                                                               |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<ShippingCostsDeliveryCountry>`` | Versandkosten Lieferland (ISO-Code)                                                                         |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<EncodingQuote>``                | Zeichenmaskierung für Anführungszeichen                                                                     |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<EncodingDoubleQuote>``          | Zeichenmaskierung für doppelte Anführungszeichen                                                            |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<EncodingSemicolon>``            | Zeichenmaskierung für Semikolons                                                                            |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+

(*) Pflichtfeld

Das folgende Beispiel demonstriert, wie ein Plugin-Exportformat aussehen könnte:

.. code-block:: xml

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshopplugin>
        <Name>Exportformat</Name>
        <Description>Beispiel eines Exportformats</Description>
        <Author>JTL-Software-GmbH</Author>
        <URL>http://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>500</ShopVersion>
        <PluginID>jtl_export</PluginID>
        <Version>1.0.0</Version>
        <Install>
            <ExportFormat>
                <Format>
                    <Name>Google Base (Plugin)</Name>
                    <FileName>googlebase.txt</FileName>
                    <Header>link    titel    beschreibung    preis    bildlink    produkttyp    id    verfügbarkeit    zustand    versand    mpn    ean</Header>
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

Portlets (ab JTL-Shop 5.0.0)
----------------------------

Plugins können auch :doc:`Portlets </shop_plugins/portlets>` für den *OnPageComposer* mitbringen.

**Ab JTL-Shop 5.x:**

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

Das Bekanntmachen der neuen Portlets geschieht via XML, in der ``info.xml``. |br|
Weitere Informationen dazu finden Sie im Abschnitt ":ref:`label_infoxml_portlets`".

Alles, was logisch zu einem Portlet gehört, befindet sich in einem eigenen Verzeichnis. |br|
Wie ein solches Portlet-Unterverzeichnis im Einzelnen aussehen kann, lesen Sie
im Abschnitt :doc:`Portlets </shop_plugins/portlets>`.

.. _label_aufbau_blueprints:

Blueprints (ab JTL-Shop 5.0.0)
------------------------------

Ebenso können Plugins auch Blueprints, also *Kompositionen von einzelnen Portlets*, definieren. |br|
Wie dies per ``info.xml`` dem Onlineshop mitgeteilt wird, lesen Sie im Abschnitt ":ref:`label_infoxml_blueprints`".

**Ab JTL-Shop 5.x:**

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


Änderungen von JTL-Shop 4.x zu JTL-Shop 5.x
-------------------------------------------

Hier eine kurze Zusammenfassung aller Änderungen für Plugins von JTL-Shop 4.x zu JTL-Shop 5.x

* neuer Installationsordner: ``<SHOP-ROOT>/plugins/<PLUGIN-ID>/``
* keine Unterordner ``version/<VERSION>/`` mehr
* XML-Root ``<jtlshopplugin>`` statt ``<jtlshop3plugin>``
* Knoten ``<Version>`` als Unterknoten von ``<Install>`` entfallen
* ``<CreateDate>`` und ``<Version>`` müssen als Unterknoten von ``<jtlshopplugin>`` angegeben werden und nicht mehr
  von ``<Install><Version>``
* Plugins erhalten den Namespace ``Plugin\<PLUGIN-ID>``
* Plugins können Migrationen ausführen aber keine SQL-Dateien
* Widget-Klassen entsprechen der in ``info.xml`` definierten Klasse und erfordern keinerlei weitere Konventionen
* Plugins können Lokalisierungen anbieten
* Plugins können Portlets und Blueprints definieren
