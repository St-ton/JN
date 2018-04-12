Allgemein
=========

Das Pluginsystem im JTL-Shop ermöglicht es, alle Arten von Zusatzfunktionalitäten im Shop hinzuzufügen ohne den Shopcode zu modifizieren. Dadurch, dass der Originalcode des Shops nicht verändert wird, bleibt er zu jeder Zeit updatefähig.
Plugins werden im Shop vom Shopbetreiber installiert. Eine Installation besteht aus dem Hochladen des Plugins in das für Plugins vorgesehene Verzeichnis ``<Shop-Root>/includes/plugins/`` und anschliessender Installation über die Pluginverwaltung im Adminbereich des Shops.
In der Pluginverwaltung können installierte Plugins auch temporär deaktiviert bzw. permanent deinstalliert werden. Die Funktionen der Pluginverwaltung können im laufenden Shopbetrieb genutzt werden. Weiter können Plugins optional durch eine Lizenz geschützt und erst mit gültigem Lizenzkey aktiviert werden.

Es gibt viele Aufgaben von Plugins im JTL-Shop:

* Plugins, die im Shopfrontend sichtbare oder unsichtbare Funktionen ausführen (Frontend-Links)
* Plugins, die nur im Shopadmin (Shopbackend) spezielle Funktionen zur Verfügung stellen wie z.B. Auswertungen, Statistiken (Custom-Links)
* neue Zahlungsmethoden Zahlungsarten als Plugin
* neue Boxen für das Frontend bereitstellen (Boxenverwaltung)
* Plugins, die neue Emailvorlagen in den Shop integrieren

Ein Plugin kann eine dieser Aufgaben oder eine Kombination davon erfüllen.
Das Pluginsystem arbeitet mit Hilfe von :doc:`Hooks </shop_plugins/hooks>`, die im Shopcode an verschiedenen Stellen hinterlegt sind. Ein Plugin kann einen oder mehrere Hooks nutzen, um eigenen Code dort auszuführen.
Sind mehrere Plugins installiert, die dieselben Hooks nutzen, so wird der Code jedes Plugins an dieser Stelle ausgeführt in der zeitlichen Reihenfolge wie die Plugins installiert wurden.

Plugins sind versioniert, wodurch sie updatefähig bleiben.
Pluginupdates können das Plugin um neue Funktionalität und/oder Fehlerbehebungen bereichern. Ein Update eines Plugins wird vom Shopbetreiber selbst durchgeführt, die Prozedur ist analog zur Installation.
Die Pluginverwaltung erkennt automatisch nach dem Upload der neuen Plugindaten im Pluginverzeichnis des Shops, dass eine neue Version des Plugins vorhanden ist und bietet einen Updatebutton an.
Nach dem Klicken des Updatebuttons wird das Plugin auf die neue Version automatisch aktualisiert. Das aktualisierte Plugin ist nach dem Update direkt aktiviert.

Plugins können eine Mindest-Version des Shops voraussetzen.
Da das Shopsystem bei einem Update um neue Funktionen bereichert werden kann, können Plugins z.B. diese neuen Funktionen erweitern oder darauf zugreifen – dies würde in einer älteren Shopversion nicht funktionieren und ggf. zu Fehlern führen.

Das Herzstück jedes Plugins ist eine XML Datei, die das Plugin beschreibt.
Diese XML Datei muss auch eine Mindest XML Strukturversion angeben, damit die vom Plugin beschriebene Funktionalität auch tatsächlich vom Shop interpretiert werden kann.
Durch die Plugin XML Version bleibt somit das Pluginsystem selbst erweiterbar. So wurde z.B. in JTL-Shop 3.04 diese XML-Struktur um selbstdefinierte Emailvorlagen erweitert, die ein Plugin über die XML Version automatisch erstellen und versenden kann.

Ein JTL-Shop kann mehrsprachig betrieben werden.
Eine im Pluginsystem integrierte Sprachvariablenverwaltung ermöglicht es Plugins, Daten in beliebig vielen Sprachen lokalisiert auszuliefern.
Die Pluginverwaltung ermöglicht dem Shopbetreiber zudem, alle Sprachvariablen für die eigenen Anforderungen anzupassen.
Sprachvariablen können weiterhin vom Shopbetreiber auch jederzeit in den Installationszustand zurückgesetzt werden.
Sobald ein Plugin mehr Sprachen mitliefert als im Shopsystem vorhanden sind, werden auch nur diese vom Shopsystem installiert.
Liefert ein Plugin andererseits Sprachvariablen in weniger Sprachen aus, als der Shop aktuell aktiviert hat, so werden die Sprachvariablen der sonstigen Sprachen mit der Standardsprache ausgefüllt.

Pluginverwaltung im Admin-Backend
---------------------------------

Die Pluginverwaltung ist der zentrale Punkt im Shop Backend, wo Plugins installiert, deaktiviert, deinstalliert, aktualisiert oder bearbeitet werden können.
Damit Plugins nicht Ihre vom Shopbetreiber konfigurierten Einstellungen verlieren, sollte man Plugins bei Nichtgebrauch lieber deaktivieren anstat sie komplett zu deinstallieren.
Deinstallierte Plugins verlieren nicht nur alle eigenen Einstellungen, alle Sprachvariablen und sogar Datenbanktabellen des Plugins werden dabei gelöscht!
Deaktivierte Plugins werden vom Shopsystem garnicht geladen und verbrauchen daher keine Serverressourcen.


Admin-Links
-----------

Jedes Plugin in JTL-Shop erhält nach der Installation einen eigenen Menüpunkt unter Plugins -> Pluginname.
Den Inhalt hinter diesem Link kann das Plugin selbst bestimmen.
Jedes Plugin kann beliebig viele Customlinks (Links, die eigenen Code ausführen und eigenen Inhalt produzieren) und Settinglinks (Links, die Einstellungen zum Plugin enthalten) definieren.
Eigene Einstellungen kann ein Plugin zwar auch selbst über einen Customlink abfragen und abspeichern, jedoch bieten Settinglinks eine sehr einfache, schnelle und sichere Methode, Einstellungen zu hinterlegen und abzufragen.
Insbesondere wird der Zugriff auf diese Einstellungen im eigenen Plugin Code stark vereinfacht, das Look&Feel von Einstellungen im Shop bleibt erhalten und man spart enorm viel Code, da benötigte Einstellungen über Settinglinks einfach in der XML Datei des Plugins hinterlegt werden - kein weiterer Code ist dabei notwendig!

Plugin-Entwickler haben über die XML-Tags ``CSS`` und ``JS`` die Möglichkeit, schon bei der Installation eigene Ressourcen in den DOM einfügen zu lassen,
um so das spätere Hinzufügen via ``pq()`` überflüssig zu machen.

.. code-block:: xml

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

Alle hier angebenen Dateien müssen im Unterordner *frontend/css/* bzw. *frontend/js/* liegen. JavaScript-Dateien lassen sich dabei über das Attribut
Position wahlweise in den Header oder Body einfügen und können über priority (0 = höchste, 5 = Standard) in der Reihenfolge modifiziert werden.

Diese Dateien werden bei entsprechend aktivierter Theme-Funktion auch minifiziert.

Im Theme müssen dazu die Smarty-Variablen ``$cPluginJsHeadd_arr``, ``$cPluginCss_arr`` und ``$cPluginJsBody_arr`` geprüft bzw. ausgegeben werden.

.. code-block:: html+smarty

    {*bei aktiviertem minify, header.tpl*}
    {if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0}
        <link type="text/css" href="{$PFAD_MINIFY}/g=plugin_css" rel="stylesheet" media="screen" />
    {/if}
    {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
        <script type="text/javascript" src="{$PFAD_MINIFY}/g=plugin_js_head"></script>
    {/if}
    {*footer.tpl:*}
    {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
        <script type="text/javascript" src="{$PFAD_MINIFY}/g=plugin_js_body"></script>
    {/if}
    {*ohne minify, header.tpl*}
    {foreach from=$cJS_arr item="cJS"}
        <script type="text/javascript" src="{$cJS}"></script>
    {/foreach}
    {if isset($cPluginJsHead_arr)}
        {foreach from=$cPluginJsHead_arr item="cJS"}
            <script type="text/javascript" src="{$cJS}"></script>
        {/foreach}
    {/if}

    {*footer.tpl:*}
    {if isset($cPluginJsHead_arr)}
        {foreach from=$cPluginJsBody_arr item="cJS"}
            <script type="text/javascript" src="{$cJS}"></script>
        {/foreach}
    {/if}

Custom-Dateien
--------------

Falls zu einer über diese Methode eingebundenen CSS-Datei ein _custom-Pendant im selben Ordner existiert, wird diese **zusätzlich** nach ihr eingebunden.
Dem Beispiel oben folgend wäre dies ``foo_custom.css`` bzw. ``bar_custom.css``. Für JavaScript-Dateien wird dieses Vorgehen nicht untertützt.
