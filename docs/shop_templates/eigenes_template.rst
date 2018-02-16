Eigenes Template
================

.. contents::
    Inhalt

*************************************************************
 Der bevorzugte Weg zum eigenen Template sind Child-Templates
*************************************************************

Um ein eigenes Template zu entwickeln, müssen Sie ein Child-Template anlegen. Wie das geht, wird auf dieser Seite erklärt.

Mit diesem Child-Template können Sie **Styles, JavaScripts oder php-Dateien** des im JTL-Shop4 mitgelieferterten Standard-Templates EVO **erweitern oder überschreiben**.
Dabei werden alle Dateien des EVO-Templates von JTL-Shop geladen, außer die, welche Sie in Ihr Child-Template kopiert haben.
In Ihrem Child-Template können Sie einzelne Passagen im Shop (Blocks) oder aber auch komplette Dateien austauschen.
Das EVO-Template bleibt dabei updatefähig, da dieses nicht von Ihnen verändert werden muss.

Die Struktur eines Child-Templates, inkl. LESS- **oder** CSS-Dateien, sieht dann nachher in etwa so aus:

.. image:: /_images/jtl-shop_child-template_struktur.jpg

.. note::

    **Hinweis:** Manche Dateien, wie z.B. ``functions.php`` :ref:`» <eigene-smarty-funktionen-integrieren>` sind nur exemplarisch in dieser Struktur abgebildet und nicht obligatorisch. Diese soll nur aufzeigen, dass Sie auch Funktionen überschreiben können.

***********************************
 Ein neues Child-Template erstellen
***********************************

**Am einfachsten ist es, wenn Sie das Beispiel-Template *Evo-Child-Example* kopieren und anpassen.**

Bis einschließlich Version 4.05 wird es im Installationspaket des JTL-Shops mitgeliefert.
Falls Sie eine aktuelle Version des Shops nutzen, können Sie es manuell von der `Projektseite <https://gitlab.jtl-software.de/jtlshop/EvoChild>`_ herunterladen.

Für ein neues Child-Template benennen Sie zunächst den Ordner ``Evo-Child-Example Kopie`` in den gewünschten Template-Namen um, z.B. ``Mein-Shop-Template``.
In diesem Unterordner ``<Shop-Root>/templates/Mein-Shop-Template`` finden Sie die Datei **template.xml**.

Folgender Code sollte mindestens in dieser Datei stehen, dabei ist zu beachten, dass unter **Parent** das Parent-Template (Ordnername) eingetragen ist, welches angepasst werden soll.
Wenn Sie das EVO-Template von JTL-Shop also erweitern möchten, sollte die template.xml folgendermaßen aussehen:

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <Template isFullResponsive="true">
        <Name>Mein-Shop-Template</Name>
        <Author>Max Mustermann</Author>
        <URL>https://www.mein-shop.de</URL>
        <Version>1.00</Version>
        <ShopVersion>403</ShopVersion>
        <Parent>Evo</Parent>
        <Preview>preview.png</Preview>
        <Description>Das ist mein eigenes Template!</Description>
    </Template>

Bereits jetzt können Sie Ihr Template im Shop-Backend aktivieren.
Da noch keine Änderungen vorgenommen wurden, erkennen Sie in Ihrem Shop noch keinen Unterschied.

.. note::

    Das Attribute **isFullResponsive="true|false"** kennzeichnet, ob sich Ihr neues Template vollständig responsive verhält,
    also automatisch an jede Auflösung anpasst.

    Wenn Sie Ihr Template vom Evo-Template ableiten, dann sollten Sie dies immer auf **true** einstellen. Das Attribut bewirkt
    bei der Einstellung auf true, dass im Backend das ``Standard-Template für mobile Endgeräte?`` nicht mehr eingestellt werden kann
    und eine Warnung ausgegeben wird, falls dies (noch) so sein sollte.

Änderungen an Template-Dateien
------------------------------

Template-Dateien (Dateiendung **.tpl**) können auf zwei Arten angepasst werden:

* Sie können einmal einzelne Teile (:doc:`Blocks</shop_templates/blocks_list>`) oder
* die komplette Struktur einer Template-Datei anpassen

Anpassungen über Blocks
^^^^^^^^^^^^^^^^^^^^^^^

:doc:`Blocks</shop_templates/blocks_list>` sind im EVO-Template vordefinierte Stellen, die im Child-Template erweitert oder ersetzt werden können.
Beispielsweise können Sie im Header Ihres Shops individuelle Dateien laden, das Logo austauschen, oder das Menü anpassen.
Das EVO-Template hat jetzt bereits viele vordefinierte Stellen, die Sie verändern können.

Blocks sind an folgender Struktur zu erkennen:

.. code-block:: html+smarty

    {block name="<name des blocks>"}...{/block}

Wenn Sie nun eine bestimmte Template-Datei verändern möchten, empfiehlt es sich, diese aus von dem EVO-Template in Ihr Child-Template zu kopieren.

.. note::

    Die Ordnerstruktur im Child-Template muss die des EVO-Templates widerspiegeln.

    Beispiel: ``templates/Evo/layout/header.tpl`` -> ``templates/Mein-Shop-Template/layout/header.tpl``

Möchten Sie beispielsweise die **header.tpl** anpassen, erstellen Sie in Ihrem Child-Template den Ordner **layout** und darin die Datei **header.tpl**.

Als erstes wird in der **header.tpl** Ihres Child-Templates folgender Code hinzugefügt:

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

Mit dieser Zeile wird dem Child-Template **header.tpl** angewiesen, das Parent-Template (EVO) **header.tpl** zu erweitern (extends).

Möchten Sie nun beispielsweise den Seitentitel verändern, finden Sie in der **header.tpl** folgende Stelle:

.. code-block:: html+smarty

    <title>{block name="head-title"}{$meta_title}{/block}</title>

Jetzt können Sie in der **header.tpl** Ihres Child-Templates Änderungen auf drei verschiedene Arten vornehmen:

**1.** Den Inhalt des Blocks ersetzen:

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

    {block name="head-title"}Mein Shop!{/block}

- **Ursprüngliche Ausgabe:** {$meta_title}
- **Neue Ausgabe:** Mein Shop!

**2.** Neuen Inhalt an das Ende des im EVO-Templates definierten Textes hängen:

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

    {block name="head-title" append} Mein Shop!{/block}

- **Ursprüngliche Ausgabe:** {$meta_title}
- **Neue Ausgabe:** {$meta_title} Mein Shop!

**3.** Neuen Inhalt an den Anfang des im EVO-Templates definierten Textes stellen:

.. code-block:: html+smarty

    {extends file="{$parent_template_path}/layout/header.tpl"}

    {block name="head-title" prepend}Mein Shop! {/block}

- **Ursprüngliche Ausgabe:** {$meta_title}
- **Neue Ausgabe:** Mein Shop! {$meta_title}

Für die meisten Anpassungen sollten diese Varianten genügen. In Ihrem Child-Template befinden sich nur noch die Template-Dateien, welche Sie verändern möchten und nicht die komplette Templatestruktur aus dem EVO-Template.
Wird das EVO-Template aktualisiert, beispielsweise durch neue Funktionen oder größeren Anpassungen der Struktur, müssen nur wenige bis keine Anpassungen in Ihrem Child-Template vorgenommen werden.

.. note::

    Eine Liste aller zur Verfügung stehenden Smary-Blocks können Sie hier finden: :doc:`Liste mit allen Blocks </shop_templates/blocks_list>`

`Weitere Infos zu Blocks finden Sie auf smarty.net <http://www.smarty.net/docs/en/language.function.block.tpl>`_

Anpassung an der gesamten Struktur
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Wollen Sie die komplette Struktur einer Template-Datei anpassen, empfiehlt es sich, die Datei aus dem EVO-Template, in Ihren neu erstellten Child-Template Ordner zu kopieren.
Dieses Vorgehen entspricht etwa der alten Variante mit Custom-Dateien (header_custom.tpl), allerdings werden diese nun vom EVO-Template abgekapselt.

Nachteil bei dieser Variante ist, dass beim Hinzufügen neuer Funktionen oder größeren Anpassungen der Struktur, händisch die Änderungen in das Child-Template übertragen werden müssen.

Wie man eigenen CSS-Code in das Child-Template einfügt, finden Sie auf dieser Seite: :doc:`Eigenes Theme </shop_templates/eigenes_theme>`
