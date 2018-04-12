Liste mit allen Blocks
======================

Hier finden Sie eine Übersicht mit allen Blocks im EVO-Template. Diese können Sie in Ihrem Child-Template überschreiben oder erweitern.

**Ordner**: ``<Shop-Root>\templates\Evo\checkout\inc_conversion_tracking.tpl``:

.. code-block:: xml

    Zeile 4:     {block name="checkout-conversion-tracking"}
    <!-- Hier können Sie eigenen Code eintragen, um Ihre Conversion-Rate zu tracken -->

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\checkout\inc_order_completed.tpl``:

.. code-block:: xml

    Zeile  2:     {block name="checkout-order-confirmation"}
    <!-- Hier können Sie zusätzliche Informationen vor Bestellabschluss einfügen-->

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\layout\footer.tpl``:

.. code-block:: xml

    Zeile  5:     {block name="footer-sidepanel-left"}
    <!-- Ein Block links im Footer. -->

.. image:: /_images/blocks/footer-sidepanel-left.png

.. code-block:: xml

    Zeile  8:     {block name="footer-sidepanel-left-content"}
    <!-- Der Inhalt des Block links im Footer. -->

.. code-block:: xml

    Zeile  41:     {block name="footer-additional"}
    <!-- Ein zusätzlicher Block im Footer. -->

.. image:: /_images/blocks/footer-additional.png

.. code-block:: xml

    Zeile  47:     {block name="footer-newsletter"}
    <!-- Ein Block im Footer, der das Newsletter-Formular enthält. -->

.. image:: /_images/blocks/footer-newsletter.png

.. code-block:: xml

    Zeile  80:     {block name="footer-socialmedia"}
    <!-- Ein Block im Footer, der die Social-Media-Icons enthält. -->

.. image:: /_images/blocks/footer-socialmedia.png

.. code-block:: xml

    Zeile  126:     {block name="footer-vat-notice"}
    <!-- Eine Zeile im Footer, die Hinweise zur Besteuerung Ihrer Artikel enthält. -->

.. image:: /_images/blocks/footer-vat-notice.png

.. code-block:: xml

    Zeile  137:     {block name="footer-copyright"}
    <!-- Eine Zeile im Footer, mit Hinweisen zum Copyright -->

.. image:: /_images/blocks/footer-copyright.png

.. code-block:: xml

    Zeile  171:     {block name="footer-js"}
    <!-- Hier können Sie eigene JavaScripte hinzufügen -->

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\layout\header.tpl``:

.. code-block:: xml

    Zeile  4:     {block name="head-meta"}
    <!-- Ein Bereich im Header, der Meta-Angaben enthält -->

    Zeile  6:     meta name="description" content={block name="head-meta-description"}"{$meta_description|truncate:1000:"":true}{/block}"
    <!-- Ein Bereich im Header, der die Meta-Description enthält. -->

    Zeile  7:     meta name="keywords" content="{block name="head-meta-keywords"}{$meta_keywords|truncate:255:"":true}{/block}"
    <!-- Ein Bereich im Header, der Meta-Keywords enthält. -->

    Zeile  13:     {block name="head-title"}{$meta_title}{/block}
    <!-- Der Titel Ihres Shops. -->

    Zeile  26:     {block name="head-resources"}
    <!-- Ein Bereich im Header, wo Sie zusätzliche CSS-Dateien hinzufügen können. -->

.. code-block:: xml

    Zeile  116:     {block name="header-branding"}
    <!-- Ein Bereich im Header, wo Sie zusätzliche, sichtbare Informationen hinterlegen können. -->

.. image:: /_images/blocks/header-branding.png

.. code-block:: xml

    Zeile  122:     {block name="logo"}
    <!-- Bereich im Header, der Ihr Logo enthält. -->

.. image:: /_images/blocks/logo.png

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\layout\header_shop_nav.tpl``:

.. code-block:: xml

    Zeile  3:     {block name="navbar-productsearch"}
    <!-- Der Bereich im Header des Shops, der die Suche enthält. -->

.. image:: /_images/blocks/navbar-productsearch.png

.. code-block:: xml

    Zeile  19:     {block name="navbar-top-user"}
    <!-- Der Bereich im Header des Shops, der den Login-Bereich enthält. -->

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\layout\header_xs_nav.tpl``:

.. note::
    Navigation für die mobile Ansicht

.. code-block:: xml

   Zeile  95:     {block name="megamenu-manufacturers"}
    <!-- Der Bereich im Mega-Menü, der die Hersteller auflistet. -->

.. image:: /_images/blocks/mobile_megamenu-manufacturers.png

.. code-block:: xml

    Zeile  117:     {block name="megamenu-pages"}
    <!-- Der Bereich im Mega-Menü, der die eigenen Seiten auflistet. -->

.. image:: /_images/blocks/mobile_megamenu-pages.png

.. code-block:: xml

    Zeile  125:     {block name="navbar-top-cms"}
    <!-- Der Bereich im Header des Shops, der eigene Seiten auflistet -->

.. image:: /_images/blocks/mobile_navbar-top-cms.png

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\productdetails\basket.tpl``:

.. code-block:: xml

    Zeile  3:     {block name="add-to-cart"}
    <!-- Der Bereich, der den In den Warenkorb-Button anzeigt. -->

.. image:: /_images/blocks/add-to-cart.png

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\productdetails\price.tpl``:

.. code-block:: xml

    Zeile  7:     {block name="price-wrapper"}
    <!-- Der Bereich, der den Preis der Artikel anzeigt. -->

.. image:: /_images/blocks/price-wrapper.png

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\productdetails\stock.tpl``:

.. code-block:: xml

    Zeile  3:     {block name="delivery-status"}
    <!-- Der Bereich, der den Lieferstatus anzeigt. -->

.. image:: /_images/blocks/delivery-status.png

----------------------------------------------------------------------------------------------------

**Ordner**: ``<Shop-Root>\templates\Evo\snippets\categories_mega.tpl``:

.. note::
    Navigation für Desktop-Ansicht

.. code-block:: xml

    Zeile  5:     {block name="megamenu-categories"}
    <!-- Der Bereich im im Mega-Menü, der die Kategorien anzeigt. -->

.. image:: /_images/blocks/megamenu-categories.png

.. code-block:: xml

    Zeile  183:     {block name="megamenu-pages"}
    <!-- Der Bereich im Mega-Menü, der die eigenen Seiten auflistet. -->

.. image:: /_images/blocks/megamenu-pages.png

.. code-block:: xml

    Zeile  189:     {block name="megamenu-manufacturers"}
    <!-- Der Bereich im Mega-Menü, der die Hersteller auflistet. -->

.. image:: /_images/blocks/megamenu-manufacturers.png