Eigenes Theme
=============

.. contents::
    Inhalt

*******************
 Was ist ein Theme?
*******************

Im JTL-Shop steuern Themes das individuelle Aussehen und Design des Shops per CSS-Regeln.
Ein Theme ist kein eigenständiges Template, sondern ausschließlich die Design-Komponente, die zu einem Template gehört.
Das Template stellt das xHTML-Ausgabedokument bereit, während das Theme die einzelnen Elemente des xHTML-Ausgabedokumentes per Cascading Stylesheets (CSS) visuell für die Bildschirm- und Druckausgabe anpasst.

.. note::
    **Wichtig:** Wie auch für Ihr eigenes Template, gilt für ein eigenes Theme, dass Sie am besten den ``mytheme``-Ordner aus dem Beispiel-Template ``Evo-Child-Example/themes`` kopieren und entsprechend umbenennen, wenn Sie zum ersten Mal ein Template/Theme erstellen.

******************************
 Exkurs: Struktur eines Themes
******************************

Ein Theme besteht aus einem Ordner, der sich im Verzeichnis ``<Shop-Root>/templates/<Template-Name>/themes/`` befindet, sowie aus der darin enthaltenen CSS-Datei ``bootstrap.css``, den LESS-Dateien ``less/theme.less`` und ``less/variables.less`` sowie **optional** aus Hintergrundgrafiken in einem Unterordner ``images``, Javascript-Dateien im Ordner ``js`` und php-Funkionen im Ordner ``php``.

.. image:: /_images/jtl-shop_child-theme_struktur.jpg

variables.less
--------------

Beinhaltet vordefinierte Variablen mit Farbwerten, Abständen, Breiten etc.

theme.less
----------

In dieser Datei werden das Aussehen und Design des Shops beeinflusst, dabei kann auf die Variablen der ``variables.less`` zurückgegriffen werden.
**Wichtig:** der Pfad zur ``base.less`` des Evo-Templates muss in Ihrer ``theme.less`` korrekt definiert sein:

.. code-block:: css

    //
    // Load core variables and mixins
    // --------------------------------------------------
    // include basic less files from EVO template

    @import "../../../../Evo/themes/base/less/base";

bootstrap.css
-------------

Diese Datei wird durch das Evo-Editor-Plugin aus der ``theme.less`` und ``variables.less`` kompiliert und beinhaltet alle CSS-Regeln für den JTL-Shop. **Diese Datei sollte nicht verändert werden, weil diese beim Kompilieren überschrieben wird**.

***************************
CSS und JavaScript anpassen
***************************

Sie können, neben dem :doc:`Ändern und Erweitern </shop_templates/eigenes_template>` von Template-Dateien, auch das CSS vom EVO-Template erweitern oder überschreiben.

Um  Ihre Eigenen CSS- oder JavaScript-Dateien in Ihrem Child-Template zu laden, gehen Sie bitte in die **template.xml** Ihres Child-Templates.

Passen Sie diese folgendermaßen an:

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

        <Settings>
            <Section Name="Theme" Key="theme">
                <Setting Description="Mein Theme" Key="theme_default" Type="select" Value="meintheme">
                    <Option Value="meintheme">Mein Theme</Option>
                </Setting>
                <Setting Description="Hintergrundbild" Key="background_image" Type="select" Value="">
                    <Option Value="">- Kein Hintergrundbild -</Option>
                    <Option Value="custom">Custom - Ihr eigenes Hintergrundbild (themes/Mein-Shop-Template/img/background.jpg)</Option>
                </Setting>
            </Section>
        </Settings>
        <Minify>
            <CSS Name="meintheme.css">
                <File Path="../Evo/themes/evo/bootstrap.css"/>
                <File Path="../Evo/themes/base/offcanvas-menu.css"/>
                <File Path="../Evo/themes/base/pnotify.custom.css"/>
                <File Path="../Evo/themes/base/jquery-slider.css"/>
                <File Path="css/meintheme.css"/>
            </CSS>
            <JS Name="jtl3.js">
                <File Path="js/meintheme.js"/>
            </JS>
        </Minify>
        <Boxes>
            <Container Position="right" Available="1"></Container>
        </Boxes>
    </Template>

Bei dieser Variante wird davon ausgegangen, dass Ihre CSS-Datei im Ordner ``<Shop-Root>/templates/Mein-Template/css`` liegt und **meintheme.css** heißt und Ihre JavaScript-Datei
im Ordner ``<Shop-Root>/templates/Mein-Template/js`` liegt und **meintheme.js** heißt. Selbstverständlich können Sie die Dateien aber nennen, wie Sie möchten.

Wenn Sie verschiedene Themes anlegen möchten, z.B. ein Weihnachts-Theme und ein Oster-Theme, können Sie Ihre template.xml folgermaßen anpassen:

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

        <Settings>
            <Section Name="Theme" Key="theme">
                <Setting Description="Mein Theme" Key="theme_default" Type="select" Value="meintheme">
                    <Option Value="weihnachtstheme">Mein Weihnachts-Theme</Option>
                    <Option Value="ostertheme">Mein Oster-Theme</Option>
                </Setting>
                <Setting Description="Hintergrundbild" Key="background_image" Type="select" Value="">
                    <Option Value="">- Kein Hintergrundbild -</Option>
                    <Option Value="custom">Custom - Ihr eigenes Hintergrundbild (themes/Mein-Shop-Template/img/background.jpg)</Option>
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
            <JS Name="jtl3.js">
                <File Path="js/meintheme.js"/>
            </JS>
        </Minify>
        <Boxes>
            <Container Position="right" Available="1"></Container>
        </Boxes>
    </Template>

Ihr Child-Template  müsste demnach mittlweile so aussehen:

.. image:: /_images/jtl-shop_child-template_struktur.jpg

.. note::

    Als Beispiel sind in diesem Child-Template CSS- **und** LESS-Files integriert. Es empfiehlt sich, sich auf eine Variante festzulegen.
    **Hinweis:** Manche Dateien, wie z.B. ``functions.php`` :ref:`» <eigene-smarty-funktionen-integrieren>` sind nur exemplarisch in dieser Struktur abgebildet und nicht obligatorisch. Diese soll nur aufzeigen, dass Sie auch Funktionen überschreiben können.

Überschreiben bestehender Skripte
---------------------------------

Falls Sie im Eltern-Template definierte JavaScript-Dateien überschreiben möchten, fügen Sie dem File-Eintrag das Attribut **override="true"** hinzu und erstellen Sie Ihre eigene Version der JavaScript-Datei im Unterverzeichnis **js**.

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

        <Minify>
            <JS Name="jtl3.js">
                <File Path="js/meintheme.js"/>
                <File Path="js/jtl.evo.js" override="true"/>
            </JS>
        </Minify>
        <Boxes>
            <Container Position="right" Available="1"></Container>
        </Boxes>
    </Template>

Dieses Beispiel würde bewirken, dass die Datei js/jtl.evo.js Ihres Child-Templates anstatt der originalen Datei des Evo-Templates eingebunden wird.
Ohne das **override**-Attribut würde die genannte Datei **zusätzlich** zur jtl.evo.js des Eltern-Templates eingebunden werden.


******************************************
Eigenes Hintergrundbild für Ihren JTL-Shop
******************************************

Um ein eigenes Hintergrundbild für Ihren JTL-Shop festlegen zu können, müssen Sie eine Option in Ihre **template.xml** hinzufügen. Bei dem o.g. Beispiel ist dies schon geschehen:

.. code-block:: xml

    ...
        <Settings>
            <Section Name="Theme" Key="theme">
                <Setting Description="Mein Theme" Key="theme_default" Type="select" Value="meintheme">
                    <Option Value="weihnachtstheme">Mein Weihnachts-Theme</Option>
                    <Option Value="ostertheme">Mein Oster-Theme</Option>
                </Setting>
                <Setting Description="Hintergrundbild" Key="background_image" Type="select" Value="">
                    <Option Value="">- Kein Hintergrundbild -</Option>
                    <Option Value="custom">Mein eigenes Hintergrundbild</Option>
                </Setting>
            </Section>
        </Settings>
    ...

Das Hintergrundbild kopieren Sie als **JPG** in den Ordner ``<Shop-Root>/templates/Mein-Shop-Template/themes/meinTheme/img/background.jpg``.
Bei den Template-Einstellungen Ihres Child-Templates im Backend des JTL-Shops können Sie nun das Hintergrundbild auswählen.

***********************
Ihr Template aktivieren
***********************

Wenn Sie nun alle Änderungen an Ihrem Child-Template vorgenommen haben, gehen Sie in das Backend Ihres JTL-Shops.
Gehen Sie im Menü auf **Template** und klicken Sie nun auf den Button **Aktivieren** neben Ihrem Child-Template.

Dort können Sie nun im Abschnitt **Theme** Ihr Theme aus der Select-Box auswählen. Auch andere Template-Einstellungen können Sie nun vornehmen und anschließend unten auf den Button **Speichern** klicken.

Gehen Sie anschließend noch im Menü auf den Punkt **System** und klicken auf **Cache**. Wählen Sie nun **Template** in der dazugehörigen Checkbox aus. Anschließend unten auf den Button **absenden**, um den Cache zu leeren.

Nun sollten Ihr Child-Template aktiviert sein und Sie sollten die Änderungen in Ihrem JTL-Shop sehen können.

 .. _arbeiten-mit-less:

*****************
Arbeiten mit LESS
*****************

Das EVO-Template arbeitet mit LESS-Dateien. LESS ist eine Abwandlung von CSS und bietet, gegenüber diesem, einige Vorteile.
So können CSS-Angabe bespielsweise verschachtelt werden.

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

Dadurch können Sie Ihre Styles besser und übersichtlicher strukturieren.

`Was LESS noch alles bietet, finden Sie auf lessscss.org <http://http://lesscss.org/>`_

*************************************
Eigene LESS-Dateien im Child-Template
*************************************

Wenn Sie in Ihrem Child-Template auch mit LESS arbeiten möchten, empfiehlt es sich, den Ordner ``mytheme`` aus dem themes-Order des EVO-Child-Example-Templates zu kopieren und entsprechend umzubenennen, z.B. in ``meinTheme``.

Die Struktur Ihres Child-Templates sollte dann folgendermaßen aussehen:

.. image:: /_images/jtl-shop_child-template_less.jpg

Sie können nun in Ihrer theme.less LESS- oder CSS-Code einfügen und Ihren Shop individuell gestalten. Wenn Sie Variablen in der Datei **variables.less** ändern, werden diese für alle Styles in Ihrem Shop geändert.
Sie könnten, z.B. die Variable ``@brand-primary`` verändern und eine eigene Farbe eintragen. @brand-primary wird für viele Elemente in JTL-Shop verwenden. Das Ändern dieser Variable hat also starken Einfluss auf das Aussehen Ihres JTL-Shops. Probieren Sie es aus!

Anschließend müssen Sie ihr Theme kompilieren.

.. note::

    LESS-Dateien müssen **nicht** in die **template.xml** eingefügt werden. EVO Theme Editor erkennt LESS-Files automatisch

**************************************************
Eigenes Theme mit dem Evo Theme Editor kompilieren
**************************************************

Gehen Sie nun in das Backend Ihres JTL-Shops. Falls noch nicht geschehen, aktivieren Sie das Plugin **Evo Theme Editor**. Anschließend öffnen Sie das Plugin über das Menü **Plugins->EVO Theme Editor**.

Wählen Sie nun in der Select-Box unter Theme Ihr Theme aus und klicken anschließend rechts auf den Button **Theme kompilieren**.

.. image:: /_images/jtl-shop_child-template_editor.jpg

**Nun ist Ihr Template kompiliert. Fertig!**

.. note::

    **Wichtig**: Ihr theme-Ordner benötigt Schreibrechte.

Updatesicherheit
----------------

Um sicher zu sein, dass Ihre Änderungen in der ``template.xml`` nicht durch ein Update rückgängig gemacht wird, empfiehlt es sich das eigene Theme in einem :doc:`Child-Template </shop_templates/eigenes_template>` abzulegen.