Portlets
========

.. |br| raw:: html

   <br />

Mit JTL-Shop 5.0 wurde eine neue Möglichkeit geschaffen, Seiten des Onlineshops "live im Frontend" mit diversen
Inhaltselementen anzureichern. |br|
Diese Inhaltselemente sind die sogenannten "Portlets". Sie können dem Onlineshop auch via Plugins hinzugefügt werden
und im Anschluss über den "*OnPage Composer*" ("OPC") auf einer beliebigen Seite des Onlineshops platziert werden.

In einem Plugin kann sich ein :ref:`Portlet-Verzeichnis <label_aufbau_portlets>` befinden, welches folgende Dateien
enthalten kann:

+------------------------------+---------------+---------------------------------------------------------------------+
| Dateiname                    | Notwendigkeit | Inhalt                                                              |
+==============================+===============+=====================================================================+
| ``[Portlet-Class-Name].php`` | oblig.        | Die PHP-Klasse des Portlets                                         |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``[Portlet-Class-Name].tpl`` | optional      | Template für die Darstellung des Portlets                           |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``[Portlet-Class-Name].css`` | optional      | Stylesheet des Portlets in der finalen Ansicht                      |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``preview.css``              | optional      | Stylesheet für die Anzeige des Portlets im Editor                   |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``icon.svg``                 | optional      | Icon für die Portlet-Palette im Editor                              |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``editor_init.js``           | optional      | Javascript, welches beim Initialisieren des Editors ausgeführt wird |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``configpanel.tpl``          | optional      | Template für ein benutzerdefiniertes Einstellungs-Modal             |
+------------------------------+---------------+---------------------------------------------------------------------+

Die Datei ``[Portlet-Class-Name].php`` beinhaltet die Portlet-Klasse und bildet somit das Herzstück des Portlets. |br|
Der Klassenname und der Name der Datei müssen selbstverständlich gleich lauten und die Klasse muss sich zudem im
Namensraum ``Plugin\[Plugin-ID]\Portlets\[Portlet-Class-Name]`` befinden. |br|
Weiterhin wird diese Klasse von ``JTL\OPC\Portlet`` abgeleitet.

**Beispiel:**

.. code-block:: php
   :caption: MyPortlet.php
   :emphasize-lines: 7

    <?php declare(strict_types=1);

    namespace Plugin\jtl_test\Portlets\MyPortlet;

    use JTL\OPC\Portlet;

    class MyPortlet extends Portlet
    {
        // ...
    }

Die Portlet-Klasse
------------------

Durch das Überschreiben von Methoden der Elternklasse ist es möglich, ein eigenes Portlet zu definieren. |br|
Folgende Methoden sind hierfür prädestiniert:

+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| Methode                                                                                        | Verwendung                                         |
+================================================================================================+====================================================+
| ``getPropertyDesc(): array`` :ref:`»» <label_getPropertyDesc>`                                 | Definieren der Portlet-Einstellungen               |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getPropertyTabs(): array`` :ref:`»» <label_getPropertyTabs>`                                 | Definieren der Reiter im Config-Modal des Portlets |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getButtonHtml(): string`` :ref:`»» <label_getButtonHtml>`                                    | Festlegen der Darstellung des Portlet-Buttons      |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getPreviewHtml(PortletInstance $instance): string`` :ref:`»» <label_getPreviewHtml>`         | Festlegen des Vorschau-Markups im *OPC-Editor*     |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getFinalHtml(PortletInstance $instance): string`` :ref:`»» <label_getFinalHtml>`             | Festlegen der finalen Darstellung des Portlets     |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getConfigPanelHtml(PortletInstance $instance): string`` :ref:`»» <label_getConfigPanelHtml>` | Ändern der Darstellung der Portlet-Konfiguration   |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+

Überschreibbare Methoden
""""""""""""""""""""""""

.. _label_getPropertyDesc:

``getPropertyDesc()``
"""""""""""""""""""""

Diese Methode definiert die einstellbaren Eigenschaften des Portlets und wie sie im Einstellungs-Dialog dargestellt
werden.

Jede Einstellung ("*Property*") wird durch den Schlüssel (*Property-ID*) des assoziativen Arrays dargestellt, welches
diese Methode zurückgibt.

Jede Property wird wiederum durch ein assoziatives Array beschrieben. |br|
Folgende Felder sind für alle Typen verfügbar:

:label: Bezeichnung im Config-Modal
:type:  :ref:`Property-Typ <label_PropertyTyp>`
:default: Vorbelegungs-Wert
:width: Breite, die das Input-Felds im Config-Modal einnimmt in % (default: 100)

**Beispiel:**

.. code-block:: php

    /**
     * @return array
     */
    public function getPropertyDesc(): array
    {
        return [
            'some-text'   => [
                'label'   => __('a text'),
                'type'    => 'text',
                'width'   => 30,
                'default' => __('Hello world!'),
            ],
            'type-select' => [
                'label'   => __('Alert Type'),
                'type'    => 'select',
                'options' => [
                    'success' => __('Success'),
                    'info'    => __('Info'),
                    'warning' => __('Warning'),
                    'danger'  => __('Danger'),
                ],
                'default' => 'info',
            ],
        ];
    }

.. _label_PropertyTyp:

Property-Typen
""""""""""""""

+---------------------------------------------------+----------------------------------------------------------------------+
| Typ                                               | Bedeutung |br|                                                       |
|                                                   | ggf. Optionen für diesen Property-Type                               |
+===================================================+======================================================================+
|  InputType::SELECT                                | Eine Select-Box mit verschiedenen Optionen |br|                      |
|                                                   | "options" - Auswahlmöglichkeiten, assoz. Array (Wert => Anzeigename) |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::RADIO                                  | Eine Radio-Button Gruppe mit verschiedenen Optionen |br|             |
|                                                   | "options" - Auswahlmöglichkeiten, assoz. Array (Wert => Anzeigename) |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::[TEXT|EMAIL|PASSWORD|NUMBER|DATE|TIME] | Einfache Eigenschaften diverser Typen                                |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::CHECKBOX                               | Checkbox, setzt ein boolesches Flag                                  |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::COLOR                                  | Ein RGB-Farbwert, konfigurierbar mit Color-Picker                    |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::IMAGE                                  | Stellt einen Bild-Uploader zur Verfügung und gibt die Bild-URL       |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::VIDEO                                  | Stellt einen Video-Uploader zur Verfügung und wählt eine URL         |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::TEXT_LIST                              | Liste von Strings                                                    |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::IMAGE_SET                              | Liste von Bildern (z. B. für Galerie oder Slider Portlets)           |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::ICON                                   | Auswahl eines FontAwesome Icons                                      |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::HIDDEN                                 | Verstecktes Input                                                    |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::HINT                                   | Hinweis                                                              |
+---------------------------------------------------+----------------------------------------------------------------------+


.. _label_getPropertyTabs:

``getPropertyTabs()``
"""""""""""""""""""""

Standardmäßig werden alle Properties des Portlets in einem einzelnen Tab dargestellt. |br|
Möchte man die Properties stattdessen in mehrere separate Tabs aufteilen, kann diese Methode überschrieben werden.

Die Methode gibt ein assoziatives Array zurück, mit dem die Properties des *Config-Modals* in verschiedene Reiter
einsortiert werden. |br|
Die gewünschte Reiterbeschriftung legt man über die Array-Schlüssel fest.

Neben einer expliziten Aufzählung benutzerdefinierter Properties können mit den Strings ``'styles'`` oder
``'animations'`` auch die mitgelieferten Eigenschaften in jeweils einem dedizierten Reiter bereitgestellt werden.

Mögliche Werte für die Reiter sind:

    * ``[<Property-ID 1>, <Property-ID 2>, ...]`` - ein Array von **Property-IDs**, die diesem Reiter angehören
    * ``'styles'`` - fügt dem Portlet die mitgelieferten Eigenschaften für **Styling** hinzu und zeigt sie in diesem
      Reiter an
    * ``'animations'`` - fügt dem Portlet die mitgelieferten Eigenschaften für **Animationen** hinzu und zeigt sie in
      diesem Reiter an

In ``getPropertyDesc()`` aufgeführte, aber nicht zugeordnete Properties werden automatisch dem
Standard-Reiter "Allgemein" zugewiesen.

**Beispiel:**

.. code-block:: php

    /**
     * @return array
     */
    public function getPropertyTabs(): array
    {
        return [
            'Icon'      => [
                'use-icon',
            ],
            __('Styles')    => 'styles',
            __('Animation') => 'animations',
        ];
    }


.. _label_getButtonHtml:

``getButtonHtml()``
"""""""""""""""""""

Diese Methode verändert die Darstellung des in der Palette gezeigten Portlet-Buttons.

**Beispiel:**

.. code-block:: php

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('fas fa-film');
    }

Im o. g. Beispiel wird ein Icon aus der *FontAwesome*-Familie gerendert anstatt der ``icon.svg``.

.. _label_getPreviewHtml:

``getPreviewHtml(PortletInstance $instance)``
"""""""""""""""""""""""""""""""""""""""""""""

Diese Methode bestimmt die Darstellung des Portlets im OPC. |br|
Es handelt sich hierbei noch nicht um die fertige Darstellung auf der Seite des Onlineshops!
Siehe dazu: ``getFinalHtml(PortletInstance $instance)``.

**Beispiel:**

.. code-block:: php

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getPreviewHtml(PortletInstance $instance): string
    {
        return $this->getHtml($instance, true);
    }


.. _label_getFinalHtml:

``getFinalHtml(PortletInstance $instance)``
"""""""""""""""""""""""""""""""""""""""""""

Diese Methode legt die Ausgabe für die finale Darstellung des Portlets fest.

**Beispiel:**

.. code-block:: php

    /**
     * @param PortletInstance $instance
     * @return string
     */
    public function getFinalHtml(PortletInstance $instance): string
    {
        return $this->getHtml($instance);
    }


.. _label_getConfigPanelHtml:

``getConfigPanelHtml(PortletInstance $instance)``
"""""""""""""""""""""""""""""""""""""""""""""""""

Die Konfiguration eines Portlets erfolgt im *Portlet-Config-Modal*. |br|
Die Darstellung dieses Modals wird vom Inhalt der Datei ``configpanel.tpl`` bestimmt, welche sich im Portlet-Verzeichnis
befinden kann.

Diese Methode liefert diesen Inhalt aus und kann ihn durch Überschreiben natürlich modifizieren. |br|

**Beispiel:**

.. code-block:: php

    /**
     * @param PortletInstance $instance
     * @return string
     * @throws \Exception
     */
    public function getConfigPanelHtml(PortletInstance $instance): string
    {
        return $this->getConfigPanelHtmlFromTpl($instance);
    }

Portlet-Templates schreiben
---------------------------

Portlet-Templates sind für die Darstellung eines Portlets verantwortlich. |br|
Standardmäßig wird die Smarty-Templatedatei ``<Portlet-Class>.tpl`` aus dem Portlet-Ordner geladen und gerendert,
und zwar sowohl für die OPC-Editor-Ansicht als auch für die finale Ansicht.

Im Template-Kontext sind folgende Smarty-Variablen definiert:

    * ``$instance`` - Die PortletInstance
    * ``$portlet`` - Das Portlet
    * ``$isPreview`` - Ein Flag für: ``true`` = "aktuell in Editor-Ansicht", ``false`` = "aktuell in finaler Ansicht"

Das gerenderte Markup sollte nur ein einziges DOM-Element ergeben.

Im *Editor-Modus* muss das Element das Attribut ``data-portlet="..."`` aufweisen. Hierin stehen alle Daten, die für
die Verarbeitung im Editor notwendig sind. |br|
Den Wert kann mit Hilfe der Methode ``{$instance->getDataAttribute()}`` bezogen werden. Mit
``{$instance->getProperty('<property-name>')}`` können Property-Werte der Portlet-Instanz abgefragt werden.

**Beispiel:**

.. code-block:: html+smarty
   :linenos:

    <h1 style="{$instance->getStyleString()}"
            {if $isPreview}data-portlet="{$instance->getDataAttribute()}"{/if}
            class="{$instance->getAnimationClass()}"
            {$instance->getAnimationDataAttributeString()}>
        {$instance->getProperty('text')}
    </h1>

Extras
""""""

Damit ein Portlet **Animationen** übernimmt (falls konfiguriert), fügt man dem Portlet-Element
folgenden Code hinzu: |br|
(siehe Zeilen 3 und 4 im obigen Beispiel)

.. code-block:: html+smarty
   :linenos:

   {* ... *}

            class="{$instance->getAnimationClass()}"
            {$instance->getAnimationDataAttributeString()}

Dies setzt die eingestellte Animations-CSS-Klasse und die Animations-Parameter über ``data-*``-Attribute.

Damit ein Portlet auch benutzerdefinierte **Style-Eigenschaften** übernimmt, fügt man dem Portlet-Element ebenfalls
noch folgendes Attribut hinzu:

.. code-block:: html+smarty

    style="{$instance->getStyleString()}"

Jede Portlet-Instanz hat eine nicht persistente, aber einheitliche ID und kann mit ``{$instance->getUid()}`` abgerufen
werden. Dies ist zum Beispiel für *Bootstrap-Tabs* nützlich.

Portlets mit Sub-Areas
----------------------

Portlets können Bereiche definieren, in denen weitere Portlets platziert werden.

Ein solcher Bereich ist ein Element mit der CSS-Klasse ``opc-area``. |br|
Das Area-Element muss für die Editor-Ansicht eine ID mittels ``data-area-id="{$areaId}"``-Attribut definieren,
wobei ``$areaId`` ein für das Portlet einheitlicher Bezeichner ist.

Für die **Editor-Ansicht** muss der Inhalt des Elements wie folgt gerendert werden:

.. code-block:: smarty

    {$instance->getSubareaPreviewHtml($areaId)}

Für die **finale Ansicht** muss der Inhalt des Elements wie folgt gerendert werden:

.. code-block:: smarty

    {$instance->getSubareaFinalHtml($areaId)}

**Beispiel:**

.. code-block:: html+smarty

    <div {if $isPreview}data-area-id="{$areaId}"{/if} class="opc-area">
        {if $isPreview}
            {$instance->getSubareaPreviewHtml($areaId)}
        {else}
            {$instance->getSubareaFinalHtml($areaId)}
        {/if}
    </div>

Portlet-Übersetzung
-------------------

In Portlet-Klasse und Templates können Sprachvariablen abgerufen werden. |br|
Dies geschieht mittels:

.. code-block:: smarty

    {__("Text-ID")}


Übersetzungen können im ``.mo``-Dateiformat im Language-Verzeichnis des Plugins unter ``portlets/`` abgelegt
werden. |br|
Konkret wäre das dann:

.. code-block:: console

    plugins/[plugin-id]/locale/[language-tag]/portlets/[Portlet-Class].mo

Wird eine Übersetzung nicht gefunden, wird deren *Text-ID* unverändert ausgegeben.

Portlet-Vorlagen - Blueprints
-----------------------------

*Blueprints* sind wiederverwendbare Portlet-Kompositionen bzw. -Vorlagen.

Diese Vorlagen können im *OPC-Editor* erstellt und exportiert werden. |br|
Sie finden *Blueprints* im Reiter "Vorlagen", wo sie auch importiert werden können.

Ebenso können Sie natürlich auch mit einem Plugin *Blueprints* ausliefern. |br|
Nähere Informationen dazu finden Sie im Abschnitt ":ref:`label_infoxml_blueprints`".
