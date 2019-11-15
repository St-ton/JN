Variablen
=========

.. |br| raw:: html

   <br />

*Pluginvariablen* stehen dem Pluginentwickler im Front- und Backend des Shops, sowie in jeder vom Plugin verwalteten
Datei, zur Verfügung. |br|
Alle unten aufgelisteten *Pluginvariablen* sind in JTL-Shop 3 und 4 *Member* des globalen Objekts ``$oPlugin``.

**Beispiel:**

Ausgabe des Pluginnamens

.. code-block:: php

    echo $oPlugin->cName;

.. attention::

    *Ab Shop 5.0.0* werden diese Variablen nur noch aus Kompatibilitätsgründen bereitgestellt und ein Zugriff auf
    sie erzeugt eine PHP-Meldung vom Typ ``E_USER_DEPRECATED``.

Nutzen Sie **ab Shop Version 5.0** daher das Interface ``JTL\Plugin\PluginInterface``. |br|
Die entsprechenden *Getter* sind in der Spalte *Methode* dokumentiert.

Von den allgemeinen Informationen des Plugins, über Sprachvariablen bis hin zu den Einstellungen des Plugins, sind alle
Variablen erreichbar.

Klassenvariablen
----------------

+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| Klassenvariable                      | Methode                                         | Funktionalität                                                                              |
+======================================+=================================================+=============================================================================================+
| ``$kPlugin``                         | ``getID(): int``                                | Eindeutiger Plugin Key                                                                      |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nStatus``                         | ``getState(): int``                             | Pluginstatus                                                                                |
|                                      |                                                 | (1 = Deaktiviert, 2 = Aktiviert und Installiert, 3 = Fehlerhaft, 4 = Update fehlgeschlagen) |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nVersion``                        | ``getMeta()->getVersion(): Version``            | Pluginversion                                                                               |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$icon``                            | ``getMeta()->getIcon(): string``                | Dateiname des Icons                                                                         |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nXMLVersion``                     | ---                                             | XML-Version                                                                                 |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nPrio``                           | ``getPriority(): int``                          | Priorität bei Plugins mit gleichem Autor                                                    |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cName``                           | ``getName(): string``                           | Name des Plugins                                                                            |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cBeschreibung``                   | ``getMeta()->getDescription(): string``         | Pluginbeschreibung                                                                          |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cAutor``                          | ``getMeta()->getAuthor(): string``              | Plugin-Autor                                                                                |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cURL``                            | ``getMeta()->getURL(): string``                 | URL zum Pluginhersteller                                                                    |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cVerzeichnis``                    | ``getPaths()->getBaseDir(): string``            | Pluginverzeichnis                                                                           |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cPluginID``                       | ``getPluginID(): string``                       | Einmalige Plugin ID                                                                         |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLizenz``                         | ``getLicense()->getKey(): string``              | Konfigurierter Lizenzschlüssel                                                              |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLizenzKlasse``                   | ``getLicense()->getClassName(): string``        | Name der Lizenzklasse                                                                       |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLicencePfad``                    | ``getLicense()->getClass(): string``            | Physischer Pfad auf dem Server zum license-Ordner                                           |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLicencePfadURL``                 | ---                                             | Vollständige URL zum license-Ordner                                                         |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLicencePfadURLSSL``              | ---                                             | Vollständige URL via https zum license-Ordner                                               |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cFrontendPfad``                   | ``getPaths()->getFrontendPath(): string``       | Physischer Pfad auf dem Server zum frontend-Ordner                                          |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cFrontendPfadURL``                | ``getPaths()->getFrontendURL(): string``        | Vollständige URL zum frontend-Ordner                                                        |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cFrontendPfadURLSSL``             | ``getPaths()->getFrontendURL(): string``        | Vollständige URL via https zum frontend-Ordner                                              |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cAdminmenuPfad``                  | ``getPaths()->getAdminPath(): string``          | Physischer Pfad auf dem Server zum adminmenu-Ordner                                         |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cAdminmenuPfadURLSSL``            | ``getPaths()->getAdminURL(): string``           | Vollständige URL zum SSL-gesicherten adminmenu-Ordner                                       |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dZuletztAktualisiert``            | ``getMeta()->getDateLastUpdate(): DateTime``    | Letztes Aktualisierungsdatum                                                                |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dInstalliert``                    | ``getMeta()->getDateInstalled(): DateTime``     | Installationsdatum                                                                          |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dErstellt``                       | ``getMeta()->getDateInstalled(): DateTime``     | Erstellungsdatum                                                                            |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginHook_arr``                 | ``getHooks(): array``                           | Array mit Hooks                                                                             |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginAdminMenu_arr``            | ``getAdminMenu()->getItems: array``             | Array mit Adminmenüs                                                                        |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginEinstellung_arr``          | ``getConfig()->getOptions(): Collection``       | Array mit gesetzten Einstellungen                                                           |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginEinstellungConf_arr``      | ``getConfig()->getOptions(): Collection``       | Array mit Einstellungen                                                                     |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginEinstellungAssoc_arr``     | ``getConfig()->getOptions(): Collection``       | Assoziatives Array mit gesetzten Einstellungen                                              |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginSprachvariable_arr``       | ``getLocalization()->getTranslations(): array`` | Assoziatives Array mit Sprachvariablen                                                      |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginFrontendLink_arr``         | ``getLinks()->getLinks(): Collection``          | Array mit Frontend Links                                                                    |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginZahlungsmethode_arr``      | ``getPaymentMethods()->getMethods(): array``    | Array mit Zahlungsmethoden                                                                  |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginZahlungsmethodeAssoc_arr`` | ``getPaymentMethods()->getMethods(): array``    | Assoziatives Array mit Zahlungsmethoden                                                     |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dInstalliert_DE``                 | ---                                             | Lokalisiertes Installationsdatum                                                            |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dZuletztAktualisiert_DE``         | ---                                             | Lokalisiertes Aktualisierungsdatum                                                          |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dErstellt_DE``                    | ---                                             | Lokalisiertes Hersteller Erstellungsdatum                                                   |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nCalledHook``                     | ---                                             | ID des aktuell ausgeführten Hooks                                                           |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$pluginCacheID``                   | ``getCache()->getID(): string``                 | individuelle Cache-ID zur Nutzung des Objekt-Caches                                         |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$pluginCacheGroup``                | ``getCache()->getGroup(): string``              | individuelle Cache-Gruppe zur Nutzung des Objekt-Caches                                     |
+--------------------------------------+-------------------------------------------------+---------------------------------------------------------------------------------------------+


Arrays
------

oPluginHook_arr
"""""""""""""""

Dieses Array beinhaltet alle, durch das Plugin genutzten, Hooks

Array von Objekten

Member: ``kPluginHook``, ``kPlugin``, ``nHook``, ``cDateiname``

+-----------------+-----------------------------------------+
| Member          | Funktionalität                          |
+=================+=========================================+
| ``kPluginHook`` | Eindeutiger Hook-Key                    |
+-----------------+-----------------------------------------+
| ``kPlugin``     | eindeutiger Plugin-Key                  |
+-----------------+-----------------------------------------+
| ``nHook``       | Hook-ID                                 |
+-----------------+-----------------------------------------+
| ``cDateiname``  | Dateiname der bei nHook ausgeführt wird |
+-----------------+-----------------------------------------+

oPluginAdminMenu_arr
""""""""""""""""""""

Array mit allen Backend Links

Array von Objekten

Member: ``kPluginAdminMenu``, ``kPlugin``, ``cName``, ``cDateiname``, ``nSort``, ``nConf``

+----------------------+-----------------------------------------------+
| Member               | Funktionalität                                |
+======================+===============================================+
| ``kPluginAdminMenu`` | Eindeutiger Plugin-Adminmenu-Key              |
+----------------------+-----------------------------------------------+
| ``kPlugin``          | Eindeutiger Plugin-Key                        |
+----------------------+-----------------------------------------------+
| ``cName``            | Name des Admin-Tabs                           |
+----------------------+-----------------------------------------------+
| ``nSort``            | Sortierungsnummer des Admin-Tabs              |
+----------------------+-----------------------------------------------+
| ``nConf``            | 0 = Custom Link auf cDateiname / 1 = Settings |
+----------------------+-----------------------------------------------+


oPluginEinstellung_arr
""""""""""""""""""""""

Array mit alle gesetzten Einstellungen des Plugins

Array von Objekten

Member: ``kPlugin``, ``cName``, ``cWert``

+-------------+-------------------------------------------+
| Member      | Funktionalität                            |
+=============+===========================================+
| ``kPlugin`` | Eindeutiger Plugin Key                    |
+-------------+-------------------------------------------+
| ``cName``   | Eindeutiger Einstellungsname der Variable |
+-------------+-------------------------------------------+
| ``cWert``   | Wert der Variable                         |
+-------------+-------------------------------------------+

oPluginEinstellungAssoc_arr
"""""""""""""""""""""""""""

Assoziatives Array mit Einstellungen

Der Unterschied zum obigen Array besteht darin, dass es assoziativ mit dem Einstellungsnamen angesprochen werden kann.

Beispiel:

.. code-block:: php

    if ($oPlugin->oPluginEinstellungAssoc_arr['mein_cName'] === 'Y') {
        //...
    }

Assoziatives Array

Key: ``cName`` |br|
Wert: ``cWert``

+-----------+-------------------+
| Member    | Funktionalität    |
+===========+===================+
| ``cWert`` | Wert der Variable |
+-----------+-------------------+


oPluginEinstellungConf_arr
""""""""""""""""""""""""""

Array mit Einstellungsoptionen

Diese Optionen werden im Backend unter dem jeweiligen Settingslinks angezeigt und können dort als Einstellung
gesetzt werden.

Array von Objekten

Member: ``kPluginEinstellungenConf``, ``kPlugin``, ``kPluginAdminMenu``, ``cName``, ``cBeschreibung``,
``cWertName``, ``cInputTyp``, ``nSort``, ``cConf``, ``oPluginEinstellungenConfWerte_arr``

+---------------------------------------+----------------------------------------------+
| Member                                | Funktionalität                               |
+=======================================+==============================================+
| ``kPluginEinstellungenConf``          | Eindeutiger PluginEinstellungs-Key           |
+---------------------------------------+----------------------------------------------+
| ``kPlugin``                           | Eindeutiger Plugin-Key                       |
+---------------------------------------+----------------------------------------------+
| ``kPluginAdminMenu``                  | Eindeutiger Plugin-Adminmenu-Key             |
+---------------------------------------+----------------------------------------------+
| ``cName``                             | Name der Einstellung                         |
+---------------------------------------+----------------------------------------------+
| ``cBeschreibung``                     | Beschreibung der Einstellung                 |
+---------------------------------------+----------------------------------------------+
| ``cWertName``                         | Wert der Variable                            |
+---------------------------------------+----------------------------------------------+
| ``cInputTyp``                         | Typ der Variable (text, zahl, selectbox,...) |
+---------------------------------------+----------------------------------------------+
| ``nSort``                             | Sortierung der Einstellung                   |
+---------------------------------------+----------------------------------------------+
| ``cConf``                             | Y = Einstellung / N = Überschrift            |
+---------------------------------------+----------------------------------------------+
| ``oPluginEinstellungenConfWerte_arr`` | Array von Optionswerten                      |
+---------------------------------------+----------------------------------------------+

oPluginEinstellungenConfWerte_arr
"""""""""""""""""""""""""""""""""

Array mit Einstellungsoptionswerten

Falls eine Einstellungsoption eine *selectbox* oder *radio* ist, beinhaltet dieses Array zu einer bestimmten
Einstellungsoption, alle Optionswerte.

Array von Objekten

Member: ``kPluginEinstellungenConf``, ``cName``, ``cWert``, ``nSort``

+------------------------------+--------------------------------------------+
| Member                       | Funktionalität                             |
+==============================+============================================+
| ``kPluginEinstellungenConf`` | Eindeutiger Plugin-Einstellungs-Key        |
+------------------------------+--------------------------------------------+
| ``cName``                    | Eindeutiger Einstellungsname der Variablen |
+------------------------------+--------------------------------------------+
| ``cWert``                    | Wert der Option                            |
+------------------------------+--------------------------------------------+
| ``nSort``                    | Sortierung der Option                      |
+------------------------------+--------------------------------------------+


oPluginSprachvariable_arr
"""""""""""""""""""""""""

Array mit alle Sprachvariablen des Plugins

Array von Objekten

Member: ``kPluginSprachvariable``, ``kPlugin``, ``cName``, ``cBeschreibung``, ``oPluginSprachvariableSprache_arr``

+--------------------------------------+----------------------------------------------------------+
| Member                               | Funktionalität                                           |
+======================================+==========================================================+
| ``kPluginSprachvariable``            | Eindeutiger Sprachvariablen-Key                          |
+--------------------------------------+----------------------------------------------------------+
| ``kPlugin``                          | Eindeutiger Plugin-Key                                   |
+--------------------------------------+----------------------------------------------------------+
| ``cName``                            | Name der Sprachvariable                                  |
+--------------------------------------+----------------------------------------------------------+
| ``cBeschreibung``                    | Beschreibung der Sprachvariable                          |
+--------------------------------------+----------------------------------------------------------+
| ``oPluginSprachvariableSprache_arr`` | Array aller lokalisierten Sprachen dieser Sprachvariable |
+--------------------------------------+----------------------------------------------------------+

oPluginSprachvariableSprache_arr
""""""""""""""""""""""""""""""""

Diese Array beinhaltet alle Sprachvariablen des jeweiligen Plugins. Es muss assoziativ mit der entsprechenden
SprachISO angesprochen werden.

Assoziatives Array

Key: ISO

Wert: Lokalisierte Sprachvariable


oPluginFrontendLink_arr
"""""""""""""""""""""""

Array mit vorhanden Frontend Links

Array von Objekten

Member: ``kLink``, ``kLinkgruppe``, ``kPlugin``, ``cName``, ``nLinkart``, ``cURL``, ``cKundengruppen``,
``cSichtbarNachLogin``, ``cDruckButton``, ``nSort``, ``oPluginFrontendLinkSprache_arr``

+------------------------------------+------------------------------------------------------------------+
| Member                             | Funktionalität                                                   |
+====================================+==================================================================+
| ``kLink``                          | Eindeutiger Link-Key                                             |
+------------------------------------+------------------------------------------------------------------+
| ``kLinkgruppe``                    | Eindeutiger Linkgruppen-Key                                      |
+------------------------------------+------------------------------------------------------------------+
| ``kPlugin``                        | Eindeutiger Plugin-Key                                           |
+------------------------------------+------------------------------------------------------------------+
| ``cName``                          | Name des Frontend-Links                                          |
+------------------------------------+------------------------------------------------------------------+
| ``nLinkart``                       | Eindeutiger Linkart-Key                                          |
+------------------------------------+------------------------------------------------------------------+
| ``cURL``                           | Pfad zur Datei die verlinkt werden soll                          |
+------------------------------------+------------------------------------------------------------------+
| ``cKundengruppen``                 | String von Kundengruppen-Keys                                    |
+------------------------------------+------------------------------------------------------------------+
| ``cSichtbarNachLogin``             | Ist der Link nur nach dem Einloggen sichtbar? Y = Ja / N = Nein  |
+------------------------------------+------------------------------------------------------------------+
| ``cDruckButton``                   | Soll die Linkseite einen Druckbutton erhalten? Y = Ja / N = Nein |
+------------------------------------+------------------------------------------------------------------+
| ``nSort``                          | Sortierungsnummer des Links                                      |
+------------------------------------+------------------------------------------------------------------+
| ``oPluginFrontendLinkSprache_arr`` | Array lokalisierten Linknamen                                    |
+------------------------------------+------------------------------------------------------------------+


oPluginSprachvariableAssoc_arr
""""""""""""""""""""""""""""""

Assoziatives Array mit alles Sprachvariablen des Plugins

Diese assoziative Array beinhaltet alle Sprachvariablen des Plugins. Sie werden direkt in der entsprechenden
Shopsprache lokalisiert und können über ``cName`` angesprochen werden.

Assoziatives Array

Key: ``cName`` |br|
Wert: ``Objekt``

Member: ``kPluginSprachvariable``, ``kPlugin``, ``cName``, ``cBeschreibung``, ``oPluginSprachvariableSprache_arr``

+--------------------------------------+-------------------------------------------------------------------+
| Member                               | Funktionalität                                                    |
+======================================+===================================================================+
| ``kPluginSprachvariable``            | Eindeutiger Plugin Sprachvariablen Key                            |
+--------------------------------------+-------------------------------------------------------------------+
| ``kPlugin``                          | Eindeutiger Plugin Key                                            |
+--------------------------------------+-------------------------------------------------------------------+
| ``cName``                            | Name der Sprachvariable                                           |
+--------------------------------------+-------------------------------------------------------------------+
| ``cBeschreibung``                    | Beschreibung der Sprachvariable                                   |
+--------------------------------------+-------------------------------------------------------------------+
| ``oPluginSprachvariableSprache_arr`` | Array aller Sprachen für die diese Sprachvariable lokalisiert ist |
+--------------------------------------+-------------------------------------------------------------------+


oPluginFrontendLinkSprache_arr
""""""""""""""""""""""""""""""

Array mit lokalisierten Namen eines bestimmten Frontend Links

Array von Objekten

Member: ``kLink``, ``cSeo``, ``cISOSprache``, ``cName``, ``cTitle``, ``cContent``, ``cMetaTitle``,
``cMetaKeywords``, ``cMetaDescription``

+----------------------+----------------------------------------+
| Member               | Funktion                               |
+======================+========================================+
| ``kLink``            | Eindeutiger Link-Key                   |
+----------------------+----------------------------------------+
| ``cSeo``             | SEO für die jeweilige Linksprache      |
+----------------------+----------------------------------------+
| ``cISOSprache``      | ISO der Linksprache                    |
+----------------------+----------------------------------------+
| ``cName``            | Lokalisierter Name des Links           |
+----------------------+----------------------------------------+
| ``cTitle``           | Lokalisierter Titel des Links          |
+----------------------+----------------------------------------+
| ``cContent``         | Lokalisierter Content des Links        |
+----------------------+----------------------------------------+
| ``cMetaTitle``       | Lokalisierter MetaTitel des Links      |
+----------------------+----------------------------------------+
| ``cMetaKeywords``    | Lokalisierte MetaKeywords des Links    |
+----------------------+----------------------------------------+
| ``cMetaDescription`` | Lokalisierte MetaDescription des Links |
+----------------------+----------------------------------------+

oPluginZahlungsmethode_arr
""""""""""""""""""""""""""

Array aller Zahlungsmethoden

Dieses Array beinhaltet alle verfügbaren Zahlungsmethoden.

Array von Objekten

Member: ``kZahlungsart``, ``cName``, ``cModulId``, ``cKundengruppen``, ``cZusatzschrittTemplate``, ``cPluginTemplate``,
``cBild``, ``nSort``, ``nMailSenden``, ``nActive``, ``cAnbieter``, ``cTSCode``, ``nWaehrendBestellung``, ``nCURL``,
``nSOAP``, ``nSOCKETS``, ``nNutzbar``, ``cTemplateFileURL``, ``oZahlungsmethodeSprache_arr``,
``oZahlungsmethodeEinstellung_arr``

+-------------------------------------+----------------------------------------------------------------------------------------+
| Member                              | Funktionalität                                                                         |
+=====================================+========================================================================================+
| ``kZahlungsart``                    | Eindeutiger Zahlungsart Key                                                            |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cName``                           | Name der Zahlungsart                                                                   |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cModulId``                        | Eindeutige Modul-ID der Zahlungart                                                     |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cKundengruppen``                  | String von Kundengruppen für die diese Zahlungsart gilt                                |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cZusatzschrittTemplate``          | Zusätzliche Daten für Transaktionen können eingegeben werden                           |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cPluginTemplate``                 | Pfad zum Template der Zahlungsart                                                      |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cBild``                           | Bildpfad der Zahlungsart                                                               |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nSort``                           | Sortierungsnummer der Zahlungsart                                                      |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nMailSenden``                     | Versendet diese Zahlungsart standardmäßig eine Email beim Abschluss? 1 = Ja / 0 = Nein |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nActive``                         | Ist diese Zahlungsart aktiv? 1 = Ja / 0 = Nein                                         |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cAnbieter``                       | Name des Anbieters der Zahlungsart                                                     |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cTSCode``                         | Trusted Shops Code                                                                     |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nWaehrendBestellung``             | Pre oder Post Bestellung                                                               |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nCURL``                           | Nutzt diese Zahlungsart das CURL Protokoll?                                            |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nSOAP``                           | Nutzt diese Zahlungsart das SOAP Protokoll?                                            |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nSOCKETS``                        | Nutzt diese Zahlungsart Sockets?                                                       |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``nNutzbar``                        | Sind alle Serverprotokolle die nötig für diese Zahlungsart sind, nutzbar?              |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``cTemplateFileURL``                | Absoluter Pfad zur Template Datei                                                      |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``oZahlungsmethodeSprache_arr``     | Lokalisierte Zahlungsart für alle angegebenen Sprachen                                 |
+-------------------------------------+----------------------------------------------------------------------------------------+
| ``oZahlungsmethodeEinstellung_arr`` | Array von lokalisierten Einstellungen                                                  |
+-------------------------------------+----------------------------------------------------------------------------------------+

oZahlungsmethodeSprache_arr
"""""""""""""""""""""""""""

Array mit lokalisierten Namen der jeweiligen Zahlungsmethode

Array von Objekten

Member: ``kZahlungsart``, ``cISOSprache``, ``cName``, ``cGebuehrname``, ``cHinweisText``

+------------------+-----------------------------+
| Member           | Funktionalität              |
+==================+=============================+
| ``kZahlungsart`` | Eindeutiger Zahlungsart-Key |
+------------------+-----------------------------+
| ``cISOSprache``  | SprachISO                   |
+------------------+-----------------------------+
| ``cName``        | Lokalisierter Name          |
+------------------+-----------------------------+
| ``cGebuehrname`` | Lokalisierter Gebührenname  |
+------------------+-----------------------------+
| ``cHinweisText`` | Lokalisierter Hinweistext   |
+------------------+-----------------------------+

oZahlungsmethodeEinstellung_arr
"""""""""""""""""""""""""""""""

Array mit Einstellungen zu einer bestimmten Zahlungsmethode

Array von Objekten

Member: ``kPluginEinstellungenConf``, ``kPlugin``, ``kPluginAdminMenu``, ``cName``, ``cBeschreibung``, ``cWertName``,
``cInputTyp``, ``nSort``, ``cConf``

+------------------------------+----------------------------------------------+
| Member                       | Funktion                                     |
+==============================+==============================================+
| ``kPluginEinstellungenConf`` | Eindeutiger PluginEinstellungs-Key           |
+------------------------------+----------------------------------------------+
| ``kPlugin``                  | Eindeutiger Plugin-Key                       |
+------------------------------+----------------------------------------------+
| ``kPluginAdminMenu``         | Eindeutiger Plugin Adminmenu-Key             |
+------------------------------+----------------------------------------------+
| ``cName``                    | Name der Einstellung                         |
+------------------------------+----------------------------------------------+
| ``cBeschreibung``            | Beschreibung der Einstellung                 |
+------------------------------+----------------------------------------------+
| ``cWertName``                | Wert der Variable                            |
+------------------------------+----------------------------------------------+
| ``cInputTyp``                | Typ der Variable (text, zahl, selectbox,...) |
+------------------------------+----------------------------------------------+
| ``nSort``                    | Sortierung der Einstellung                   |
+------------------------------+----------------------------------------------+
| ``cConf``                    | Y = Einstellung / N = Überschrift            |
+------------------------------+----------------------------------------------+
