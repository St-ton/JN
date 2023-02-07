Primärfarbe wechseln
====================

.. |br| raw:: html

   <br />

**Wechseln der Primärfarbe am Beispiel des NOVA-Templates**

Um die Primärfarbe im NOVA-Template zu wechseln, erstellen Sie sich zunächst ein eigenes Child Template. |br|
Folgen Sie hierzu der Anleitung unter ":ref:`label_eigenes_child_template`" und aktivieren Sie das Child-Template.

Im zweiten Schritt benötigen Sie das
Plugin `JTL Theme-Editor <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_theme_editor>`_. |br|
Im Plugin `JTL Theme-Editor` wechseln Sie in das Theme, welches Sie verwenden wollen (beispielsweise "my-nova" oder
"my-evo") und dort in die Datei ``_variables.scss``. (siehe: ":doc:`/shop_templates/theme_edit`") |br|
Tragen Sie in dem dafür vorgesehenen Abschnitt ("eigene Variablen") Ihre neue Primärfarbe ein:

.. code-block:: scss

    $primary: #008B8B;

Speichern Sie unten links die Datei und klicken Sie oben rechts auf "Theme kompilieren".

Sobald der Lade-Spinner am "Theme kompilieren"-Button aufgehört hat, sich zu drehen, ist das Theme kompiliert und Sie
können sich Ihre Änderung im Frontend ansehen.

Auf die gleiche Weise können Sie auch die primäre Schriftgröße an Ihre Bedürfnisse anpassen:

.. code-block:: scss

    $font-size-base: rem(16px);
    $font-size-lg: rem(18px);
    $font-size-sm: rem(14px);
    $font-size-xs: rem(13px);
    $h1-font-size: rem(36px);
    $h2-font-size: rem(29px);
    $h3-font-size: rem(19px);

