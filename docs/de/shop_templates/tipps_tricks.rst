Tipps und Tricks
================

.. |br| raw:: html

    <br />

Bei diesen Tipps & Tricks wird davon ausgegangen, dass
ein :doc:`eigenes Child-Template </shop_templates/eigenes_template>` angelegt wurde. |br|
Dies stellt unter anderem sicher, dass das entsprechende (Parent-)Template weiterhin updatefähig bleibt.

.. note::

    Die JTL-Templates des Shops basieren auf leicht unterschiedlichen Technologien zur Seitendarstellung: |br|
    Das **EVO-Template** nutzt als CSS-Spracherweiterung **LESS**, |br|
    Das **NOVA-Template** nutzt hierfür **Sass** (SCSS).

Beide Technologien sind Erweiterungen der herkömmlichen CSS-Syntax und schaffen hauptsächlich die Möglichkeit, CSS
ähnlich komfortabel zu schreiben wie Programm-Code. |br|
Diese Technologien setzen einen Pre-Prozessor voraus, um die jeweilige Style-Sprache in CSS zu übersetzen.
Je nach Template und Shop-Version gibt es hierfür unterschiedliche Werkzeuge
(siehe Abschnitt ":doc:`Theme editieren <theme_edit>`").

LESS und SCSS
-------------

Mithilfe von LESS und SCSS können Sie verschiedene Werte für Farben, Schriften, Abstände und Rahmen
als *Variablen* definieren, die dann in allen oder auch nur einigen Templates benutzt werden können. |br|
Um nun schnell und einfach das Aussehen von JTL-Shop zu verändern, bietet es sich an, diese LESS- oder
SCSS-Variablen des Templates in einem Child-Template zu überschreiben.

Wie Sie mit LESS-Dateien in Ihrem Child-Template arbeiten, finden Sie im
Bereich: :ref:`Arbeiten mit LESS <arbeiten-mit-less>`. |br|
In diesem Abschnitt wird ebenso beschrieben, wie Sie die Variablen des Templates mit Ihrem Child-Template überschreiben
können.

Javascript in Templatedateien
-----------------------------

Jede Templatedatei (``*.tpl``) wird von der Template-Engine `Smarty` gelesen und interpretiert. Hierbei nutzt `Smarty`
für die Auszeichnungen von Variablen- und Code-Ersetzungen geschweifte Klammern (``{``, ``}``). |br|
Geschweifte Klammern sind aber ebenso in Javascript ein Sprachelement um beispielsweise Code-Blöcke zu umschließen.

Damit sich diese beiden unterschiedlichen Auszeichnungsarten nicht überschneiden, ist es möglich,
Javascript-Code von der Bearbeitung durch `Smarty` auszuschließen. |br|
Hierfür existieren zwei unterschiedliche Ansätze.

Für kleinere Javascript-Codefragmente ist es möglich, alle öffnenden und schließenden geschweiften
Klammern durch zwei `Smarty`-Funktionen zu ersetzen - ``{ldelim}`` für ``{`` und ``{rdelim}`` für ``}`` - und sie
anschließend von Smarty wieder als echte Klammern ausgeben zu lassen.

**Beispiel:**

.. code-block:: smarty

    Dies ist eine smarty.tpl Datei,<br>
    die ein Javascript enthält.<br>

    <script>
        function helloWorld() {ldelim}
            alert('Hello World');
        {rdelim}

    </script>

(Siehe auch: `Smarty Docs ldelim,rdelim <https://www.smarty.net/docsv2/de/language.function.ldelim.tpl>`_)

Für umfangreicheren Code empfehlen wir allerdings die übersichtlichere Variante mit den zwei Smarty-Tags
``{literal}`` und ``{/literal}``. |br|
Mit diesen beiden Tags läßt sich ein größerer Javascript-Block ganz einfach umschließen und von der Verarbeitung durch
`Smarty` ausschließen.

**Beispiel:**

.. code-block:: smarty

    Dies ist eine smarty.tpl Datei,<br>
    die ein Javascript enthält.<br>

    {literal}
    <script>
        function helloWorld() {
            alert('Hello World');
        }

    </script>
    {/literal}

(Siehe auch: `Smarty Docs literal <https://www.smarty.net/docsv2/de/language.function.literal.tpl>`_)

Möchten Sie in Ihrem Javascript weiterhin Variablen durch `Smarty` ersetzen lassen, kann der ``literal``-Block
auch vor der `Smarty`-Variable beendet und nach ihr wieder begonnen werden.

**Beispiel:**

.. code-block:: smarty

    Dies ist eine smarty.tpl Datei,<br>
    die ein Javascript enthält.<br>

    {literal}
    <script>
        function helloWorld() {
            alert({/literal}'{$HelloWorldText}'{literal});
        }

    </script>
    {/literal}

In diesem Fall hätten Sie zwei getrennte ``literal``-Blöcke, die `Samrty` nicht interpretiert. |br|
Die Variable in der Mitte wird dann wie gewohnt von `Smarty` ersetzt.

Theme-Variablen
---------------

Diese Variablen sind, soweit möglich, in einigen wenigen Dateien zusammengefasst.

Im *EVO-Template* liegen sie im Ordner ``<Shop-Root>/templates/Evo/themes/bootstrap/less/variables.less``. |br|
Im *NOVA-Template* liegen sie im Ordner ``<Shop-Root>templates/NOVA/themes/clear/sass/_variables.scss``.

.. hint::

    Es gibt noch viele weitere Variablen in der ``variables.less`` bzw. ``_variables.scss``.
    Sehen Sie sich diese Datei(en) an und probieren Sie es aus, einige Werte zu ändern. |br|

Merkmale abfragen
-----------------

Merkmale dienen, auf der Artikeldetailseite, der Auflistung bestimmter Artikeleigenschaften wie z. B. der Farbe des
Produktes. |br|
Merkmale werden in `JTL-Wawi <https://guide.jtl-software.de/jtl-wawi/artikel/merkmale-anlegen/>`_, pro
Sprache, definiert.

**Template-Code** |br|
im EVO-Template: ``templates/Evo/productdetails/attributes.tpl`` :

.. code-block:: smarty

    {block name='productdetails-attributes-shop-attributes'}
        {foreach $Artikel->Attribute as $Attribut}
            <div class="list-group-item attr-custom">
                <div class="list-group-item-heading">{$Attribut->cName}: </div>
                <div class="list-group-item-text attr-value">{$Attribut->cWert}</div>
            </div>
        {/foreach}
    {/block}

im NOVA-Template: ``templates/NOVA/productdetails/attributes.tpl`` :

.. code-block:: smarty

    {block name='productdetails-attributes-shop-attributes'}
        {foreach $Artikel->Attribute as $Attribut}
            <tr class="attr-custom">
                <td class="h6">{$Attribut->cName}: </td>
                <td class="attr-value">{$Attribut->cWert}</td>
            </tr>
        {/foreach}
    {/block}

Der Zugriff ist auch über ein assoziatives Array möglich:

.. code-block:: smarty

    {assign var="attrname" value="Name des Funktionsattributes hier eintragen"}
    {$Artikel->AttributeAssoc.$attrname}

Funktionsattribute
------------------

In JTL-Wawi können Sie in den Artikeldetails im Reiter "Attribute/Merkmale" sogenannte Funktionsattribute im
Artikel hinterlegen. |br|
Anders als Artikelattribute (siehe vorheriger Abschnitt "Merkmale") werden Funktionsattribute nicht mehrsprachig
definiert, da sie Funktionalitäten und Aktionen im Shop auslösen bzw. das Template steuern können. |br|
(Siehe auch: `Beispielartikel mit Funktionsattributen im JTL-Demoshop <https://demo.jtl-shop.de/Frei-definierte-Attribute>`_)

Funktionsattribute am Artikel stehen templateseitig in den Artikeldetails als Variable zur Verfügung und können
artikelbezogen im Frontend abgefragt werden.

Funktionsattribute können im Template per ``{$Artikel->FunktionsAttribute.funktionsattributname}`` ausgelesen
werden. |br|
("*funktionsattributname*" reflektiert hier den Namen des Funktionsattributes, wie es in JTL-Wawi definiert wurde)

Natürlich können Sie auch eigene Funktionsattribute in JTL-Wawi anlegen und diese im Shop-Template nutzen.

.. attention::

    Schreiben Sie Funktionsattributnamen auch dann in Kleinbuchstaben, wenn deren Namen in
    JTL-Wawi Großbuchstaben enthalten.

**Beispiel:**

Sie möchten ein Funktionsattribut ``highlightclass`` neu erstellen und abfragen sowie abhängig davon den Hintergrund
der Kurzbeschreibung auf der Artikeldetailseite in Gelb erscheinen lassen, quasi "highlighted".

Wir gehen hier wieder von einem eigenen Child-Template aus (siehe ":ref:`label-eigenes-template`"). |br|
Definieren Sie die CSS-Klasse in einer eigenen ``custom.css`` Datei. |br|
Geladen wird diese CSS-Klasse via ``template.xml``, Tag ``<Minify><CSS Name="clear.css">...</CSS>`` für das jeweilige
Theme. In NOVA ist es das Theme "clear". |br|

.. code-block:: css

    /* custom.css */
    .highlightclass {
        background-color: yellow;
    }

Das neue Funktionsattribut soll den Name ``highlightclass`` tragen und muss natürlich noch in JTL-Wawi
angelegt werden. |br|
Rufen Sie dazu in JTL-Wawi die Artikelstammdaten des Zielartikels auf und wechseln Sie in den Reiter
"Attribute/Merkmale". Diese zweigeteilte Maske beinhaltet im oberen Bereich "Artikelattribute" die Attribute,
die wir anreichern wollen. |br|
Klicken Sie rechts auf :guilabel:`Attribute verwalten` und wählen Sie in der folgenden Maske unter "Attribut
anlegen" den Unterpunkt "neues Funktionsattribut" aus. Vergeben Sie einen Namen und legen Sie den Onlineshop
fest, an den dieses Attribut gesendet werden soll.

Ändern Sie nun in der Template-Datei ``templates/NOVA/productdetails/details.tpl`` den folgenden Code-Block so ab, |br|
dass Sie den Wert des Funktionsattributes einfügen können, wenn er gesetzt ist:

.. code-block:: smarty
    :emphasize-lines: 4

    /* productdetails/details.tpl */
    {block name='productdetails-details-info-description'}
        {include file='snippets/opc_mount_point.tpl' id='opc_before_short_desc'}
        <div class="{if !empty($Artikel->FunktionsAttribute.highlightclass)}{$Artikel->FunktionsAttribute.highlightclass} {/if}shortdesc mb-2 d-none d-md-block" itemprop="description">
            {$Artikel->cKurzBeschreibung}
        </div>
    {/block}

**Sonderfall: "Sonderzeichen im Funktionsattributnamen"** |br|
Bei Sonderzeichen im Namen des Funktionsattributes können Sie wie folgt darauf zugegreifen:

.. code-block:: smarty

    {assign var="fktattrname" value="größe"}
    {$Artikel->FunktionsAttribute.$fktattrname}

Kategorieattribute abfragen
---------------------------

Ähnlich den Funktionsattributen eines Artikels lassen sich in der JTL-Wawi, in den Kategoriedetails, auch
Kategorieattribute definieren. Diese werden beim Synchronisieren zum Onlineshop übertragen und können dort
Steuerungsaufgaben übernehmen können.

Beginnend mit Shop-Version 4.0 werden Kategorie-Funktionsattribute und Kategorieattribute unterschieden. |br|
Kategorie-Funktionsattribute (``categoryFunctionAttributes``) sind key/value-Paare die zur Aufnahme der
Funktionsattribute dienen, während Kategorieattribute in Form von "*array of objects*" lokalisierte Kategorieattribute
aufnehmen. |br|
Funktionsattribute dienen der Steuerung von Aktionen im Onlineshop selbst nur im Template, während
Kategorieattribute lokalisierte Werte - passend zur eingestellten Shop-Sprache - enthalten können. |br|

Diese Kategorieattribute können im Template wie folgt abgefragt werden:

**PHP-Code für Funktionsattribut** |br|
(Einbindung in Plugins oder in die :ref:`php/functions.php <eigene-smarty-funktionen-integrieren>` des Templates):

.. code-block:: php
    :emphasize-lines: 4

    $Kategorien = new KategorieListe();
    $Kategorien->getAllCategoriesOnLevel( 0 );
    foreach ($Kategorien->elemente as $Kategorie) {
      $funktionsWert = $Kategorie->categoryFunctionAttributes['meinkategoriefunktionsattribut'];
    }

**PHP-Code für lokalisiertes Attribut** |br|
(Einbindung als Plugin oder in die :ref:`php/functions.php <eigene-smarty-funktionen-integrieren>` des Templates):

.. code-block:: php
    :emphasize-lines: 4

    $Kategorien = new KategorieListe();
    $Kategorien->getAllCategoriesOnLevel( 0 );
    foreach ($Kategorien->elemente as $Kategorie) {
      $attributWert = $Kategorie->categoryFunctionAttributes['meinkategorieattribut']->cWert;
    }

**Template-Code** |br|
zur Steuerung mittels Kategorie-Funktionsattributen in der Kategorieansicht
(am besten mit der :doc:`Smarty Debug-Konsole </shop_programming_tips/debug>` nach dem eigenen Kategorieattribut
suchen):

.. code-block:: smarty

    {if $oNavigationsinfo->oKategorie->KategorieAttribute.meinkategoriefunktionsattribut === 'machedies'}
        <span>MacheDies</span>
    {else}
        <span>MacheDas</span>
    {/if}

**Template-Code** |br|
zur Ausgabe eines lokalisierten Kategorieattributs in Kategorieansicht
(am besten mit der :doc:`Smarty Debug-Konsole </shop_programming_tips/debug>` nach dem eigenen Kategorieattribut
suchen):

.. code-block:: smarty

    <span>{$oNavigationsinfo->oKategorie->KategorieAttribute.meinkategorieattribut->cWert}</span>

Eigene Sprachvariablen verwenden
--------------------------------

Um eigene Sprachvariablen zu erstellen, öffnen Sie im Backend von JTL-Shop die "*Sprachverwaltung*"
(Einstellungen -> Sprachverwaltung) und klicken Sie auf die Schaltfläche :guilabel:`Variable hinzufügen` . |br|
Per Smarty-Funktion ``{lang}`` und den Parametern ``key`` und ``section`` können Sie diese Variablen im Template
verwenden.

**Beispiel:**

Fügen Sie über die *Sprachverwaltung* folgende Sprachvariable hinzu:

    :Sprachsektion:  custom
    :Variable:       "safetyBoxTitle"
    :Wert Deutsch:   "SSL-Verschlüsselung"
    :Wert Englisch:  "SSL-Encryption"

Template-Code:

.. code-block:: smarty

    {lang key="safetyBoxTitle" section="custom"}

PHP-Code (z. B. in Plugins, wobei hier im Beispiel der Plugin-Kontext gegeben ist; zu erkennen am ``$this->``):

.. code-block:: php

    $langVar = $this->getLocalization()->getTranslation('safetyBoxTitle');

**Sprachvariable als Smarty-Variable speichern und abfragen:**

Template-Code:

.. code-block:: smarty

    {* Sprachvariable einfügen *}
    {lang key="safetyBoxTitle" section="custom"}

    {* Variable mit assign zuweisen *}
    {lang assign="testVariableSafetyBoxTitle" key="safetyBoxTitle" section="custom"}

    {* die zuvor zugewiesene Variable kann nun normal aufgerufen oder abgefragt werden *}
    {if $testVariableSafetyBoxTitle eq "SSL-Verschlüsselung"}<span class="de">{$testVariableSafetyBoxTitle}</span>{else}<span>{$testVariableSafetyBoxTitle}</span>{/if}


.. _eigene-smarty-funktionen-integrieren:

Erstellen eigener Smarty-Funktionen
-----------------------------------

Um eigene Smarty-Funktionen zu registrieren, gibt es template-abhängig zwei Wege.

Evo-Template
++++++++++++

Wenn Sie ein Child-Template des Evo-Templates verwenden, legen Sie im Wurzelverzeichnis Ihres Child-Templates
einen Ordner ``php/`` an. Erzeugen Sie dort eine Datei namens ``functions.php``.

Um die Update-Fähigkeiten Ihres Parent-Templates weiterhin zu gewährleisten, fügen Sie folgenden Inhalt ein:

.. code-block:: php
    :emphasize-lines: 6

    <?php
    /**
     * @global JTLSmarty $smarty
     */

    include realpath(__DIR__ . '/../../Evo/php/functions.php');


.. attention::

    Die so erstellte ``functions.php`` ersetzt das Original aus dem Vatertemplate vollständig!

Theoretisch könnten Sie einfach eine komplette Kopie der Datei aus dem Parent-Template erstellen und dort Ihre
Änderungen vornehmen. Das ist jedoch nicht sehr sinnvoll, da dann bei jedem Update von JTL-Shop alle Änderungen
nachgezogen werden müssten. |br|
Besser ist es, das Original einfach per ``include`` in das eigene Script einzubinden (siehe Beispiel oben).

NOVA-Template
+++++++++++++

Wenn Sie ein Child-Template des NOVA-Templates verwenden, erstellen Sie im Wurzelverzeichnis Ihres Child-Templates
eine PHP-Klasse namens ``Bootstrap.php`` mit folgendem Inhalt:

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Template\[NOVA-child-name];

    /**
     * Class Bootstrap
     * @package Template\[NOVA-child-name]
     */
    class Bootstrap extends \Template\NOVA\Bootstrap
    {
        // eigene Methoden
    }


.. hint::

    Die PHP-Datei, wie auch die PHP-Klasse, wird beim Start automatisch geladen und ermöglicht das Registrieren
    von Smarty-Plugins. |br|
    Danach können Sie Ihre eigenen Smarty-Funktionen implementieren und in Smarty registrieren.

Funktionen im Evo-Child registrieren
++++++++++++++++++++++++++++++++++++

Im nachfolgenden Beispiel wird eine Funktion zur Berechnung der Kreiszahl PI in die PHP-Datei ``functions.php``
eingebunden und in Smarty registriert:

.. code-block:: php

    $smarty->registerPlugin('function', 'getPI', 'getPI');

    function getPI($precision)
    {
        $iterator = 1;
        $factor   = -1;
        $nenner   = 3;

        for ($i = 0; $i < $precision; $i++) {
            $iterator = $iterator + $factor / $nenner;
            $factor  *= -1;
            $nenner  += 2;
        }

        return $iterator * 4;
    }


Funktionen im NOVA-Child registrieren
+++++++++++++++++++++++++++++++++++++

Im nachfolgenden Beispiel wird eine Methode zur Berechnung der Kreiszahl PI in die ``Bootstrap``-Klasse eingebunden und
in Smarty registriert:

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Template\[NOVA-child-name];

    use Smarty;

    /**
     * Class Bootstrap
     * @package Template\[NOVA-child-name]
     */
    class Bootstrap extends \Template\NOVA\Bootstrap
    {
        public function boot(): void
        {
            parent::boot();
            try {
                $this->getSmarty()->registerPlugin(Smarty::PLUGIN_FUNCTION, 'getPI', [$this, 'getPI']);
            } catch (\SmartyException $e) {
                throw new \RuntimeException('Problems during smarty instantiation: ' . $e->getMessage());
            }
        }

        public function getPI($args)
        {
            $precision = $args['precision'];
            $iterator  = 1;
            $factor    = -1;
            $nenner    = 3;

            for ($i = 0; $i < $precision; $i++) {
                $iterator = $iterator + $factor / $nenner;
                $factor   *= -1;
                $nenner   += 2;
            }

            return $iterator * 4;
        }
    }

Funktionen nutzen
+++++++++++++++++

Die Funktion ``getPI()``  kann dann im Template z. B. mit ``{getPI precision=12}`` verwendet werden.


Überschreiben bestehender Funktionen
------------------------------------

Das Überschreiben von Funktionalitäten ist ebenfalls möglich.

Funktionen im Evo-Child überschreiben
+++++++++++++++++++++++++++++++++++++

In Ihrem Evo-Child muss lediglich die Registrierung der originalen Funktion zuerst mit ``$smarty->unregisterPlugin``
aufgehoben werden. |br|
Danach kann die neue Funktion registriert werden.

Im nachfolgenden Beispiel wird die Funktion ``trans`` des EVO-Templates dahingehend erweitert, dass bei
nicht vorhandener Übersetzung der Text "*-no translation-*" ausgegeben wird.

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
            $trans = '-no translation-';
        }

        return $trans;
    }

Funktionen im NOVA-Child überschreiben
++++++++++++++++++++++++++++++++++++++

In Ihrem NOVA-Child überschreiben sie Funktionen, indem Sie die entsprechende Basisklasse des NOVA-Templates
``templates/NOVA/Plugins.php`` mit einer eigenen Klasse in Ihrem NOVA-Child ``templates/[NOVA-child-name]/Plugins.php``
erweitern.

Im nachfolgenden Beispiel wird die Funktion ``getTranslation()`` des NOVA-Templates dahingehend erweitert, dass bei
nicht vorhandener Übersetzung der Text "*-no translation-*" ausgegeben wird.

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Template\[NOVA-child-name];

    use JTL\Shop;

    /**
     * Class Bootstrap
     * @package Template\[NOVA-child-name]
     */
    class Plugins extends \Template\NOVA\Plugins
    {
        public function getTranslation($mixed, $to = null): ?string
        {
            $to = $to ?: Shop::getLanguageCode();

            if ($this->hasTranslation($mixed, $to)) {
                return \is_string($mixed) ? $mixed : $mixed[$to];
            }

            return '-no translation-';
        }
    }



Unabhängige Artikellisten erzeugen
----------------------------------

Ab JTL-Shop Version 3.10, bis einschließlich 5.0, ist es möglich, eigene Artikel-Arrays über eine
Smarty-Funktion ``{get_product_list}`` zu erzeugen. |br|
Dies kann beispielsweise dazu genutzt werden, um auf bestimmte Artikel(-gruppen) abseits von Cross-Selling gesondert
aufmerksam zu machen.

Der Funktion können die folgenden Parameter übergeben werden:

+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Parametername              | Typ     | Pflichtattribut | Beschreibung                                                                                                                                                     |
+============================+=========+=================+==================================================================================================================================================================+
| ``nLimit``                 | Numeric | Ja              | Maximale Anzahl Artikel, welche geholt werden sollen                                                                                                             |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``cAssign``                | String  | Ja              | Name der Smarty-Variable, in welcher das Array mit Artikeln gespeichert wird                                                                                     |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kKategorie``             | Numeric | --              | Primärschlüssel einer Kategorie, siehe Datenbank ``tkategorie.kKategorie``                                                                                       |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kHersteller``            | Numeric | --              | Primärschlüssel eines Herstellers, siehe Datenbank ``thersteller.kHersteller``                                                                                   |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kArtikel``               | Numeric | --              | Primärschlüssel eines Artikels, siehe Datenbank ``tartikel.kArtikel``                                                                                            |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kSuchanfrage``           | String  | --              | Primärschlüssel einer Suchanfrage, siehe Datenbank ``tsuchcache.kSuchCache``                                                                                     |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kMerkmalWert``           | String  | --              | Primärschlüssel eines Merkmalwerts, siehe Datenbank ``tmerkmalwert.kMerkmalwert``                                                                                |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kSuchspecial``           | Numeric | --              | Filterung nach *Suchspecials*, siehe Tabelle unten "*Suchspecialschlüssel*"                                                                                      |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kKategorieFilter``       | Numeric | --              | Zusätzlicher Filter nach einer Kategorie in Kombination mit einem Hauptfilter z. B. ``kHersteller.``                                                             |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``kHerstellerFilter``      | Numeric | --              | Zusätzlicher Filter nach einem Hersteller in Kombination mit einem Hauptfilter z. B. ``kKategorie``. Primärschlüssel siehe Datenbank ``thersteller.kHersteller`` |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``nBewertungSterneFilter`` | Numeric | --              | Zusätzlicher Filter nach Mindest-Durschnittsbewertung in Kombination mit einem Hauptfilter, z. B. kKategorie.                                                    |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``cPreisspannenFilter``    | String  | --              | Zusätzlicher Filter nach Preisspanne in Kombination mit einem Hauptfilter, z. B. ``kKategorie``. Schreibweise für "von 20 € bis 40,99 €": "20_40.99"             |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``nSortierung``            | Numeric | --              | Gibt an, nach welchem Artikelattribut sortiert werden soll. Details siehe Tabelle unten "*Sortierungsschlüssel*"                                                 |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``cMerkmalFilter``         | String  | --              | Primärschlüssel der Merkmalwerte durch Semikolon getrennt, z. B. "100;101". Primärschlüsselangabe siehe Datenbank ``tmerkmalwert.kMerkmalwert``                  |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``cSuchFilter``            | String  | --              | Primärschlüssel der Suchfilter durch Semikolon getrennt, z. B. "200;201". Primärschlüsselangabe siehe Datenbank ``tsuchcache.kSuchCache``                        |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| ``cSuche``                 | String  | --              | Suchbegriff, z. B. "zwiebel ananas baguette"                                                                                                                     |
+----------------------------+---------+-----------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------+

**Beispiel**

Binden Sie den folgenden Code im Template ein:

.. code-block:: smarty

    <h2>Unsere Verkaufschlager aus dem Bereich Gemüse</h2>
    {get_product_list kKategorie=9 nLimit=3 nSortierung=11 cAssign="myProducts"}
    {if $myProducts}
      <ul>
      {foreach name=custom from=$myProducts item=oCustomArtikel}
        <li><a href="{$oCustomArtikel->cURLFull}">{$oCustomArtikel->cName}</a></li>
      {/foreach}
      </ul>
    {/if}


**Sortierungsschlüssel**

+-------------------------+------+--------------------------+
| Name                    | Wert | Konstante                |
+=========================+======+==========================+
| Standard                | 100  | SEARCH_SORT_STANDARD     |
+-------------------------+------+--------------------------+
| Artikelname von A bis Z | 1    | SEARCH_SORT_NAME_ASC     |
+-------------------------+------+--------------------------+
| Artikelname von Z bis A | 2    | SEARCH_SORT_NAME_DESC    |
+-------------------------+------+--------------------------+
| Preis aufsteigend       | 3    | SEARCH_SORT_PRICE_ASC    |
+-------------------------+------+--------------------------+
| Preis absteigend        | 4    | SEARCH_SORT_PRICE_DESC   |
+-------------------------+------+--------------------------+
| EAN                     | 5    | SEARCH_SORT_EAN          |
+-------------------------+------+--------------------------+
| neuste zuerst           | 6    | SEARCH_SORT_NEWEST_FIRST |
+-------------------------+------+--------------------------+
| Artikelnummer           | 7    | SEARCH_SORT_PRODUCTNO    |
+-------------------------+------+--------------------------+
| Verfügbarkeit           | 8    | SEARCH_SORT_AVAILABILITY |
+-------------------------+------+--------------------------+
| Gewicht                 | 9    | SEARCH_SORT_WEIGHT       |
+-------------------------+------+--------------------------+
| Erscheinungsdatum       | 10   | SEARCH_SORT_DATEOFISSUE  |
+-------------------------+------+--------------------------+
| Bestseller              | 11   | SEARCH_SORT_BESTSELLER   |
+-------------------------+------+--------------------------+
| Bewertungen             | 12   | SEARCH_SORT_RATING       |
+-------------------------+------+--------------------------+

**Suchspecialschlüssel**

+-----------------------+----------+---------------------------------+
| Name                  | Wert     | Konstante                       |
+=======================+==========+=================================+
| Bestseller            | 1        | SEARCHSPECIALS_BESTSELLER       |
+-----------------------+----------+---------------------------------+
| Sonderangebote        | 2        | SEARCHSPECIALS_SPECIALOFFERS    |
+-----------------------+----------+---------------------------------+
| Neu im Sortiment      | 3        | SEARCHSPECIALS_NEWPRODUCTS      |
+-----------------------+----------+---------------------------------+
| Top-Angebote          | 4        | SEARCHSPECIALS_TOPOFFERS        |
+-----------------------+----------+---------------------------------+
| In Kürze verfügbar    | 5        | SEARCHSPECIALS_UPCOMINGPRODUCTS |
+-----------------------+----------+---------------------------------+
| Top bewertet          | 6        | SEARCHSPECIALS_TOPREVIEWS       |
+-----------------------+----------+---------------------------------+
| Ausverkauft           | 7        | SEARCHSPECIALS_OUTOFSTOCK       |
+-----------------------+----------+---------------------------------+
| Auf Lager             | 8        | SEARCHSPECIALS_ONSTOCK          |
+-----------------------+----------+---------------------------------+
| Vorbestellung möglich | 9        | SEARCHSPECIALS_PREORDER         |
+-----------------------+----------+---------------------------------+
