Aufbau
======

Ein Plugin besteht aus einer Verzeichnisstruktur die physikalisch auf dem Datenträger des Shops vorhanden sein muss und einer XML-Datei (info.xml), die für die Installation und Updates des Plugins zuständig ist.
Die info.xml ist der zentrale Kern jedes Plugins. Dort wird definiert, welche Dateien ein Plugin nutzt, welche Aufgaben es übernehmen soll und welche Identität das Plugin hat.

Die Installationsdatei und damit auch die Verzeichnisstruktur variiert je nach Aufgabenbereich des jeweiligen Plugins. In der JTL-Shop Ordnerstruktur existiert ein fest definierter Ordner, der alle Plugins beinhaltet.
Von dort aus greift das System auf Pluginressourcen und Installationsinformationen zu.

.. note::

    Ein Plugin zur automatischen Erstellung von JTL-Shop-Plugins findet sich im `öffentlichen Gitlab-Repository <https://gitlab.jtl-software.de/jtlshop/JTLpluginBootstrapper>`_.
    Dadurch kann das manuelle Erstellen der info.xml und der Dateistruktur entfallen.

Verzeichnisstruktur
-------------------

Ein Plugin benötigt eine festdefinierte Verzeichnisstruktur, damit es installiert werden kann. Es gibt einige Ausnahmen, wobei man gewisse Verzeichnisse weglassen oder nach eigenen Vorlieben strukturieren kann.
Jedes Plugin hat sein eigenes Unterverzeichnis innerhalb des Pluginverzeichnisses.

Das Pluginverzeichnis ``plugins``, in dem alle Plugins des Shops zu finden sind, liegt im Ordner ``/includes/``, welcher im Shop Root zu finden ist. Demnach könnte ein typisches Plugin unter ``<shoproot>/includes/plugins/<Ihr_Pluginordner>`` zu finden sein.

Es sollte darauf geachtet werden, stets aussagekräftige und eindeutige Pluginnamen zu vergeben, damit es niemals eine Pluginverzeichniskollision gibt, was andernfalls bei vielen Plugins unterschiedlicher Autoren in einen Shop vorkommen könnte.
Das neuere Pluginverzeichnis würde demnach beim Upload das ältere überschreiben und das ursprüngliche Plugin würde nicht mehr funktionieren. Wir empfehlen daher dringend, das Pluginverzeichnis um eindeutige Merkmale wie z.B. den Firmennamen des Autors zu erweitern.

Jedes Plugin muss mindestens einen Versionsordner enthalten. Die Versionen fangen bei der Ganzzahl 100 an (Bedeutung Version 1.00) und werden mit 101, 102 usw. weitergeführt.
Die ganzzahligen Versionssnummern sind gleichzeitig die Ordnernamen. D.h. jedes Plugin muss auf jeden Fall den Ordner ``100/`` enthalten (siehe Versionen).

Mögliche weitere Ordner
-----------------------

+---------------+-------------------------------------------------------------------------------------------------------+
| Ordnername    | Funktion                                                                                              |
+===============+=======================================================================================================+
| adminmenu     | Shopadmin Tabs, um eigenen Inhalt im Adminbereich auszugeben bzw. um Einstellungen zu implementieren. |
+---------------+-------------------------------------------------------------------------------------------------------+
| frontend      | Frontend Links zu Seiten im Shop mit eigenem Inhalt                                                   |
+---------------+-------------------------------------------------------------------------------------------------------+
| paymentmethod | Implementierung von Zahlungsmethoden im Shop.                                                         |
+---------------+-------------------------------------------------------------------------------------------------------+
| sql           | SQL-Datei, um eigene Datenbanktabellen anzulegen, Daten dort abzulegen oder zu verändern.             |
+---------------+-------------------------------------------------------------------------------------------------------+


Versionen
---------

Da sich Plugins mit der Zeit auch weiterentwickeln können, gibt es eine Versionierung der Plugins.
Damit besteht die Möglichkeit, ein Plugin mit dem Updatemechanismus des Pluginsystems zu aktualisieren, um neue Funktionalität einzuführen oder Fehler zu beheben.

Jedes Plugin muss den Ordner ``version`` enthalten. Dieser Ordner enthält alle bisher erschienenen Versionen des Plugins. Jedes Plugin muss die niedrigste Grundversion 100 (Bedeutung Version 1.00) enthalten.
In den jeweiligen Unterordnern (Pluginversionen) befinden sich alle Ressourcen des Plugins für die jeweilige Version.

Wird eine neue Version vom Plugin entwickelt, wird die Version um 1 hochgezählt, d.h. die Versionierung von Plugins ist fortlaufend: 100, 101, 102, 103, …
Eine Versionsgrenze nach oben existiert nicht.

Um ein Plugin zu aktualisieren, überträgt man die info.xml in das jeweilige Pluginverzeichnis sowie alle neuen Versionsverzeichnisse in das Verzeichnis ``version`` des jeweiligen Pluginverzeichnisses.
D.h. wurde etwa die Version 113 von einem Plugin erstellt, so kopiert man die <pluginname>/info.xml sowie alle <pluginname>/version/* Versionsverzeichnisse in den Shop.
Die Pluginverwaltung im Adminbereich erkennt dabei automatisch, ob Updates zu einem Plugin vorliegen und bietet einen entsprechenden Updatebutton an.

Beispiel:
In der info.xml wurden zwei Versionen definiert. Demnach würden die Unterordner von *version* wie folgt aussehen: */version/100/* und */version/101/*.

Für jede Version, die in der Installationsdatei definiert wurde, muss auch ein physischer Ordner existieren.


info.xml
--------

Auf der obersten Ebene eines Pluginverzeichnisses des jeweiligen Plugins, liegt die XML Installationsdatei *info.xml*.
Jedes Plugin muss eine Datei names info.xml enthalten, die alle Informationen über das Plugin und seine Ressourcen enthält. Den Aufbau dieser XML-Installationsdatei beschreiben die folgenden Abschnitte.


SQL
---

Jede Version eines Plugins hat die Möglichkeit, eine SQL-Datei anzugeben, welche beliebige SQL-Befehle ausführt.
Diese SQL-Datei kann z.B. zum Erstellen neuer Tabellen oder zum Verändern von Daten in der Datenbank genutzt werden.
Falls in der info.xml eine SQL-Datei angegeben wurde, muss diese auch physikalisch vorhanden sein.
Zu beachten gilt, wenn eine neue Tabelle in der SQL-Datei angelegt wird, sprich der SQL Befehl ``CREATE TABLE`` genutzt wird, muss der Tabellenname eine bestimmte Konvention einhalten.
Der Name muss mit *xplugin_* beginnen, gefolgt von der eindeutigen *PluginID_* und endet mit einem beliebigen Namen (Syntax: ``xplugin_<PluginID>_<belieber Name>``).

Beispiel: Lautet die PluginID jtl_exampleplugin und die Tabelle soll **tuser** heißen, so muss der Tabellenname letztlich **xplugin_jtl_exampleplugin_tuser** lauten.
Der SQL-Ordner liegt im Ordner jeweiligen Pluginversion. Beispiel: Ein Plugin in der Version 102:

.. code-block:: xml

    <Version nr ="102">
        <SQL>install.sql</SQL>
        <CreateDate>2016-03-17</CreateDate>
    </Version>

Hier muss die Datei *install.sql* (der Dateiname der SQL-Datei wird in der info.xml festgelegt) im SQL-Ordner namens **sql** der Version 102 liegen. Der Dateipfad sieht daher in diesem Beispiel wie folgt aus:

``<pluginname>/version/102/sql/install.sql``

Pro Pluginversion kann es immer nur eine SQL-Datei geben. Falls in der info.xml keine SQL-Datei für eine Version angegeben wurde, sollte man das SQL-Verzeichnis in der jeweiligen Version weglassen.

Bei der Installation wird jede SQL-Datei von der kleinsten zur größten Version inkrementell abgearbeitet. D.h. liegt ein Plugin in der Version 1.23 vor, so werden bei der Installation die SQL-Dateien aller Versionen, Version 1.00 - 1.23, nacheinander ausgeführt!
Analog verhält es sich bei einem Update. Hat man die Version 1.07 von einem Plugin installiert und möchte nun auf Version 1.13 updaten, so werden beim Update alle SQL-Dateien ab 1.08 ausgeführt.

Adminmenü Verzeichnisstruktur
-----------------------------

Das Adminmenu befindet sich in jedem Versionsordner eines Plugins. Falls kein Adminmenu in der info.xml definiert wurde, kann dieser Ordner weggelassen werden.
Ein Plugin kann beliebig viele eigene Links (Custom Links) im Adminbereich enthalten. Falls Custom Links in der info.xml angegeben wurden, muss in jedem Ordner adminmenu für jeden Custom Link eine ausführbare PHP-Datei enthalten sein.
Weitere Verzeichnisse sind dem Pluginentwickler selbst überlassen. Es ist natürlich auch möglich, das Adminmenü nur mit Einstellungen (Setting Links) zu füllen.


.. code-block:: xml

    <Adminmenu>
        <Customlink sort="1">
            <Name>Statistik</name>
            <Filename>stats.php</Filename>
        </Customlink>
    </Adminmenu>

In diesem Beispiel wird im Shop-Backend ein Custom Link erstellt, der als Tab mit dem Namen "Statistik" erscheinen soll. Dieser Tab führt die Datei stats.php im Ordner adminmenu aus.
Diese Datei inkludiert die Smarty Templateengine und lädt ein eigenes Template, das in einem selbstdefinierten Ordner abgelegt werden kann.

Frontendmenu Verzeichnisstruktur
--------------------------------

Im Frontendmenü können selbstdefinierte Links im Shop-Frontend erstellt werden, wo eigene PHP-Dateien ausgeführt werden. Der Ordner ``frontend`` befindet sich im jeweiligen Versionsordner des Plugins.
Falls kein Frontendmenü in der info.xml definiert wurde, kann dieser Ordner auch weggelassen werden. Es können beliebig viele Frontend Links eingebunden werden.

Jeder Frontend Link benötigt eine Smarty Templatedatei, um Inhalt im Shop anzuzeigen. Diese Templatedatei liegt im ``template``-Ordner des jeweiligen Ordners ``frontend``.
Der Pfad zur Templatedatei für das untere Beispiel würde also ``/meinplugin/version/102/frontend/template/`` lauten.

Sobald man ein Plugin installiert hat das Frontend Links beinhaltet, sollte man darauf achten, dass die Links den jeweiligen Linkgruppen des Shops zugewiesen werden müssen.
Dazu kann man in der Pluginverwaltung in der Spalte Linkgruppen den Button Bearbeiten klicken und man gelangt zur Linkübersicht des Shops, wo man einzelne Links in andere Linkgruppen verschieben kann.
Die Installation des Plugins stellt Frontend Links im Shop3 standardmäßig in die erste CMS Linkgruppe ein, ab Version 4 in die Linkgruppe *hidden*.

Die Links des jeweiligen Plugins werden farblich markiert dargestellt. Diese können nun in die gewünschte Linkgruppe via Selectbox verschoben werden.

Paymentmethod Verzeichnisstruktur
---------------------------------

Ein Plugin kann beliebig viele Zahlungsmethoden im Shop implementieren. Im jeweiligen Versionsordner des Plugins wird im Falle, dass das Plugin Zahlungsarten hinzufügen soll, der Unterordner ``paymentmethod`` notwendig.
Für eine bessere Übersicht sollte für jede Zahlungsmethode die das Plugin implementieren soll im Ordner ``paymentmethod`` ein Unterordner angelegt werden.
In diesem Unterorder liegt dann die PHP-Klassendatei und weitere Ressourcen für die jeweilige Zahlungsmethode. Im Beispiel heißt der Ordner für die Zahlungsmethode ``paypal``.

.. code-block:: xml

    <ClassFile>paypal/paypal.class.php</ClassFile>
    <TemplateFile>paypal/template/bestellabschluss.tpl</TemplateFile>

Für jede Zahlungsmethode kann eine Template-Datei angegeben werden. Diese ist für die Anzeige der zahlungsartspezifischen Inhalte zuständig.

Aufbau der info.xml
-------------------

In der XML-Installationsdatei *info.xml* werden das Plugin und seine Funktionen sowie Ressourcen definiert. Diese Datei ist das wichtigste Element eines Plugins, da sie für die Installation und Updates zuständig ist.
Informationen wie der Pluginname, der Autor oder die Beschreibung werden in dieser Datei hinterlegt. Es werden Hooks, an dem das Plugin eingebunden werden soll sowie Pfade zu Ressourcen definiert.
Die Installation von Plugins besteht aus zwei Schritten und kann im laufenden Betrieb des Shops vorgenommen werden:

* Upload des Plugins in das Verzeichnis ``includes/plugins/`` des Shops oder ab Version 4 via direktem Upload im Backend
* Installationsanstoß im Shopadmin über den Link *Pluginverwaltung*. Die Installation verläuft vollautomatisch.

Ein weiterer wichtiger Aspekt ist, dass die Installationsdatei (info.xml) auch für Updates des Plugins zuständig ist.

Der Inhalt der info.xml ist in XML. Ein Plugin kann in die folgenden Hauptbestandteile aufgeteilt werden:

* Globale Plugin-Informationen
* Versionen
* Adminmenü mit Custom Links und Setting Links
* Zahlungsmethoden
* Frontend Links
* Sprachvariablen
* E-Mail-Templates
* Plugin-Boxen
* Plugin-Lizensierung
* statische Ressourcen

Falls Bereiche im Plugin nicht gebraucht werden, sollte der komplette Block weggelassen werden. Die globalen Informationen können dabei nicht weggelassen werden.

Der Rumpf
---------

Das Hauptelement der XML-Datei heißt sowohl für Shop3 als auch Version 4 *<jtlshop3plugin>.* Damit wird der Rumpf festgelegt.

.. code-block:: xml

  <jtlshop3plugin>
    ...
  </jtlshop3plugin>

Globale Plugin-Informationen
----------------------------

Nach dem Rumpf der XML-Datei folgen allgemeine Informationen, die als Kindelemente angehängt werden.

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


+-------------------+-------------------------------------------------+
| Elementname       | Funktion                                        |
+===================+=================================================+
| Name*             | Name des Plugins ([\a-\zA-\Z0-\9_])             |
+-------------------+-------------------------------------------------+
| Description       | Pluginbeschreibung                              |
+-------------------+-------------------------------------------------+
| Author            | Herausgeber eines Plugins                       |
+-------------------+-------------------------------------------------+
| URL               | Link zum Pluginherausgeber                      |
+-------------------+-------------------------------------------------+
| XMLVersion*       | XML Installationsroutinen Version ([0-9]{3})    |
+-------------------+-------------------------------------------------+
| ShopVersion       | Mindest-Shop-Version (>= 300, < 400)            |
+-------------------+-------------------------------------------------+
| Shop4Version      | Mindest-Shop4-Version (>= 400)                  |
+-------------------+-------------------------------------------------+
| PluginID*         | Plugin-Identifikator ([\a-\zA-\Z0-\9_])         |
+-------------------+-------------------------------------------------+
| Icon              | Dateiname zu einem Icon                         |
+-------------------+-------------------------------------------------+

(*)Pflichtfelder

Name
~~~~

Der Name des Plugins wird in der Pluginverwaltung und den automatisch generierten Menüs im Backend dargestellt und dient der Identifizierung des Plugins.

Description
~~~~~~~~~~~

Die Beschreibung wird unterhalb des Plugin-Namens im Tab "Verfügbar" der Pluginverwaltung dargestellt und sollte eine kurze Funktionsbeschreibung des Plugins enthalten.


Author
~~~~~~

Der Autor wird im Admin-Menü des Plugins dargestellt. Hier kann sowohl eine Firma als auch eine Privatperson eingetragen werden.


URL
~~~

Die URL sollte einen Link zum Hersteller oder einer dedizierten Plugin-Seite enthalten, sodass der Kunde schnell und einfach weitere Informationen oder Support erhalten kann.

XMLVersion
~~~~~~~~~~

Da sich mit der Zeit auch die Anforderungen an das Pluginsystem ändern können, kann sich auch die XML-Installationsdatei ändern. Daher ist die Angabe der XML-Version sehr wichtig, um auch die richtigen Parameter für das eigene Plugin zur Verfügung zu haben.

ShopVersion
~~~~~~~~~~~

ShopVersion gibt die Mindest-Version für Shop3 an. Ist sie höher als die aktuell installierte Shopversion, so wird eine Fehlermeldung im Backend angezeigt und das Plugin kann nicht installiert werden.
Falls nur dieser Wert, nicht aber ``Shop4Version`` konfiguiert wurde, erscheint in einem Shop 4.00+ ein Hinweis, dass das Plugin möglicherweise nicht in dieser Version funktioniert, es kann jedoch trotzdem installiert werden.

Shop4Version
~~~~~~~~~~~~

Shop4Version gibt die Mindest-Version für Shop4 an. Wurde nur dieser Wert und nicht ``ShopVersion`` konfiguriert, ist eine Installation nur in JTL Shop 4.00+ möglich.


PluginID
~~~~~~~~

Die PluginID identifiziert ein Plugin im Shop eindeutig. Es muss genau darauf geachtet werden, eine sinnvolle und einmalige ID für das eigene Plugin zu wählen, damit gleichnamige Plugins unterschiedlicher Hersteller nicht kollidieren.

Beispiel-ID für ein Plugin: **SoftwareFirma_PluginName**

Namenskonvention: Es sind nur Zeichen a-z bzw. A-Z, 0-9 und der Unterstrich erlaubt (Punkt und Bindestrich sind laut Konvention nicht erlaubt).


Icon
~~~~

Aktuell noch nicht implementiert, perspektivisch zur besseren Übersicht geplant.


Install-Block
~~~~~~~~~~~~~

Nach den Globalen Plugin-Informationen folgt der Installationsblock. Dieser sieht wie folgt aus:

 <Install>

 </Install>

Alle Informationen zum Plugin wie z.B. Version und verwendete Hooks werden in diesem Block als Kindelemente aufgeführt.

Plugin-Versionierung
--------------------

Ein Plugin kann beliebig viele Versionen beinhalten. Die Versionierung fängt ab Version 100 an und wird dann mit 101, 102 usw. weitergeführt.
Es muss mindestens ein Block mit der Version 100 vorhanden sein.

.. code-block:: xml

    <Version nr="100">
        <CreateDate>2015-05-17</CreateDate>
    </Version>

Es besteht zu jeder Version die Möglichkeit, eine SQL-Datei anzugeben, die bei der Installation bzw. Aktualisierung ausgeführt wird. Hierbei gilt es die Pluginverzeichnisstruktur für SQL-Dateien zu beachten.

.. code-block:: xml

    <Version nr="100">
        <SQL>install.sql</SQL>
        <CreateDate>2016-05-17</CreateDate>
    </Version>


+-------------+-------------------------------------------+
| Elementname | Funktion                                  |
+=============+===========================================+
| nr*         | Versionsnummer des Plugins ([0-9]+)       |
+-------------+-------------------------------------------+
| SQL         | SQL-Datei                                 |
+-------------+-------------------------------------------+
| CreateDate  | Erstellungsdatum der Version (YYYY-MM-DD) |
+-------------+-------------------------------------------+

(*)Pflichtfelder

Falls weitere Versionen zu einem Plugin existieren, werden diese untereinander aufgeführt.

.. code-block:: xml

    <Version nr="100">
        <CreateDate>2015-03-25</CreateDate>
    </Version>
    <Version nr="101">
        <CreateDate>2015-04-15</CreateDate>
    </Version>


Plugin-Hooks
------------

Nach der Versionierung folgt das ``<Hooks>`` Element. In diesem Element werden jene Stellen im Shop definiert, an denen das Plugin Code ausführen soll.

Der Frontend-Link und Zahlungsmethoden benötigen keine expliziten Hookangaben, da diese an einem bestimmten Hook vom System aus eingebunden werden. In diesem Fall kann der Hook Block ganz weggelassen werden.

.. code-block:: xml

    <Hooks>
        <Hook id="129">onlineuser.php</Hook>
        <Hook id="130">managemenet.php</Hook>
    </Hooks>

Die ID identifiziert hierbei eindeutig eine bestimmte Stelle im Shopcode. Die angegebene PHP-Datei wird dann am Hook der ID ausgeführt.
Möchten Sie Beispielsweise nach dem Erstellen eines Artikelobjektes am Objekt noch einige Member verändern, können Sie den entsprechenden Hook benutzen um dies zu erledigen.

+-------------+------------------------------------------------------------------------+
| Elementname | Funktion                                                               |
+=============+========================================================================+
| id*         | Eindeutige HookID ([0\-9]+)                                            |
+-------------+------------------------------------------------------------------------+
| priority    | Priorität (ab Version 4.05, niedriger => früherer Auführung) ([0\-9]+) |
+-------------+------------------------------------------------------------------------+
| Hook        | PHP-Datei im Ordner frontend, die an ID ausgeführt wird.               |
+-------------+------------------------------------------------------------------------+

(*) Pflichtfelder

Eine Liste der Hook-IDs finden Sie in der :doc:`Hook-Referenz </shop_plugins/hook_list>`.


Adminmenü
---------

Im Administrationsbereich des JTL Shops werden im Menüpunkt **Pluginverwaltung** alle Plugins angezeigt, die entweder nicht installiert (verfügbar), fehlerhaft oder installiert sind.
Falls kein Adminmenü gewünscht ist, lassen Sie bitte den kompletten <Adminmenu> Block weg.

Fehlerhafte Plugins werden mit dem entsprechenden Fehlercode angezeigt. Eine Tabelle mit möglichen Fehlercodes, finden Sie unter :doc:`Fehlercodes </shop_plugins/fehlercodes>`.

In der XML-Installationsdatei wird das Adminmenü unter dem ``<Hooks>`` Element positioniert.

.. code-block:: xml

    <Adminmenu>
        ...
    </Adminmenu>

In diesem Element folgen nach Bedarf das Kindelement ``<Customlink>`` (Custom Links) und ``<Settinglink>`` (Setting Links). Falls kein ``<Customlink>`` und ``<Settinglink>`` existiert, wird der ``<Adminmenu>`` Block weggelassen.


Objektcache
-----------

Sollen bei Installation des Plugins bestimmte Inhalte des Objektcaches gelöscht werden, weil das Plugin beispielsweise Artikeldaten modifizieren soll, so kann im Element ``<FlushTags>`` eine Liste von Tags angegeben werden.

.. code-block:: xml

    <FlushTags>CACHING_GROUP_CATEGORY, CACHING_GROUP_ARTICLE</FlushTags>

Für weitere Informationen zum Caching und den vorhandenen Tags, siehe Kapitel :doc:`Cache </shop_plugins/cache>`.


Custom Links
------------

Custom Links werden im Adminbereich unter dem jeweiligen Plugin angezeigt. Mit Hilfe dieser Links kann ein Plugin Seiten mit eigenem Inhalt im Backend anlegen, die Informationen für den Shopbetreiber bereitstellen.
Customlinks werden im Backend in Tabs dargestellt.

.. code-block:: xml

    <Customlink sort="1">
        <Name>Statistik</Name>
        <Filename>stats.php</Filename>
    </Customlink>


+-------------+-------------------------------------+
| Elementname | Funktion                            |
+=============+=====================================+
| sort*       | Sortierungsnummer des Tabs          |
+-------------+-------------------------------------+
| Name*       | Name des Tabs ([a\-zA\-Z0\-9\_\-]+) |
+-------------+-------------------------------------+
| Filename*   | Ausführbare PHP-Datei               |
+-------------+-------------------------------------+

(*)Pflichtfelder

Setting Links
-------------

Setting Links sind Tabs, die Einstellungen zum Plugin abfragen. Hier können beliebig viele Einstellungen angelegt werden.
Einstellungen können unterschiedliche Werte abfragen (Text, Zahl, Auswahl aus einer Selectbox). Diese Einstellungen können durch den Shopbetreiber im Backend konfiguriert und dann im eigenen Plugin-Code abgefragt werden.

.. code-block:: xml

    <Settingslink sort="2">
        <Name>Einstellungen</Name>
        <Setting type="text" initialValue="Y" sort="4" conf="N">
            <Name>Online Watcher</Name>
            <Description>Online Watcher</Description>
            <ValueName>onlinewatcher</ValueName>
        </Setting>
    <Settingslink>

+-------------+---------------------+
| Elementname | Funktion            |
+=============+=====================+
| Name*       | Name des Tabs       |
+-------------+---------------------+
| Setting*    | Einstellungselement |
+-------------+---------------------+

(*)Pflichtfelder


+------------------+-------------------------------------------------------------------+
| Elementename     | Funktion                                                          |
+==================+===================================================================+
| Name*            | Name der Einstellung ([a\-zA\-Z0\-9\_\-]+)                        |
+------------------+-------------------------------------------------------------------+
| type*            | Einstellungstyp (text, zahl, selectbox, ab Shop4 checkbox, radio) |
+------------------+-------------------------------------------------------------------+
| initialValue*    | Vorrausgewählte Einstellung                                       |
+------------------+-------------------------------------------------------------------+
| Setting sort     | Sortierung der Einstellung (Höher = weiter unten)                 |
+------------------+-------------------------------------------------------------------+
| conf*            | Y = echte Einstellung, N = Überschrift                            |
+------------------+-------------------------------------------------------------------+
| Description      | Beschreibung der Einstellung                                      |
+------------------+-------------------------------------------------------------------+
| ValueName*       | Name der Einstellungsvariable, die im PHP-Code genutzt wird       |
+------------------+-------------------------------------------------------------------+
| SelectboxOptions | Optionales Kindelement bei type = selectbox                       |
+------------------+-------------------------------------------------------------------+
| RadioOptions     | Optionales Kindelement bei type = radio                           |
+------------------+-------------------------------------------------------------------+
| sort*            | Sortierungsnummer des Tabs                                        |
+------------------+-------------------------------------------------------------------+
| OptionsSource    | Dynamische Quelle für Optionen in Checkbox/Selectbox              |
+------------------+-------------------------------------------------------------------+

(*)Pflichtfelder

Falls der Typ der Einstellung eine **selectbox** ist, muss das Kindelement <SelectboxOptions> angegeben werden.

.. code-block:: xml

    <SelectboxOptions>
        <Option value="Y" sort="1">Ja</Option>
        <Option value="N" sort="2">Nein</Option>
    </SelectboxOptions>

+-------------+----------------------------------------------+
| Elementname | Funktion                                     |
+=============+==============================================+
| Option*     | Angezeigter Wert in der Selectbox-Option     |
+-------------+----------------------------------------------+
| value*      | Wert der Selectbox-Option                    |
+-------------+----------------------------------------------+
| sort        | Sortierung der Option (Höher = weiter unten) |
+-------------+----------------------------------------------+

(*)Pflichtfelder


Falls der Typ der Einstellung **radio** ist, muss das Kindelement <RadioOptions> angegeben werden.

.. code-block:: xml

    <RadioOptions>
        <Option value="Y" sort="1">Ja</Option>
        <Option value="N" sort="2">Nein</Option>
        <Option value="V" sort="3">Vielleicht</Option>
    </RadioOptions>

+-------------+----------------------------------------------+
| Elementname | Funktion                                     |
+=============+==============================================+
| Option*     | Angezeigter Wert in der Radio-Option         |
+-------------+----------------------------------------------+
| value*      | Wert der Radio-Option                        |
+-------------+----------------------------------------------+
| sort        | Sortierung der Option (Höher = weiter unten) |
+-------------+----------------------------------------------+

(*)Pflichtfelder

Ab Version 5.0.0 kann als Typ auch "none" gewählt werden. Diese Optionen werden nicht im Settings-Tab angezeigt.
Dies bietet sich an, falls eine eigene Darstellung in einem anderen Tab für die Option gewählt werden soll.
Der Wert wird dann trotzdem in der Plugin-Instanz gespeichert, sodass kein Umweg über eigene SQL-Logik erforderlich ist.
Allerdings muss der Objektcache ggf. manuell invalidiert werden.


Statt oder zusätzlich zu RadioOptions bzw. SelectboxOptions kann seit Version 4.05 das Element OptionsSource hinzugefügt werden.
Sobald es vorhanden ist, wird das RadioOptions- bzw. SelectboxOptions-Element ignoriert.

+-------------+----------------------------------------------+
| Elementname | Funktion                                     |
+=============+==============================================+
| File*       | Dateiname, relativ zu adminmenu              |
+-------------+----------------------------------------------+

Hierdurch können in einer PHP-Datei dynamische Optionswerte definiert werden. Dies ist insbesondere dann sinnvoll, wenn keine statischen Auswahlmöglichkeiten wie "Ja/Nein" o.Ä. zur Auswahl angeboten werden sollten, sondern z.B. Artikel/Kategorien/Seiten oder andere Shop-spezifische Werte.

Die angegebene Datei muss ein Array von Objekten ausgeben, wobei als Objektmember jeweils cWert und cName und optional nSort vorhanden sein müssen.

Beispiel für eine dynamische Option:

.. code-block:: php

    <?php
        $options = [];
        $option  = new stdClass();

        $option->cWert = 123;
        $option->cName = 'Wert A';
        $option->nSort = 1;
        $options[]     = $option;

        $option        = new stdClass();
        $option->cWert = 456;
        $option->cName = 'Wert B';
        $option->nSort = 2;
        $options[]     = $option;

        $option        = new stdClass();
        $option->cWert = 789;
        $option->cName = 'Wert C';
        $option->nSort = 2;
        $options[]     = $option;

        return $options;

In diesem Beispie würden entsprechend die 3 Auswahlmöglichkeiten "Wert A", "Wert B" und "Wert C" zur Auswahl stehen.


Frontend Links
--------------

Mit Hilfe von Frontend Links ist ein Plugin in der Lage einen Link im JTL-Shop anzulegen und den Inhalt zu verwalten.
Es können beliebig viele Link-Elemente <Link> angelegt werden. Falls kein Link angegeben wird, sollte der Block <FrontendLink> komplett weggelassen werden.
Normalerweise werden Links im Shopbackend unter CMS (Eigene Seiten) angelegt. Dort können durch Plugins angelegte Links im Nachhinein verwaltet werden.

Jeder Link kann in beliebig vielen Sprachen lokalisiert werden. Dazu wird das Element <LinkLanguage> mit dessen Attribut iso (Großbuchstaben ISO 639-2/B z.b. Deutschland = GER) verwendet.
Es werden jedoch immer nur maximal die Sprachen installiert, die der Shop auch beinhaltet. Hat ein Plugin weniger als die im Shop installierten Sprachen hinterlegt, werden alle weiteren Shopsprachen mit der Standardsprache aufgefüllt.

Jeder Frontend Link benötigt eine Smarty Template-Datei. Es gibt zwei verschiedene Arten, dessen Inhalt anzuzeigen. Die erste Möglichkeit besteht darin, den Inhalt in einem definierten Bereich (Contentbereich) des Shops anzuzeigen.
Dies wird durch das Element <Template> erreicht. Die zweite Möglichkeit wäre, den Inhalt auf einer komplett neuen Seite zu zeigen. Dies benötigt das Element <FullscreenTemplate>.
Beide Anzeigemöglichkeiten können nicht gleichzeitig in der info.xml definiert werden. Eine der beiden Varianten muss gesetzt sein.

Im folgenden Beispiel wird die Smarty Template-Datei (onlineuser.tpl), welche sich im Ordner *template* befindet, im fest definierten Contentbereich des Shops geladen.

.. code-block:: xml

    <FrontendLink>
        <Link>
            <Filename>onlineuser.php</Filename>
            <Name>Online Watcher</Name>
            <Template>onlineuser.tpl</Template>
            <VisibleAfterLogin>N</VisibleAfterLogin>
            <PrintButton>N</PrintButton>
            <NoFollow>N</NoFollow>
            <SSL>2</SSL>
            <LinkLanguage iso="GER">
                <Seo>Online Watcher</Seo>
                <Name>Online Watcher</Name>
                <Title>Online Watcher</Title>
                <MetaTitle>Online Watcher</MetaTitle>
                <MetaKeywords>Online Watcher, Online, Watcher</MetaKeywords>
                <MetaDescription>Zeigt die momentan aktiven Besucher im eingestellten Zeitraum an.</MetaDescription>
            </LinkLanguage>
        </Link>
    </FrontendLink>

Ein Frontend Link benötigt keinen expliziten Hook, denn das System bindet den Link automatisch an einem fest definierten Hook.

Link:

+---------------------+--------------------------------------------------------+
| Elementname         | Funktion                                               |
+=====================+========================================================+
| Filename*           | Auszuführende Datei beim Link                          |
+---------------------+--------------------------------------------------------+
| Name*               | Name des Links ([a-zA-Zo-9 ]+)                         |
+---------------------+--------------------------------------------------------+
| Template*           | Smarty-Templatedatei die den Linkinhalt anzeigt        |
+---------------------+--------------------------------------------------------+
| FullscreenTemplate* | Smarty-Templatedatei die den Linkinhalt anzeigt        |
+---------------------+--------------------------------------------------------+
| VisibleAfterLogin*  | Nur anzeigen wenn der User eingeloggt ist ([NY]{1,1})  |
+---------------------+--------------------------------------------------------+
| PrintButton*        | Druckbutton anzeigen ([NY]{1,1})                       |
+---------------------+--------------------------------------------------------+
| NoFollow*           | NoFollow Attribut in den HTML Code einfügen([NY]{1,1}) |
+---------------------+--------------------------------------------------------+
| LinkLanguage*       |                                                        |
+---------------------+--------------------------------------------------------+
| SSL                 | 0 oder 1 für Standard, 2 für erzwungenes SSL           |
+---------------------+--------------------------------------------------------+

LinkLanguage

+-----------------+-------------------------------------------------+
| Elementname     | Funktion                                        |
+=================+=================================================+
| iso*            | Sprach.ISO ([A\-Z]{3})                          |
+-----------------+-------------------------------------------------+
| Seo*            | SEO Name des Links ([a\-zA\-Z0\-9 ]+)           |
+-----------------+-------------------------------------------------+
| Name*           | Name des Links ([a\-zA\-Z0\-9 ]+)               |
+-----------------+-------------------------------------------------+
| Title*          | Titel des Links ([a\-zA\-Z0\-9 ]+)              |
+-----------------+-------------------------------------------------+
| MetaTitle*      | Meta Title des Links ([a\-zA\-Z0\-9,. ]+)       |
+-----------------+-------------------------------------------------+
| MetaKeywords*   | Meta Keywords des Links ([a\-zA\-Z0\-9, ]+)     |
+-----------------+-------------------------------------------------+
| MetaDescription | Meta Description des Links ([a\-zA\-Z0\-9,. ]+) |
+-----------------+-------------------------------------------------+


Zahlungsmethoden
----------------

Das JTL-Shop Pluginsystem ist in der Lage, eine oder mehrere Zahlungsmethoden zugleich ohne Eingriff in den Shopcode zu implementieren.
Das Hauptelement <PaymentMethod> wird unter dem Element <FrontendLink> eingefügt. Es können beliebig viele Zahlungsmethoden (<Method>) implementiert werden.
Falls das Plugin keine Zahlungsmethode implementieren soll, wird der <PaymentMethod> Block ganz weggelassen.

.. code-block:: xml

    <PaymentMethod>
        ...
    </PaymentMethod>

+-------------+-----------------+
| Elementname | Funktion        |
+=============+=================+
| Method*     | Zahlungsmethode |
+-------------+-----------------+

(*)Pflichtfeld

.. code-block:: xml

    <Method>
        <Name>PayPal (Plugin)</Name>
        <PictureURL>paypal/template/paypal.gif</PictureURL>
        <Sort>3</Sort>
        <SendMail>0</SendMail>
        <Provider>PayPal</Provider>
        <TSCode>PAYPAL</TSCode>
        <PreOrder>0</PreOrder>
        <Soap>0</Soap>
        <Curl>0</Curl>
        <Sockets>0</Sockets>
        <ClassFile>paypal/paypal.class.php</ClassFile>
        <ClassName>PayPal</ClassName>
        <TemplateFile>paypal/template/bestellabschluss.tpl</TemplateFile>
        <MethodLanguage iso="GER">
            <Name>PayPal</Name>
            <ChargeName>PayPal</ChargeName>
            <InfoText>Wir sorgen für einfache, schnelle und sichere Zahlungen beim online Einkaufen und Verkaufen.</InfoText>
        </MethodLanguage>
        <Setting type="text" initialValue="" sort="1" conf="Y">
            <Name>PayPal Empfänger-Emailadresse</Name>
            <Description>An diese Emailadresse werden PayPal Zahlungen eingehen.</Description>
            <ValueName>paypal_email</ValueName>
        </Setting>
    </Method>

+------------------------+-------------------------------------------------------------+
| Elementname            | Funktion                                                    |
+========================+=============================================================+
| Name*                  | Name der Zahlungsmethode                                    |
+------------------------+-------------------------------------------------------------+
| PictureURL*            | Link zu einem Logo                                          |
+------------------------+-------------------------------------------------------------+
| Sort*                  | Sortierungsnummer der Zahlungsmethode ([0\-9]+)             |
+------------------------+-------------------------------------------------------------+
| SendMail*              | Versendet eine Email beim Zahlungseingang. 1 = Ja, 0 = Nein |
+------------------------+-------------------------------------------------------------+
| Provider               | Zahlungsanbieter                                            |
+------------------------+-------------------------------------------------------------+
| TSCode*                | Trusted Shops TSCode([A\-Z\_]+)                             |
+------------------------+-------------------------------------------------------------+
| PreOrder*              | Pre(1) -oder Post(0) Bestellung([0\-1]{1})                  |
+------------------------+-------------------------------------------------------------+
| Soap*                  | Übertragungsprotokoll Flag ([0\-1]{1})                      |
+------------------------+-------------------------------------------------------------+
| Curl*                  | Übertragungsprotokoll Flag ([0\-1]{1})                      |
+------------------------+-------------------------------------------------------------+
| Sockets*               | Übertragungsprotokoll Flag ([0\-1]{1})                      |
+------------------------+-------------------------------------------------------------+
| Class File*            | Name der PHP Klasse ([a\-zA\-Z0\-9\/_\-.]+.php)             |
+------------------------+-------------------------------------------------------------+
| ClassName*             | Exakter Name der Klasse                                     |
+------------------------+-------------------------------------------------------------+
| TemplateFile           | Name der Template-Datei ([a\-zA\-Z0\-9\/_\-.]+.tpl)         |
+------------------------+-------------------------------------------------------------+
| AdditionalTemplateFile | Template-Datei für einen Zusatzschritt                      |
+------------------------+-------------------------------------------------------------+
| MethodLanguage*        | Lokalisierung der Zahlungsmethode                           |
+------------------------+-------------------------------------------------------------+
| Setting                | Einstellungen der Zahlungsmethode                           |
+------------------------+-------------------------------------------------------------+

(*) Pflichtfelder

Die Elemente <Soap>, <Curl> und <Sockets> beschreiben die nötigen Serveranforderungen, die für die Zahlungsmethode notwendig sind. Falls die Zahlungsmethode z.B. auf einem POST-Formular aufgebaut ist, kann man jedem Element eine 0 zuweisen.
Im Element <TemplateFile> kann der Name oder Pfad zu einer Smarty Template-Datei angegeben werden. Dort können dann z.B. POST-Formulare ausgegeben werden.

Im Element <AdditionalTemplateFile> kann außerdem eine Smarty-Template-Datei für einen Zahlungs-Zusatzschritt angegeben werden. Hier können z.B. Kreditkarteninfos abgefragt werden.

Das Element <TSCode> kann folgende Werte enthalten: "DIRECT_DEBIT", "CREDIT_CARD", "INVOICE", "CASH_ON_DELIVERY", "PREPAYMENT", "CHEQUE", "PAYBOX", "PAYPAL", "CASH_ON_PICKUP", "FINANCING", "LEASING", "T_PAY", "CLICKANDBUY", "GIROPAY", "GOOGLE_CHECKOUT", "SHOP_CARD", "DIRECT_E_BANKING", "OTHER".

MethodLanguage:
Es können beliebig viele Sprachen für eine Zahlungsmethode implementiert werden, jedoch muss mindestens eine enthalten sein.

+-------------+-------------------------------------------------+
| Elementname | Funktion                                        |
+=============+=================================================+
| iso*        | Sprachcode der jeweiligen Sprache               |
+-------------+-------------------------------------------------+
| Name*       | Name der Zahlungsmethode                        |
+-------------+-------------------------------------------------+
| ChargeName* | Sortierungsnummer der Zahlungsmethode ([0\-9]+) |
+-------------+-------------------------------------------------+
| InfoText*   |                                                 |
+-------------+-------------------------------------------------+

(*) Pflichtfelder

Setting:

Jede Zahlungsmethode kann beliebeig viele Einstellungen enthalten. Z.B. die Logindaten für einen bestimmten Shopbetreiber. Diese Einstellungen werden im Backend bei der jeweilligen Zahlungsmethode angezeigt und können dort editiert werden.

+------------------+---------------------------------------------------+
| Elementname      | Funktion                                          |
+==================+===================================================+
| type*            | Einstellungstyp (text, zahl, selectbox)           |
+------------------+---------------------------------------------------+
| initValue*       | Vorrausgewählte Einstellung                       |
+------------------+---------------------------------------------------+
| sort*            | Sortierung der Einstellung (Höher = weiter unten) |
+------------------+---------------------------------------------------+
| conf*            | Y = echte Einstellung, N = Überschrift            |
+------------------+---------------------------------------------------+
| Name*            | Name der Einstellung                              |
+------------------+---------------------------------------------------+
| Description*     | Beschreibung der Einstellungsvariable             |
+------------------+---------------------------------------------------+
| ValueName*       | Name der Einstellungsvariable                     |
+------------------+---------------------------------------------------+
| SelectboxOptions | Optionales Element der bei type = selectbox       |
+------------------+---------------------------------------------------+

(*) Pflichtfelder

Sprachvariablen
---------------

Sprachvariablen sind lokalisierte Variablen, die für verschiedene Sprachen hinterlegt und abgerufen werden können.
Sofern die Sprachen vom Shop und die Sprachen des Plugins übereinstimmen, passen sich die Sprachvariablen für jede eingestellte Sprache im Shop automatisch an (lokalisiert).
Sollte das Plugin Frontend Links bereitstehen, so sollte jede textuelle Ausgabe mittels dieser Sprachvariablen ausgegeben werden.

Anpassung der Sprachvariablen in den Plugin-Einstellungen des Admin-Bereichs
Sprachvariablen können nach der Installation eines Plugins vom Shopbetreiber angepasst werden. Dazu befindet sich ein Button „Sprachvariablen bearbeiten“ bei jedem Plugin mit Sprachvariablen in der Pluginverwaltung.
Sprachvariablen können auf ihren Ursprungswert zurückgesetzt werden. Bei einem Pluginupdate oder beim Deaktivieren eines Plugins, bleiben durch den Shopbetreiber angepasste Sprachvariablen erhalten.
Erst bei einer Deinstallation des Plugins werden die Sprachvariablen endgültig gelöscht.

Einbindung in info.xml im <Install> Block
Ein Plugin kann beliebig viele Sprachvariablen definieren. Das Hauptelement der Sprachvariablen heißt <Locales> und jede Sprachvariable wird im Element <Variable> definiert.

Das Element <Locales> ist ein Kindelement von <Install>.

Wichtig: Änderungen an der info.xml sind erst nach einer Plugin-Neuinstallation sichtbar, da die Variablen bei der Installation in die Datenbank geschrieben werden.

.. code-block:: xml

    <Locales>
        <Variable>
            <Name>dani_onlinewatcher_activeuser</Name>
            <Description>Aktive Shopbesucher</Description>
            <VariableLocalized iso="GER">Aktive Shopbesucher</VariableLocalized>
            <VariableLocalized iso="ENG">Onlineuser</VariableLocalized>
        </Variable>
    </Locales>

+--------------------+---------------------------------+
| Elementname        | Funktion                        |
+====================+=================================+
| Name*              | Name der Sprachvariable         |
+--------------------+---------------------------------+
| Description*       | Beschreibung der Sprachvariable |
+--------------------+---------------------------------+
| VariableLocalized* | Lokalisierter Name              |
+--------------------+---------------------------------+
| iso*               | Sprach-ISO ([A\-Z]{3})          |
+--------------------+---------------------------------+

(*) Pflichtfelder

In einem Elementblock <Variable>, können beliebig viele <VariableLocalized> eingebunden werden. Das ISO Attribut arbeitet nach Großbuchstaben ISO 639-2/B.

E-Mail Templates
----------------

Ein Plugin kann auch neue Emailtypen definieren, die versendet werden können. Dabei kann der E-Mail-Inhalt eines Templates für alle im Shop verfügbaren Sprachen vorbelegt werden.
Die vordefinierten Texte sind weiterhin in der E-Mail Vorlagenverwaltung im Admin-Backend durch den Shop-Betreiber editierbar.

Mit dem Ausgangselement <Emailtemplate>, das im Element <Install> eingefügt wird, wird eine neue Emailvorlage definiert:

.. code-block:: xml

    <Emailtemplate>
        <Template>
            <Name>Zahlungs-Erinnerungsemail</Name>
            <Description></Description>
            <Type>text/html</Type>
            <ModulId>zahlungserinnerung</ModulId>
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
                <Subject>Reminder</Subject>
                <ContentHtml></ContentHtml>
                <ContentText></ContentText>
            </TemplateLanguage>
        </Template>
    </Emailtemplate>
    
+------------------+--------------------------------------------------------------------------------------------+
| Template         | Pro Emailvorlage muss es ein Element Template geben                                        |
+==================+============================================================================================+
| Name             | Name der Emailvorlage                                                                      |
+------------------+--------------------------------------------------------------------------------------------+
| Description      | Beschreibung der Emailvorlage                                                              |
+------------------+--------------------------------------------------------------------------------------------+
| Type             | Sendeformat der Emailvorlage (html/text oder text)                                         |
+------------------+--------------------------------------------------------------------------------------------+
| ModulId          | Eindeutiger Schlüssel der Emailvorlage                                                     |
+------------------+--------------------------------------------------------------------------------------------+
| Active           | Aktivierungsflag der Emailvorlage (Y/N)                                                    |
+------------------+--------------------------------------------------------------------------------------------+
| AKZ              | Anbieterkennzeichnung in der Emailvorlage anhängen (1/0)                                   |
+------------------+--------------------------------------------------------------------------------------------+
| AGB              | Allgemeine Geschäftsbedingungen in der Emailvorlage anhängen (1/0)                         |
+------------------+--------------------------------------------------------------------------------------------+
| WRB              | Widerrufsbelehrung in der Emailvorlage anhängen (1/0)                                      |
+------------------+--------------------------------------------------------------------------------------------+
| TemplateLanguage | Lokalisierte Inhalte pro Sprache (min. eine Sprache muss vorhanden sein) (Key = SprachISO) |
+------------------+--------------------------------------------------------------------------------------------+
| Subject          | Betreff der Emailvorlage in der jeweiligen Sprache                                         |
+------------------+--------------------------------------------------------------------------------------------+
| ContentHtml      | Inhalt in HTML                                                                             |
+------------------+--------------------------------------------------------------------------------------------+
| ContentText      | Inhalt als Text                                                                            |
+------------------+--------------------------------------------------------------------------------------------+

Plugin-Boxen
------------

Dank der Boxenverwaltung des JTL-Shop ist der Shopbetreiber in der Lage, einfach und schnell Boxen im Shop zu verschieben, anzulegen oder zu löschen.

Ein Plugin ist in der Lage, einen neuen Boxentyp anzulegen. Diese neue Box kann in der Boxenverwaltung ausgewählt und einer Stelle im JTL-Shop zugewiesen werden.
Der Inhalt dieser Box wird durch ein Template, das der Box zugewiesen wird, gesteuert. Dort können beliebige Inhalte angezeigt werden.

Sie erstellen einen neuen Boxtypen, indem Sie folgenden neuen Block in der info.xml anlegen:

.. code-block:: xml

    <Boxes>
     ...
    </Boxes>

In diesem Block können beliebig viele Unterelemente vom Typ <Box> liegen. Das heißt, ein Plugin kann beliebig viele Boxentypen anlegen. Vergeben Sie stets eindeutige Boxennamen, damit sich diese nicht mit anderen Plugins überschneiden.

XML Darstellung in der info.xml:

.. code-block:: xml

    <Boxes>
        <Box>
            <Name>Template Switcher</Name>
            <Available>0</Available>
            <TemplateFile>box_tswitcher.tpl</TemplateFile>
        </Box>
    </Boxes>

+--------------+--------------------------------------+
| Elementname  | Beschreibung                         |
+==============+======================================+
| Name         | Name des Boxentyps                   |
+--------------+--------------------------------------+
| Available    | Seite in der Die Box verfügbar ist   |
+--------------+--------------------------------------+
| TemplateFile | Templatedatei mit dem Inhalt der Box |
+--------------+--------------------------------------+

Das folgende Beispiel demonstriert, wie man eine Plugin-Box zum Wechseln des Shoptemplates erzeugt.

.. code-block:: xml

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshop3plugin>
        <Name>Template Switcher</Name>
        <Description>Ändert in der Session das JTL-Shop Template.</Description>
        <Author>Daniel Böhmer</Author>
        <URL>http://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>300</ShopVersion>
        <PluginID>dani_tswitcher</PluginID>
        <Install>
            <Version nr="100">
                <CreateDate>2010-07-05</CreateDate>
            </Version>
            <Hooks>
                <Hook id="132">switcher.php</Hook>
                <Hook id="133">smarty.php</Hook>
            </Hooks>
            <Boxes>
                <Box>
                    <Name>Template Switcher</Name>
                    <Available>0</Available>
                    <TemplateFile>box_tswitcher.tpl</TemplateFile>
                </Box>
            </Boxes>
        </Install>
    </jtlshop3plugin>

Plugin-Widgets
--------------

Mit Plugin-Widgets lassen sich einfach und schnell eigene Widgets im Backend Dashboard des JTL-Shop implementieren.

Ein Plugin ist in der Lage, ein AdminWidget anzulegen. Der Inhalt dieses Widgets wird durch ein Template gesteuert. Dort können beliebige Inhalte angezeigt werden.

Sie erstellen einen neues AdminWidget, indem Sie folgenden neuen Block in der info.xml anlegen:

.. code-block:: xml

    <AdminWidget>
     ...
    </AdminWidget>

In diesem Block können beliebig viele Unterelemente vom Typ <Widget> liegen. Das heißt, ein Plugin kann beliebig viele AdminWidgets anlegen.

XML Darstellung in der info.xml:

.. code-block:: xml

    <AdminWidget>
        <Widget>
            <Title>Serverinfo (Plugin)</Title>
            <Class>ServerInfo</Class>
            <Container>center</Container>
            <Description>Beispielplugin</Description>
            <Pos>1</Pos>
            <Expanded>1</Expanded>
            <Active>1</Active>
        </Widget>
    </AdminWidget>

+-------------+-----------------------------------------------------------------------+
| Elementname | Beschreibung                                                          |
+=============+=======================================================================+
| Title*      | Titelüberschrift des AdminWidgets                                     |
+-------------+-----------------------------------------------------------------------+
| Class*      | Klassenname der PHP-Klasse die den Inhalt des Widgets bereitstellt    |
+-------------+-----------------------------------------------------------------------+
| Container*  | Position des Dashboardcontainers. Werte: center, left, right          |
+-------------+-----------------------------------------------------------------------+
| Description | Beschreibung des AdminWidgets                                         |
+-------------+-----------------------------------------------------------------------+
| Pos*        | Vertikale Position im Container. Ganzzahl (1 = oben)                  |
+-------------+-----------------------------------------------------------------------+
| Expanded*   | AdminWidget soll ausgeklappt oder minimiert sein. Ganzzahl, 0 oder 1. |
+-------------+-----------------------------------------------------------------------+
| Active*     | AdminWidget direkt sichtbar im Dashboard. Ganzzahl, 0 oder 1.         |
+-------------+-----------------------------------------------------------------------+


Der Klassenname wird wie folgt generiert:

* Annahme *<Class>Info</Class>* und die PluginId lautet *<PluginID>jtl_test</PluginID>*.

* Dann muss im Verzeichnis "/version/xxx/adminmenu/widget/" vom Plugin die folgende Klasse mit Namen "class.WidgetInfo_jtl_test.php" liegen: ``class.Widget + <Class> + _ + <PluginID> + .php``

* Die Klasse in der Datei muss wie folgt lauten: ``Widget + <Class> +_ + <PluginID>`` und muss von der Basisklasse "WidgetBase" abgeleitet sein. In Beispiel also ``class WidgetInfo_jtl_test extends WidgetBase {}``


Das folgende Beispiel demonstriert, wie man ein Plugin-Widget zum Anzeigen der Serverinformationen erzeugt:

.. code-block:: xml

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshop3plugin>
        <Name>AdminWidget Serverinfo</Name>
        <Description>Erstellt ein Widget mit Serverinformationen</Description>
        <Author>JTL-Software-GmbH</Author>
        <URL>https://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>310</ShopVersion>
        <PluginID>dani_adminwidget</PluginID>
        <Install>
            <Version nr="100">
                <CreateDate>2016-05-17</CreateDate>
            </Version>
            <AdminWidget>
                <Widget>
                    <Title>Serverinfo (Plugin)</Title>
                    <Class>ServerInfo</Class>
                    <Container>center</Container>
                    <Description>Beispielplugin</Description>
                    <Pos>1</Pos>
                    <Expanded>1</Expanded>
                    <Active>1</Active>
                </Widget>
            </AdminWidget>
        </Install>
    </jtlshop3plugin>

Plugin-Exportformate
--------------------

Mit einem Plugin-Exportformat lassen sich schnell und einfach Exportformate in den JTL-Shop integrieren.
Sie erstellen einen neues AdminWidget, indem Sie folgenden neuen Block in der info.xml anlegen:

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
    
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| Elementname                  | Beschreibung                                                                                                |
+==============================+=============================================================================================================+
| Name                         | Name des Exportformats                                                                                      |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| FileName                     | Dateiname ohne Pfadangabe in welche die Artikel exportiert werden sollen                                    |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| Header                       | Kopfzeile der Exportdatei                                                                                   |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| Content                      | Exportformat (Smarty)                                                                                       |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| footer                       | Fußzeile der Exportdatei                                                                                    |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| Encoding                     | ASCII oder UTF-8-Kodierung der Exportdatei                                                                  |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| VarCombiOption               | 1 = Väter- und Kindartikel exportieren / 2 = Nur Väterartikel exportieren / 3 = Nur Kindartikel exportieren |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| SplitSize                    | In wie große Dateien soll das Exportformat gesplittet werden? (Megabyte)                                    |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| OnlyStockGreaterZero         | Nur Produkte mit Lagerbestand über 0                                                                        |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| OnlyPriceGreaterZero         | Nur Produkte mit Preis über 0                                                                               |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| OnlyProductsWithDescription  | Nur Produkte mit Beschreibung                                                                               |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| ShippingCostsDeliveryCountry | Versandkosten Lieferland (ISO-Code)                                                                         |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| EncodingQuote                | Zeichenmaskierung für Anführungszeichen                                                                     |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| EncodingDoubleQuote          | Zeichenmaskierung für doppelte Anführungszeichen                                                            |
+------------------------------+-------------------------------------------------------------------------------------------------------------+
| EncodingSemicolon            | Zeichenmaskierung für Semikolons                                                                            |
+------------------------------+-------------------------------------------------------------------------------------------------------------+


Das folgende Beispiel demonstriert, wie ein Plugin-Exportformat aussehen könnte:

.. code-block:: xml

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshop3plugin>
        <Name>Exportformat Test</Name>
        <Description>Beispielplugin zum Erstellen eines Exportformats</Description>
        <Author>JTL-Software-GmbH, Daniel Boehmer</Author>
        <URL>http://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>311</ShopVersion>
        <PluginID>jtl_dani_export</PluginID>
        <Install>
            <Version nr="100">
                <CreateDate>2011-07-11</CreateDate>
            </Version>
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
    </jtlshop3plugin>

Plugin-Lizensierung
-------------------

Bei der Erstellung kommerzieller Shop-Plugins stellt sich die Frage, wie das eigene Plugin gegen unauthorisierte Weitergabe und Nutzung abgesichert werden kann.

Ein Plugin kann dem Shopsystem mittels eines Blocks in der info.xml mitteilen, dass es unter einer bestimmten Lizenz steht und diese abgefragt werden muss.
Dazu stellt der JTL-Shop eine Interface-Klasse zur Verfügung, die das Plugin nutzen kann, um eine bestimmte Lizenzmethode zu überschreiben.
Diese Methode wird dann beim Aufruf des Plugins stets überprüft. Wie und mit welchen mitteln das Plugin seine Lizenz überprüft, muss selbst implementiert werden.
Am Ende der Methode muss dem System nur mitgeteilt werden, ob die Prüfung erfolgreich war oder fehlschlug.

Zwei Einträge müssen in die Info.xml eingefügt werden, damit die Lizenzprüfung ausgeführt wird:

.. code-block:: xml

    <LicenceClass>jtl_license_examplePluginLicence</LicenceClass>
    <LicenceClassFile>class.PluginLicence.php</LicenceClassFile>

Die o.g. Elemente befinden sich dabei direkt im Block <jtlshop3plugin>.

Beispiel:

.. code-block:: xml

    <?xml version='1.0' encoding="ISO-8859-1"?>
    <jtlshop3plugin>
        <Name>Lizenz-Beispiel</Name>
        <Description>Zeig alle Module des Shops an.</Description>
        <Author>JTL-Software-GmbH</Author>
        <URL>https://www.jtl-software.de</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>300</ShopVersion>
        <PluginID>jtl_license_example</PluginID>
        <LicenceClass>jtl_license_examplePluginLicence</LicenceClass>
        <LicenceClassFile>class.PluginLicence.php</LicenceClassFile>
        <Install>
            <Version nr="100">
                <CreateDate>2016-05-17</CreateDate>
            </Version>
            <Hooks>
                <Hook id="132">example.php</Hook>
            </Hooks>
        </Install>
    </jtlshop3plugin>

+------------------+-------------------------------------------------------------------------------------------------------------------+
| Elementname      | Beschreibung                                                                                                      |
+==================+===================================================================================================================+
| LicenceClass     | Gibt an, wie die Lizenzprüfungsklasse des Plugins heißt, die von der JTL-Shop Interface-Klasse PluginLizenz erbt. |
+------------------+-------------------------------------------------------------------------------------------------------------------+
| LicenceClassFile | Gibt den Dateinamen der Lizenzprüfungsklasse des Plugins an.                                                      |
+------------------+-------------------------------------------------------------------------------------------------------------------+


Es gibt also eine bestimmte Klasse die das Plugin mitbringen muss, die die Lizenzprüfung durchführt. Name der Klasse und Dateiname der Klasse müssen in der info.xml angegeben werden.
Die Lizenzklasse muss im Ordner licence liegen, der sich wiederum im Ordner der jeweiligen Pluginversion befindet. Beispiel: <pluginname>/version/100/licence/

Im o.g. Beispiel lautet die Lizenzklasse vom Plugin ``jtl_license_examplePluginLicence`` und befindet sich in der Datei class.PluginLicence.php.

Beispiel wie eine Lizenzprüfung im Minimalfall ausschauen könnte:

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

Im Beispiel ist zu erkennen, dass die vorher in der info.xml angegebenen Klasse dani_extviewerPluginLicence von der Interfaceklasse PluginLizenz aus dem JTL-Shop erbt.
Diese Interfaceklasse beinhaltet die Methode checkLicence die es zu überschreiben gilt. In unserem Beispiel fragt diese Methode den Parameter $cLicence ab. Die Methode muss den boolschen Wert true oder false zurückgeben, damit das System dieses Plugin ausführt oder nicht.

Es bietet sich an, die Plugin-Lizenzklasse mit Hilfe von ionCube zu verschlüsseln, um Manipulationen vorzubeugen.

.. note::
    Der JTL-Shop selbst benötigt seit Version 4.00 kein Ioncube mehr - es ist also nicht garantiert, dass potentielle Käufer tatsächlich bereits Ioncube auf ihrem Server installiert haben.

Checkbox-Spezialfunktionen
--------------------------

Über die Pluginschnittstelle lassen sich Checkboxfunktionen registrieren, welche dann als Spezialfunktion in der Checkboxverwaltung dem Kunden zur Verfügung stehen.

Beispiel-XML (muss in den install-Block):

.. code-block:: xml

    <CheckBoxFunction>
        <Function>
            <Name>Name der Spezialfunktion</Name>
            <ID>meinespezialfunktion</ID>
        </Function>
    </CheckBoxFunction>

Damit wird dann bei Plugin-Installation eine neue Zeile in tcheckboxfunktion geschrieben.

Wird die Checkbox angehakt und ist dafür Spezialfunktion Plugin gewählt, dann wird die jeweilige Plugin php-Datei inkludiert.


Statische Ressourcen
--------------------

Seit Shop 4.00 haben Plugins die Möglichkeit, bereits in der XML-Definition statische Ressourcen - also JavaScript- und CSS-Dateien - anzugeben, die im Frontend auf allen Seiten eingebunden werden.
Die hat den Vorteil, dass sie nicht einzeln über das Template bzw. via **pq()** eingebunden werden müssen und darüber hinaus auf direkt Minifiziert werden können.

Die entsprechenden XML-Blöcke lauten *<CSS>* bzw. *<JS>* und sind direkte Unterknoten von *<Install>*. Die angegebenen Dateien müssen im Ordner ``<Plugin-Ordner>/version/<Versionsnummer>/frontend/js/`` respektive ``<Plugin-Ordner>/version/<Versionsnummer>/frontend/css/`` liegen.
Beispiel für das Einfügen von jeweils zwei JavaScript- und CSS-Dateien:

.. code-block:: xml

    <CSS>
        <file>
            <name>datei1.css</name>
            <priority>4</priority>
        </file>
        <file>
            <name>datei2.css</name>
            <priority>9</priority>
        </file>
    </CSS>
    <JS>
        <file>
            <name>script1.js</name>
            <priority>8</priority>
            <position>body</position>
        </file>
        <file>
            <name>script2.js</name>
        </file>
    </JS>


CSS file:

+-------------+----------------------------------------------------------------------------+
| Elementname | Beschreibung                                                               |
+=============+============================================================================+
| name*       | Der Dateiname im Unterordner css/                                          |
+-------------+----------------------------------------------------------------------------+
| priority    | Die Priorität von 0\-10, je höher, desto später wird die Datei eingebunden |
+-------------+----------------------------------------------------------------------------+

JS file:

+-------------+----------------------------------------------------------------------------+
| Elementname | Beschreibung                                                               |
+=============+============================================================================+
| name*       | Der Dateiname im Unterordner js/                                           |
+-------------+----------------------------------------------------------------------------+
| priority    | Die Priorität von 0\-10, je höher, desto später wird die Datei eingebunden |
+-------------+----------------------------------------------------------------------------+
| position    | Die Position im DOM, an der die Datei eingebunden wird, "body" oder "head" |
+-------------+----------------------------------------------------------------------------+