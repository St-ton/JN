Allgemein
=========

.. |br| raw:: html

    <br />

Das Pluginsystem von JTL-Shop ermöglicht es, verschiedene Arten von Zusatzfunktionalitäten hinzuzufügen,
ohne den Core-Code von JTL-Shop zu modifizieren. Dadurch bleibt JTL-Shop jederzeit updatefähig.

Plugins werden vom Shopbetreiber/Admin installiert. |br|
Die Installation besteht aus dem Hochladen des Plugins in das für Plugins vorgesehene Verzeichnis
``<Shop-Root>/includes/plugins/`` (bei Shop-Versionen 4+), bzw. das Verzeichnis ``<Shop-Root>/plugins/``
(bei Shop Versionen 5+) und anschließender Installation über die Pluginverwaltung im Adminbereich des Shops.
In der Pluginverwaltung können installierte Plugins außerdem *temporär deaktiviert* bzw. *permanent deinstalliert*
werden. Die Funktionen der Pluginverwaltung können im laufenden Shopbetrieb genutzt werden. |br|
Weiterhin können Plugins optional durch eine Lizenzprüfung geschützt werden.

Es gibt viele Arten von Plugins, die verschiedenste Aufgaben in JTL-Shop wahrnehmen können:

* Ausführen sichtbarer oder unsichtbarer Funktionen im Frontend von JTL-Shop ("*Frontend-Links*")
* Bereitstellung spezieller Funktionen im Backend on JTL-Shop, wie z. B. Auswertungen und
  Statistiken ("*Custom-Links*")
* Bereitstellung neuer Zahlungsmethoden als "Zahlungsarten-Plugin"
* Bereitstellung neuer Boxen für das Frontend von JTL-Shop ("Boxenverwaltung")
* Integration neuer E-Mail-Vorlagen in JTL-Shop
* und vieles mehr

Ein Plugin kann *eine* dieser Aufgaben oder *eine Kombination* davon erfüllen.

Das Pluginsystem arbeitet mit Hilfe von :doc:`Hooks </shop_plugins/hooks>`, die im Code von JTL-Shop an verschiedenen
Stellen hinterlegt sind. |br|
Ein Plugin kann einen oder mehrere Hooks nutzen, um eigenen Code an diesen Stellen auszuführen.

.. hint::

    Sind mehrere Plugins installiert, die dieselben Hooks nutzen, wird der Code *jedes* Plugins an dieser Stelle
    ausgeführt, und zwar in der zeitlichen Reihenfolge, wie die Plugins installiert wurden.

Plugins sind *versioniert*, wodurch sie updatefähig bleiben. |br|
Pluginupdates können neue Funktionalitäten und/oder Fehlerbehebungen enthalten.

Das Update eines Plugins wird vom Shopbetreiber selbst durchgeführt, die Prozedur ist analog zur Installation. |br|
Nach dem Upload einer neuen Pluginversion in das Pluginverzeichnis des Onlineshops, erkennt die Pluginverwaltung
automatisch, daß eine neue Version des Plugins vorhanden ist und bietet einen Update-Button an. |br|
Nach dem Klicken des Update-Buttons wird das Plugin automatisch  auf die neue Version aktualisiert. Das aktualisierte
Plugin ist nach dem Update direkt aktiviert.

Plugins benötigen ggf. eine Mindestversion des Onlineshops. |br|
Da das Onlineshopsystem bei einem Update des Onlineshops um neue Funktionen bereichert werden kann, können Plugins
z. B. diese neuen Funktionen erweitern oder darauf zugreifen. Dies würde in einer älteren Version des Onlineshops
nicht funktionieren und ggf. zu Fehlern führen.

Das Herzstück jedes Plugins ist die XML Datei ``info.xml``, die das Plugin beschreibt. |br|
Diese XML-Datei muss auch eine Mindest-XML-Strukturversion angeben, damit die vom Plugin beschriebene Funktionalität
auch tatsächlich von JTL-Shop interpretiert werden kann. Durch die Plugin-XML-Version bleibt somit das Pluginsystem
selbst erweiterbar. |br|
So wurde z. B. in JTL-Shop 3.04 diese XML-Struktur um selbst definierte E-Mail-Vorlagen erweitert, die ein Plugin über
die XML-Version automatisch erstellen und versenden kann.

Ein JTL-Shop kann mehrsprachig betrieben werden. |br|
Durch die im Pluginsystem integrierte Sprachvariablenverwaltung können Daten in beliebig vielen Sprachen
lokalisiert ausgeliefert werden. |br|
Die Pluginverwaltung ermöglicht dem Shopbetreiber zudem, alle Sprachvariablen für die eigenen Anforderungen anzupassen.
Sprachvariablen können weiterhin vom Shopbetreiber jederzeit auf den Installationszustand zurückgesetzt werden. |br|
Wenn ein Plugin mehr Sprachen mitliefert als im Onlineshopsystem vorhanden sind, werden nur die im System vorhandenen
Sprachen installiert. Liefert ein Plugin andererseits Sprachvariablen in weniger Sprachen aus als aktuell im Onlineshop
aktiviert sind, werden die Sprachvariablen der sonstigen Sprachen mit der Standardsprache ausgefüllt.

Pluginverwaltung im Backend von JTL-Shop
----------------------------------------

Die Pluginverwaltung ist der zentrale Ort im Backend von JTL-Shop, an dem Plugins installiert/deinstalliert,
aktiviert/deaktiviert, aktualisiert oder konfiguriert werden können.
Ab JTL Shop 5 trägt sie den Namen "Plugin-Manager".

Bei der *Deinstallation* eines Plugins werden Plugineinstellungen und eventuell zusätzlich durch das Plugin
geschriebene Tabellen gelöscht. Anders bei der *Deaktivierung* eines Plugins: Hier bleiben Plugineinstellungen
und -tabellen erhalten, das Plugin wird jedoch nicht weiter ausgeführt.

.. important::

    Deinstallierte Plugins verlieren nicht nur alle eigenen Einstellungen und alle Sprachvariablen, es werden auch
    alle Datenbanktabellen des Plugins gelöscht! |br|
    Deaktivierte Plugins werden vom Onlineshopsystem nicht geladen und verbrauchen keine Systemressourcen.

Plugin-Installation
"""""""""""""""""""

Die Installation von Plugins besteht aus zwei Schritten. Sie können diese im laufenden Betrieb des Onlineshops
vornehmen.

1. Laden Sie das Plugin hoch: |br|
   **ab Shop Version 4.x** in das Verzeichnis ``includes/plugins/``, |br|
   **ab Shop Version 5.x** in das Verzeichnis ``plugins/`` |br|
   (Der Upload erfolgt in "ausgepackter" Form. Dateiarchive, wie z. B. ``*.zip`` oder ``*.tgz``,
   werden *nicht unterstützt*.)
2. Starten Sie die Installation im Backend über den Menüpunkt "*Pluginverwaltung*" im Reiter "*Vorhanden*". |br|
   Die Installation läuft vollautomatisch ab.

Plugin-Konfiguration
""""""""""""""""""""

Jedes Plugin in JTL-Shop erhält nach der Installation einen eigenen Eintrag in der Pluginverwaltung. |br|
Der hier angezeigte Name entspricht dem Inhalt des Tags ``<Description>`` in der ``info.xml`` des jeweiligen Plugins
und stellt somit den textuellen Namen dieses Plugins dar.

Jedes Plugin kann beliebig viele *Custom Links* und *Setting Links* definieren. |br|
*Custom Links* sind Links, die eigenen Code ausführen und eigenen Inhalt produzieren.  |br|
*Setting Links* enthalten Einstellungen zum Plugin.

Plugins können eigene Einstellungen über einen *Custom Link* abfragen und abspeichern.
Schneller und sicherer können Einstellungen jedoch über *Setting Links* hinterlegt und
abgefragt werden. |br|
Insbesondere wird der Zugriff auf diese Einstellungen im eigenen Code des Plugins stark vereinfacht und das Look&Feel
von Einstellungen im Shop bleibt erhalten. Zudem wird viel Programmcode gespart, da benötigte Einstellungen
über *Setting Links* einfach in der XML-Datei des Plugins hinterlegt werden können.
Hierbei ist kein weiterer Code notwendig!
