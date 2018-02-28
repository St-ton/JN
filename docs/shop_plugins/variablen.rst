Variablen
=========

Pluginvariablen stehen dem Pluginentwickler im Front- und Backend des Shops sowie in jeder vom Plugin verwalteten Datei zur Verfügung.
Alle unten aufgelisteten Pluginvariablen sind Member des globalen Objekts *$oPlugin*.

Beispiel:

Ausgabe des Pluginnamens

.. code-block:: php

    echo $oPlugin->cName;

Zu den allgemeinen Informationen des Plugins bis über Sprachvariablen oder Einstellungen des Plugins sind alle Variablen erreichbar.


+---------------------------------+---------------------------------------------------------------------------------------------------------+
| Klassenvariable                 | Funktionalität                                                                                          |
+=================================+=========================================================================================================+
| kPlugin                         | Eindeutiger Plugin Key                                                                                  |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| nStatus                         | Pluginstatus: 1 = Deaktiviert, 2 = Aktiviert und Installiert, 3 = Fehlerhaft, 4 = Update fehlgeschlagen |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| nVersion                        | Pluginversion                                                                                           |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| icon                            | Dateiname des Icons                                                                                     |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| nXMLVersion                     | XML-Version                                                                                             |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| nPrio                           | Priorität bei Plugins mit gleichem Autor                                                                |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cName                           | Name des Plugins                                                                                        |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cBeschreibung                   | Pluginbeschreibung                                                                                      |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cAutor                          | Plugin Autor                                                                                            |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cURL                            | URL zum Pluginhersteller                                                                                |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cVerzeichnis                    | Pluginverzeichnis                                                                                       |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cPluginID                       | Einmalige Plugin ID                                                                                     |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cLizenz                         | Konfigurierter Lizenzschlüssel                                                                          |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cLizenzKlasse                   | Name der Lizenzklasse                                                                                   |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cLicencePfad                    | Physischer Pfad auf dem Server zum license-Ordner                                                       |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cLicencePfadURL                 | Vollständige URL zum license-Ordner                                                                     |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cLicencePfadURLSSL              | Vollständige URL via https zum license-Ordner                                                           |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cFrontendPfad                   | Physischer Pfad auf dem Server zum frontend-Ordner                                                      |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cFrontendPfadURL                | Vollständige URL zum frontend-Ordner                                                                    |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cFrontendPfadURLSSL             | Vollständige URL via https zum frontend-Ordner                                                          |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cAdminmenuPfad                  | Physischer Pfad auf dem Server zum adminmenu-Ordner                                                     |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| cAdminmenuPfadURLSSL            | Vollständige URL zum SSL-gesicherten adminmenu-Ordner                                                   |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| dZuletztAktualisiert            | Letztes Aktualisierungsdatum                                                                            |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| dInstalliert                    | Installationsdatum                                                                                      |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| dErstellt                       | Erstellungsdatum                                                                                        |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginHook_arr                 | Array mit Hooks                                                                                         |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginAdminMenu_arr            | Array mit Adminmenüs                                                                                    |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginEinstellung_arr          | Array mit gesetzten Einstellungen                                                                       |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginEinstellungConf_arr      | Array mit Einstellungen                                                                                 |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginEinstellungAssoc_arr     | Assoziatives Array mit gesetzten Einstellungen                                                          |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginSprachvariable_arr       | Assoziatives Array mit Sprachvariablen                                                                  |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginFrontendLink_arr         | Array mit Frontend Links                                                                                |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginZahlungsmethode_arr      | Array mit Zahlungsmethoden                                                                              |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| oPluginZahlungsmethodeAssoc_arr | Assoziatives Array mit Zahlungsmethoden                                                                 |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| dInstalliert_DE                 | Lokalisiertes Installationsdatum                                                                        |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| dZuletztAktualisiert_DE         | Lokalisiertes Aktualisierungsdatum                                                                      |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| dErstellt_DE                    | Lokalisiertes Hersteller Erstellungsdatum                                                               |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| nCalledHook                     | ID des aktuell ausgeführten Hooks                                                                       |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| pluginCacheID                   | individuelle Cache-ID zur Nutzung des Objekt-Caches                                                     |
+---------------------------------+---------------------------------------------------------------------------------------------------------+
| pluginCacheGroup                | individuelle Cache-Gruppe zur Nutzung des Objekt-Caches                                                 |
+---------------------------------+---------------------------------------------------------------------------------------------------------+


Arrays
------

**oPluginHook_arr**

Dieses Array beinhaltet alle durch das Plugin genutzen Hooks.

Array von Objekten

Member: kPluginHook, kPlugin, nHook, cDateiname

+-----------------+-----------------------------------------+
| Member          | Funktionalität                          |
+=================+=========================================+
| kPluginHook     | Eindeutiger Hook-Key                    |
+-----------------+-----------------------------------------+
| kPlugin         | eindeutiger Plugin-Key                  |
+-----------------+-----------------------------------------+
| nHook           | Hook-ID                                 |
+-----------------+-----------------------------------------+
| cDateiname      | Dateiname der bei nHook ausgeführt wird |
+-----------------+-----------------------------------------+

**oPluginAdminMenu_arr**

Array mit allen Backend Links.

Array von Objekten

Member: kPluginAdminMenu, kPlugin, cName, cDateiname, nSort, nConf

+------------------+-----------------------------------------------+
| Member           | Funktionalität                                |
+==================+===============================================+
| kPluginAdminMenu | Eindeutiger Plugin-Adminmenu-Key              |
+------------------+-----------------------------------------------+
| kPlugin          | Eindeutiger Plugin-Key                        |
+------------------+-----------------------------------------------+
| cName            | Name des Admin-Tabs                           |
+------------------+-----------------------------------------------+
| nSort            | Sortierungsnummer des Admin-Tabs              |
+------------------+-----------------------------------------------+
| nConf            | 0 = Custom Link auf cDateiname / 1 = Settings |
+------------------+-----------------------------------------------+


**oPluginEinstellung_arr**

Dieses Array beinhaltet alle gesetzten Einstellungen des Plugins.

Array von Objekten

Member: kPlugin, cName, cWert

+---------+-------------------------------------------+
| Member  | Funktionalität                            |
+=========+===========================================+
| kPlugin | Eindeutiger Plugin Key                    |
+---------+-------------------------------------------+
| cName   | Eindeutiger Einstellungsname der Variable |
+---------+-------------------------------------------+
| cWert   | Wert der Variable                         |
+---------+-------------------------------------------+

**oPluginEinstellungAssoc_arr**

Array mit Einstellungen.

Der Unterschied zum obigen Array besteht darin, dass es assoziativ mit dem Einstellungsnamen angesprochen werden kann.

Beispiel:

.. code-block:: php

    if ($oPlugin->oPluginEinstellungAssoc_arr['mein_cName'] === 'Y') {
        //...
    }

Assoziatives Array

Key: cName

Wert: cWert

+--------+-------------------+
| Member | Funktionalität    |
+========+===================+
| cWert  | Wert der Variable |
+--------+-------------------+


**oPluginEinstellungConf_arr**

Array mit Einstellungsoptionen.

Diese Optionen werden im Backend unter dem jeweiligen Settingslinks angezeigt und können dort als Einstellung gesetzt werden.

Array von Objekten

Member: kPluginEinstellungenConf, kPlugin, kPluginAdminMenu, cName, cBeschreibung, cWertName, cInputTyp, nSort, cConf, oPluginEinstellungenConfWerte_arr

+-----------------------------------+----------------------------------------------+
| Member                            | Funktionalität                               |
+===================================+==============================================+
| kPluginEinstellungenConf          | Eindeutiger PluginEinstellungs-Key           |
+-----------------------------------+----------------------------------------------+
| kPlugin                           | Eindeutiger Plugin-Key                       |
+-----------------------------------+----------------------------------------------+
| kPluginAdminMenu                  | Eindeutiger Plugin-Adminmenu-Key             |
+-----------------------------------+----------------------------------------------+
| cName                             | Name der Einstellung                         |
+-----------------------------------+----------------------------------------------+
| cBeschreibung                     | Beschreibung der Einstellung                 |
+-----------------------------------+----------------------------------------------+
| cWertName                         | Wert der Variable                            |
+-----------------------------------+----------------------------------------------+
| cInputTyp                         | Typ der Variable (text, zahl, selectbox,...) |
+-----------------------------------+----------------------------------------------+
| nSort                             | Sortierung der Einstellung                   |
+-----------------------------------+----------------------------------------------+
| cConf                             | Y = Einstellung / N = Überschrift            |
+-----------------------------------+----------------------------------------------+
| oPluginEinstellungenConfWerte_arr | Array von Optionswerten                      |
+-----------------------------------+----------------------------------------------+

**oPluginEinstellungenConfWerte_arr**

Array mit Einstellungsoptionswerten. Falls eine Einstellungsoption eine selectbox oder radio ist, beinhaltet dieses Array zu einer bestimmten Einstellungsoption, alle Optionswerte.

Array von Objekten

Member: kPluginEinstellungenConf, cName, cWert, nSort

+--------------------------+--------------------------------------------+
| Member                   | Funktionalität                             |
+==========================+============================================+
| kPluginEinstellungenConf | Eindeutiger Plugin-Einstellungs-Key        |
+--------------------------+--------------------------------------------+
| cName                    | Eindeutiger Einstellungsname der Variablen |
+--------------------------+--------------------------------------------+
| cWert                    | Wert der Option                            |
+--------------------------+--------------------------------------------+
| nSort                    | Sortierung der Option                      |
+--------------------------+--------------------------------------------+


**oPluginSprachvariable_arr**

Dieses Array beinhaltet alle Sprachvariablen des Plugins.

Array von Objekten

Member: kPluginSprachvariable, kPlugin, cName, cBeschreibung, oPluginSprachvariableSprache_arr

+----------------------------------+------------------------------------------------------------------+
| Member                           | Funktionalität                                                   |
+==================================+==================================================================+
| kPluginSprachvariable            | Eindeutiger Sprachvariablen-Key                                  |
+----------------------------------+------------------------------------------------------------------+
| kPlugin                          | Eindeutiger Plugin-Key                                           |
+----------------------------------+------------------------------------------------------------------+
| cName                            | Name der Sprachvariable                                          |
+----------------------------------+------------------------------------------------------------------+
| cBeschreibung                    | Beschreibung der Sprachvariable                                  |
+----------------------------------+------------------------------------------------------------------+
| oPluginSprachvariableSprache_arr | Array aller lokalisierten Sprachen dieser Sprachvariable         |
+----------------------------------+------------------------------------------------------------------+

**oPluginSprachvariableSprache_arr**

Diese Array beinhaltet alle Sprachvariablen des jeweiligen Plugins. Es muss assoziativ mit der entsprechenden SprachISO angesprochen werden.

Assoziatives Array

Key:ISO

Wert: Lokalisierte Sprachvariable


**oPluginFrontendLink_arr**

Array mit vorhanden Frontend Links.

Array von Objekten

Member: kLink, kLinkgruppe, kPlugin, cName, nLinkart, cURL, cKundengruppen, cSichtbarNachLogin, cDruckButton, nSort, oPluginFrontendLinkSprache_arr

+--------------------------------+------------------------------------------------------------------+
| Member                         | Funktionalität                                                   |
+================================+==================================================================+
| kLink                          | Eindeutiger Link-Key                                             |
+--------------------------------+------------------------------------------------------------------+
| kLinkgruppe                    | Eindeutiger Linkgruppen-Key                                      |
+--------------------------------+------------------------------------------------------------------+
| kPlugin                        | Eindeutiger Plugin-Key                                           |
+--------------------------------+------------------------------------------------------------------+
| cName                          | Name des Frontend-Links                                          |
+--------------------------------+------------------------------------------------------------------+
| nLinkart                       | Eindeutiger Linkart-Key                                          |
+--------------------------------+------------------------------------------------------------------+
| cURL                           | Pfad zur Datei die verlinkt werden soll                          |
+--------------------------------+------------------------------------------------------------------+
| cKundengruppen                 | String von Kundengruppen-Keys                                    |
+--------------------------------+------------------------------------------------------------------+
| cSichtbarNachLogin             | Ist der Link nur nach dem Einloggen sichtbar? Y = Ja / N = Nein  |
+--------------------------------+------------------------------------------------------------------+
| cDruckButton                   | Soll die Linkseite einen Druckbutton erhalten? Y = Ja / N = Nein |
+--------------------------------+------------------------------------------------------------------+
| nSort                          | Sortierungsnummer des Links                                      |
+--------------------------------+------------------------------------------------------------------+
| oPluginFrontendLinkSprache_arr | Array lokalisierten Linknamen                                    |
+--------------------------------+------------------------------------------------------------------+


**oPluginSprachvariableAssoc_arr**

Diese assoziative Array beinhaltet alle Sprachvariablen des Plugins. Sie werden direkt in der entsprechenden Shopsprache lokalisiert und können über cName angesprochen werden.

Assoziatives Array

Key: cName Wert: Objekt

Member: kPluginSprachvariable, kPlugin, cName, cBeschreibung, oPluginSprachvariableSprache_arr

+----------------------------------+-------------------------------------------------------------------+
| Member                           | Funktionalität                                                    |
+==================================+===================================================================+
| kPluginSprachvariable            | Eindeutiger Plugin Sprachvariablen Key                            |
+----------------------------------+-------------------------------------------------------------------+
| kPlugin                          | Eindeutiger Plugin Key                                            |
+----------------------------------+-------------------------------------------------------------------+
| cName                            | Name der Sprachvariable                                           |
+----------------------------------+-------------------------------------------------------------------+
| cBeschreibung                    | Beschreibung der Sprachvariable                                   |
+----------------------------------+-------------------------------------------------------------------+
| oPluginSprachvariableSprache_arr | Array aller Sprachen für die diese Sprachvariable lokalisiert ist |
+----------------------------------+-------------------------------------------------------------------+


**oPluginFrontendLinkSprache_arr**

Array mit lokalisierten Namen eines bestimmten Frontend Links.

Array von Objekten

Member: kLink, cSeo, cISOSprache, cName, cTitle, cContent, cMetaTitle, cMetaKeywords, cMetaDescription

+------------------+-----------------------------------------+
| Member           | Funktion                                |
+==================+=========================================+
| kLink            | Eindeutiger Link-Key                    |
+------------------+-----------------------------------------+
| cSeo             | SEO für die jeweilige Linksprache       |
+------------------+-----------------------------------------+
| cISOSprache      | ISO der Linksprache                     |
+------------------+-----------------------------------------+
| cName            | Lokalisierter Name des Links            |
+------------------+-----------------------------------------+
| cTitle           | Lokalisierter Titel des Links           |
+------------------+-----------------------------------------+
| cContent         | Lokalisierter Content des Links         |
+------------------+-----------------------------------------+
| cMetaTitle       | Lokalisierter MetaTitel des Links       |
+------------------+-----------------------------------------+
| cMetaKeywords    | Lokalisierte MetaKeywords des Links     |
+------------------+-----------------------------------------+
| cMetaDescription | Lokalisierte MetaDescription des Links  |
+------------------+-----------------------------------------+

**oPluginZahlungsmethode_arr**

Dieses Array beinhaltet alle verfügbaren Zahlungsmethoden.

Array von Objekten

Member: kZahlungsart, cName, cModulId, cKundengruppen, cZusatzschrittTemplate, cPluginTemplate, cBild, nSort, nMailSenden, nActive, cAnbieter, cTSCode, nWaehrendBestellung, nCURL, nSOAP, nSOCKETS, nNutzbar, cTemplateFileURL, oZahlungsmethodeSprache_arr, oZahlungsmethodeEinstellung_arr

+---------------------------------+----------------------------------------------------------------------------------------+
| Member                          | Funktionalität                                                                         |
+=================================+========================================================================================+
| kZahlungsart                    | Eindeutiger Zahlungsart Key                                                            |
+---------------------------------+----------------------------------------------------------------------------------------+
| cName                           | Name der Zahlungsart                                                                   |
+---------------------------------+----------------------------------------------------------------------------------------+
| cModulId                        | Eindeutige Modul-ID der Zahlungart                                                     |
+---------------------------------+----------------------------------------------------------------------------------------+
| cKundengruppen                  | String von Kundengruppen für die diese Zahlungsart gilt                                |
+---------------------------------+----------------------------------------------------------------------------------------+
| cZusatzschrittTemplate          | Zusätzliche Daten für Transaktionen können eingegeben werden                           |
+---------------------------------+----------------------------------------------------------------------------------------+
| cPluginTemplate                 | Pfad zum Template der Zahlungsart                                                      |
+---------------------------------+----------------------------------------------------------------------------------------+
| cBild                           | Bildpfad der Zahlungsart                                                               |
+---------------------------------+----------------------------------------------------------------------------------------+
| nSort                           | Sortierungsnummer der Zahlungsart                                                      |
+---------------------------------+----------------------------------------------------------------------------------------+
| nMailSenden                     | Versendet diese Zahlungsart standardmäßig eine Email beim Abschluss? 1 = Ja / 0 = Nein |
+---------------------------------+----------------------------------------------------------------------------------------+
| nActive                         | Ist diese Zahlungsart aktiv? 1 = Ja / 0 = Nein                                         |
+---------------------------------+----------------------------------------------------------------------------------------+
| cAnbieter                       | Name des Anbieters der Zahlungsart                                                     |
+---------------------------------+----------------------------------------------------------------------------------------+
| cTSCode                         | Trusted Shops Code                                                                     |
+---------------------------------+----------------------------------------------------------------------------------------+
| nWaehrendBestellung             | Pre oder Post Bestellung                                                               |
+---------------------------------+----------------------------------------------------------------------------------------+
| nCURL                           | Nutzt diese Zahlungart das CURL Protokoll?                                             |
+---------------------------------+----------------------------------------------------------------------------------------+
| nSOAP                           | Nutzt diese Zahlungart das SOAP Protokoll?                                             |
+---------------------------------+----------------------------------------------------------------------------------------+
| nSOCKETS                        | Nutzt diese Zahlungart Sockets?                                                        |
+---------------------------------+----------------------------------------------------------------------------------------+
| nNutzbar                        | Sind alle Serverprotokolle die nötig für diese Zahlungsart sind, nutzbar?              |
+---------------------------------+----------------------------------------------------------------------------------------+
| cTemplateFileURL                | Absoluter Pfad zur Template Datei                                                      |
+---------------------------------+----------------------------------------------------------------------------------------+
| oZahlungsmethodeSprache_arr     | Lokalisierte Zahlungsart für alle angegebenen Sprachen                                 |
+---------------------------------+----------------------------------------------------------------------------------------+
| oZahlungsmethodeEinstellung_arr | Array von lokalisierten Einstellungen                                                  |
+---------------------------------+----------------------------------------------------------------------------------------+


**oZahlungsmethodeSprache_arr**

Array mit lokalisierten Namen der jeweiligen Zahlungsmethode.

Array von Objekten

Member: kZahlungsart, cISOSprache, cName, cGebuehrname, cHinweisText

+--------------+-----------------------------+
| Member       | Funktionalität              |
+==============+=============================+
| kZahlungsart | Eindeutiger Zahlungsart-Key |
+--------------+-----------------------------+
| cISOSprache  | SprachISO                   |
+--------------+-----------------------------+
| cName        | Lokalisierter Name          |
+--------------+-----------------------------+
| cGebuehrname | Lokalisierter Gebührenname  |
+--------------+-----------------------------+
| cHinweisText | Lokalisierter Hinweistext   |
+--------------+-----------------------------+

**oZahlungsmethodeEinstellung_arr**

Array mit Einstellungen zu einer bestimmten Zahlungsmethode.

Array von Objekten

Member: kPluginEinstellungenConf, kPlugin, kPluginAdminMenu, cName, cBeschreibung, cWertName, cInputTyp, nSort, cConf

+--------------------------+----------------------------------------------+
| Member                   | Funktion                                     |
+==========================+==============================================+
| kPluginEinstellungenConf | Eindeutiger PluginEinstellungs-Key           |
+--------------------------+----------------------------------------------+
| kPlugin                  | Eindeutiger Plugin-Key                       |
+--------------------------+----------------------------------------------+
| kPluginAdminMenu         | Eindeutiger Plugin Adminmenu-Key             |
+--------------------------+----------------------------------------------+
| cName                    | Name der Einstellung                         |
+--------------------------+----------------------------------------------+
| cBeschreibung            | Beschreibung der Einstellung                 |
+--------------------------+----------------------------------------------+
| cWertName                | Wert der Variable                            |
+--------------------------+----------------------------------------------+
| cInputTyp                | Typ der Variable (text, zahl, selectbox,...) |
+--------------------------+----------------------------------------------+
| nSort                    | Sortierung der Einstellung                   |
+--------------------------+----------------------------------------------+
| cConf                    | Y = Einstellung / N = Überschrift            |
+--------------------------+----------------------------------------------+