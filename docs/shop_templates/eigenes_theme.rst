.. _label-eigenes-theme:

Eigenes Theme
=============

.. |br| raw:: html

    <br />

Was ist ein Theme?
-------------------

Ein Theme ist kein eigenständiges Template, sondern ausschließlich die Design-Komponente, die zu einem Template
gehört. |br|
In JTL-Shop steuern Themes das individuelle Aussehen und Design des Shops per CSS-Regeln. |br|
Das Template stellt das XHTML-Ausgabedokument bereit, während das Theme die einzelnen Elemente des
XHTML-Ausgabedokumentes per Cascading Stylesheets (CSS) visuell für die Bildschirm- und Druckausgabe anpasst.

.. important::

    Wie auch für Ihr eigenes Template, gilt für ein eigenes Theme: |br|
    kopieren Sie sich das jeweilige Beispiel-Theme ("my-evo" oder "my-nova") aus dem Verzeichnis ``themes/``,
    des entsprechenden Templates, welches Sie unter
    `Beispiel-Templates <https://gitlab.com/jtl-software/jtl-shop/child-templates>`_ finden in Ihr Child-Template und
    bennenen dieses Ihren Wünschen gemäß um, wenn Sie das erste Mal ein Theme erstellen.

Struktur eines Themes
---------------------

Ein *Theme* besteht aus einem Verzeichnis, welches sich unterhalb von ``[Shop-Root]/templates/[Template-Name]/themes/``
befindet, sowie aus der darin enthaltenen CSS-Datei ``bootstrap.css`` und den LESS-Dateien ``less/theme.less``
sowie ``less/variables.less``, sofern es sich um ein EVO-Child-Template handelt.

In einem NOVA-Child-Template hingegen beinhaltet das Verzeichnis ``[Theme-name]/sass/`` alle Style-Dateien. Hier
wären das die Dateien ``sass/_allstyles.scss`` und ``sass/_variables.scss``.

**Optional** kann in beiden Templates das Verzeichnis ``images/`` zum Beispiel Hintergrundgrafiken enthalten.
Das Unterverzeichnis ``js/`` kann Javascript-Dateien und das Unterverzeichnis ``php/`` kann php-Funktionen enthalten.

**Beispiel, EVO:**

.. code-block:: console
   :emphasize-lines: 10-19

    templates/Evo/
    ├── account/
    ├── ...
    ├── themes/
    │   ├── base/
    │   │   └── ...
    │   ├── bootstrap/
    │   │   └── ...
    │   ├── ...
    │   ├── myTheme/
    │   │   ├── images/
    │   │   │   ├── background.jpg
    │   │   │   └── ...
    │   │   ├── less/
    │   │   │   ├── theme.less
    │   │   │   └── variables.less
    │   │   ├── bootstrap.css
    │   │   ├── custom.css
    │   │   └── preview.png
    │   └── ...

Die Datei ``bootstrap.css`` wird durch das Theme-Editor-Plugin aus den Dateien ``theme.less`` und ``variables.less``
kompiliert und beinhaltet alle CSS-Regeln für den JTL-Shop.

.. caution::

    Diese Datei sollte nicht verändert werden, weil sie beim Kompilieren überschrieben wird.

**Beispiel, NOVA:**

.. code-block:: console
   :emphasize-lines: 7-17

    templates/NOVA/
    ├── account/
    ├── ...
    ├── themes/
    │   ├── base/
    │   │   └── ...
    │   ├── myTheme/
    │   │   ├── images/
    │   │   │   ├── background.jpg
    │   │   │   └── ...
    │   │   ├── sass/
    │   │   │   ├── _allstyles.scss
    │   │   │   ├── _variables.scss
    │   │   │   └── myTheme.scss
    │   │   ├── myTheme.css
    │   │   ├── myTheme.css.map
    │   │   └── custom.css
    │   └─ ...

variables.less / _variables.scss
""""""""""""""""""""""""""""""""

Diese Datei beinhaltet vordefinierte Variablen mit Farbwerten, Abständen, Breiten etc.

theme.less
""""""""""

In dieser Datei werden das Aussehen und das Design des Shops beeinflusst. Dabei kann auf die Variablen
der ``variables.less`` bzw. der ``_variables.scss`` zurückgegriffen werden.

.. attention::

    Der Pfad zur ``base.less`` des Templates muss in Ihrer ``theme.less`` korrekt definiert sein, |br|

**Beispiel:**

.. code-block:: css

    // Load core variables and mixins
    // --------------------------------------------------
    //
    // include basic less files from EVO template

    @import "../../../../Evo/themes/base/less/base";

CSS und JavaScript anpassen
---------------------------

Sie können, neben dem :doc:`Ändern und Erweitern </shop_templates/eigenes_template>` von Template-Dateien, auch das
CSS des Templates erweitern oder überschreiben.

Um  Ihre eigenen CSS- oder JavaScript-Dateien in Ihrem Child-Template zu laden, gehen Sie bitte in die ``template.xml``
Ihres Child-Templates.

Passen Sie diese folgendermaßen an:

.. code-block:: xml
   :emphasize-lines: 23,26

    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <Template isFullResponsive="true">
        <Name>Mein-Shop-Template</Name>
        <Author>Max Mustermann</Author>
        <URL>https://www.mein-shop.de</URL>
        <Parent>Evo</Parent>
        <Preview>preview.png</Preview>
        <Description>Mein erstes Child-Template</Description>

        <Settings>
            <Section Name="Theme" Key="theme">
                <Setting Description="Mein Theme" Key="theme_default" Type="select" Value="mytheme">
                    <Option Value="mytheme">Mein erstes Theme</Option>
                </Setting>
            </Section>
        </Settings>
        <Minify>
            <CSS Name="mytheme.css">
                <File Path="../Evo/themes/evo/bootstrap.css"/>
                <File Path="../Evo/themes/base/offcanvas-menu.css"/>
                <File Path="../Evo/themes/base/pnotify.custom.css"/>
                <File Path="../Evo/themes/base/jquery-slider.css"/>
                <File Path="css/mytheme.css"/>
            </CSS>
            <JS Name="mythememini.js">
                <File Path="js/mytheme.js"/>
            </JS>
        </Minify>
        <Boxes>
            <Container Position="right" Available="1"></Container>
        </Boxes>
    </Template>

Bei dieser Variante wird davon ausgegangen, dass Ihre CSS-Datei im Ordner
``[Shop-Root]/templates/Mein-Shop-Template/css/`` liegt und ``mytheme.css`` heißt und Ihre JavaScript-Datei
im Ordner ``[Shop-Root]/templates/Mein-Shop-Template/js/`` liegt und ``mytheme.js`` heißt. |br|
Selbstverständlich können Sie die Dateien auch benennen, wie Sie möchten, solange Sie Ihre Referenzierung
dementsprechend anpassen.

Wenn Sie verschiedene Themes anlegen möchten, z.B. ein Weihnachts-Theme und ein Oster-Theme, können Sie
Ihre ``template.xml`` folgendermaßen anpassen:

.. code-block:: xml
   :emphasize-lines: 24,31,34

    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <Template isFullResponsive="true">
        <Name>Mein-Shop-Template</Name>
        <Author>Max Mustermann</Author>
        <URL>https://www.mein-shop.de</URL>
        <Parent>Evo</Parent>
        <Preview>preview.png</Preview>
        <Description>Mein erstes Child-Template</Description>

        <Settings>
            <Section Name="Theme" Key="theme">
                <Setting Description="Mein Theme" Key="theme_default" Type="select" Value="mytheme">
                    <Option Value="weihnachtstheme">Mein Weihnachts-Theme</Option>
                    <Option Value="ostertheme">Mein Oster-Theme</Option>
                </Setting>
            </Section>
        </Settings>
        <Minify>
            <CSS Name="weihnachtstheme.css">
                <File Path="../Evo/themes/evo/bootstrap.css"/>
                <File Path="../Evo/themes/base/offcanvas-menu.css"/>
                <File Path="../Evo/themes/base/pnotify.custom.css"/>
                <File Path="../Evo/themes/base/jquery-slider.css"/>
                <File Path="css/weihnachtstheme.css"/>
            </CSS>
            <CSS Name="ostertheme.css">
                <File Path="../Evo/themes/evo/bootstrap.css"/>
                <File Path="../Evo/themes/base/offcanvas-menu.css"/>
                <File Path="../Evo/themes/base/pnotify.custom.css"/>
                <File Path="../Evo/themes/base/jquery-slider.css"/>
                <File Path="css/ostertheme.css"/>
            </CSS>
            <JS Name="mythememini.js">
                <File Path="js/mytheme.js"/>
            </JS>
        </Minify>
        <Boxes>
            <Container Position="right" Available="1"></Container>
        </Boxes>
    </Template>

Wenn Sie unserem Beispiel gefolgt sind, müsste demnach Ihr Child-Template mittlerweile so aussehen:

.. code-block:: console
   :emphasize-lines: 6-7,9

    templates/
    ├── Evo/
    ├── NOVA/
    └── Mein-Shop-Template/
        ├── css/
        │   ├── ostertheme.css
        │   └── weihnachtstheme.css
        ├── js
        │   └── mytheme.js
        ├── layout
        │   └── header.tpl
        ├── php
        │   └── functions.php
        ├── themes/
        │   └── meinTheme/
        │       ├── images/
        │       │   └── ...
        │       ├── less/
        │       │   ├── theme.less
        │       │   └── variables.less
        │       ├── background.jpg
        │       ├── mytheme.css
        │       ├── custom.css
        │       └── preview.png
        ├── template.xml
        └── preview.png

.. note::

    Als Beispiel sind in diesem Child-Template CSS- **und** LESS-Files integriert. Wir empfehlen Ihnen, sich auf eine
    Variante festzulegen. |br|

    Manche Dateien, wie z.B. ``functions.php`` :ref:`»» <eigene-smarty-funktionen-integrieren>` sind nur
    exemplarisch in dieser Struktur abgebildet und nicht obligatorisch. Das soll an dieser Stelle nur aufzeigen,
    dass Sie auch Funktionen überschreiben können.

 .. _arbeiten-mit-less:

Arbeiten mit LESS
-----------------

Das EVO- wie auch das NOVA-Template arbeiten mit LESS-Dateien.  |br|
LESS kann als sprachliche Erweiterung von CSS verstanden werden und bietet gegenüber alleinigem CSS einige Vorteile.
So können CSS-Angaben beispielsweise verschachtelt und wiederverwendet werden. |br|
Dadurch können Sie Ihre Styles besser und übersichtlicher strukturieren.

.. note::

    LESS setzt einen Pre-Prozessor voraus, welcher die Sprachkonstrukte von LESS in CSS übersetzt. |br|
    Dieser Pre-Processor wird über das JTL-Plugin "JTL Theme-Editor" bereitgestellt.

Hier sehen Sie den Unterschied zwischen CSS und LESS:

**CSS**

.. code-block:: css

    header {
        padding: 5px;
    }

    header #header-branding {
        padding: 15px 0;
    }

**LESS**

.. code-block:: scss

    header {
        padding: 5px;
        #header-branding {
            padding: 25px;
        }
    }

Weitere Informationen dazu, was *LESS* Ihnen bieten kann, finden Sie auf `lessscss.org <http://lesscss.org/>`_

Im NOVA-Template gehen wir noch einen Schritt weiter. |br|
Dieses Template verwendet die modernere Technologie Sass, die noch mehr Möglichkeiten bietet als LESS. Die
Sprachdateien von Sass sind an der Erweiterung ``.scss`` zu erkennen.

Mehr zu *Sass* finden sie auf `sass-lang.org <https://sass-lang.com/>`_

Eigene LESS-Dateien im Theme
----------------------------

Wenn Sie in Ihrem Child-Template auch mit LESS arbeiten möchten, empfiehlt es sich, den Ordner ``mytheme/`` aus dem
``themes/``-Order des Example-Child-Templates zu kopieren und entsprechend umzubenennen, z.B. in ``meinTheme/``.

.. code-block:: console
   :emphasize-lines: 10-12

    templates/
    ├── Evo/
    ├── NOVA/
    └── Mein-Shop-Template/
        ├── themes/
        │   ├── base
        │   └── meinTheme/
        │       ├── images/
        │       │   └── background.jpg
        │       ├── less/
        │       │   ├── theme.less
        │       │   └── variables.less
        │       ├── mytheme.css
        │       ├── custom.css
        │       └── preview.png
        ├── template.xml
        └── preview.png


Sie können nun in Ihrer ``theme.less`` LESS- oder CSS-Code einfügen und Ihren Shop individuell gestalten. Wenn Sie
Variablen in der Datei ``variables.less`` ändern, werden diese für alle Styles in Ihrem Shop geändert. Sie
können z. B. die Variable ``@brand-primary`` verändern und eine eigene Farbe eintragen. ``@brand-primary`` wird für
viele Elemente in JTL-Shop verwendet. Das Ändern dieser Variable hat also starken Einfluss auf das Aussehen
von JTL-Shop. |br|
Probieren Sie es aus!

**Anschließend müssen Sie Ihr Theme noch kompilieren!** |br|
(siehe `Eigenes Theme mit dem Theme-Editor kompilieren`_)

.. note::

    LESS-Dateien müssen **nicht** in die ``template.xml`` eingefügt werden. Der Theme-Editor erkennt LESS-Files
    automatisch.


.. _label_eigenestheme_kompilieren:

Eigenes Theme mit dem Theme-Editor kompilieren
----------------------------------------------

Gehen Sie hierfür in das Backend von JTL-Shop.  |br|
Falls noch nicht geschehen, aktivieren Sie das Plugin "*Evo Editor*" (Shop 4.x) bzw. das Plugin "**JTL Theme-Editor**"
(ab Shop 5.0). |br|
Anschließend öffnen Sie das Plugin über seinen "Einstellungen"-Button in der Pluginverwaltung. |br|
Wählen Sie nun in der Select-Box unter Theme Ihr Theme aus und klicken anschließend rechts auf den
Button :guilabel:`Theme kompilieren`.

.. image:: /_images/jtl-shop_child-template_editor.jpg

Nun ist Ihr Template fertig kompiliert. |br|

.. important::

    Ihr ``theme``-Ordner benötigt Schreibrechte.

Weitere Möglichkeiten um Ihr Theme zu bearbeiten, finden Sie im Abschnitt ":doc:`theme_edit`".

Updatesicherheit
----------------

Um sicherzugehen, dass Ihre Änderungen in der Datei ``template.xml`` nicht durch ein Update rückgängig gemacht werden,
empfehlen wir, das eigene Theme in einem :doc:`Child-Template </shop_templates/eigenes_template>` abzulegen.
