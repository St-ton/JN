Tipps und Tricks
================

.. contents::
    Inhalt

Die Anleitungen auf dieser Seite beschäftigen sich mit dem Template EVO des JTL-Shop 4.

Bei diesen Tipps & Tricks wird davon ausgegangen, dass ein :doc:`eigenes Child-Template </shop_templates/eigenes_template>` angelegt wurde.
Dies stellt sicher, dass das EVO-Template weiterhin updatefähig bleibt.

***********************
LESS-Variablen anpassen
***********************

Um schnell und einfach das Aussehen Ihres JTL-Shops zu beeinflussen, bietet es sich an, die LESS-Variablen des EVO-Templates zu überschreiben.
Diese Variablen sind verantwortlich für Farbwerte, Schriftarten, Abstände und Rahmen.

Wie Sie mit LESS-Files in Ihrem Child-Template arbeiten, finden Sie in im Bereich: :ref:`Arbeiten mit LESS <arbeiten-mit-less>`.
Dort steht auch beschrieben, wie Sie Variablen des EVO-Templates mit Ihrem Child-Template überschreiben können.

Die Datei, welche alle Variablen enthält, finden Sie im Ordner ``<Shop-Root>/templates/Evo/themes/bootstrap/less/variables.less``

*******************************************
Die wichtigsten LESS-Variablen im Überblick
*******************************************

Farben
------

.. code-block:: scss

    @brand-primary:         #428bca;
    @brand-success:         #5cb85c;
    @brand-info:            #5bc0de;
    @brand-warning:         #f0ad4e;
    @brand-danger:          #d9534f;

    @body-bg:               #fff;
    @text-color:            @gray-dark;

    @link-color:            @brand-primary;
    @link-hover-color:      darken(@link-color, 15%);

| **@brand-primary:** Hauptfarbe: diese Farbe wird an den meisten Stellen verwendet, z.B. für Buttons.
  Tragen Sie hier die dominante Farbe Ihres Corporate Designs ein.
| **@brand-success:** Erfolgsmeldung: diese Farbe ist die Hintergrundfarbe der Boxen von Erfolgsmeldungen im Shop. Standard: Hellgrün.
| **@brand-info:** Infomeldung: diese Farbe ist die Hintergrundfarbe der Boxen von Informationsmeldungen im Shop. Standard: Hellblau.
| **@brand-warning:** Warnmeldung: diese Farbe ist die Hintergrundfarbe der Boxen von Warnmeldungen im Shop. Standard: Orange.
| **@brand-danger:** Fehlermeldung: diese Farbe ist die Hintergrundfarbe der Boxen von Fehlermeldungen im Shop. Standard: Rot.
|
| **@body-bg:** Hintergrundfarbe des gesamten Shops.
| **@text-color:** Schriftfarbe des Fließtextes
|
| **@link-color:** Farbe der Links
| **@link-hover-color:** Hover-Farbe der Links

----------------------------------------------------------------------------------------------------

Schrift
-------

.. code-block:: scss

    @font-family-sans-serif:  "Helvetica Neue", Helvetica, Arial, sans-serif;
    @font-family-serif:       Georgia, "Times New Roman", Times, serif;
    @font-family-monospace:   Menlo, Monaco, Consolas, "Courier New", monospace;
    @font-family-base:        @font-family-sans-serif;
    @font-size-base:          14px;

| **@font-family-sans-serif:** Hauptschrift-Familien für serifenlose Schrift.
| **@font-family-serif:** Hauptschrift-Familien für Serifenschrift.
| **@font-family-monospace:** Hauptschrift-Familien für Monospace-Schrift. Wird verwendet für ``<code>``, ``<kdb>`` und ``<pre>`` .
| **@font-family-base:** Definiert, welche Variable als Hauptschriftart verwendet wird.
| **@font-size-base:** Standardschriftgröße für Fliestext, Links etc.
|

.. code-block:: scss

    @headings-font-family:    inherit;
    @headings-font-weight:    500;
    @headings-line-height:    1.1;
    @headings-color:          inherit;

| **@headings-font-family:** Schriftfamilie für Überschriften.
| **@headings-font-weight:** Schriftschnitt für Überschriften.
| **@headings-line-height:** Zeilenhöhe der Überschriften.
| **@headings-color:** Schriftfarbe für Überschriften.
|

.. note::

    Schriftgrößen für Überschriften werden automatisch berechnet. Wenn Sie diese Berechnungen anpassen möchten, müssen Sie die weiteren Variablen für ``@font-size-h1``-h6 bearbeiten.

----------------------------------------------------------------------------------------------------

Buttons
-------

.. code-block:: scss

    @btn-font-weight:                normal;

    @btn-default-color:              #333;
    @btn-default-bg:                 #fff;
    @btn-default-border:             #ccc;

    @btn-primary-color:              #fff;
    @btn-primary-bg:                 @brand-primary;
    @btn-primary-border:             darken(@btn-primary-bg, 5%);

| **@btn-font-weight:** Schriftschnitt für Buttons
|
| **@btn-default-color:** Schriftfarbe der Buttons
| **@btn-default-bg:** Hintergrundfarbe der Buttons
| **@btn-default-border:** Rahmenfarbe der Buttons
|
| **@btn-primary-color:** Schriftfarbe für primäre Buttons
| **@btn-primary-bg:** Hintergrundfarbe der primären Buttons
| **@btn-primary-border:** Rahmenfarbe der primären Buttons
|

----------------------------------------------------------------------------------------------------

Navigation
----------

.. code-block:: scss

    @navbar-default-color:             #777;
    @navbar-default-bg:                #f8f8f8;
    @navbar-default-border:            darken(@navbar-default-bg, 6.5%);

    @navbar-default-link-color:                #777;
    @navbar-default-link-hover-color:          #333;
    @navbar-default-link-hover-bg:             transparent;
    @navbar-default-link-active-color:         #555;
    @navbar-default-link-active-bg:            darken(@navbar-default-bg, 6.5%);
    @navbar-default-link-disabled-color:       #ccc;
    @navbar-default-link-disabled-bg:          transparent;

| **@navbar-default-color:** Schriftfarbe der Standard-Navigation
| **@navbar-default-bg:** Hintergrundfarbe der Standard-Navigation
| **@navbar-default-border:** Rahmenfarbe der Standard-Navigation
|
| **@navbar-default-link-color:** Linkfarbe der Standard-Navigation
| **@navbar-default-link-hover-color:** Link-Hoverfarbe der Standard-Navigation
| **@navbar-default-link-hover-bg:** Hintergrundfarbe der Links der Standard-Navigation
| **@navbar-default-link-active-color:** Aktive Linkfarbe der Standard-Navigation
| **@navbar-default-link-active-bg:** Aktive Hintergrundfarbe der Links der Standard-Navigation
|

.. note::

    Es gibt noch viele weitere Variablen, aber mit dieser Übersicht können Sie schon ein individuelles Theme erstellen. Probieren Sie ruhig weitere Variablen aus!

*************************
Artikelattribute abfragen
*************************

Artikelattribute dienen in den Artikeldetails der Auflistung bestimmter Artikeleigenschaften wie z.B. Füllmenge. Artikelattribute werden in `JTL-Wawi pro Sprache definiert <http://guide.jtl-software.de/jtl/Betrieb:Artikel-/Kategoriepflege>`_.
Siehe auch `JTL-Demoshop <https://demo.jtl-shop.de/Frei-definierte-Attribute>`_.

Standardmäßig werden Artikelattribute im Shop in den Artikeldetails unter dem Beschreibungstext aufgelistet, sofern Artikelattribute vorhanden sind.

**Template-Code** (In artikel_inc.tpl):

.. code-block:: smarty

    {if $Artikel->Attribute|@count > 0}
      <div class="attributes">
        {foreach name=Attribute from=$Artikel->Attribute item=Attribut}
          <p><b>{$Attribut->cName}:</b> {$Attribut->cWert}</p>
        {/foreach}
      </div>
    {/if}

Der Zugriff ist auch über ein assoziatives Array möglich:

.. code-block:: smarty

    {assign var="attrname" value="Name des Funktionsattributes hier eintragen"}
    {$Artikel->AttributeAssoc.$attrname}

******************
Funktionsattribute
******************

In JTL-Wawi lassen sich in den Artikeldetails im Reiter Sonstiges/Sonderpreise Funktionsattribute zu dem Artikel hinterlegen. Anders als Artikelattribute (siehe vorheriger Abschnitt) werden Funktionsattribute nicht mehrsprachig definiert.
Funktionsattribute an einem Artikel lösen ein bestimmtes Ereignis aus oder steuern gewisse Funktionen im Shop oder Template.
Siehe auch `Beispielartikel mit Funktionsattributen im JTL-Demoshop <https://demo.jtl-shop.de/Frei-definierte-Attribute>`_.

Funktionsattribute am Artikel stehen templateseitig in den Artikeldetails als Variable zur Verfügung und können auf solchen Seiten abgefragt werden.
Standardmäßig unterstützt der Shop die folgenden Funktionsattribute: `Funktionsattribute für JTL-Shop <https://demo.jtl-shop.de/Frei-definierte-Attribute>`_.

Funktionsattribute können im Template per ``{$Artikel->FunktionsAttribute.FUNKTIONSATTRIBUTNAME}`` ausgelesen werden (**FUNKTIONSATTRIBUTNAME** durch den von Ihnen gewählten Funktionsattributnamen in JTL-Wawi ersetzen).

Natürlich können auch eigene Funktionsattribute in JTL-Wawi angelegt, und im Shop-Template abgefragt werden.

.. note::

    **Wichtig:** Funktionsattributnamen müssen lowercase (nur Kleinbuchstaben) ausgeschrieben werden, auch wenn der Name in JTL-Wawi Großbuchstaben enthält.

**Beispiel:**
Wir möchten ein Funktionsattribut ``body_class`` abfragen und abhängig davon eine besondere CSS-Klasse für das body-Element setzen:

**Template-Code** (für header.tpl):

.. code-block:: smarty

    <body{if $Artikel->FunktionsAttribute.body_class} class="{$Artikel->FunktionsAttribute.body_class}"{/if} id="page_type_{$nSeitenTyp}">

**Sonderfall Sonderzeichen im Funktionsattributnamen:**
Bei Sonderzeichen im Namen des Funktionsattributs kann wie folgt darauf zugegriffen werden:

.. code-block:: smarty

    {assign var="fktattrname" value="größe"}
    {$Artikel->FunktionsAttribute.$fktattrname}

***************************
Kategorieattribute abfragen
***************************

In JTL-Wawi lassen sich in den Kategoriedetails Kategorieattribute definieren, welche beim Synchronisieren zum Shop übertragen werden.
Beginnend mit Shop-Version 4.05 werden Kategorie-Funktionsattribute (``categoryFunctionAttributes``) als key/value pair zur Aufnahme der
Funktionsattribute und ``categoryAttributes`` als array of objects mit den lokalisierten Kategorieattributen unterschieden.
Funktionsattribute dienen der Steuerung von Aktionen oder der Ansicht im Shop, während Kategorieattributen lokalisierte Werte - passend
zur eingestellten Sprache - enthalten können.
Diese Kategorieattribute können im Template wie folgt abgefragt werden:

**PHP-Code für Funktionsattribut** (Einbindung als Plugin oder Einbindung in :ref:`php/functions.php <eigene-smarty-funktionen-integrieren>`):

.. code-block:: php

    $Kategorien = new KategorieListe();
    $Kategorien->getAllCategoriesOnLevel( 0 );
    foreach ($Kategorien->elemente as $Kategorie) {
      $funktionsWert = $Kategorie->categoryFunctionAttributes['meinkategoriefunktionsattribut'];
    }

**PHP-Code für lokalisiertes Attribut** (Einbindung als Plugin oder Einbindung in :ref:`php/functions.php <eigene-smarty-funktionen-integrieren>`):

.. code-block:: php

    $Kategorien = new KategorieListe();
    $Kategorien->getAllCategoriesOnLevel( 0 );
    foreach ($Kategorien->elemente as $Kategorie) {
      $attributWert = $Kategorie->categoryFunctionAttributes['meinkategorieattribut']->cWert;
    }

**Template-Code** zur Steuerung mittels Kategorie-Funktionsattributen in Kategorieansicht (am besten mit der :doc:`Smarty Debug-Konsole  </shop_templates/debug>` nach dem eigenen Kategorieattribut suchen):

.. code-block:: smarty

    {if $oNavigationsinfo->oKategorie->KategorieAttribute.meinkategoriefunktionsattribut === 'machedies'}
        <span>MacheDies</span>
    {else}
        <span>MacheDas</span>
    {/if}

**Template-Code** zur Ausgabe eines lokalisierten Kategorieattributs in Kategorieansicht (am besten mit der :doc:`Smarty Debug-Konsole  </shop_templates/debug>` nach dem eigenen Kategorieattribut suchen):

.. code-block:: smarty

    <span>{$oNavigationsinfo->oKategorie->KategorieAttribute.meinkategorieattribut->cWert}</span>

********************************************************
Eigene Sprachvariablen anlegen und ins Template einfügen
********************************************************

In der Sprachverwaltung im JTL-Shop-Admin-Backend ( Admin -> Sprachverwaltung ) lassen sich im Hinzufügen-Reiter eigene Sprachvariablen hinzufügen. Per Smarty-Funktion ``{lang}`` und den Parametern ``key`` und ``section`` können Sie diese Variablen im Template verwenden.

Beispiel:
Wir fügen über die Sprachverwaltung folgende Sprachvariable hinzu:

* Sektion: custom
* Variable: "safetyBoxTitle"
* Wert Deutsch: "SSL-Verschlüsselung"
* Wert Englisch: "SSL-Encryption"

**Template-Code**:

.. code-block:: smarty

    {lang key="safetyBoxTitle" section="custom"}

**PHP-Code** (z.B. für Plugins):

.. code-block:: php

    echo $GLOBALS['oSprache']->gibWert('safetyBoxTitle', 'custom');

**Sprachvariable als Smarty-Variable speichern und abfragen:**

**Template-Code**:

.. code-block:: smarty

    {* Sprachvariable einfügen *}
    {lang key="safetyBoxTitle" section="custom"}

    {* Variable mit assign zuweisen *}
    {lang assign="testVariableSafetyBoxTitle" key="safetyBoxTitle" section="custom"}

    {* die zuvor zugewiesene Variable kann nun normal aufgerufen oder abgefragt werden *}
    {if $testVariableSafetyBoxTitle eq "SSL-Verschlüsselung"}<span class="de">{$testVariableSafetyBoxTitle}</span>{else}<span>{$testVariableSafetyBoxTitle}</span>{/if}


**********************************************************
Eigene Artikellisten erzeugen und ins Template integrieren
**********************************************************

Ab JTL-Shop3.10 ist es möglich, eigene Artikel-Arrays über eine Smarty-Funktion ``{get_product_list}`` zu erzeugen.
Der Funktion können die folgenden Parameter übergeben werden:

+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| Parametername           | Typ      | Pflichtattribut | Beschreibung                                                                                                                                            |
+=========================+==========+=================+=========================================================================================================================================================+
| nLimit                  | Numeric  | Ja              | Maximale Anzahl Artikel, welche geholt werden sollen                                                                                                    |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| cAssign                 | String   | Ja              | Name der Smarty-Variable, in welchem das Array mit Artikeln gespeichert wird                                                                            |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kKategorie              | Numeric  | -               | Primärschlüssel einer Kategorie, siehe Datenbank tkategorie.kKategorie                                                                                  |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kHersteller             | Numeric  | -               | Primärschlüssel eines Herstellers, siehe Datenbank thersteller.kHersteller                                                                              |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kArtikel                | Numeric  | -               | Primärschlüssel eines Artikels, siehe Datenbank tartikel.kArtikel                                                                                       |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kSuchanfrage            | String   | -               | Primärschlüssel einer Suchanfrage, siehe Datenbank tsuchcache.kSuchCache                                                                                |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kMerkmalWert            | String   | -               | Primärschlüssel eines Merkmalwerts, siehe Datenbank tmerkmalwert.kMerkmalwert                                                                           |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kTag                    | String   | -               | Primärschlüssel eines Tags siehe ttag.kTag                                                                                                              |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kSuchspecial            | Numeric  | -               | Filterung nach Suchspecials, siehe Tabelle unten Suchspecialschlüssel                                                                                   |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kKategorieFilter        | Numeric  | -               | Zusätzlicher Filter nach einer Kategorie in Kombination mit einem Hauptfilter z.B. kHersteller.                                                         |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| kHerstellerFilter       | Numeric  | -               | Zusätzlicher Filter nach einem Hersteller in Kombination mit einem Hauptfilter z.B. kKategorie. Primärschlüssel siehe Datenbank thersteller.kHersteller |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| nBewertungSterneFilter  | Numeric  | -               | Zusätzlicher Filter nach Mindest-Durschnittsbewertung in Kombination mit einem Hauptfilter z.B. kKategorie.                                             |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| cPreisspannenFilter     | String   | -               | Zusätzlicher Filter nach Preisspanne in Kombination mit einem Hauptfilter z.B. kKategorie. Schreibweise für von 20€ bis 40.99€: "20_40.99"              |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| nSortierung             | Numeric  | -               | gibt an nach welchem Artikelattribut sortiert werden soll. Details siehe Tabelle unten Sortierungsschlüssel                                             |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| cMerkmalFilter          | String   | -               | Primärschlüssel der Merkmalwerte durch Semikolon getrennt z.B. "100;101". Primärschlüsselangabe siehe Datenbank tmerkmalwert.kMerkmalwert               |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| cSuchFilter             | String   | -               | Primärschlüssel der Suchfilter durch Semikolon getrennt z.B. "200;201". Primärschlüsselangabe siehe Datenbank tsuchcache.kSuchCache                     |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| cTagFilter              | String   | -               | Primärschlüssel der Tags durch Semikolon getrennt z.B. "300;301". Primärschlüsselangabe siehe Datenbank ttag.kTag                                       |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+
| cSuche                  | String   | -               | Suchbegriff z.B. "zwiebel ananas baguette"                                                                                                              |
+-------------------------+----------+-----------------+---------------------------------------------------------------------------------------------------------------------------------------------------------+

**Beispieleinbindung in Template-Code**
Den folgenden Code binden wir im Template in die Datei /tpl_inc/seite_startseite.tpl ein:

.. code-block:: smarty

    <h2>Unsere Verkaufsschlager aus dem Bereich Gemüse</h2>
    {get_product_list kKategorie=21 nLimit=3 nSortierung=11 cAssign="myProducts"}
    {if $myProducts}
      <ul>
      {foreach name=custom from=$myProducts item=oCustomArtikel}
        <li>{$oCustomArtikel->cName}</li>
      {/foreach}
      </ul>
    {/if}

**Sortierungsschlüssel**

+----------------------+----------+
| Standard             | 100      |
+======================+==========+
| Name A-Z             | 1        |
+----------------------+----------+
| Name Z-A             | 2        |
+----------------------+----------+
| Preis 1..9           | 3        |
+----------------------+----------+
| Preis 9..1           | 4        |
+----------------------+----------+
| EAN                  | 5        |
+----------------------+----------+
| neuste zuerst        | 6        |
+----------------------+----------+
| Artikelnummer        | 7        |
+----------------------+----------+
| Verfügbarkeit        | 8        |
+----------------------+----------+
| Gewicht              | 9        |
+----------------------+----------+
| Erscheinungsdatum    | 10       |
+----------------------+----------+
| Bestseller           | 11       |
+----------------------+----------+
| Bewertungen          | 12       |
+----------------------+----------+

**Suchspecialschlüssel**

+-----------------------+----------+
| Bestseller            | 100      |
+=======================+==========+
| Sonderangebote        | 1        |
+-----------------------+----------+
| Neu im Sortiment      | 2        |
+-----------------------+----------+
| Top Angebote          | 3        |
+-----------------------+----------+
| In Kürze verfügbar    | 4        |
+-----------------------+----------+
| Top bewertet          | 5        |
+-----------------------+----------+
| Ausverkauft           | 6        |
+-----------------------+----------+
| Auf Lager             | 7        |
+-----------------------+----------+
| Vorbestellung Möglich | 8        |
+-----------------------+----------+

***********************************************************
Eigene Kategorielisten erzeugen und im Template integrieren
***********************************************************

Ab JTL-Shop3.10 ist es möglich, eigene Kategorie-Arrays über eine Smarty-Funktion ``{get_category_list}`` zu erzeugen.
Der Funktion können die folgenden Parameter übergeben werden:

+-----------------+----------+------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------+
| Parametername   | Typ      | Pflichtattribut  | Beschreibung                                                                                                                                             |
+=================+==========+==================+==========================================================================================================================================================+
| nLimit          | Numeric  | Ja               | Maximale Anzahl Kategorien, welche geholt werden sollen                                                                                                  |
+-----------------+----------+------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------+
| cAssign         | String   | Ja               | Name der Smarty-Variable, in welchem das Array mit Kategorien gespeichert wird                                                                           |
+-----------------+----------+------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------+
| cKatAttrib      | String   | -                | Kategorieattribut, welches die gewünschten Kategorien selektiert. Es wird nur der Name berücksichtigt, Kategorieattribut-Wert wird nicht berücksichtigt. |
+-----------------+----------+------------------+----------------------------------------------------------------------------------------------------------------------------------------------------------+

Beispiel:
Wir versehen in JTL-Wawi mehrere Kategorien mit dem Kategorieattribut "frontpage". Als Wert kann ein beliebiger Wert z.B. 1 eingetragen werden (wird nicht beachtet).

Als nächstes kopieren wir die Datei ``/templates/Evo/page/index.tpl in das Child-Template`` ``/templates/Mein-Shop-Template/page/index.tpl``

Im Template ``/templates/Mein-Shop-Template/page/index.tpl`` fügen wir dann den folgenden Code ein:

.. code-block:: php

    {get_category_list cKatAttrib='frontpage' cAssign='oCategory_arr'}

    {if $oCategory_arr}
       <ul>
       {foreach name=Kategorieliste from=$oCategory_arr item=oCategory nLimit=2}
          <li>
             <a href="{$oCategory->cURL}">
             <img src="{$oCategory->cBildURL}" alt="" /><br />
             {$oCategory->cName}
             </a>
          </li>
       {/foreach}
       </ul>
    {/if}


**********************************************************
Artikelabhängig eigene Artikeldetails in details.tpl laden
**********************************************************

Ab JTL-Shop3.12 können Sie in JTL-Wawi ein Funktionsattribut "tpl_artikeldetails" nutzen und als Wert eine Ersatz-Datei für details.tpl z.B. "details_minimal.tpl" eingeben.
Legen Sie dazu zunächst eine Kopie der Datei ``/productdetails/details.tpl`` in Ihrem Child-Template an und nehmen Sie in der Datei ``/productdetails/details.tpl`` dann die gewünschten Änderungen vor (z.B. Lagerampel ausblenden, Artikelkurzbeschreibung über den Preis etc.).
Im Anschluß fügen Sie den Artikeln, für welche dieses Template geladen werden soll, ein Funktionsatttribut in JTL-Wawi hinzu: Name: "tpl_artikeldetails", Wert: "details.tpl".

Bei Variationskombinationen müssen Sie auch jedem Kindartikel das jeweilige Funktionsattribut zuweisen.

Beispiel im Demoshop: `https://demo.jtl-shop.de/SAT-Komplettanlage <https://demo.jtl-shop.de/SAT-Komplettanlage>`_

**************************************
Google Webfont für Grafikpreise nutzen
**************************************

Wählen Sie unter `http://www.google.com/webfonts <http://www.google.com/webfonts>`_ die gewünschte Webfont aus.
Im nachfolgenden Beispiel nehmen wir die Schriftart "Open Sans"

Fügen Sie folgende CSS-Regel in Ihrem Child-Template die ``/themes/css/theme.css`` oder, falls Sie mit LESS arbeiten, in die ``/themes/meinTheme/less/theme.less`` ein:

.. code-block:: smarty

    @import url("//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,300,700"); /* Diese CSS-Regel muss am Anfang der theme.css stehen */

    /* für alle Container mit Klasse .price neue Schriftart setzen */
    .price {
        font-family: 'Open Sans';
        color: red;
    }

.. _eigene-smarty-funktionen-integrieren:

************************************
Eigene Smarty-Funktionen integrieren
************************************

Um in Ihrem Template eigene Smarty-Funktionen nutzen zu können, legen sie im Verzeichnis ``/php`` eine Datei `` functions.php`` an.
Diese Datei wird automatisch beim Start geladen und ermöglicht das Registrieren von Smarty-Plugins.

.. note::

    Die so erstellte ``functions.php`` ersetzt das Original aus dem Vatertemplate vollständig! Es muss deshalb Sorge getragen werden, dass **alle** geerbten Funktionen ebenfalls implementiert werden!

Um die geerbte Funktionalität sicherzustellen, können Sie einfach eine komplette Kopie der Datei aus dem Vatertemplate erstellen und dann dort Ihre Änderungen vornehmen.
Das ist jedoch nicht sehr sinnvoll, da dann bei jedem Shop-Update alle Änderungen nachgezogen werden müßten. Besser ist es das Original einfach per ``include`` in das eigene Script einzubinden.

.. code-block:: php

    <?php
    /**
     * Eigene Smarty-Funktionen mit Vererbung aus dem Vatertemplate
     *
     * @global JTLSmarty $smarty
     */

    include realpath(__DIR__ . '/../../Evo/php/functions.php');

Danach können Sie Ihre eigenen Smarty-Funktionen implementieren und in Smarty registrieren. Im nachfolgenden Beispiel wird eine Funktion zur Berechnung der Kreiszahl PI eingebunden.

.. code-block:: php

    $smarty->registerPlugin('function', 'getPI', 'getPI');

    function getPI($precision)
    {
        $iterator = 1;
        $factor   = -1;
        $nenner   = 3;

        for ($i = 0; $i < $precision; $i++) {
            $iterator = $iterator + $factor / $nenner;
            $factor   = $factor * -1;
            $nenner  += 2;
        }

        return $iterator * 4;
    }

Die Funktion ``getPI``  kann dann im Template z.B. mit ``{getPi(12)}`` verwendet werden.

Überschreiben von bestehenden Funktionen
----------------------------------------

Das überschreiben von Funktionalitäten ist ebenfalls möglich. Hierzu muss lediglich die Registrierung der originalen Funktion zuerst mit ``$smarty->unregisterPlugin`` aufgehoben werden.
Danach kann die eigene Funktion registriert werden. Im nachfolgenden Beispiel wird die Funktion ``trans`` des Evo-Templates dahingehend erweitert, dass bei nichtvorhandener Übersetzung der
Text *-no translation-* ausgegeben wird.

.. code-block:: php

    $smarty->unregisterPlugin('modifier', 'trans')
           ->registerPlugin('modifier', 'trans', 'get_MyTranslation');

    /**
     * Input: ['ger' => 'Titel', 'eng' => 'Title']
     *
     * @param string|array $mixed
     * @param string|null $to - locale
     * @return null|string
     */
    function get_MyTranslation($mixed, $to = null)
    {
        // Aufruf der "geerbten" Funktion aus dem Original
        $trans = get_translation($mixed, $to);

        if (!isset($trans)) {
            $trans = 'no translation';
        }

        return $trans;
    }
