Allgemein
=========

.. |br| raw:: html

    <br />

Das Pluginsystem im JTL-Shop ermöglicht es, verschiedene Arten von Zusatzfunktionalitäten hinzuzufügen,
ohne den Shop-Core-Code zu modifizieren. Dadurch bleibt der Shop jederzeit updatefähig.

Plugins werden vom Shopbetreiber/Admin installiert. |br|
Die Installation besteht aus dem Hochladen des Plugins in das für Plugins vorgesehene Verzeichnis
``<Shop-Root>/includes/plugins/`` (bei Shop Versionen 4+), bzw. das Verzeichnis ``<Shop-Root>/plugins/``
(bei Shop Versionen 5+) und anschließender Installation über die Pluginverwaltung im Adminbereich des Shops.
In der Pluginverwaltung können installierte Plugins außerdem *temporär deaktiviert* bzw. *permanent deinstalliert* werden.
Die Funktionen der Pluginverwaltung können im laufenden Shopbetrieb genutzt werden. |br|
Weiterhin können Plugins optional durch eine Lizenzprüfung geschützt werden.

Es gibt viele Arten von Plugins, die verschiedenste Aufgaben im JTL-Shop wahrnehmen können:

* Plugins, die im Shopfrontend sichtbare oder unsichtbare Funktionen ausführen ("*Frontend-Links*")
* Plugins, die nur im Shop-Backend spezielle Funktionen zur Verfügung stellen, wie z.B. Auswertungen und
  Statistiken ("*Custom-Links*")
* neue Zahlungsmethoden bereitstellen als "Zahlungsarten-Plugin"
* neue Boxen für das Frontend bereitstellen ("Boxenverwaltung")
* Plugins, die neue E-Mail-Vorlagen in den Shop integrieren
* und vieles mehr

Ein Plugin kann *eine* dieser Aufgaben oder *eine Kombination* davon erfüllen.

Das Pluginsystem arbeitet mit Hilfe von :doc:`Hooks </shop_plugins/hooks>`, die im Shop-Code an verschiedenen Stellen
hinterlegt sind. |br|
Ein Plugin kann einen oder mehrere Hooks nutzen, um eigenen Code an diesen Stellen auszuführen.

.. hint::

    Sind mehrere Plugins installiert, die dieselben Hooks nutzen, so wird der Code *jedes* Plugins an dieser Stelle
    ausgeführt, in der zeitlichen Reihenfolge, wie die Plugins installiert wurden.

Plugins sind *versioniert*, wodurch sie updatefähig bleiben. |br|
Plugin-updates können das Plugin um neue Funktionalität und/oder Fehlerbehebungen bereichern.

Die Pluginverwaltung erkennt automatisch, nach dem Upload der neuen Plugindaten, im Pluginverzeichnis des Shops,
dass eine neue Version des Plugins vorhanden ist und bietet einen Update-Button an. |br|
Ein Update eines Plugins wird vom Shopbetreiber selbst durchgeführt, die Prozedur ist analog zur Installation. |br|
Nach dem Klicken des Update-Buttons wird das Plugin automatisch  auf die neue Version aktualisiert. Das aktualisierte
Plugin ist nach dem Update direkt aktiviert.

Plugins können eine Mindestversion des Shops voraussetzen. |br|
Da das Shopsystem bei einem Shop-Update um neue Funktionen bereichert werden kann, können Plugins z.B. diese neuen
Funktionen erweitern oder darauf zugreifen. Dies würde in einer älteren Shopversion nicht funktionieren und ggf. zu
Fehlern führen.

Das Herzstück jedes Plugins ist die XML Datei ``info.xml``, die das Plugin beschreibt. |br|
Diese XML-Datei muss auch eine Mindest-XML-Strukturversion angeben, damit die vom Plugin beschriebene Funktionalität
auch tatsächlich vom Shop interpretiert werden kann. Durch die Plugin-XML-Version bleibt somit das Pluginsystem selbst
erweiterbar. |br|
So wurde z.B. in JTL-Shop 3.04 diese XML-Struktur um selbst definierte Emailvorlagen erweitert, die ein Plugin über die
XML-Version automatisch erstellen und versenden kann.

Ein JTL-Shop kann mehrsprachig betrieben werden. |br|
Eine im Pluginsystem integrierte Sprachvariablenverwaltung ermöglicht es Plugins, Daten in beliebig vielen Sprachen
lokalisiert auszuliefern. |br|
Die Pluginverwaltung ermöglicht dem Shopbetreiber zudem, alle Sprachvariablen für die eigenen Anforderungen anzupassen.
Sprachvariablen können weiterhin vom Shopbetreiber auch jederzeit in den Installationszustand zurückgesetzt werden. |br|
Sobald ein Plugin mehr Sprachen mitliefert als im Shopsystem vorhanden sind, werden auch nur diese vom Shopsystem
installiert.  Liefert ein Plugin andererseits Sprachvariablen in weniger Sprachen aus, als der Shop aktuell aktiviert
hat, so werden die Sprachvariablen der sonstigen Sprachen mit der Standardsprache ausgefüllt.

Pluginverwaltung im Shop-Backend
--------------------------------

Die Pluginverwaltung ist der zentrale Punkt im Shop-Backend, wo Plugins installiert/deinstalliert,
aktiviert/deaktiviert, aktualisiert oder konfiguriert werden können.

Bei einer Plugin-Deinstallation werden Plugin-Einstellungen und eventuell zusätzlich durch das Plugin geschriebene
Tabellen gelöscht. Anders bei der Plugin-Deaktivierung: Hier bleiben Plugin-Einstellungen und -Tabellen erhalten,
das Plugin wird jedoch nicht weiter ausgeführt.

.. important::

    Deinstallierte Plugins verlieren nicht nur alle eigenen Einstellungen und alle Sprachvariablen, es werden auch
    alle Datenbanktabellen des Plugins gelöscht! |br|
    Deaktivierte Plugins werden vom Shopsystem nicht geladen und verbrauchen keine Systemressourcen.

Plugin-Installation
"""""""""""""""""""

Die Installation von Plugins besteht aus zwei Schritten und kann im laufenden Betrieb des Shops vorgenommen werden:

1. Upload des Plugins |br|
   **ab Shop Version 4.x** in das Verzeichnis ``includes/plugins/``, |br|
   **ab Shop Version 5.x** in das Verzeichnis ``plugins/`` |br|
   (Der Upload erfolgt in "ausgepackter" Form. Dateiarchive, wie z.B. ``*.zip`` oder ``*.tgz``,
   werden *nicht unterstützt*.)
2. Auslösen der Installation im Backend, über den Menüpunkt "*Pluginverwaltung*", im Reiter "*Vorhanden*". |br|
   Die Installation läuft vollautomatisch ab.

Plugin-Konfiguration
""""""""""""""""""""

Jedes Plugin, in JTL-Shop, erhält nach der Installation einen eigenen Eintrag in der Pluginverwaltung. |br|
Den Inhalt hinter diesem Eintrag kann das Plugin selbst bestimmen.

Jedes Plugin kann beliebig viele *Custom Links* (Links, die eigenen Code ausführen und eigenen Inhalt produzieren) und
*Setting Links* (Links, die Einstellungen zum Plugin enthalten) definieren.

Eigene Einstellungen kann ein Plugin zwar auch selbst über einen *Custom Link* abfragen und abspeichern,
jedoch bieten *Setting Links* eine Methode die sehr viel schneller und sicherer ist, um Einstellungen zu hinterlegen und
abzufragen. |br|
Insbesondere wird der Zugriff auf diese Einstellungen im eigenen Plugin-Code stark vereinfacht, das Look&Feel
von Einstellungen im Shop bleibt erhalten und man spart viel Programmcode, da benötigte Einstellungen
über *Setting Links* einfach in der XML Datei des Plugins hinterlegt werden können - kein weiterer Code ist
hierbei notwendig!
