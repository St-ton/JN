Portlets
========

.. |br| raw:: html

   <br />

As of JTL-Shop 5.0 you now have the option to enrich "live on the front end" pages of the online shop with various
content elements. |br|
These content elements are called "portlets". They can also be added to the online shop via plug-ins
and subsequently placed on any page of the shop via the "*OnPage Composer*" ("OPC").

In a plug-in there may be a :ref:`Portlet Directory <label_aufbau_portlets>` which can contain any of the
following files:

+------------------------------+---------------+---------------------------------------------------------------------+
| File name                    | Need          | Content                                                             |
+==============================+===============+=====================================================================+
| ``[Portlet-Class-Name].php`` | obligatory    | The PHP class of the portlet                                        |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``[Portlet-Class-Name].tpl`` | optional      | Template for the portlet’s appearance                               |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``[Portlet-Class-Name].css`` | optional      | Stylesheet of the portlet in the final view                         |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``preview.css``              | optional      | Stylesheet for displaying the portlet in the editor                 |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``icon.svg``                 | optional      | Icon for the portlet palette in the editor                          |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``editor_init.js``           | optional      | Javascript file, which is executed when the editor is initialised   |
+------------------------------+---------------+---------------------------------------------------------------------+
| ``configpanel.tpl``          | optional      | Template for a user-defined settings modal                          |
+------------------------------+---------------+---------------------------------------------------------------------+

The file ``[Portlet-Class-Name].php`` contains the portlet class and therefore forms the portlet's core. |br|
The class name and file name must be identical, and the class must also be in the
namespace ``Plugin\[Plugin-ID]\Portlets\[Portlet-Class-Name]``. |br|
Additionally, this class is derived from ``JTL\OPC\Portlet``.

**Example:**

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

The portlet class
-----------------

By overriding methods of the parent class, it is possible to define a custom portlet. |br|
The following methods are made for this purpose:

+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| Method                                                                                         | Application                                        |
+================================================================================================+====================================================+
| ``getPropertyDesc(): array`` :ref:`»» <label_getPropertyDesc>`                                 | To define the portlet settings                     |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getPropertyTabs(): array`` :ref:`»» <label_getPropertyTabs>`                                 | To define the tabs in the portlet’s config modal   |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getButtonHtml(): string`` :ref:`»» <label_getButtonHtml>`                                    | To set the appearance of the portlet button        |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getPreviewHtml(PortletInstance $instance): string`` :ref:`»» <label_getPreviewHtml>`         | To set the preview markup in the *OPC Editor*      |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getFinalHtml(PortletInstance $instance): string`` :ref:`»» <label_getFinalHtml>`             | To set the final appearance of the portlet         |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+
| ``getConfigPanelHtml(PortletInstance $instance): string`` :ref:`»» <label_getConfigPanelHtml>` | To change the portlet configuration display        |
+------------------------------------------------------------------------------------------------+----------------------------------------------------+

Overridable methods
"""""""""""""""""""

.. _label_getPropertyDesc:

``getPropertyDesc()``
"""""""""""""""""""""

This method defines the adjustable properties of the portlet and how they are displayed
in the settings dialog.

Each setting ("*property*") is represented by the key (*property ID*) of the associative array returned by
this method.

Each property is in turn declared by an associative array. |br|
The following fields are available for all property types:

:label: Name in the config modal
:type:  :ref:`Property-Typ <label_PropertyTyp>`
:default: Default values
:width: Width that the input field occupies in the config modal in %. (default: 100)

**Example:**

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

Property types
""""""""""""""

+---------------------------------------------------+----------------------------------------------------------------------+
| Type                                              | Meaning |br|                                                         |
|                                                   | or options for this property type                                    |
+===================================================+======================================================================+
|  InputType::SELECT                                | A select box with various options |br|                               |
|                                                   | "options" - selection options, assoc. array (value => display name)  |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::RADIO                                  | A radio button group with various options |br|                       |
|                                                   | "options" - selection options, assoc. array (value => display name)  |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::[TEXT|EMAIL|PASSWORD|NUMBER|DATE|TIME] | Simple properties of different types                                 |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::CHECKBOX                               | Check box, sets a boolean flag                                       |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::COLOR                                  | A RGB colour value, configurable with colour picker                  |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::IMAGE                                  | Provides an image uploader and gives the image URL                   |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::VIDEO                                  | Provides a video uploader and chooses a URL                          |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::TEXT_LIST                              | List of strings                                                      |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::IMAGE_SET                              | List of images (e.g. for gallery or slider portlets)                 |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::ICON                                   | Font Awesome icon selection                                          |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::HIDDEN                                 | Hidden input                                                         |
+---------------------------------------------------+----------------------------------------------------------------------+
| InputType::HINT                                   | Hint                                                                 |
+---------------------------------------------------+----------------------------------------------------------------------+


.. _label_getPropertyTabs:

``getPropertyTabs()``
"""""""""""""""""""""

By default, all properties of the portlets are displayed in a single tab. |br|
This method can be overridden if you want to display them in various tabs instead.

The method returns an associative array that is used to sort the properties of the *config modal* into
different tabs. |br|
The desired tab label is defined in the array keys.

In addition to an explicit enumeration of user-defined properties, the ``'styles'`` or
``'animations'`` strings can also be used to provide the included properties each in a dedicated tab.

Possible tab values are:

    * ``[<Property-ID 1>, <Property-ID 2>, ...]`` - ein Array von **Property-IDs**, die diesem Reiter angehören
    * ``'styles'`` - adds the properties included for **styling** to the portlet and displays them in this
      tab.
    * ``animations'`` - adds the properties included for **animations** to the portlet and displays them in
      this tab

Properties listed in ``getPropertyDesc()``, but not assigned, are automatically assigned to the
default "General" tab.

**Example:**

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

This method alters the display of the portlet buttons in the palette.

**Example:**

.. code-block:: php

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        return $this->getFontAwesomeButtonHtml('fas fa-film');
    }

In the above example, an icon from the *Font Awesome* family is rendered instead of ``icon.svg``.

.. _label_getPreviewHtml:

``getPreviewHtml(PortletInstance $instance)``
"""""""""""""""""""""""""""""""""""""""""""""

This method determines the representation of the portlet in the OPC. |br|
This is not the finished appearance on the page of the online shop!
See also: ``getFinalHtml(PortletInstance $instance)``.

**Example:**

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

This method sets the output for the final appearance of the portlet.

**Example:**

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

The configuration of a portlet is done in the *Portlet-Config-Modal*. |br|
The appearance of this modal is determined by the contents of the file ``configpanel.tpl``, which may be located in the
portlet directory.

This method returns this content and can, of course, modify it by overriding. |br|

**Example:**

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

Writing portlet templates
-------------------------

Portlet templates are responsible for the appearance of a portlet. |br|
By default, the Smarty template file ``<Portlet-Class>.tpl`` will be loaded from the portlet folder and rendered,
both for the OPC editor view and for the final view.

In the template context the following smarty variables are defined:

    * ``$instance`` - The portlet instance
    * ``$portlet`` - The portlet
    * ``$isPreview`` - A flag for: ``true`` = "currently in editor view", ``false`` = "currently in final view"

The rendered markup should only result in a single DOM element.

In editor mode, the element must have the attribute ``data-portlet="..."``...."`` aufweisen. Hierin stehen alle Daten, die für
This contains all the data necessary for processing in the editor. |br|
The value can be obtained using the ``{$instance->getDataAttribute()}`` method. With
``{$instance->getProperty('<property-name>')}`` property values of the portlet instance can be queried.

**Example:**

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

In order for a portlet to inherit **animations** (if configured), add the following code
to the portlet element: |br|
(See rows 3 and 4 in the above example)

.. code-block:: html+smarty
   :linenos:

   {* ... *}

            class="{$instance->getAnimationClass()}"
            {$instance->getAnimationDataAttributeString()}

This sets the set animation CSS class and animation parameters via ``data-*`` attributes.

In order for a portlet to also take on user-defined **style properties**, add the following attribute to the portlet
element as well:

.. code-block:: html+smarty

    style="{$instance->getStyleString()}"

Each portlet instance has a non-persistent but uniform ID and can be retrieved
with ``{$instance->getUid()}``. This is useful for *bootstrap tabs*, for example.

Portlets with subareas
----------------------

Portlets can define areas where additional portlets are placed.

This type of area is an element with the CSS class ``opc-area``. |br|
The area element must define an ID for the editor view using the ``data-area-id="{$areaId}"`` attribute,
where ``$areaId`` is a standard identifier for the portlet.

For the **editor view**, the content of the element must be rendered as follows:

.. code-block:: smarty

    {$instance->getSubareaPreviewHtml($areaId)}

For the **final view**, the content of the element must be rendered as follows:

.. code-block:: smarty

    {$instance->getSubareaFinalHtml($areaId)}

**Example:**

.. code-block:: html+smarty

    <div {if $isPreview}data-area-id="{$areaId}"{/if} class="opc-area">
        {if $isPreview}
            {$instance->getSubareaPreviewHtml($areaId)}
        {else}
            {$instance->getSubareaFinalHtml($areaId)}
        {/if}
    </div>

Portlet translation
-------------------

Language variables can be called up in portlet classes and templates. |br|
This is done using:

.. code-block:: smarty

    {__("Text-ID")}


Translations can be put in the ``.mo`` file format in the plug-in's language directory
under ``portlets/``. |br|
Here, specifically:

.. code-block:: console

    plugins/[plugin-id]/locale/[language-tag]/portlets/[Portlet-Class].mo

If a translation is not found, its *text ID* is output unchanged.

Portlet templates - blueprints
------------------------------

*Blueprints* are portlet compositions or templates that can be used over and over.

These templates can be created and exported from the *OPC editor*. |br|
*Blueprints* can be found in the "Templates" tab, where they can also be imported.

Similarly, you can of course distribute *blueprints* with a plug-in. |br|
You can find more information on this in the ":ref:`label_infoxml_blueprints`" section.
