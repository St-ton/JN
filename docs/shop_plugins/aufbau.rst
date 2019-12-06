Aufbau
======

.. |br| raw:: html

   <br />

Ein Plugin besteht aus einer *Verzeichnisstruktur*, die physikalisch auf dem Datenträger des Shops vorhanden sein muss,
und einer XML-Datei (``info.xml``, siehe auch :doc:`hier <infoxml>`), die für die Installation und die Updates des
Plugins zuständig ist. |br|
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

Es sollte darauf geachtet werden, stets aussagekräftige und eindeutige Pluginnamen zu vergeben, um Überschneidungen in
Plugin-Verzeichnisnamen zu vermeiden.
Das neuere Pluginverzeichnis würde demnach beim Upload das ältere überschreiben und das ursprüngliche Plugin
würde nicht mehr funktionieren. Wir empfehlen, den Plugin-Verzeichnisnamen um eindeutige Merkmale
wie z.B. den Firmennamen des Autors zu erweitern.

**Bis Shop Version 4.x** liegt das Pluginverzeichnis ``plugins/``, in dem alle Plugins des Shops zu finden sind,
im Ordner ``<Shop-Root>/includes/``. |br|
Demnach könnte ein typisches Plugin unter ``[Shop-Rot]/includes/plugins/[Ihr_Pluginordner]`` zu finden sein.

Jedes Plugin, in einem Shop Version 4.x, muss mindestens einen *Versionsordner* enthalten. |br|
Die Versionen fangen bei der Ganzzahl 100 an (Bedeutung: Version 1.00) und werden mit 101, 102 usw. weitergeführt.
Die ganzzahligen Versionsnummern sind gleichzeitig die Ordnernamen, unterhalb des *Versionsordners*. |br|
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

**Ab Shop Version 5.x** befindet sich der Plugin-Ordner direkt unterhalb der Shop-Root,
also ``[Shop-Root]/plugins/[Ihr_Pluginordner]``.

.. attention::

    Ab Shop Version 5.x ist besonders darauf zu achten, dass der **Plugin-Ordnername** zwingend
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

+--------------------+--------------------------------------------------------------------------------------------------------+
| Ordnername         | Funktion                                                                                               |
+====================+========================================================================================================+
| ``adminmenu/``     | Shopadmin Tabs, um eigenen Inhalt im Adminbereich auszugeben bzw. um Einstellungen zu implementieren.  |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``frontend/``      | Frontend Links zu Seiten im Shop mit eigenem Inhalt                                                    |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``paymentmethod/`` | Implementierung von Zahlungsmethoden im Shop.                                                          |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``sql/``           | Nur bis 4.x, SQL-Datei, um eigene Datenbanktabellen anzulegen, Daten dort abzulegen oder zu verändern. |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``src/``           | Ab 5.0.0, plugin-spezifische Helper-Klassen (organisiert als Packages)                                 |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``locale/``        | Ab 5.0.0, Übersetzungsdateien                                                                          |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``Migrations/``    | Ab 5.0.0, SQL-Migrationen                                                                              |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``Portlets/``      | Ab 5.0.0, OPC-Portlets                                                                                 |
+--------------------+--------------------------------------------------------------------------------------------------------+
| ``blueprints/``    | Ab 5.0.0, OPC-Blueprints                                                                               |
+--------------------+--------------------------------------------------------------------------------------------------------+

Verzeichnisstruktur Payment
"""""""""""""""""""""""""""

Ein Plugin kann beliebig viele Zahlungsmethoden im Shop implementieren. |br|
Hierfür wird ein Unterordner namens ``paymentmethod/`` nötig, der in Shop Version 4.x unterhalb der jeweiligen
Pluginversion und ab Shop Version 5.x, direkt unterhalb der Plugin-Root, liegt.

**Beispiel, Shop Version 4.x**

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

**Beispiel, Shop Version 5.x**

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

Unterhalb des Ordners ``paymentmethod/`` ist es sinnvoll, mindestens den Ordner ``template/`` anzulegen und dort
entsprechend die Templates abzulegen, die zahlungsartspezifische Inhalte anzeigen. |br|
Die eigentlichen Zahlart-Klassen sind direkt unterhalb von ``paymentmethod/`` anzuordnen. |br|
Eventuelle "Helper"-Klassen, hingegen, werden unterhalb des plugin-spezifischen ``src/``-Ordners platziert, in dem sie
selbstverständlich namespace-konform in Packages organisiert werden sollten. |br|

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

Im Abschnitt :ref:`label_infoxml_paymentmethode`, finden sie ein **Beispiel**, wie diese Verzeichnisstruktur
in der ``info.xlm`` definiert wird.


.. _label_aufbau_versionierung:

Versionierung
-------------

Wie die XML-Definition der Plugin-Version aussieht, finden sie
im ``info.xml``-Abschnitt ":ref:`label_infoxml_versionierung`".

bis Shop Version 4.x
""""""""""""""""""""

Da sich Plugins mit der Zeit auch weiterentwickeln können, gibt es eine Versionierung der Plugins. |br|
Damit besteht die Möglichkeit, ein Plugin mit dem Updatemechanismus des Pluginsystems zu aktualisieren,
um neue Funktionalität einzuführen oder Fehler zu beheben.

Jedes Plugin muss den Ordner ``version/`` enthalten. |br|
Dieser Ordner enthält alle bisher erschienenen Versionen des Plugins. Jedes Plugin muss die niedrigste Version
100 (Bedeutung Version 1.00) enthalten. |br|
In diesen Unterordnern (Versions-Ordnern) befinden sich alle Ressourcen des Plugins für die jeweilige Version.

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

Wird eine neue Version entwickelt, wird die Version um 1 hochgezählt, d.h. die Versionierung
ist fortlaufend: 100, 101, 102, 103, und so weiter. Eine Versionsgrenze nach oben existiert nicht.

Um ein Plugin zu aktualisieren, überträgt man die info.xml in das jeweilige Pluginverzeichnis sowie alle neuen
Versionsverzeichnisse in das Verzeichnis ``version`` des jeweiligen Pluginverzeichnisses.
D.h. wurde etwa die Version 113 von einem Plugin erstellt, so kopiert man die <pluginname>/info.xml sowie
alle <pluginname>/version/* Versionsverzeichnisse in den Shop.
Die Pluginverwaltung im Adminbereich erkennt dabei automatisch, ob Updates zu einem Plugin vorliegen und bietet
einen entsprechenden Updatebutton an.

Beispiel:
In der info.xml wurden zwei Versionen definiert. Demnach würden die Unterordner von *version* wie folgt
aussehen: */version/100/* und */version/101/*.

Für jede Version, die in der Installationsdatei definiert wurde, muss auch ein physischer Ordner existieren.

ab Shop Version 5.x
"""""""""""""""""""

.. important::
    Ab Shop Version 5.0 entfällt der Unterordner ``version/`` und alle anderen Ordner müssen direkt unterhalb
    des Plugin-Ordners angelegt werden!

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

Wie sich die Versionierung in der ``info.xml`` wiederspiegelt, lesen Sie
im entsprechenden Abschnitt ":ref:`label_infoxml_versionierung`".


.. _label_infoxml_sql:

SQL im Plugin
-------------

Bis Shop Version 4.x
""""""""""""""""""""

Jede Version eines Plugins hat die Möglichkeit, eine SQL-Datei anzugeben, welche beliebige SQL-Befehle ausführt. |br|
Diese SQL-Datei kann z.B. zum Erstellen neuer Tabellen oder zum Verändern von Daten in der Datenbank genutzt werden.
Falls in der ``info.xml`` eine SQL-Datei angegeben wurde, muss diese auch physikalisch vorhanden sein. |br|
Zu beachten gilt, wenn eine neue Tabelle in der SQL-Datei angelegt wird, sprich: der SQL Befehl ``CREATE TABLE``
genutzt wird, muss der Tabellenname eine bestimmte Konvention einhalten.
Der Name muss mit ``xplugin_`` beginnen, gefolgt von der eindeutigen ``[PluginID]_`` und endet mit einem
beliebigen Namen (daraus ergibt sich dann: ``xplugin_[PluginID]_[belieber Name]``).

Beispiel: Lautet die PluginID "*jtl_exampleplugin*" und die Tabelle soll "*tuser*" heißen, so muss der Tabellenname
letztlich "*xplugin_jtl_exampleplugin_tuser*" lauten. |br|
Der SQL-Ordner liegt im Ordner der jeweiligen Pluginversion.

**Beispiel:**

Bei einem Plugin in der Version 102, muss der entsprechende Abschnitt der ``info.xml`` dann wie folgt aussehen:

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

Pro Plugin-Version kann es immer nur eine SQL-Datei geben. Falls in der ``info.xml`` keine SQL-Datei für eine Version
angegeben wurde, sollte man das SQL-Verzeichnis in der jeweiligen Version *weglassen*.

Bei der Installation wird jede SQL-Datei von der kleinsten zur größten Version inkrementell abgearbeitet. |br|
D.h.: liegt ein Plugin in der Version 1.23 vor, so werden bei der Installation die SQL-Dateien aller Versionen,
Version 1.00 - 1.23, nacheinander ausgeführt!
Analog verhält es sich bei einem Update. Hat man die Version 1.07 von einem Plugin installiert und möchte nun
auf Version 1.13 updaten, so werden beim Update alle SQL-Dateien ab 1.08 bis 1.13 ausgeführt.

ab Shop Version 5.x
"""""""""""""""""""

Ab Shop 5.0.0 wird der Unterordner ``sql/`` *nicht mehr unterstützt* und somit auch keine SQL-Dateien mehr
ausgeführt. |br|

.. hint::

    Plugins können nun, wie der Shop selbst, *Migrationen* nutzen.

Diese müssen *nicht mehr* in der ``info.xml`` definiert werden, sondern im Unterordner ``Migrations/``
des Plugin-Verzeichnisses liegen. |br|
Das Namensschema der Datei- und somit auch Klassennamen lautet ``Migration<YYYYMMDDHHmi>.php``.

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

Alle Plugin-Migrationen müssen das Interface ``JTL\Update\IMigration`` implementieren
und im Namespace ``Plugin\<PLUGIN-ID>\Migrations`` liegen. |br|
Dieses Interface definiert die zwei wichtigsten Methoden ``up()`` zur Ausführung von SQL-Code
und ``down()`` zum Zurücknehmen dieser Änderungen.

Ein **Beispiel** könnte wie folgt lauten:

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

Ab Shop 5.0.0 können Plugin-Optionen mehrsprachig gestaltet werden. |br|
Zu diesem Zweck kann ein Plugin vom gleichen Mechanismus Gebrauch machen,
wie das Shop-Backend - `gettext <https://www.gnu.org/software/gettext/>`_.

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
``info.xml``, im Abschnitt ":ref:`label_infoxml_locale`".

.. _label_adminmenu_structure:

"adminmenu" Struktur
--------------------

Das Adminmenu befindet sich bei einem Shop, der Version bis 4.x, in jedem Versionsordner des Plugins und
bei Shops ab Version 5.x, direkt in der Plugin-Root. |br|
(Falls kein Adminmenu in der ``info.xml`` definiert wurde, kann dieser Ordner auch weggelassen werden.)

Ein Plugin kann beliebig viele eigene Links (:ref:`label_infoxml_custom_links`) im Adminbereich enthalten. |br|
Falls *Custom Links* in der ``info.xml`` angegeben wurden, muss in jedem Ordner ``adminmenu/``, für jeden
*Custom Link*, eine entsprechende PHP-Datei enthalten sein. |br|

.. code-block:: xml
   :emphasize-lines: 4

    <Adminmenu>
        <Customlink sort="1">
            <Name>Statistik</name>
            <Filename>stats.php</Filename>
        </Customlink>
    </Adminmenu>

In diesem Beispiel wird im Shop-Backend ein *Custom Link* erstellt, der als Tab mit dem Namen "Statistik" erscheinen
soll.  Dieser Tab führt die Datei ``stats.php``, im Ordner ``adminmenu``, aus. Diese Datei inkludiert die Smarty
Templateengine und lädt ein eigenes Template, das in einem selbst definierten Ordner abgelegt werden kann.

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

"frontend" Struktur
-------------------

Im Frontendmenü können selbst definierte Links im Shop-Frontend erstellt werden, so dass dort eigene PHP-Dateien
ausgeführt werden. |br|
Der Ordner ``frontend/`` befindet sich, bei Shop Version 4.x, im jeweiligen Versionsordner des Plugins und ab
Shop Version 5.x direkt in der Plugin-Root. |br|
(Falls kein Frontendmenü in der ``info.xml`` definiert wurde, kann dieser Ordner auch weggelassen werden.) |br|
Es können beliebig viele *Frontend Links* eingebunden werden.

Wie *Fontend Links*, in der ``infox.xml``, definiert werden, finden sie im Abschnitt :ref:`label_infoxml_fontendlinks`.

Jeder *Frontend Link* benötigt eine Smarty-Templatedatei, um Inhalt im Shop anzuzeigen. |br|
Diese Templatedatei liegt im ``template/``-Ordner des jeweiligen Ordners ``frontend/``.
Der Pfad zur Templatedatei für das untere Beispiel würde also ``/meinplugin/version/102/frontend/template/`` lauten.

**Beispiel für Shop Version 5.x:**

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

Um dies zu bewerkstelligen, bietet die Pluginverwaltung die Spalte "Linkgruppe".
Der, im Falle vorhandener *Frontend Links*, dort angezeigte Button führt den Administrator zur Verwaltung der
Linkgruppen (ab Shop Version 4.x "Seiten -> Eigene Seiten"). |br|

Die Installation des Plugins stellt *Frontend Links* (in Shop3 in die erste CMS Linkgruppe) ab Shop Version 4
in die Linkgruppe "*hidden*" ein.

Die Links des jeweiligen Plugins werden hier farblich hervorgehoben, um das Auffinden der plugin-eigenen
*Frontend Links* zu erleichtern. |br|
Die *Fontend Links* des Plugins können nun, via Selectbox, in andere Linkgruppen verschoben werden.


.. _label_aufbau_frontend_res:

Frontend Ressourcen
-------------------

Weiterhin gehören zur Struktur des Verzeichnisses ``frontend/`` die zusätzlichen "*Frontend Ressourcen*".

**Beispiel bis Shop Version 4.x:**

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

**Beispiel ab Shop Version 5.x:**

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

Weitere Informationen finden Sie im ``info.xml``-Abschnitt ":ref:`label_aufbau_fontend_res`".


.. _label_aufbau_boxen:

Boxen
-----

Ein Plugin kann ebenso Boxen für das Shop-Frontend mitbringen. |br|
Das Verzeichnis für diese Darstellungselemente befinden sich ebenfalls im Ordner ``frontend/``.

**Beispiel bis Shop Version 4.x:**

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

    Von Shop 4.x zu Shop 5.0 hat sich der Name dieses Verzeichnisses von ``boxen/`` (Shop 4.x)
    zu ``boxes/`` (ab Shop 5.0) geändert.

**Beispiel ab Shop Version 5.x:**

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

Wie Sie diese neuen Boxen in der ``info.xml`` definieren und dem Shop bekannt machen,
finden Sie im Abschnitt ":ref:`label_infoxml_boxen`".


.. _label_aufbau_widgets:

Widgets
-------

Auch im Backend des Shops lassen sich via Plugin neue Elemente einfügen. So z.B. im Dashboard des
Administrationsbereiches. |br|
Hierfür werden *Widgets* eingesetzt. Wie sie der Logik des Shops bekannt gemacht werden, erfahren Sie im
``info.xml``-Abschnitt ":ref:`label_infoxml_widgets`".

Platziert werden die zugehörigen Dateien wie folgt:

**bis Shop Version 4.x:**

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

**ab Shop Version 5.x:**

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

Lizensierung
------------

Bei kommerziellen Shop-Plugins ist es möglich, eine eigene Klasse die Lizenzprüfung erledigen zu lassen. |br|
Nähere Informationen hierzu, finden Sie im Kapitel ``info.xml`` unter dem Abschnitt ":ref:`label_infoxml_license`".

Ihre Klasse zur Lizenzprüfung erhält hier ihren Platz:

**bis Shop verion 4.x:**

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

**ab Shop Version 5.x:**

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

Der Platz, im Root-Verzeichnis Ihres Plugins, ist für Shop Version 4.x, wie auch für 5.x, der Gleiche. |br|






Exportformate
-------------

Mit einem Plugin-Exportformat lassen sich neue Exportformate in den JTL-Shop integrieren.
Sie erstellen einen neues Exportformate, indem Sie folgenden neuen Block in der info.xml anlegen:

.. code-block:: xml

    <ExportFormat>
     ...
    </ExportFormat>

In diesem Block können beliebig viele Unterelemente vom Typ <Format> liegen. Das heißt, ein Plugin kann beliebig viele Exportformate anlegen.

XML Darstellung in der info.xml:

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
| ``<Name>``                         | Name des Exportformates                                                                                     |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<FileName>``                     | Dateiname ohne Pfadangabe in welche die Artikel exportiert werden sollen                                    |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Header>``                       | Kopfzeile der Exportdatei                                                                                   |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Content>``                      | Exportformat (Smarty)                                                                                       |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<footer>``                       | Fußzeile der Exportdatei                                                                                    |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<Encoding>``                     | ASCII oder UTF-8-Kodierung der Exportdatei                                                                  |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<VarCombiOption>``               | 1 = Väter- und Kindartikel exportieren / 2 = Nur Väterartikel exportieren / 3 = Nur Kindartikel exportieren |
+------------------------------------+-------------------------------------------------------------------------------------------------------------+
| ``<SplitSize>``                    | In wie große Dateien soll das Exportformat gesplittet werden? (Megabyte)                                    |
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

Portlets (ab 5.0.0)
-------------------

Ab Shop 5.0.0 können Plugins auch :doc:`Portlets </shop_plugins/portlets>` für den *OnPageComposer* mitbringen.

**ab Shop Version 5.x:**

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
Nachzulesen im Abschnitt ":ref:`label_infoxml_portlets`".

Alles, was logisch zu einem Portlet gehört, befindet sich in einem eigenen Verzeichnis. |br|
Wie ein solches Portlet-Unterverzeichnis im Einzelnen aussehen kann, lesen Sie
im Abschnitt :doc:`Portlets </shop_plugins/portlets>`.

.. _label_aufbau_blueprints:

Blueprints (ab 5.0.0)
---------------------

Ab Shop 5.0.0 können Plugins auch Blueprints, also *Kompositionen von einzelnen Portlets*, definieren. |br|
Wie dies per ``info.xml`` dem Shop mitgeteilt wird lesen im Abschnitt ":ref:`label_infoxml_blueprints`".

**ab Shop Version 5.x:**

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


Änderungen von Shop Version 4.x zu Version 5.x
----------------------------------------------

Hier eine kurze Zusammenfassung aller Änderungen für Plugins von Shop 4.X zu 5.X

* neuer Installationsordner: ``<SHOP-ROOT>/plugins/<PLUGIN-ID>/``
* keine Unterordner ``version/<VERSION>/`` mehr
* XML-Root ``<jtlshopplugin>`` statt ``<jtlshop3plugin>``
* Knoten ``<Version>`` als Unterknoten von ``<Install>`` entfallen
* ``<CreateDate>`` und ``<Version>`` müssen als Unterknoten von ``<jtlshopplugin>`` angegeben werden und nicht mehr
  von ``<Install><Version>``
* Plugins erhalten den Namespace ``Plugin\<PLUGIN-ID>``
* Plugins können Migrationen ausführen aber keine SQL-Dateien
* Widget-Klassen entsprechen der in der info.xml definierten Klasse und erfordern keinerlei weitere Konventionen
* Plugins können Lokalisierungen anbieten
* Plugins können Portlets und Blueprints definieren
