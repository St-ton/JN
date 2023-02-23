Die ``info.xml``
================

.. |br| raw:: html

   <br />

Im Root-Verzeichnis jedes Plugins liegt die XML-Datei ``info.xml``. |br|
In dieser Datei sind alle Informationen zum Plugin hinterlegt.

Die Datei beinhaltet neben dem Namen des Plugins, der Beschreibung und dem Autor auch **alle** technischen
Informationen. Darunter fallen z. B. Pfade zu Ressourcen, verwendete Hooks, Sprachvariablen, Einstellungselemente
und mehr. |br|

.. hint::

    Diese Datei ist das wichtigste Element eines Plugins, da sie für die Installation sowie für Updates zuständig ist.

Ein Plugin kann in folgende Hauptbestandteile aufgeteilt werden, die durch die ``info.xml`` definiert werden:

* Globale Plugin-Informationen
* Versionen
* Adminmenü mit *Custom-Links* und *Setting-Links*
* Zahlungsmethoden
* Frontend-Links
* Sprachvariablen
* E-Mail-Templates
* Plugin-Boxen
* Plugin-Lizenzierung
* statische Ressourcen

Falls Bereiche im Plugin nicht gebraucht werden, sollten Sie den kompletten Block weggelassen. |br|
Die globalen Informationen können dabei nicht weggelassen werden.

Der Rumpf
---------

Das Hauptelement stellt den Rumpf der XML-Datei dar. Es heißt
sowohl **für JTL-Shop 3 als auch JTL-Shop 4** ``<jtlshop3plugin>``. |br|
**Ab JTL-Shop 5.x** heißt es ``<jtlshopplugin>``.

**Bis JTL-Shop 4.x:**

.. code-block:: xml

  <jtlshop3plugin>
    ...
  </jtlshop3plugin>

**Ab JTL-Shop 5.x:**

.. code-block:: xml

  <jtlshopplugin>
    ...
  </jtlshopplugin>

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


+----------------------+-----------------------------------------------------+
| Elementname          | Funktion                                            |
+======================+=====================================================+
| ``<Name>`` *         | Name des Plugins (``[a-zA-Z0-9_]``)                 |
+----------------------+-----------------------------------------------------+
| ``<Description>``    | Plugin-Beschreibung                                 |
+----------------------+-----------------------------------------------------+
| ``<Author>``         | Herausgeber eines Plugins                           |
+----------------------+-----------------------------------------------------+
| ``<URL>``            | Link zum Pluginherausgeber                          |
+----------------------+-----------------------------------------------------+
| ``<XMLVersion>`` *   | Version der ``info.xml`` (``[0-9]{3}``)             |
+----------------------+-----------------------------------------------------+
| ``<ShopVersion>``    | Mindestversion von JTL-Shop |br|                    |
|                      | (>= 300, < 400, >= 500 oder auch *5.0.0-beta.3*)    |
+----------------------+-----------------------------------------------------+
| ``<MinShopVersion>`` | ab 5.0.0 - Mindestversion von JTL-Shop 5            |
+----------------------+-----------------------------------------------------+
| ``<MaxShopVersion>`` | ab 5.0.0 - Maximalversion von JTL-Shop 5            |
+----------------------+-----------------------------------------------------+
| ``<Shop4Version>``   | Mindestversion von JTL-Shop 4 (>= 400)              |
+----------------------+-----------------------------------------------------+
| ``<PluginID>`` *     | Plugin-Identifikator (``[a-zA-Z0-9_]``)             |
+----------------------+-----------------------------------------------------+
| ``<Icon>``           | Dateiname zu einem Icon                             |
+----------------------+-----------------------------------------------------+
| ``<Version>``        | ab JTL-Shop 5.0.0 - die Plugin-Version (``[0-9]+``) |
+----------------------+-----------------------------------------------------+
| ``<CreateDate>``     | ab 5.0.0 - Erstellungsdatum (YYYY-MM-DD)            |
+----------------------+-----------------------------------------------------+
| ``<ExsID>``          | ab 5.0.0 - ExtensionStore-ID                        |
+----------------------+-----------------------------------------------------+

(*)Pflichtfelder

Name
""""

Der Name des Plugins wird in der Plugin-Verwaltung und den automatisch generierten Menüs im Backend dargestellt. Er
dient der Identifizierung des Plugins.

Description
"""""""""""

Die Beschreibung wird in der Plugin-Verwaltung im Tab "Verfügbar" unterhalb des Plugin-Namens dargestellt. Sie enthält
eine kurze Funktionsbeschreibung des Plugins.

Author
""""""

Der Autor wird im Admin-Menü des Plugins dargestellt. Hier kann sowohl eine Firma als auch eine Privatperson
eingetragen werden.

URL
"""

Die URL sollte einen Link zum Hersteller oder einer dedizierten Plugin-Seite enthalten, sodass der Kunde schnell
und einfach weitere Informationen oder Support erhalten kann.

XMLVersion
""""""""""

Da sich mit der Zeit auch die Anforderungen an das Plugin-System ändern können, kann sich auch die
XML-Installationsdatei ändern. Daher ist die Angabe der XML-Version sehr wichtig, um auch die richtigen Parameter
für das eigene Plugin zur Verfügung zu haben.

ShopVersion
"""""""""""

*ShopVersion* gibt die Version von JTL-Shop an, die mindestens benötigt wird. Ist sie höher als die aktuell
installierte Version des Onlineshops, wird eine Fehlermeldung im Backend angezeigt und das Plugin kann nicht
installiert werden. Falls nur dieser Wert, nicht aber ``Shop4Version``, konfiguriert wurde, erscheint in JTL-Shop 4.00+
der Hinweis, dass das Plugin möglicherweise in dieser Version nicht funktioniert. Es kann jedoch trotzdem installiert
werden. |br|
Das explizite Angeben einer einzelnen Versionsnummer ist ebenfalls möglich, ergibt allerdings nur temporär zu
Entwicklerzwecken Sinn (siehe z. B.: *5.0.0-beta.3*)
**Ab JTL-Shop 5.0.0 sollte dieser Tag durch <MinShopVersion> ersetzt werden**

MinShopVersion
""""""""""""""

*MinShopVersion* entspricht ab Shop 5.0.0 dem alten Tag *ShopVersion*.

MaxShopVersion
""""""""""""""

*MaxShopVersion* gibt die Version von JTL-Shop an, die höchstens unterstützt wird. Ist die tatsächlich installierte
Version von JTL-Shop höher, wird im Backend eine Warnung angezeigt.

Shop4Version
""""""""""""

*Shop4Version* gibt die Mindest-Version für JTL-Shop 4 an. Wurde nur dieser Wert und nicht ``ShopVersion`` konfiguriert,
ist eine Installation nur in JTL-Shop 4.x möglich. |br|
**Ab JTL-Shop 5.0.0 wird dieser Tag nicht mehr unterstützt!**

Plugin-ID
"""""""""

Die Plugin-ID identifiziert ein Plugin im Onlineshop eindeutig.  |br|
Vergeben Sie unbedingt eine sinnvolle und einmalige ID für das eigene Plugin, damit
gleichnamige Plugins unterschiedlicher Hersteller nicht kollidieren.

Beispiel-ID für ein Plugin: "*SoftwareFirma_PluginName*"

**Namenskonvention:**
Es sind nur die Zeichen ``a-z`` bzw. ``A-Z``, ``0-9`` und der Unterstrich erlaubt. |br|
Punkt und Bindestrich sind nicht erlaubt.

Ab JTL-Shop 5.0.0 entspricht die Plugin-ID außerdem dem automatisch zugewiesenen PSR-4 Namespace
(angeführt vom Präfix ``Plugin\``) für das gesamte Plugin. |br|
Achten Sie deshalb darauf, dass der Ordnername des Plugins der Plugin-ID entspricht. Ein Plugin mit der
Plugin-ID "*mycompany_someplugin*" erhält so den Namespace ``Plugin\mycompany_someplugin``.

Icon
""""

Aktuell noch nicht implementiert, perspektivisch zur besseren Übersicht geplant.

Version
"""""""

Ab JTL-Shop 5.x ist dies eine Pflichtangabe zur Definition der Pluginversion.

CreateDate
""""""""""

Ab JTL-Shop 5.x ist dies eine Pflichtangabe zur Definition des Erstellungsdatums der jeweiligen Pluginversion. |br|
Das Datum muss im Format ``YYYY-MM-DD`` angegeben werden, beispielsweise "*2019-03-21*" für den 21. März 2019.

ExsID
"""""

Die ``ExsID`` muss für alle Plugins ab JTL-Shop 5.0.0 angegeben werden, wenn das Plugin im JTL-Store vertrieben werden
soll. |br|
Sie finden die ``ExsID`` im Kundencenter, nachdem Sie dort eine Extension für den Marktplatz angelegt haben.

Install-Block
"""""""""""""

Nach den globalen Plugininformationen folgt der Installationsblock. Dieser sieht wie folgt aus:

.. code-block:: xml

    <Install>

    </Install>

Alle Informationen zum Plugin werden in diesem Block als Kindelemente aufgeführt.


.. _label_infoxml_versionierung:

Versionierung
-------------

Wie die zur Definition passende Verzeichnisstruktur aussieht, finden Sie unter "Aufbau"
im Abschnitt ":ref:`label_aufbau_versionierung`".

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

(*)Pflichtfelder

Lesen Sie hierzu unter Aufbau auch den Abschnitt ":ref:`label_infoxml_sql`".

Falls weitere Versionen zu einem Plugin existieren, werden diese untereinander aufgeführt.

.. code-block:: xml

    <Version nr="100">
        <CreateDate>2015-03-25</CreateDate>
    </Version>
    <Version nr="101">
        <CreateDate>2015-04-15</CreateDate>
    </Version>

Ab JTL-Shop 5.x
"""""""""""""""

**Ab JTL-Shop 5.0.0 entfällt dieser Block!**

Es befindet sich in der ``info.xml`` lediglich die wesentlich vereinfachtere Struktur:

.. code-block:: xml

    <jtlshopplugin>
        ...
        <CreateDate>2018-11-13</CreateDate>
        <Version>1.0.0</Version>
        ...
    </jtlshopplugin>


.. _label_infoxml_hooks:

Plugin-Hooks
------------

Nach der Versionierung folgt das ``<Hooks>``-Element. In diesem Element werden jene Stellen im Onlineshop definiert,
an denen das Plugin Code ausführen soll.

Der *Frontend-Link* und die *Zahlungsmethoden* benötigen keine expliziten Hookangaben, da diese an einem bestimmten
Hook vom System aus eingebunden werden.

**Beispiel:**

.. code-block:: xml

    <Hooks>
        <Hook id="129">onlineuser.php</Hook>
        <Hook id="130">managemenet.php</Hook>
    </Hooks>

Die *ID* identifiziert hierbei eindeutig eine bestimmte Stelle im Code von JTL-Shop. Die angegebene PHP-Datei wird dann
am Hook der *ID* ausgeführt. |br|
Möchten Sie z. B. nach dem Erstellen eines Artikelobjektes am Objekt noch einige Member verändern, so
können Sie den entsprechenden Hook benutzen, um dies zu erledigen.

+----------------+----------------------------------------------------------------------------+
| Elementname    | Funktion                                                                   |
+================+============================================================================+
| ``<id>`` *     | Eindeutige Hook-ID (``[0-9]+``)                                            |
+----------------+----------------------------------------------------------------------------+
| ``<priority>`` | Priorität (ab JTL-Shop 4.05, niedriger => frühere Ausführung) (``[0-9]+``) |
+----------------+----------------------------------------------------------------------------+
| ``<Hook>``     | PHP-Datei im Ordner ``frontend/``, die an ID ausgeführt wird               |
+----------------+----------------------------------------------------------------------------+

(*) Pflichtfelder

Werden keine Hooks vom Plugin benötigt, können Sie den gesamten Hook-Container weglassen.

Eine Liste der Hook-IDs finden Sie in der ":doc:`Hook-Liste </shop_plugins/hook_list>`". |br|
Weitere Informationen zum Hook-System des Onlineshops finden Sie im Kapitel ":doc:`/shop_plugins/hooks`".

Ab JTL-Shop 5.x existiert eine neue Alternative zu den bekannten Hooks in JTL-Shop - der *EventDispatcher*. |br|
Wie Sie Gebrauch von diesem neuen Feature machen, finden Sie im Kapitel "Bootstrapping"
unter ":ref:`label_bootstrapping_eventdispatcher`".

.. _label_infoxml_license:

Lizenzierung
------------

Bei der Erstellung kommerzieller Plugins für JTL-Shop stellt sich die Frage, wie das eigene Plugin gegen unautorisierte
Weitergabe und Nutzung abgesichert werden kann.

Ein Plugin kann dem Onlineshop via ``info.xml`` mitteilen, dass es unter einer bestimmten Lizenz steht und diese
abgefragt werden muss. |br|
Für diesen Zweck stellt der Onlineshop eine Interface-Klasse zur Verfügung, die das Plugin nutzen kann, um eine
bestimmte Lizenzmethode zu überschreiben. Diese Methode wird dann beim Aufruf des Plugins stets überprüft.

Wie und mit welchen Mitteln das Plugin seine Lizenz überprüft, muss selbst implementiert werden. |br|
Am Ende der Methode muss dem System nur mitgeteilt werden, ob die Prüfung erfolgreich war oder fehlschlug.

Um dem Onlineshop mitzuteilen, dass eine Lizenzprüfung benötigt wird, fügen Sie folgende Elemente in die ``info.xml``
ein:

.. code-block:: xml

    <LicenceClass>PluginLicence</LicenceClass>
    <LicenceClassFile>PluginLicence.php</LicenceClassFile>

+------------------------+------------------------------------------------------------------------------------------------------+
| Elementname            | Beschreibung                                                                                         |
+========================+======================================================================================================+
| ``<LicenceClass>``     | Lizenzprüfungsklasse des Plugins, die von der Interface-Klasse ``PluginLizenz`` des Onlineshops erbt |
+------------------------+------------------------------------------------------------------------------------------------------+
| ``<LicenceClassFile>`` | Dateinamen der Lizenzprüfungsklasse des Plugins                                                      |
+------------------------+------------------------------------------------------------------------------------------------------+

(*) Pflichtfeld

Wo Sie die benötigten Dateien ablegen können, erfahren Sie im Kapitel "Aufbau"
im Abschnitt ":ref:`label_aufbau_license`".

**Bis JTL-Shop 4.x**

**Beispiel:**

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

**Beispiel:**

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

**Ab JTL-Shop 5.x**

In JTL-Shops der Versionen 5.x ist die Methodik der Interface-Vorschrift verglichen mit den Vorgängerversionen
gleich geblieben. Allerdings ist hier die Unterstützung von *namespaces* hinzugekommen. |br|

**Beispiel**:

.. code-block:: xml
   :emphasize-lines: 11,12

    <?xml version='1.0' encoding="UTF-8"?>
    <jtlshopplugin>
        <Name>SimpleExample</Name>
        <Description>Ein einfaches Bespiel</Description>
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

Die entsprechende Lizenzprüfungsklasse mit *namespace* würde dann so aussehen:

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

Weiterhin bietet es sich an, die Pluginlizenzklasse mit Hilfe von "*ionCube*" zu verschlüsseln, um Manipulationen
vorzubeugen.

.. important::
    JTL-Shop selbst benötigt seit Version 4.00 kein *Ioncube* mehr. |br|
    Es ist also nicht garantiert, dass potentielle Käufer tatsächlich bereits *Ioncube* auf ihrem Server installiert haben.


.. _label_infoxml_frontend_res:

Frontend-Ressourcen
-------------------

Pluginentwickler haben über die XML-Tags ``<CSS>`` und ``<JS>`` die Möglichkeit, eigene Ressourcen
im Plugin mitzuliefern, die im Frontend auf allen Seiten eingebunden werden. |br|
Dies hat den Vorteil, dass sie nicht einzeln über das Template bzw. via ``pq()`` ("phpQuery") eingebunden werden
müssen. Darüber hinaus können sie auch direkt minifiziert werden.

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

*CSS*-Datei:

+----------------+-----------------------------------------------------------------------------------------------+
| Elementname    | Beschreibung                                                                                  |
+================+===============================================================================================+
| ``<name>`` *   | Der Dateiname im Unterordner ``css/`` (siehe auch: :ref:`Aufbau <label_aufbau_frontend_res>`) |
+----------------+-----------------------------------------------------------------------------------------------+
| ``<priority>`` | Die Priorität von 0\-10, je höher, desto später wird die Datei eingebunden                    |
+----------------+-----------------------------------------------------------------------------------------------+

*JS*-Datei:

+----------------+----------------------------------------------------------------------------------------------+
| Elementname    | Beschreibung                                                                                 |
+================+==============================================================================================+
| ``<name>`` *   | Der Dateiname im Unterordner ``js/`` (siehe auch: :ref:`Aufbau <label_aufbau_frontend_res>`) |
+----------------+----------------------------------------------------------------------------------------------+
| ``<priority>`` | Die Priorität von 0\-10, je höher, desto später wird die Datei eingebunden                   |
+----------------+----------------------------------------------------------------------------------------------+
| ``<position>`` | Die Position im DOM, an der die Datei eingebunden wird, "body" oder "head"                   |
+----------------+----------------------------------------------------------------------------------------------+

(*) Pflichtfeld

Alle hier angegebenen Dateien müssen im Unterordner ``frontend/css/`` bzw. ``frontend/js/`` liegen.
Eine Beispieldarstellung finden Sie im Abschnitt "Aufbau" unter ":ref:`label_aufbau_frontend_res`". |br|
JavaScript-Dateien lassen sich dabei über das Attribut "*position*" wahlweise in den Header oder Body einfügen.
Sie können über "*priority*" (0 = höchste, 5 = Standard) in der Reihenfolge modifiziert werden.

Falls zu einer über diese Methode eingebundenen CSS-Datei ein ``_custom.css``-Pendant im selben Ordner existiert,
wird dieses **zusätzlich** nach der eigentlichen CSS-Datei eingebunden. |br|
Dem obigen Beispiel folgend wären dies ``foo_custom.css`` bzw. ``bar_custom.css``. |br|

.. attention::

    Für JavaScript-Dateien wird dieses Vorgehen nicht unterstützt.

Minify
""""""

Diese Dateien werden, bei entsprechend aktivierter Theme-Funktion, auch minifiziert. |br|
Im Theme müssen dazu die Smarty-Variablen ``$cPluginJsHeadd_arr``, ``$cPluginCss_arr`` und ``$cPluginJsBody_arr``
geprüft bzw. ausgegeben werden.

**Beispiel:**

.. code-block:: html+smarty

    {*
        mit aktiviertem minify, header.tpl
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
        ohne minify, header.tpl
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

Objektcache
-----------

Sollen bei der Installation des Plugins bestimmte Inhalte des Objektcaches gelöscht werden, weil das Plugin
beispielsweise Artikeldaten modifizieren soll, so kann im Element ``<FlushTags>`` eine Liste von *Tags* angegeben
werden, welche die einzelnen Zwischenspeicher repräsentieren, die zum Zeitpunkt der Installation zurückgesetzt werden
sollen.

.. code-block:: xml

    <FlushTags>CACHING_GROUP_CATEGORY, CACHING_GROUP_ARTICLE</FlushTags>

Weitere Informationen zum Caching und den vorhandenen *Tags* finden Sie im Kapitel ":doc:`Cache </shop_plugins/cache>`".

.. _label_infoxml_boxen:

Boxen
-----

Mit der *Boxenverwaltung* von JTL-Shop kann der Onlineshopbetreiber einfach und schnell Boxen im Onlineshop
verschieben, anlegen oder löschen.

Ein Plugin ist ebenfalls in der Lage, einen neuen Boxentyp anzulegen. Wo die Templates für diese Boxen im Plugin
zu platzieren sind, finden Sie unter "Aufbau", im Abschnitt ":ref:`label_aufbau_boxen`". |br|
Diese neue Box kann in der Boxenverwaltung ausgewählt und einer Stelle in JTL-Shop zugewiesen werden. Der Inhalt dieser
Box wird durch ein Template, das der Box zugewiesen ist, gesteuert. Dort können beliebige Inhalte angezeigt werden.

Sie erstellen einen neuen Boxentyp, indem Sie folgenden neuen XML-Knoten in der ``info.xml`` anlegen:

.. code-block:: xml
   :emphasize-lines: 3-5

   <Install>
       ...
       <Boxes>
            ...
       </Boxes>
       ...
   </Install>

Innerhalb dieses Knotens können dann beliebig viele Unterelemente vom Typ ``<Box>`` liegen. |br|
Das heißt, ein Plugin kann beliebig viele Boxentypen anlegen.

Vergeben Sie stets eindeutige Boxennamen, damit sich diese nicht mit anderen Plugins überschneiden.

**Beispiel:**

.. code-block:: xml

    <Boxes>
        <Box>
            <Name>ExampleBoxFromExamplePlugin</Name>
            <Available>0</Available>
            <TemplateFile>example_box.tpl</TemplateFile>
        </Box>
    </Boxes>

+--------------------+------------------------------------------------------------------+
| Elementname        | Beschreibung                                                     |
+====================+==================================================================+
| ``<Name>``         | Name des Boxentyps                                               |
+--------------------+------------------------------------------------------------------+
| ``<Available>``    | Seitentyp, in dem die Box angezeigt wird |br|                    |
|                    | (z. B.: 0= jede Seite, 1= Artikeldetails, 2= Artikelliste, usw.) |
+--------------------+------------------------------------------------------------------+
| ``<TemplateFile>`` | Templatedatei mit dem Inhalt der Box                             |
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

Mit Plugin-Widgets lassen sich einfach und schnell eigene Widgets im Backend-Dashboard von JTL-Shop implementieren.

Ein Plugin ist in der Lage, ein *AdminWidget* anzulegen.
Der Inhalt dieses Widgets wird durch eine Klasse und ein Template gesteuert. Somit können beliebige Inhalte
angezeigt werden. Wo die zugehörigen Dateien zu platzieren sind, erfahren Sie unter "Aufbau"
im Abschnitt ":ref:`label_aufbau_widgets`".

Sie erstellen einen neues *AdminWidget*, indem Sie folgenden neuen XML-Knoten im XML-Container ``<Install>``
in Ihrer ``info.xml`` einfügen:

.. code-block:: xml
   :emphasize-lines: 3-5

   <Install>
       ...
       <AdminWidget>
           ...
       </AdminWidget>
       ...
   </Install>

In diesem XML-Container können sich beliebig viele Unterelemente vom Typ ``<Widget>`` befinden.
Das heißt, ein Plugin kann beliebig viele *AdminWidgets* anlegen.

**Beispiel:**

.. code-block:: xml

    <AdminWidget>
        <Widget>
            <Title>Serverinfo (Plugin)</Title>
            <Class>Info</Class>
            <Container>center</Container>
            <Description>Beispielplugin</Description>
            <Pos>1</Pos>
            <Expanded>1</Expanded>
            <Active>1</Active>
        </Widget>
    </AdminWidget>

+-------------------+-----------------------------------------------------------------------+
| Elementname       | Beschreibung                                                          |
+===================+=======================================================================+
| ``<Title>`` *     | Titelüberschrift des AdminWidgets                                     |
+-------------------+-----------------------------------------------------------------------+
| ``<Class>`` *     | Klassenname der PHP-Klasse, die den Inhalt des Widgets bereitstellt   |
+-------------------+-----------------------------------------------------------------------+
| ``<Container>`` * | Position des Dashboardcontainers. Werte: center, left, right          |
+-------------------+-----------------------------------------------------------------------+
| ``<Description>`` | Beschreibung des AdminWidgets                                         |
+-------------------+-----------------------------------------------------------------------+
| ``<Pos>`` *       | Vertikale Position im Container. Ganzzahl (1 = oben)                  |
+-------------------+-----------------------------------------------------------------------+
| ``<Expanded>`` *  | AdminWidget soll ausgeklappt oder minimiert sein. Ganzzahl, 0 oder 1. |
+-------------------+-----------------------------------------------------------------------+
| ``<Active>`` *    | AdminWidget direkt sichtbar im Dashboard. Ganzzahl, 0 oder 1.         |
+-------------------+-----------------------------------------------------------------------+

(*) Pflichtfeld

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

**Beispiel:**

.. code-block:: php

   <?php

   class WidgetInfo_jtl_test extends WidgetBase
   {
   }

Widgets ab JTL-Shop 5.x
"""""""""""""""""""""""

Ab JTL-Shop 5.0.0 werden Klassen wie folgt generiert:

* Annahme: Das XML schreibt vor, die Klasse heißt ``<Class>Info</Class>``
  und die Plugin-ID lautet ``<PluginID>jtl_test</PluginID>``.

* Dann muss im Verzeichnis ``/adminmenu/widget/`` des Plugins die Datei ``Info.php`` liegen
  (siehe auch: Abschnitt ":ref:`Aufbau / Widgets <label_aufbau_widgets>`")

* Die Klasse in der Datei muss den Namen "*Info*" tragen und von der Basisklasse "*AbstractWidget*" abgeleitet sein.

* Die Klasse muss im Namespace ``<PluginID>`` liegen.

**Beispiel:**

.. code-block:: php

    <?php

    namespace jtl_test;

    use JTL\Widgets\AbstractWidget;

    class Info extends AbstractWidget
    {
    }

.. _label_infoxml_portlets:

Portlets (ab 5.0.0)
-------------------

Ab Shop 5.0.0 können Plugins auch :doc:`Portlets </shop_plugins/portlets>` für den *OnPageComposer* definieren. |br|
Dies geschieht über den XML-Knoten ``<Portlets>``, der seinerseits unlimitiert viele Unterknoten vom Typ ``<Portlet>``
enthalten kann.

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
| Elementname    | Beschreibung                                                                        |
+================+=====================================================================================+
| ``<Title>`` *  | Titel des Portlets (lokalisierbar mit PO-Datei),                                    |
|                | wie im "*OPC-Editor*" (Frontend) und unter "*OnPage Composer*" im Backend angezeigt |
+----------------+-------------------------------------------------------------------------------------+
| ``<Class>`` *  | Klassenname des Portlets                                                            |
+----------------+-------------------------------------------------------------------------------------+
| ``<Group>`` *  | Name der Gruppe, unter welcher das Portlet in der Portlet-Palette eingeordnet ist   |
+----------------+-------------------------------------------------------------------------------------+
| ``<Active>`` * | Status (1 = aktiviert, 0 = deaktiviert)                                             |
+----------------+-------------------------------------------------------------------------------------+

(*) Pflichtfeld

Portlets bestehen immer aus einer PHP-Datei mit dem Dateinamen ``<Portlet-Class-Name>.php``, die eine einzelne Klasse
mit dem Namen ``<Portlet-Class-Name>`` definiert und sich im
Namespace ``Plugin\[Plugin-ID]\Portlets\[Portlet-Class-Name]`` befinden muss. |br|
Diese neue Portletklasse sollte immer von der OPC-Portlet-Klasse ``JTL\OPC\Portlet`` des Onlineshops erben. |br|

**Beispiel:**

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Plugin\jtl_test\Portlets;

    use JTL\OPC\Portlet;

    class MyPortlet extends Portlet
    {
        // ...
    }

Die korrekte Platzierung aller entsprechenden Dateien in Ihrem Plugin finden Sie unter "Aufbau"
im Abschnitt ":ref:`label_aufbau_portlets`". |br|
Wie Sie mit Ihren neuen Portlets weiter verfahren können, lesen Sie
im Abschnitt :doc:`Portlets </shop_plugins/portlets>`.

.. _label_infoxml_blueprints:

Blueprints (ab 5.0.0)
---------------------

Ab JTL-Shop 5.0.0 können Plugins auch Blueprints, also *Kompositionen von einzelnen Portlets*, definieren.

Hierfür definiert man ebenfalls wieder einen neuen Knoten namens ``Blueprints`` im Container ``Install``, der
seinerseits wiederum unlimitiert Unterknoten des Typs ``Blueprint`` enthalten kann.

.. code-block:: xml

    <Install>
        ...
        <Blueprints>
           <Blueprint>
               <Name>Bild links Text rechts</Name>
               <JSONFile>image_4_text_8.json</JSONFile>
           </Blueprint>
           <Blueprint>
               <Name>Text links Bild rechts</Name>
               <JSONFile>text_8_image_4.json</JSONFile>
           </Blueprint>
        </Blueprints>
        ...
    </Install>


Blueprint:

+------------------+----------------------------------------------------------------+
| Elementname      | Beschreibung                                                   |
+==================+================================================================+
| ``<Name>`` *     | Der im OPC Control Center angezeigte Name                      |
+------------------+----------------------------------------------------------------+
| ``<JSONFile>`` * | Name der JSON-Datei im Unterordner ``blueprints/`` des Plugins |
+------------------+----------------------------------------------------------------+

(*) Pflichtfeld

Erstellt werden können die json-Datein über den Export im *OPC-Editor*. |br|
Wie diese Struktur unterhalb Ihres Plugins aussieht, finden Sie unter "Aufbau"
im Abschnitt ":ref:`label_aufbau_blueprints`".

Consent-Manager (ab 5.0.0)
--------------------------

Ab JTL-Shop 5.0.0 können Plugins eigene Einträge im Consent-Manager definieren. |br|
Um das zu realisieren, fügen Sie den XML-Knoten ``<ServicesRequiringConsent>`` in die ``info.xml`` Ihres Plugins
ein. Dieser XML-Knoten kann selbst wiederum beliebig viele Unterknoten des Typs ``<Vendor>`` beinhalten.

**Beispiel:**

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
| Elementname           | Beschreibung                                                                        |
+=======================+=====================================================================================+
| ``<ID>`` *            | ID des Elements (``[a-zA-Z0-9_]``)                                                  |
+-----------------------+-------------------------------------------------------------------------------------+
| ``<Company>`` *       | Firmenname                                                                          |
+-----------------------+-------------------------------------------------------------------------------------+
| ``<Localization>`` *  | Gruppe von Übersetzungen                                                            |
+-----------------------+-------------------------------------------------------------------------------------+


``<Localization>``:

+------------------------+-------------------------------------------------------------------------------------+
| Elementname            | Beschreibung                                                                        |
+========================+=====================================================================================+
| ``<Name>`` *           | Name der Funktionalität                                                             |
+------------------------+-------------------------------------------------------------------------------------+
| ``<Purpose>`` *        | Zweck des Cookies                                                                   |
+------------------------+-------------------------------------------------------------------------------------+
| ``<Description>`` *    | Ausführliche Beschreibung des Zweckes und der Funktionalität                        |
+------------------------+-------------------------------------------------------------------------------------+
| ``<PrivacyPolicy>`` *  | Externer Link zur Datenschutzerklärung                                              |
+------------------------+-------------------------------------------------------------------------------------+


(*) Pflichtfeld


Adminmenü
---------

Im Administrationsbereich von JTL-Shop werden im Menüpunkt **Pluginverwaltung** alle Plugins angezeigt, die entweder
nicht installiert (verfügbar), fehlerhaft oder installiert sind.
Falls kein Adminmenü gewünscht ist, lassen Sie bitte den kompletten ``<Adminmenu>``-Container weg.

Fehlerhafte Plugins werden mit dem entsprechenden Fehlercode angezeigt. |br|
Eine Tabelle mit möglichen Fehlercodes finden Sie unter :doc:`Fehlercodes </shop_plugins/fehlercodes>`.

.. code-block:: xml

    <Adminmenu>
        ...
    </Adminmenu>

In diesem Element folgen, je nach Bedarf, das Kindelement ``<Customlink>`` (":ref:`label_infoxml_custom_links`") und
``<Settinglink>`` (":ref:`label_infoxml_setting_links`"). |br|
Falls kein ``<Customlink>`` und kein ``<Settinglink>`` existiert, wird der ``<Adminmenu>``-Container ignoriert.

.. _label_infoxml_custom_links:

Custom-Links
------------

*Custom-Links* werden im Adminbereich unter dem jeweiligen Plugin angezeigt. |br|
Mit Hilfe dieser Links kann ein Plugin Seiten mit eigenem Inhalt im Backend anlegen, die Informationen für den
Betreiber des Onlineshops bereitstellen. |br|
*Custom-Links* werden im Backend in Tabs dargestellt.

.. code-block:: xml

    <Customlink sort="1">
        <Name>Statistik</Name>
        <Filename>stats.php</Filename>
    </Customlink>


+----------------------+-------------------------------------+
| Elementname          | Funktion                            |
+======================+=====================================+
| Attribut ``sort=`` * | Sortierungsnummer des Tabs          |
+----------------------+-------------------------------------+
| ``<Name>`` *         | Name des Tabs (``[a-zA-Z0-9_\-]+``) |
+----------------------+-------------------------------------+
| ``<Filename>`` *     | Ausführbare PHP-Datei               |
+----------------------+-------------------------------------+

(*)Pflichtfelder

.. _label_infoxml_setting_links:

Setting-Links
-------------

*Setting-Links* sind Tabs, die Einstellungen zum Plugin abfragen. |br|
Hier können beliebig viele Einstellungen angelegt werden. Einstellungen können unterschiedliche Werte abfragen
(z. B Text, Zahl, Auswahl aus einer Selectbox). Diese Einstellungen können durch den Betreiber des Onlineshops im
Backend konfiguriert und dann im eigenen Plugin-Code abgefragt werden.

.. code-block:: xml

    <Settingslink sort="2">
        <Name>Einstellungen</Name>
        <Setting type="text" initialValue="Y" sort="4" conf="N">
            <Name>Online Watcher</Name>
            <Description>Online Watcher</Description>
            <ValueName>onlinewatcher</ValueName>
        </Setting>
    </Settingslink>

``<Settinglink>``:

+----------------------+----------------------------+
| Elementname          | Funktion                   |
+======================+============================+
| Attribut ``sort=`` * | Sortierungsnummer des Tabs |
+----------------------+----------------------------+
| ``<Name>`` *         | Name des Tabs              |
+----------------------+----------------------------+
| ``<Setting>`` *      | Einstellungselement        |
+----------------------+----------------------------+

(*)Pflichtfelder

.. _label_infoxml_settingtypes:

``<Setting>``:

+------------------------------+-------------------------------------------------------------------------------------------+
| Elementname                  | Funktion                                                                                  |
+==============================+===========================================================================================+
| Attribut ``type=`` *         | Einstellungstyp (``text``, ``textarea``, ``selectbox``, ``checkbox``, ``radio``, |br|     |
|                              | ``colorpicker``, ``email``, ``date``, ``time``, ``tel``, ``url`` [ab Shop 5.0: ``none``]) |
+------------------------------+-------------------------------------------------------------------------------------------+
| Attribut ``initialValue=`` * | Vorausgewählte Einstellung                                                                |
+------------------------------+-------------------------------------------------------------------------------------------+
| Attribut ``sort=``           | Sortierung der Einstellung (höher = weiter unten)                                         |
+------------------------------+-------------------------------------------------------------------------------------------+
| Attribut ``conf=`` *         | Y = echte Einstellung, N = Überschrift                                                    |
+------------------------------+-------------------------------------------------------------------------------------------+
| ``<Name>`` *                 | Name der Einstellung (``[a-zA-Z0-9_\-]+``)                                                |
+------------------------------+-------------------------------------------------------------------------------------------+
| ``<Description>``            | Beschreibung der Einstellung                                                              |
+------------------------------+-------------------------------------------------------------------------------------------+
| ``<ValueName>`` *            | Name der Einstellungsvariable, die im PHP-Code genutzt wird                               |
+------------------------------+-------------------------------------------------------------------------------------------+
| ``<SelectboxOptions>``       | Optionales Kindelement bei type = selectbox                                               |
+------------------------------+-------------------------------------------------------------------------------------------+
| ``<RadioOptions>``           | Optionales Kindelement bei type = radio                                                   |
+------------------------------+-------------------------------------------------------------------------------------------+
| ``<OptionsSource>``          | Dynamische Quelle für Optionen in Checkbox/Selectbox                                      |
+------------------------------+-------------------------------------------------------------------------------------------+

(*)Pflichtfelder

**Ab JTL-Shop 5.0.0** kann als Typ auch "``type=none``" gewählt werden. Solche Optionen werden nicht im Settings-Tab
angezeigt. |br|
Dies bietet sich an, falls eine eigene Darstellung in einem anderen Tab für die Option gewählt werden soll.
Der Wert wird dann trotzdem in der Plugininstanz gespeichert, sodass kein Umweg über eine eigene SQL-Logik
erforderlich ist. Allerdings müssen Sie den Objektcache ggf. manuell invalidieren.

Falls der Typ der Einstellung ``type=selectbox`` ist, geben Sie das Kindelement ``<SelectboxOptions>`` an.

.. code-block:: xml

    <SelectboxOptions>
        <Option value="Y" sort="1">Ja</Option>
        <Option value="N" sort="2">Nein</Option>
    </SelectboxOptions>

+-----------------------+----------------------------------------------+
| Elementname           | Funktion                                     |
+=======================+==============================================+
| ``<Option>`` *        | Angezeigter Wert in der Selectbox-Option     |
+-----------------------+----------------------------------------------+
| Attribut ``value=`` * | Wert der Selectbox-Option                    |
+-----------------------+----------------------------------------------+
| Attribut ``sort=``    | Sortierung der Option (höher = weiter unten) |
+-----------------------+----------------------------------------------+

(*)Pflichtfelder

Falls der Typ der Einstellung ``type=radio`` ist, geben Sie das Kindelement ``<RadioOptions>`` an.

.. code-block:: xml

    <RadioOptions>
        <Option value="Y" sort="1">Ja</Option>
        <Option value="N" sort="2">Nein</Option>
        <Option value="V" sort="3">Vielleicht</Option>
    </RadioOptions>

+-----------------------+----------------------------------------------+
| Elementname           | Funktion                                     |
+=======================+==============================================+
| ``<Option>`` *        | Angezeigter Wert in der Radio-Option         |
+-----------------------+----------------------------------------------+
| Attribut ``value=`` * | Wert der Radio-Option                        |
+-----------------------+----------------------------------------------+
| Attribut ``sort=``    | Sortierung der Option (höher = weiter unten) |
+-----------------------+----------------------------------------------+

(*)Pflichtfelder

Anstelle von oder zusätzlich zu *RadioOptions* bzw. *SelectboxOptions* können Sie seit JTL-Shop 4.05 das
Element ``<OptionsSource>`` hinzufügen. Sobald es vorhanden ist, wird das RadioOptions- bzw. SelectboxOptions-Element
ignoriert.

+--------------+---------------------------------+
| Elementname  | Funktion                        |
+==============+=================================+
| ``<File>`` * | Dateiname, relativ zu adminmenu |
+--------------+---------------------------------+

(*)Pflichtfelder

Hierdurch können in einer PHP-Datei dynamische Optionswerte definiert werden. |br|
Dies ist insbesondere dann sinnvoll, wenn keine statischen Auswahlmöglichkeiten wie "Ja/Nein" o. Ä. zur Auswahl
angeboten werden sollten, sondern z. B. Artikel/Kategorien/Seiten oder andere onlineshopspezifische Werte. |br|
Die angegebene Datei muss ein Array von Objekten liefern, wobei als Objektmember jeweils "*cWert*" und "*cName*" und
optional "*nSort*" vorhanden sein müssen.

Die entsprechende Datei muss sich hierbei im Ordner ``adminmenu/`` des Plugins befinden.
(siehe auch: Abschnitt :ref:`label_adminmenu_structure`)

**Beispiel für eine dynamische Option**:

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

In diesem Beispiel würden entsprechend die 3 Auswahlmöglichkeiten "*Wert A*", "*Wert B*" und "*Wert C*" zur Auswahl
stehen.


.. _label_infoxml_locale:

Übersetzung von Settings
------------------------

Ab JTL-Shop 5.0.0 können Plugin-Optionen mehrsprachig gestaltet werden. |br|
Dies betrifft in jedem ``<Setting>``-Element die Knoten ``<Name>`` und ``<Description>`` sowie die Werte von
``<SelectboxOptions>`` und ``<RadioOptions>``.
Die jeweiligen Werte können als *msgid*-Schlüssel in der ``base.po`` des Plugins angegeben und übersetzt werden.

Generell müssen Sie hierzu im Unterordner ``locale/`` des Plugins für jede zu übersetzende Sprache einen Unterordner
mit zugehörigem IETF-Language-Tag und darin die Datei ``base.po`` erstellen. |br|
Die entsprechende Verzeichnisstruktur finden Sie unter "Aufbau"
im Abschnitt ":ref:`label_aufbau_locale`".

**Beispiel:**

Angenommen, Sie möchten die folgende Option in die Sprachen Englisch und Deutsch übersetzen:

.. code-block:: xml

    <Setting type="selectbox" initialValue="Y" sort="1" conf="Y">
        <Name>Finden Sie das hier hilfreich?</Name>
        <Description>Stellt eine simple Ja/Nein-Frage</Description>
        <ValueName>myplugin_is_helpful</ValueName>
        <SelectboxOptions>
            <Option value="Y" sort="0">Ja</Option>
            <Option value="N" sort="1">Nein</Option>
            <Option value="V" sort="2">Vielleicht</Option>
        </SelectboxOptions>
    </Setting>

Im Beispiel wollen Sie eine einfache "Ja/Nein"-Frage in den Settings unseres Plugins stellen.

Legen Sie folgende Dateien unter der Plugin-Root an:

* ``locale/de-DE/base.po``
* ``locale/en-US/base.po``

Ausführliche Informationen dazu finden Sie im Kapitel "Aufbau" im Abschnitt ":ref:`label_aufbau_locale`".

Der Inhalt könnte für *Deutsch* folgendermaßen aussehen (``de-DE/base.po``):

.. code-block:: pot

    msgid "Ja"
    msgstr "Ja"

    msgid "Nein"
    msgstr "Nein"

    msgid "Finden Sie das hier hilfreich?"
    msgstr "Finden Sie das hier hilfreich?"

    msgid "Stellt eine simple Ja/Nein-Frage"
    msgstr "Stellt eine simple Ja/Nein-Frage"


und für *Englisch* entsprechend so (``en-US/base.po``):

.. code-block:: pot

    msgid "Ja"
    msgstr "Yes"

    msgid "Nein"
    msgstr "No"

    msgid "Finden Sie das hier hilfreich?"
    msgstr "Do you find this helpful?"

    msgid "Stellt eine simple Ja/Nein-Frage"
    msgstr "Asks a simple yes/no question"

In unserem Beispiel haben wir absichtlich den String "*Vielleicht*" weder aufgeführt, noch übersetzt. |br|
Dies soll verdeutlichen, dass "*Veilleicht*" in allen Sprachen *unverändert* ausgegeben wird.

Anschließend müssen Sie die .po-Dateien nur noch z. B. mit `Poedit <https://poedit.net/>`_ zur ``base.mo``
kompilieren.

.. note:

    Checkbox-Spezialfunktionen
    --------------------------

    Über die Pluginschnittstelle lassen sich auch Checkboxfunktionen registrieren, welche dann als Spezialfunktion in der
    Checkboxverwaltung zur Verfügung stehen.

    **Beispiel:**

    .. code-block:: xml

        <CheckBoxFunction>
            <Function>
                <Name>Name der Spezialfunktion</Name>
                <ID>meinespezialfunktion</ID>
            </Function>
        </CheckBoxFunction>

    Hiermit wird bei einer Plugin-Installation ein neuer Eintrag in die Tabelle ``tcheckboxfunktion`` geschrieben.

    Wird die Checkbox angehakt und ist dafür "*Spezialfunktion Plugin*" gewählt, so wird die jeweilige php-Datei des
    Plugins inkludiert.


.. _label_infoxml_frontendlinks:

Frontend-Links
--------------

Mit Hilfe von *Frontend-Links* ist ein Plugin in der Lage, einen Link in JTL-Shop anzulegen
und den Inhalt zu verwalten. |br|
Sie können beliebig viele Elemente des Typs ``<Link>`` anlegen. Falls kein *Frontend-Link* angegeben wird, sollten Sie
den Block ``<FrontendLink>`` komplett weglassen. |br|

In Versionen bis JTL-Shop 4.x werden Links in *Linkgruppen-Verwaltung* unter CMS ("Seiten -> Eigene Seiten") angelegt.
Dort können durch Plugins angelegte Links im Nachhinein verwaltet werden. |br|
Ab JTL-Shop 5.x werden neue *Frontend-Links*, unter "Eigene Inhalte" -> "Seiten", der Linkgruppe "Hidden" zugewiesen.

Um nun beispielsweise den Frontend-Link "JTL Test Page" des JTL-Plugins
"`Demo Plugin <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_test>`_" in Ihrem Onlineshop sichtbar zu
machen, können Sie ihn aus der Linkgruppe "Hidden" in die Linkgruppe "Megamenu" verschieben.
Im Megamenü Ihres Onlineshops wird sodann dieser neue Frontend-Link als letzter Eintrag angezeigt.

Jeder Link kann in beliebig vielen Sprachen *lokalisiert* werden. |br|
Dazu wird das Element ``<LinkLanguage>`` mit seinem Attribut ``iso`` verwendet. Sein Inhalt wird in Großbuchstaben
geschrieben (z. B.: für Deutsch = GER).
Es werden jedoch immer nur maximal die Sprachen installiert, die der Onlineshop auch beinhaltet. |br|
Hat ein Plugin weniger als die im Onlineshop installierten Sprachen hinterlegt, werden alle weiteren Onlineshopsprachen
mit der Standardsprache aufgefüllt.

Jeder Frontend-Link benötigt eine Smarty-Templatedatei. |br|
Es gibt zwei verschiedene Arten, diese Inhalte anzuzeigen. |br|
Die erste Möglichkeit besteht darin, den Inhalt in einem definierten Bereich (*Contentbereich*) des Onlineshop
anzuzeigen. Dies wird durch das Element ``<Template>`` erreicht. |br|
Die zweite Möglichkeit wäre, den Inhalt auf einer komplett neuen Seite zu zeigen. Dies benötigt das
Element ``<FullscreenTemplate>``. |br|

.. important::

    Eine der beiden Varianten muss gesetzt sein. |br|
    Es ist **nicht** möglich, beide Anzeigemöglichkeiten **gleichzeitig** in der ``info.xml`` zu definieren.

Im folgenden Beispiel wird die Smarty-Templatedatei ``test_page.tpl``, die sich
im Ordner ``template/`` befindet, im fest definierten Contentbereich des Onlineshops geladen.

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

Ein Frontend-Link benötigt keinen expliziten Hook, denn das System bindet den Link automatisch an einem fest
definierten Hook.

Link:

+----------------------------+---------------------------------------------------------+
| Elementname                | Funktion                                                |
+============================+=========================================================+
| ``<Filename>`` *           | Auszuführende Datei beim Link                           |
+----------------------------+---------------------------------------------------------+
| ``<Name>`` *               | Name des Links (``[a-zA-Z0-9 ]+``)                      |
+----------------------------+---------------------------------------------------------+
| ``<Template>`` *           | Smarty-Templatedatei die den Linkinhalt anzeigt         |
+----------------------------+---------------------------------------------------------+
| ``<FullscreenTemplate>`` * | Smarty-Templatedatei die den Linkinhalt anzeigt         |
+----------------------------+---------------------------------------------------------+
| ``<VisibleAfterLogin>`` *  | Nur anzeigen wenn der User eingeloggt ist ([NY]{1,1})   |
+----------------------------+---------------------------------------------------------+
| ``<PrintButton>`` *        | Druckbutton anzeigen ([NY]{1,1})                        |
+----------------------------+---------------------------------------------------------+
| ``<NoFollow>`` *           | NoFollow-Attribut in den HTML Code einfügen ([NY]{1,1}) |
+----------------------------+---------------------------------------------------------+
| ``<LinkLanguage>`` *       |                                                         |
+----------------------------+---------------------------------------------------------+
| ``<Identifier>``           | Unveränderbare ID, seit 5.1.0 (``[a-zA-Z0-9 ]+``)       |
+----------------------------+---------------------------------------------------------+
| ``<SSL>``                  | 0 oder 1 für Standard, 2 für erzwungenes SSL            |
+----------------------------+---------------------------------------------------------+

LinkLanguage

+-----------------------+--------------------------------------------------+
| Elementname           | Funktion                                         |
+=======================+==================================================+
| ``<iso>`` *           | Sprach.ISO (``[A-Z]{3}``)                        |
+-----------------------+--------------------------------------------------+
| ``<Seo>`` *           | SEO-Name des Links (``[a-zA-Z0-9 ]+``)           |
+-----------------------+--------------------------------------------------+
| ``<Name>`` *          | Name des Links (``[a-zA-Z0-9 ]+``)               |
+-----------------------+--------------------------------------------------+
| ``<Title>`` *         | Titel des Links (``[a-zA-Z0-9 ]+``)              |
+-----------------------+--------------------------------------------------+
| ``<MetaTitle>`` *     | Meta Title des Links (``[a-zA-Z0-9,. ]+``)       |
+-----------------------+--------------------------------------------------+
| ``<MetaKeywords>`` *  | Meta Keywords des Links (``[a-zA-Z0-9, ]+``)     |
+-----------------------+--------------------------------------------------+
| ``<MetaDescription>`` | Meta Description des Links (``[a-zA-Z0-9,. ]+``) |
+-----------------------+--------------------------------------------------+

(*) Pflichtfeld


.. _label_infoxml_paymentmethode:

Zahlungsmethoden
----------------

Das Pluginsystem von JTL-Shop ist in der Lage, eine oder mehrere Zahlungsmethoden zugleich
zu implementieren. Dabei wird nicht in den Code von JTL-Shop eingegriffen. |br|
Das Hauptelement ``<PaymentMethod>`` wird unter dem Element ``<Install>`` eingefügt. Es kann beliebig viele
Zahlungsmethoden (``<Method>``) enthalten. |br|
Falls das Plugin keine Zahlungsmethode implementieren soll, wird der ``<PaymentMethod>`` Block ganz weggelassen.

.. code-block:: xml

    <Install>
        ...
        <PaymentMethod>
            ...
        </PaymentMethod>
        ...
    </Install>

+----------------+-----------------+
| Elementname    | Funktion        |
+================+=================+
| ``<Method>`` * | Zahlungsmethode |
+----------------+-----------------+

(*) Pflichtfeld

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

Für JTL-Shop 5 sieht der in der oberen Abbildung gelb hervorgehobene Teil der Struktur wie folgt aus:

.. code-block:: xml

    <Method>
        ...
        <ClassFile>PayPalPlus.php</ClassFile>
        <ClassName>PayPalPlus</ClassName>
        ...
    </Method>

+------------------------------+-----------------------------------------------------------------+
| Elementname                  | Funktion                                                        |
+==============================+=================================================================+
| ``<Name>`` *                 | Name der Zahlungsmethode                                        |
+------------------------------+-----------------------------------------------------------------+
| ``<PictureURL>`` *           | Link zu einem Logo                                              |
+------------------------------+-----------------------------------------------------------------+
| ``<Sort>`` *                 | Sortierungsnummer der Zahlungsmethode (``[0-9]+``)              |
+------------------------------+-----------------------------------------------------------------+
| ``<SendMail>`` *             | Versand einer E-Mail bei Zahlungseingang (1 = "Ja", 0 = "Nein") |
+------------------------------+-----------------------------------------------------------------+
| ``<Provider>``               | Zahlungsanbieter                                                |
+------------------------------+-----------------------------------------------------------------+
| ``<TSCode>`` *               | Trusted Shops TSCode(``[A-Z_]+``)                               |
+------------------------------+-----------------------------------------------------------------+
| ``<PreOrder>`` *             | Pre(1)- oder Post(0)-Bestellung(``[0-1]{1}``)                   |
+------------------------------+-----------------------------------------------------------------+
| ``<Soap>`` *                 | Übertragungsprotokoll Flag (``[0-1]{1}``)                       |
+------------------------------+-----------------------------------------------------------------+
| ``<Curl>`` *                 | Übertragungsprotokoll Flag (``[0-1]{1}``)                       |
+------------------------------+-----------------------------------------------------------------+
| ``<Sockets>`` *              | Übertragungsprotokoll Flag (``[0-1]{1}``)                       |
+------------------------------+-----------------------------------------------------------------+
| ``<ClassFile>`` *            | Name der Datei der PHP-Klasse (``[a-zA-Z0-9\/_\-.]+.php``)      |
+------------------------------+-----------------------------------------------------------------+
| ``<ClassName>`` *            | Name der Klasse                                                 |
+------------------------------+-----------------------------------------------------------------+
| ``<TemplateFile>``           | Name der Templatedatei (``[a-zA-Z0-9\/_\-.]+.tpl``)             |
+------------------------------+-----------------------------------------------------------------+
| ``<AdditionalTemplateFile>`` | Templatedatei für einen Zusatzschritt                           |
+------------------------------+-----------------------------------------------------------------+
| ``<MethodLanguage>`` *       | Lokalisierung der Zahlungsmethode                               |
+------------------------------+-----------------------------------------------------------------+
| ``<Setting>``                | Einstellungen der Zahlungsmethode                               |
+------------------------------+-----------------------------------------------------------------+

(*) Pflichtfelder

Die Elemente ``<Soap>``, ``<Curl>`` und ``<Sockets>`` benennen die erforderlichen Kommunikationswege zum Server
des Zahlungsanbieters, die von diesem Zahlungsmethodenplugin genutzt werden sollen. |br|
Diese Elemente werden bei der Installation des Plugins vom Plugin-System des Shops geprüft und mit den vorhandenen
Möglichkeiten des aktuellen Shop-Servers abgeglichen (z. B. das Vorhandensein entsprechender PHP-Module). Die Prüfung
erfolgt hierbei in einer **ODER**-Verknüpfung. |br|
D.h. sobald eine der geforderten Übertragungarten zum Server des Zahlungsnabieters verfügbar ist, wird auch das
Zahlungsmethodenplugin nach der Installation als arbeitsfähig markiert. |br|
In einigen Fällen werden jedoch mehrere Übertragungswege benötigt, beispielsweise SOAP zur Nutzerdatenübertragung
und CURL für Liquiditätsprüfungen. Das Plugin-System prüft jedoch standardmäßig nicht, ob alle benötigten
Übertragungswege zur Verfügung stehen. Deshalb müssen Sie selbst programmatisch dafür sorgen (z. B. mithilfe der
Methode ":ref:`isValidIntern() <label_public-function-method-isValidIntern>`"), dass das Plugin dem Plugin-System
mitteilt, wenn nicht alle benötigten Übertragungswege verfügbar sind. |br|
Falls das Zahlungsplugin dagegen z.B. auf einem POST-Formular aufgebaut ist, kann man hier jedem Element
eine ``0`` zuweisen.

Im Element ``<TemplateFile>`` kann der Name oder Pfad zu einer Smarty Template-Datei angegeben werden.
Dort können dann z.B. POST-Formulare ausgegeben werden. |br|

.. _label_AdditionalTemplateFile:

Im Element ``<AdditionalTemplateFile>`` können Sie außerdem eine Smarty-Templatedatei für einen zusätzlichen
Zahlungsschritt angeben. Hier können z. B. Kreditkarteninfos abgefragt werden.

Das Element ``<TSCode>`` kann folgende Werte enthalten: "*DIRECT_DEBIT*", "*CREDIT_CARD*", "*INVOICE*",
"*CASH_ON_DELIVERY*", "*PREPAYMENT*", "*CHEQUE*", "*PAYBOX*", "*PAYPAL*", "*CASH_ON_PICKUP*", "*FINANCING*",
"*LEASING*", "*T_PAY*", "*CLICKANDBUY*", "*GIROPAY*", "*GOOGLE_CHECKOUT*", "*SHOP_CARD*", "*DIRECT_E_BANKING*",
"*OTHER*".

Der XML-Knoten ``<MethodLanguage>`` sorgt für die Mehrsprachigkeit der Zahlungsmethode. |br|
Sie können beliebig viele Sprachen für eine Zahlungsmethode implementieren. Eine Sprache muss jedoch mindestens
enthalten sein.

+--------------------+------------------------------------------------------------------+
| Elementname        | Funktion                                                         |
+====================+==================================================================+
| ``<iso>`` *        | Sprachcode der jeweiligen Sprache                                |
+--------------------+------------------------------------------------------------------+
| ``<Name>`` *       | Name der Zahlungsmethode                                         |
+--------------------+------------------------------------------------------------------+
| ``<ChargeName>`` * | Sortierungsnummer der Zahlungsmethode (``[0-9]+``)               |
+--------------------+------------------------------------------------------------------+
| ``<InfoText>`` *   | Kurzbeschreibung der Zahlart, wie sie im Frontend angezeigt wird |
+--------------------+------------------------------------------------------------------+

(*) Pflichtfelder

Der XML-Knoten ``<Setting>`` ermöglicht es dem Plugin, spezifische Einstellungen des Onlineshopbetreibers
entgegenzunehmen. |br|
Jede Zahlungsmethode kann beliebig viele Einstellungen enthalten, z.B. die Logindaten für einen bestimmten
Onlineshopbetreiber. Diese Einstellungen werden im Backend bei der jeweiligen Zahlungsmethode angezeigt und können dort
editiert werden.

+------------------------+---------------------------------------------------+
| Elementname            | Funktion                                          |
+========================+===================================================+
| ``<type>`` *           | Einstellungstyp (text, zahl, selectbox)           |
+------------------------+---------------------------------------------------+
| ``<initValue>`` *      | Vorausgewählte Einstellung                        |
+------------------------+---------------------------------------------------+
| ``<sort>`` *           | Sortierung der Einstellung (höher = weiter unten) |
+------------------------+---------------------------------------------------+
| ``<conf>``  *          | Y = echte Einstellung, N = Überschrift            |
+------------------------+---------------------------------------------------+
| ``<Name>`` *           | Name der Einstellung                              |
+------------------------+---------------------------------------------------+
| ``<Description>`` *    | Beschreibung der Einstellungsvariable             |
+------------------------+---------------------------------------------------+
| ``<ValueName>`` *      | Name der Einstellungsvariable                     |
+------------------------+---------------------------------------------------+
| ``<SelectboxOptions>`` | Optionales Element bei type = selectbox           |
+------------------------+---------------------------------------------------+

(*) Pflichtfelder

Weitere Informationen zum Thema "Zahlungsarten im Plugin" finden Sie im Kapitel ":doc:`payment_plugins`".

Sprachvariablen
---------------

Sprachvariablen sind lokalisierte Variablen, die für verschiedene Sprachen hinterlegt und abgerufen werden können. |br|
Sofern die Sprachen von JTL-Shop und die Sprachen des Plugins übereinstimmen, passen sich die Sprachvariablen für jede
eingestellte Sprache im Onlineshop automatisch lokalisiert an. |br|
Sollte das Plugin *Frontend-Links* bereitstellen, so sollte jede textuelle Ausgabe mittels dieser Sprachvariablen
ausgegeben werden.

.. note::

    *Sprachvariablen* sind nicht zu verwechseln mit den ":ref:`label_infoxml_locale`", im Backend des Onlineshops.

Anpassung der Sprachvariablen in den Plugineinstellungen des Adminbereichs
""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Sprachvariablen können nach der Installation eines Plugins vom Betreiber des Onlineshops angepasst werden. |br|
Zu diesem Zweck stellt die Pluginverwaltung die Spalte "*Sprachvariablen*" bereit, in der sich die Schaltfläche
"*Bearbeiten*" befinden kann, sobald ein Plugin Sprachvariablen bereitstellt.

Ein Plugin kann beliebig viele Sprachvariablen definieren. |br|
Das Hauptelement der Sprachvariablen heißt ``<Locales>`` und jede Sprachvariable wird im Element ``<Variable>``
definiert. |br|
``<Locales>`` ist ein Unterknoten von ``<Install>``. |br|
Im XML-Container ``<Variable>`` können beliebig viele ``<VariableLocalized>``-Knoten eingebunden werden.

.. code-block:: xml

    <Locales>
        <Variable>
            <Name>xmlp_lang_var_1</Name>
            <Description>Eine Beispiel-Variable.</Description>
            <VariableLocalized iso="GER">PI ist %s und Parameter 2 lautet: %s.</VariableLocalized>
            <VariableLocalized iso="ENG">PI is %s and parameter 2 has the value: %s.</VariableLocalized>
        </Variable>
        <Variable>
            <Description>Eine weitere Beispiel-Variable.</Description>
            <Name>xmlp_lang_var_2</Name>
            <VariableLocalized iso="GER">Ich bin variabel!</VariableLocalized>
            <VariableLocalized iso="ENG">I'm variable!</VariableLocalized>
            <Type>textarea</Type>
        </Variable>
    </Locales>

+---------------------------+----------------------------------+
| Elementname               | Funktion                         |
+===========================+==================================+
| ``<Name>`` *              | Name der Sprachvariable          |
+---------------------------+----------------------------------+
| ``<Description>`` *       | Beschreibung der Sprachvariable  |
+---------------------------+----------------------------------+
| ``<VariableLocalized>`` * | Lokalisierter Name               |
+---------------------------+----------------------------------+
| ``<Type>``                | Typ des Eingabefeldes (ab 5.0.0) |
+---------------------------+----------------------------------+

(*) Pflichtfelder

.. hint::

    Diesbezügliche Änderungen an der ``info.xml`` sind erst nach einer Neuinstallation des Plugins sichtbar, da die
    Variablen **bei der Installation** in die Datenbank geschrieben werden.

Die Angabe des Typs ist seit Shop 5.0.0 möglich, aber optional. Standardmäßig wird er auf "text" gestellt, was einem
einfachen Texteingabefeld im Backend entspricht. Für längere Texte bietet sich der Typ "textarea" an.
Prinzipiell lassen sich hier alle in JTL\Plugin\Admin\InputType definierten Typen nutzen.

Sprachvariablen können auf ihren Ursprungswert zurückgesetzt werden. |br|
Bei einem Pluginupdate oder beim Deaktivieren eines Plugins bleiben die Sprachvariablen erhalten, die durch den
Betreiber des Onlineshops angepasst wurden. Erst bei einer Deinstallation des Plugins werden die Sprachvariablen
endgültig gelöscht.

Nutzung im Plugin
"""""""""""""""""

Es sei folgendes Beispiel-XML gegeben:

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
                <Description>Eine Beispiel-Variable.</Description>
            </Variable>
            <Variable>
                <Name>lang_var_two</Name>
                <Description>Eine Beispiel-Variable mit Platzhalter.</Description>
                <VariableLocalized iso="GER">Hallo, mein Name ist %s.</VariableLocalized>
                <VariableLocalized iso="ENG">Hello, my name is %s.</VariableLocalized>
            </Variable>
        </Locales>
        ...
    </Install>

Der Wert der Sprachvariablen kann via PHP auf folgende Weise ausgegeben werden:

JTL-Shop 4.x
""""""""""""

.. code-block:: php

    $test1 = $oPlugin->oPluginSprachvariableAssoc_arr['lang_var_one']; // hat Wert "Ich bin variabel!"
    $test2 = sprintf($oPlugin->oPluginSprachvariableAssoc_arr['lang_var_two'], "Peter"); // hat Wert "Hallo, mein Name ist Peter."

JTL-Shop 5.x
""""""""""""

.. code-block:: php

    // z.B. innerhalb der Bootstrap.php in der Boot-Methode:
    $plugin = $this->getPlugin();
    $test1  = $plugin->getLocalization()->getTranslation('lang_var_one');
    $test2  = \sprintf($plugin->getLocalization()->getTranslation('lang_var_two'), 'Peter');


Ab Shop 5.1.0 können Sprachvariablen direkt innerhalb von Templatedateien genutzt werden.
Nutzen Sie dafür die Syntax ``{lang key='variablen-name' section='meine-plugin-id'}`` - im Beispiel also

.. code-block:: php

    {lang key='lang_var_one' section='jtl_example_plugin'}
    {lang key='lang_var_two' section='jtl_example_plugin' printf='Peter'}


.. _label_infoxml_email:

E-Mail-Templates
----------------

Ein Plugin kann auch neue E-Mail-Typen definieren, die als E-Mail versendet werden können. Dabei kann der E-Mail-Inhalt
eines Templates für alle im Onlineshop verfügbaren Sprachen vorbelegt werden. Die vordefinierten Texte sind weiterhin
in der E-Mail-Vorlagenverwaltung im Backend durch den Betreiber des Onlineshops editierbar.

Der Hauptknoten ``<Emailtemplate>``, welcher im Container ``<Install>`` liegt, definiert eine neue E-Mailvorlage.

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

+------------------------+--------------------------------------------------------------------------------------------+
| Element                | Funktion                                                                                   |
+========================+============================================================================================+
| ``<Template>``         | Hauptcontainerelement (pro E-Mail-Vorlage muss es ein Element ``<Template>`` geben)        |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Name>``             | Name der E-Mail-Vorlage                                                                    |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Description>``      | Beschreibung der E-Mail-Vorlage                                                            |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Type>``             | Sendeformat der E-Mail-Vorlage (html/text oder text)                                       |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<ModulId>``          | Eindeutiger Schlüssel der E-Mail-Vorlage                                                   |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Active>``           | Aktivierungsflag der E-Mail-Vorlage (Y/N)                                                  |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<AKZ>``              | Anbieterkennzeichnung in der E-Mail-Vorlage anhängen (1/0)                                 |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<AGB>``              | Allgemeine Geschäftsbedingungen in der E-Mail-Vorlage anhängen (1/0)                       |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<WRB>``              | Widerrufsbelehrung in der E-Mail-Vorlage anhängen (1/0)                                    |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<TemplateLanguage>`` | Lokalisierte Inhalte pro Sprache (min. eine Sprache muss vorhanden sein) (Key = SprachISO) |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<Subject>``          | Betreff der E-Mail-Vorlage in der jeweiligen Sprache                                       |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<ContentHtml>``      | Inhalt als HTML                                                                            |
+------------------------+--------------------------------------------------------------------------------------------+
| ``<ContentText>``      | Inhalt als Text                                                                            |
+------------------------+--------------------------------------------------------------------------------------------+

(*) Pflichtfeld

Weitere Informationen zum Thema "E-Mail-Templates im Plugin" finden Sie im Kapitel ":doc:`mailing`".
