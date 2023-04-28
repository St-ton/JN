Variables
=========

.. |br| raw:: html

   <br />

*Plug-in variables* are available to the plug-in developer in the front and back ends of the online shop, as well as in every file managed
by a plug-in. |br|
All *plug-in variables* listed below are *members* of the global object ``$oPlugin`` in JTL-Shop 3 and 4.

**Example:**

Plug-in name output

.. code-block:: php

    echo $oPlugin->cName;

.. attention::

    *As of JTL-Shop 5.0.0* these variables are only provided for compatibility reasons. Accessing them
    will produce a ``E_USER_DEPRECATED`` type error.

Therefore, **as of JTL-Shop 5.0** use only the ``JTL\Plugin\PluginInterface`` interface. |br|
The corresponding *getters* are documented in the *methods* column.

**Example:**

in the PHP file of a front end link

.. code-block:: php

    $smarty->assign([
        'pluginName' => $plugin->getMeta()->getName()
    ]);

in a front end template

.. code-block:: smarty

   {$plugin->getMeta()->getName()}


All variables are accessible via the methods listed here, from the plug-in's general information to language variables
and plug-in settings.


Class variables
---------------

+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| Class variable                       | Methods                                           | Functions                                                                                   |
+======================================+===================================================+=============================================================================================+
| ``$kPlugin``                         | ``getID(): int``                                  | Unique plug-in key                                                                          |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nStatus``                         | ``getState(): int``                               | Plug-in status                                                                              |
|                                      |                                                   | (1 = Deactivated, 2 = Activated and installed, 3 = Faulty, 4 = Update failed)               |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nVersion``                        | ``getMeta()->getVersion(): Version``              | Plug-in version                                                                             |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$icon``                            | ``getMeta()->getIcon(): string``                  | Icon file name                                                                              |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nXMLVersion``                     | ---                                               | XML-Version                                                                                 |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nPrio``                           | ``getPriority(): int``                            | Priority for plug-ins with the same author                                                  |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cName``                           | ``getMeta()->getName(): string``                  | Plug-in name                                                                                |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cBeschreibung``                   | ``getMeta()->getDescription(): string``           | Plug-in description                                                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cAutor``                          | ``getMeta()->getAuthor(): string``                | Plug-in author                                                                              |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cURL``                            | ``getMeta()->getURL(): string``                   | URL to the plug-in creator                                                                  |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cVerzeichnis``                    | ``getPaths()->getBaseDir(): string``              | Plug-in directory                                                                           |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cPluginID``                       | ``getPluginID(): string``                         | One-time plug-in ID                                                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLizenz``                         | ``getLicense()->getKey(): string``                | Configured licence key                                                                      |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLizenzKlasse``                   | ``getLicense()->getClassName(): string``          | Licence class name                                                                          |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLicencePfad``                    | ``getLicense()->getClass(): string``              | Physical path to the *licence* folder in the server                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLicencePfadURL``                 | ---                                               | Complete URL to *license* folder                                                            |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cLicencePfadURLSSL``              | ---                                               | Complete URL via https to *license* folder                                                  |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cFrontendPfad``                   | ``getPaths()->getFrontendPath(): string``         | Physical path to the *front end* folder in the server                                       |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cFrontendPfadURL``                | ``getPaths()->getFrontendURL(): string``          | Complete URL to the *front end* folder                                                      |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cFrontendPfadURLSSL``             | ``getPaths()->getFrontendURL(): string``          | Complete URL via https to the *front end* folder                                            |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cAdminmenuPfad``                  | ``getPaths()->getAdminPath(): string``            | Physical path to the *admin menu* folder in the server                                      |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$cAdminmenuPfadURLSSL``            | ``getPaths()->getAdminURL(): string``             | Complete URL to the SSL-secured *admin menu* folder                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dZuletztAktualisiert``            | ``getMeta()->getDateLastUpdate(): DateTime``      | Date of last update                                                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dInstalliert``                    | ``getMeta()->getDateInstalled(): DateTime``       | Date of installation                                                                        |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dErstellt``                       | ``getMeta()->getDateInstalled(): DateTime``       | Date of creation                                                                            |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginHook_arr``                 | ``getHooks(): array``                             | Array with hooks                                                                            |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginAdminMenu_arr``            | ``getAdminMenu()->getItems: array``               | Array with admin menus                                                                      |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginEinstellung_arr``          | ``getConfig()->getOptions(): Collection``         | Array with set settings                                                                     |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginEinstellungConf_arr``      | ``getConfig()->getOptions(): Collection``         | Array with settings                                                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginEinstellungAssoc_arr``     | ``getConfig()->getOptions(): Collection``         | Associative array with set settings                                                         |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ---                                  | ``getConfig()->getValue(<ValueName>): mixed``     | Wert einer einzelnen Einstellung                                                            |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginSprachvariable_arr``       | ``getLocalization()->getTranslations(): array``   | Associative array with language variables                                                   |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginFrontendLink_arr``         | ``getLinks()->getLinks(): Collection``            | Array with front end links                                                                  |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginZahlungsmethode_arr``      | ``getPaymentMethods()->getMethods(): array``      | Array with payment methods                                                                  |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$oPluginZahlungsmethodeAssoc_arr`` | ``getPaymentMethods()->getMethodsAssoc(): array`` | Associative array with payment methods                                                      |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dInstalliert_DE``                 | ---                                               | Lokalisiertes Installationsdatum                                                            |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dZuletztAktualisiert_DE``         | ---                                               | Lokalisiertes Aktualisierungsdatum                                                          |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$dErstellt_DE``                    | ---                                               | Lokalisiertes Hersteller-Erstellungsdatum                                                   |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$nCalledHook``                     | ---                                               | ID des aktuell ausgeführten Hooks                                                           |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$pluginCacheID``                   | ``getCache()->getID(): string``                   | Individual cache ID for use of the object cache                                             |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+
| ``$pluginCacheGroup``                | ``getCache()->getGroup(): string``                | Individual cache group for use of the object cache                                          |
+--------------------------------------+---------------------------------------------------+---------------------------------------------------------------------------------------------+


Arrays
------

oPluginHook_arr
"""""""""""""""

This array contains all the hooks used via the plug-in.

Type: *Array of objects*

Member: ``kPluginHook``, ``kPlugin``, ``nHook``, ``cDateiname``

+-----------------+----------------------------------------------+
| Member          | Function                                     |
+=================+==============================================+
| ``kPluginHook`` | Unique hook key                              |
+-----------------+----------------------------------------------+
| ``kPlugin``     | Unique plug-in key                           |
+-----------------+----------------------------------------------+
| ``nHook``       | Hook ID                                      |
+-----------------+----------------------------------------------+
| ``cDateiname``  | File name executed with ``nHook``            |
+-----------------+----------------------------------------------+

oPluginAdminMenu_arr
""""""""""""""""""""

Array with all back end links

Type: *Array of objects*

Member: ``kPluginAdminMenu``, ``kPlugin``, ``cName``, ``cDateiname``, ``nSort``, ``nConf``

+----------------------+-----------------------------------------------+
| Member               | Function                                      |
+======================+===============================================+
| ``kPluginAdminMenu`` | Unique plug-in admin menu key                 |
+----------------------+-----------------------------------------------+
| ``kPlugin``          | Unique plug-in key                            |
+----------------------+-----------------------------------------------+
| ``cName``            | Admin tab name                                |
+----------------------+-----------------------------------------------+
| ``nSort``            | Admin tab sorting number                      |
+----------------------+-----------------------------------------------+
| ``nConf``            | 0 = Custom link to cDateiname / 1 = Settings |
+----------------------+-----------------------------------------------+


oPluginEinstellung_arr
""""""""""""""""""""""

Array with all set plug-in settings

Type: *Array of objects*

Member: ``kPlugin``, ``cName``, ``cWert``

+-------------+-------------------------------------------+
| Member      | Function                                  |
+=============+===========================================+
| ``kPlugin`` | Unique plug-in key                        |
+-------------+-------------------------------------------+
| ``cName``   | Unique setting name of variable           |
+-------------+-------------------------------------------+
| ``cWert``   | Variable value                            |
+-------------+-------------------------------------------+

oPluginEinstellungAssoc_arr
"""""""""""""""""""""""""""

Associative array with settings

The difference with the array above is that the respective settings can be called up associatively
with their *ValueName*.

**Example:**

JTL-Shop 4

.. code-block:: php

    if ($oPlugin->oPluginEinstellungAssoc_arr['mein_cName'] === 'Y') {
        //...
    }

**Example:**

JTL-Shop 5

.. code-block:: php

    if ($plugin->getOptions()->getValue('mein_cName') === 'Y') {
        //...
    }


Type: *Associative array*

Key: ``cName`` |br|
Value: ``cWert``

+-----------+-------------------+
| Member    | Function          |
+===========+===================+
| ``cWert`` | Variable value    |
+-----------+-------------------+


oPluginEinstellungConf_arr
""""""""""""""""""""""""""

Array with settings options

These options are displayed in the back end under the respective settings link and can be set
there as a setting.

Type: *Array of objects*

Member: ``kPluginEinstellungenConf``, ``kPlugin``, ``kPluginAdminMenu``, ``cName``, ``cBeschreibung``,
``cWertName``, ``cInputTyp``, ``nSort``, ``cConf``, ``oPluginEinstellungenConfWerte_arr``

+---------------------------------------+----------------------------------------------+
| Member                                | Function                                     |
+=======================================+==============================================+
| ``kPluginEinstellungenConf``          | Unique plug-in settings key                  |
+---------------------------------------+----------------------------------------------+
| ``kPlugin``                           | Unique plug-in key                           |
+---------------------------------------+----------------------------------------------+
| ``kPluginAdminMenu``                  | Unique plug-in admin menu key                |
+---------------------------------------+----------------------------------------------+
| ``cName``                             | Setting name                                 |
+---------------------------------------+----------------------------------------------+
| ``cBeschreibung``                     | Setting description                          |
+---------------------------------------+----------------------------------------------+
| ``cWertName``                         | Variable value                               |
+---------------------------------------+----------------------------------------------+
| ``cInputTyp``                         | Variable type (text, number, select box,...) |
+---------------------------------------+----------------------------------------------+
| ``nSort``                             | Setting sorting                              |
+---------------------------------------+----------------------------------------------+
| ``cConf``                             | Y = Setting / N = Heading                    |
+---------------------------------------+----------------------------------------------+
| ``oPluginEinstellungenConfWerte_arr`` | Array of option values                       |
+---------------------------------------+----------------------------------------------+

oPluginEinstellungenConfWerte_arr
"""""""""""""""""""""""""""""""""

Array with setting options

If a setting option is a *select box* or *radio*, this array contains all option values for a given
setting option.

Type: *Array of objects*

Member: ``kPluginEinstellungenConf``, ``cName``, ``cWert``, ``nSort``

+------------------------------+--------------------------------------------+
| Member                       | Function                                   |
+==============================+============================================+
| ``kPluginEinstellungenConf`` | Unique plug-in settings key                |
+------------------------------+--------------------------------------------+
| ``cName``                    | Unique setting name of the variables       |
+------------------------------+--------------------------------------------+
| ``cWert``                    | Option value                               |
+------------------------------+--------------------------------------------+
| ``nSort``                    | Option sorting                             |
+------------------------------+--------------------------------------------+


oPluginSprachvariable_arr
"""""""""""""""""""""""""

Array with plug-in language variables

Type: *Array of objects*

Member: ``kPluginSprachvariable``, ``kPlugin``, ``cName``, ``cBeschreibung``, ``oPluginSprachvariableSprache_arr``

+--------------------------------------+----------------------------------------------------------+
| Member                               | Function                                                 |
+======================================+==========================================================+
| ``kPluginSprachvariable``            | Unique language variable key                             |
+--------------------------------------+----------------------------------------------------------+
| ``kPlugin``                          | Unique plug-in key                                       |
+--------------------------------------+----------------------------------------------------------+
| ``cName``                            | Language variable name                                   |
+--------------------------------------+----------------------------------------------------------+
| ``cBeschreibung``                    | language variable description                            |
+--------------------------------------+----------------------------------------------------------+
| ``oPluginSprachvariableSprache_arr`` | Array of localised languages of this language variable   |
+--------------------------------------+----------------------------------------------------------+

oPluginSprachvariableSprache_arr
""""""""""""""""""""""""""""""""

This array contains all language variables of the respective plug-in. It must be addressed associatively with the
corresponding language ISO.

Associative array

Key: ISO

Value: Localised language variable


oPluginFrontendLink_arr
"""""""""""""""""""""""

Array with available front end links

Type: *Array of objects*

Member: ``kLink``, ``kLinkgruppe``, ``kPlugin``, ``cName``, ``nLinkart``, ``cURL``, ``cKundengruppen``,
``cSichtbarNachLogin``, ``cDruckButton``, ``nSort``, ``oPluginFrontendLinkSprache_arr``

+------------------------------------+------------------------------------------------------------------+
| Member                             | Function                                                         |
+====================================+==================================================================+
| ``kLink``                          | Unique link key                                                  |
+------------------------------------+------------------------------------------------------------------+
| ``kLinkgruppe``                    | Unique link group key                                            |
+------------------------------------+------------------------------------------------------------------+
| ``kPlugin``                        | Unique plug-in key                                               |
+------------------------------------+------------------------------------------------------------------+
| ``cName``                          | Front end link name                                              |
+------------------------------------+------------------------------------------------------------------+
| ``nLinkart``                       | Unique link type key                                             |
+------------------------------------+------------------------------------------------------------------+
| ``cURL``                           | Path to file that is to be linked                                |
+------------------------------------+------------------------------------------------------------------+
| ``cKundengruppen``                 | String of customer groups keys                                   |
+------------------------------------+------------------------------------------------------------------+
| ``cSichtbarNachLogin``             | Is link visible only after login? Y = Yes / N = No               |
+------------------------------------+------------------------------------------------------------------+
| ``cDruckButton``                   | Should the link page have a push button? Y = Yes / N = No        |
+------------------------------------+------------------------------------------------------------------+
| ``nSort``                          | Link sorting number                                              |
+------------------------------------+------------------------------------------------------------------+
| ``oPluginFrontendLinkSprache_arr`` | Array of localised link names                                    |
+------------------------------------+------------------------------------------------------------------+


oPluginSprachvariableAssoc_arr
""""""""""""""""""""""""""""""

Associative array with all plug-in language variables

This associative array contains all plug-in language variables. They will be directly localised in the language
of the online shop and can be called up via ``cName``.

Type: *Associative array*

Key: ``cName`` |br|
Value: ``Objekt``

Member: ``kPluginSprachvariable``, ``kPlugin``, ``cName``, ``cBeschreibung``, ``oPluginSprachvariableSprache_arr``

+--------------------------------------+--------------------------------------------------------------------+
| Member                               | Function                                                           |
+======================================+====================================================================+
| ``kPluginSprachvariable``            | Unique plug-in language variable key                               |
+--------------------------------------+--------------------------------------------------------------------+
| ``kPlugin``                          | Unique plug-in key                                                 |
+--------------------------------------+--------------------------------------------------------------------+
| ``cName``                            | Language variable name                                             |
+--------------------------------------+--------------------------------------------------------------------+
| ``cBeschreibung``                    | Language variable description                                      |
+--------------------------------------+--------------------------------------------------------------------+
| ``oPluginSprachvariableSprache_arr`` | Array of all languages localised for this language variable        |
+--------------------------------------+--------------------------------------------------------------------+


oPluginFrontendLinkSprache_arr
""""""""""""""""""""""""""""""

Array with localised names of certain front end links

Type: *Array of objects*

Member: ``kLink``, ``cSeo``, ``cISOSprache``, ``cName``, ``cTitle``, ``cContent``, ``cMetaTitle``,
``cMetaKeywords``, ``cMetaDescription``

+----------------------+----------------------------------------+
| Member               | Function                               |
+======================+========================================+
| ``kLink``            | Unique link key                        |
+----------------------+----------------------------------------+
| ``cSeo``             | SEO for relevant link language         |
+----------------------+----------------------------------------+
| ``cISOSprache``      | ISO of link language                   |
+----------------------+----------------------------------------+
| ``cName``            | Localised link name                    |
+----------------------+----------------------------------------+
| ``cTitle``           | Localised link title                   |
+----------------------+----------------------------------------+
| ``cContent``         | Localised link content                 |
+----------------------+----------------------------------------+
| ``cMetaTitle``       | Localised link meta title              |
+----------------------+----------------------------------------+
| ``cMetaKeywords``    | Localised link meta keywords           |
+----------------------+----------------------------------------+
| ``cMetaDescription`` | Localised link meta description        |
+----------------------+----------------------------------------+

oPluginZahlungsmethode_arr
""""""""""""""""""""""""""

Array of all payment methods

This array contains all available payment methods.

Type: *Array of objects*

Member: ``kZahlungsart``, ``cName``, ``cModulId``, ``cKundengruppen``, ``cZusatzschrittTemplate``, ``cPluginTemplate``,
``cBild``, ``nSort``, ``nMailSenden``, ``nActive``, ``cAnbieter``, ``cTSCode``, ``nWaehrendBestellung``, ``nCURL``,
``nSOAP``, ``nSOCKETS``, ``nNutzbar``, ``cTemplateFileURL``, ``oZahlungsmethodeSprache_arr``,
``oZahlungsmethodeEinstellung_arr``

+-------------------------------------+-----------------------------------------------------------------------------------------+
| Member                              | Function                                                                                |
+=====================================+=========================================================================================+
| ``kZahlungsart``                    | Unique payment method key                                                               |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cName``                           | Name of payment method                                                                  |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cModulId``                        | Unique payment method module-ID                                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cKundengruppen``                  | String of customer groups to which the payment method applies                           |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cZusatzschrittTemplate``          | Additional data for transactions can be entered                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cPluginTemplate``                 | Path to payment method template                                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cBild``                           | Image path to payment method                                                            |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nSort``                           | Payment method sorting number                                                           |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nMailSenden``                     | Does this payment method send an email upon payment by default? 1 = Yes / 0 = No        |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nActive``                         | Is this payment method active? 1 = Yes / 0 = No                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cAnbieter``                       | Name of payment method provider                                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cTSCode``                         | Trusted Shops code                                                                      |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nWaehrendBestellung``             | Pre- or post-order                                                                      |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nCURL``                           | Does this payment method use the cURL protocol?                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nSOAP``                           | Does this payment method use the SOAP protocol?                                         |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nSOCKETS``                        | Does this payment method use sockets?                                                   |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``nNutzbar``                        | Are all server protocols required for this payment method usable?                       |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``cTemplateFileURL``                | Absolute path to template file                                                          |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``oZahlungsmethodeSprache_arr``     | Localised payment method for all specified languages                                    |
+-------------------------------------+-----------------------------------------------------------------------------------------+
| ``oZahlungsmethodeEinstellung_arr`` | Array of localised settings                                                             |
+-------------------------------------+-----------------------------------------------------------------------------------------+

oZahlungsmethodeSprache_arr
"""""""""""""""""""""""""""

Array with localised names of the respective payment methods

Type: *Array of objects*

Member: ``kZahlungsart``, ``cISOSprache``, ``cName``, ``cGebuehrname``, ``cHinweisText``

+------------------+-----------------------------+
| Member           | Function                    |
+==================+=============================+
| ``kZahlungsart`` | Unique payment method key   |
+------------------+-----------------------------+
| ``cISOSprache``  | ISO language code           |
+------------------+-----------------------------+
| ``cName``        | Localised name              |
+------------------+-----------------------------+
| ``cGebuehrname`` | Localised fee name          |
+------------------+-----------------------------+
| ``cHinweisText`` | Localised text              |
+------------------+-----------------------------+

oZahlungsmethodeEinstellung_arr
"""""""""""""""""""""""""""""""

Array with settings for a certain payment method

Type: *Array of objects*

Member: ``kPluginEinstellungenConf``, ``kPlugin``, ``kPluginAdminMenu``, ``cName``, ``cBeschreibung``, ``cWertName``,
``cInputTyp``, ``nSort``, ``cConf``

+------------------------------+----------------------------------------------+
| Member                       | Function                                     |
+==============================+==============================================+
| ``kPluginEinstellungenConf`` | Unique plug-in settings key                  |
+------------------------------+----------------------------------------------+
| ``kPlugin``                  | Unique plug-in key                           |
+------------------------------+----------------------------------------------+
| ``kPluginAdminMenu``         | Unique plug-in admin menu key                |
+------------------------------+----------------------------------------------+
| ``cName``                    | Setting name                                 |
+------------------------------+----------------------------------------------+
| ``cBeschreibung``            | Setting description                          |
+------------------------------+----------------------------------------------+
| ``cWertName``                | Variable value                               |
+------------------------------+----------------------------------------------+
| ``cInputTyp``                | Variable type (text, number, select box,...) |
+------------------------------+----------------------------------------------+
| ``nSort``                    | Setting sorting                              |
+------------------------------+----------------------------------------------+
| ``cConf``                    | Y = Settings / N = Heading                   |
+------------------------------+----------------------------------------------+