Template Einstellungen
======================

.. |br| raw:: html

        <br />

Was bewirken diese Einstellungen?
---------------------------------

Mit diesen Einstellungen ist es möglich, verschiedene Teile des Templates schon beim Aufruf der Seite auf
unterschiedliche Art und Weise reagieren zu lassen.

Die Template-Einstellungen befinden sich im Shop-Backend unter ``Templates -> [Template-Name] -> Einstellungen``
und werden aus der, zum Template zugehörigen, ``template.xml`` generiert. |br|
Es befinden sich bereits einige vordefinierte Einstellungen in der ``template.xml``. Sie können aber auch selbst
Einstellungen hinzufügen, vorzugsweise in einem eigenen :doc:`Child-Template </shop_templates/eigenes_template>`.
In den Template-Einstellungen können Sie festlegen, wie die Seite ausgegeben wird — zum Beispiel, welches Theme
verwendet werden soll oder ob im Footer Links angezeigt werden sollen und Einiges mehr.

Beschreibung der einzelnen Einstellungen
----------------------------------------

Bei jedem Template werden einige vordefinierte Einstellungen mitgeliefert, deren Bedeutung im Folgenden genauer erklärt
wird.

Allgemein
"""""""""

Komprimierung von JavaScript- und CSS-Dateien
    Wird diese Einstellung aktiviert, werden Javascript und CSS-Dateien komprimiert, um so die Dateigröße zu verringern
    und damit Traffic zu sparen.

Komprimierung des HTML-Ausgabedokuments
    Die gesamte HTML-Struktur wird bei der Ausgabe komprimiert, um ebenfalls die Größe der Datei zu verringern, die zum
    Browser übertragen werden muss.

Komprimierung von Inline-CSS-Code
    CSS-Code, der sich innerhalb des HTML und nicht in einer separaten Datei befindet, wird komprimiert.

Komprimierung von Inline-JavaScript-Code
    JavaScript-Code, der sich innerhalb des HTML und nicht in einer separaten Datei befindet, wird komprimiert.

Benutzerdefinierte Template-Dateien verwenden?
    Wird diese Einstellung aktiviert, können im Template für jede Template-Datei (.tpl) benutzerdefinierte Dateien
    erstellt werden. Kopieren Sie dafür die jeweilige Datei und hängen Sie an den Dateinamen ``_custom`` an. |br|
    Beispiel: Kopieren Sie die Datei ``header.tpl`` und benennen Sie die kopierte Datei
    in ``header_custom.tpl`` um. |br|
    Diese Dateien werden beim Update des Templates nicht überschrieben und sind deshalb,
    nach :doc:`Child-Template </shop_templates/eigenes_template>`, das bevorzugte Mittel um minimale Änderungen am
    Template vorzunehmen.

.. caution::

    Diese Einstellung gilt nur für das EVO-Template und nicht für EVO-Child-Templates, da dort alle vorhandenen
    Dateien die äquivalente Original-Datei überschreiben. |br|
    Die Funktion ist veraltet und im NOVA-Template bereits nicht mehr vorhanden.

Cron aktivieren?
    Der Cron übernimmt die Aufgabe, sich ständig wiederholende Aufgaben abzuschließen, z. B.: Aktualisierung der
    Statistiken etc. (Empfohlene Einstellung: an)

.. attention::

    Diese Funktion ist nur noch in Shop 4.x vorhanden!

Theme
"""""

Theme
    Hier wird das Theme ausgewählt, das für das Template verwendet werden soll. |br|
    Im EVO-Template kann aus 16 Themes ausgewählt werden. Aktuell ist für das NOVA-Template nur das Standard-Theme
    verfügbar, weitere Themes werden jedoch folgen.

Hintergrundbild
    Das Evo-Template wird mit 13 Hintergrundbildern ausgeliefert. Diese befinden sich im
    Ordner ``<Shop-Root>/templates/Evo/themes/base/images/backgrounds``.

    Falls Sie ein eigenes Theme erstellt haben und ein eigenes Hintergrundbild verwenden möchten, legen Sie dieses
    bitte in den Ordner ``<Shop-Root>/templates/Evo/themes/ihrTheme``. Geben Sie dem Hintergrundbild den
    Dateinamen ``background.jpg``. Wählen Sie es anschließend in den *Theme-Einstellungen* unter *Hintergrundbild* aus,
    indem Sie in der Selectbox den Eintrag *Custom* selktieren.
    Verfahren Sie genauso, wenn Sie das EVO-Child-Template verwenden.

.. hint::

    Diese Einstellung gilt nur für das EVO-Template.

Boxed Layout
    Hier wird festgelegt, ob für die Darstellung CSS-Boxes verwendet werden oder ob ein strikte Trennung der
    Elemente erfolgen soll.

.. hint::

    Diese Einstellung gilt nur für das EVO-Template.

Sliderposition / Full-Width Slider |br| Bannerposition / Full-Width Banner
    Diese beiden Optionen entscheiden, ob Slider und Banner über die gesamte Bildschirmbreite hinweg dargestellt
    werden oder nur über dem Content-Bereich.

.. important::

    Für das EVO-Template gilt hier die Ausschlussregel, dass diese Option nur greift, wenn
    die "*Boxed Layout*"-Darstellung **aktiv** ist.

Mitlaufendes Megameü im Header
    Das Megamenü wird bim Scrollen permanent angezeigt.

Favicon
    Ein Favicon ist ein kleines Bild (32x32, 16x16) welches in den Browser-Tabs neben dem Titel der Seite angezeigt
    wird.

Warenkorb-Mengen-Optionen in Dropdown anzeigen?
    Beim EVO-Template wird im Warenkorb die Menge der Warenkorbposition als Dropdown angezeigt. |br|
    Im NOVA-Template gibt es hingegen eine Plus- und eine Minus-Schaltfläche neben der Menge. |br|
    Wird diese Option deaktiviert, kann in beiden Templates die Artikelmenge als Ziffer eingegeben werden.

Megamenü
""""""""

Kategorien
    Ist diese Option aktiv, werden alle Hauptkategorien des Shops im Megamenü dargestellt.
    Falls Sie diese Option deaktivieren, müssen Sie in der Boxenverwaltung eine Kategoriebox für jede Seite aktivieren,
    damit Ihre Kunden die Kategorien weiterhin erreichen.

Hauptkategorie Infobereich
    Ist diese Option aktiviert, wird im Megamenü ein zusätzliches Bild für die Hauptkategorie angezeigt. Andernfalls
    sehen Sie nur die Unterkategorien.

Kategoriebilder
    Diese Option bewirkt die Anzeige von Kategoriebildern anstelle von Kategorienamen.

Unterkategorien:
    Hiermit werden zusätzlich zu den Hauptkategorien auch die Unterkategorien angezeigt.

Seiten der Linkgruppe 'megamenu'
    Ist diese Option aktiviert, dann achtet das Megamenü auf eine Linkgruppe mit dem Namen ``megamenu`` und zeigt diese
    Links zusätzlich an. |br|
    Diese Linkgruppe kann man unter ``Inhalte -> Eigene Seiten`` hinzufügen. Diese Seiten können dann im Megamenü
    hierarchisch aufgeklappt werden.

Hersteller-Dropdown
    Aktiviert einen zusätzlichen Menüpunkt im Megamenü, welcher eine Liste aller Hersteller anzeigt, die aktuell Artikel im
    Shop anbieten.

Footer-Einstellungen
""""""""""""""""""""

Newsletter-Anmeldung im Footer
    Diese Einstellung blendet ein Eingabefeld für die Anmeldung zum Newsletter im Footer ein. |br|
    Wenn Sie diese Option aktivieren, beachten Sie bitte auch die Einstellungen zum Newsletter!

Social-Media-Buttons im Footer
    Mit der Aktivierung dieser Einstellung wird für jede der folgenden Zeilen, die mit einem Link gefüllt sind, die
    entsprechende Social-Media-Schaltfläche im Footer eingeblendet.

    *Facebook-Link   : ...
    Twitter-Link    :
    GooglePlus-Link :
    YouTube-Link    :
    Xing-Link       :
    LinkedIn-Link   :
    Vimeo-Link      :
    Instagram-Link  :
    Pinterest-Link  :
    Skype-Link      :*

Listen- und Galerieansicht
"""""""""""""""""""""""""""

Hovereffekt für Zusatzinfos
    Durch Aktivieren dieser Einstellung werden Details zum Artikel in einer Hover-Box oder bei Touchdisplays per Tap
    angezeigt.

.. hint::

    Im NOVA-Template wirkt sich diese Einstellung nur auf die Listenansicht aus. Die Galerieansicht wird nicht
    beeinflusst.

Variationsauswahl anzeigen
    Hier legen Sie für Variationskombinationen fest, wie viele Variationen maximal in der Listen- oder Galerieansicht
    zur Auswahl angezeigt werden sollen. Bei Artikeln, die über mehr Variationen verfügen, wird die Variationsauswahl
    in der Listen- oder Galerieansicht nicht angezeigt.

.. hint::

    Die Option funktioniert nur, wenn "*Hovereffekt für Zusatzinfos*" aktiviert ist. |br|
    Im NOVA-Template wirkt sich diese Einstellung nur auf die Listenansicht aus. Die Galerieansicht wird nicht
    beeinflusst.

Anzahl der möglichen Variationswerte für Radio und Swatches
    Wenn Sie die Option "*Variationsauswahl anzeigen*" eingeschränkt haben, können Sie hier festlegen, wie viele
    Radio-Buttons bzw. Swatches zur Variationsauswahl in der Listen- oder Galerieansicht angezeigt werden sollen.
    Bei Artikeln mit mehr Variationswerten wird keine Auswahl in der Listen- oder Galerieansicht angezeigt.

.. hint::

    Die Option funktioniert nur, wenn "*Variationsauswahl anzeigen*" aktiviert ist.

Quickview für Artikeldetails
    Mit dieser Option legen Sie fest, ob die wichtigsten Artikeldetails bereits in der Listen- bzw. Galerieansicht
    angezeigt werden können. |br|
    Bei aktivierter Option wird über dem Artikelbild die Schaltfläche "Vorschau" angezeigt, wenn Ihre Kunden mit der
    Maus über das Bild fahren.

.. hint::

    Diese Einstellung gilt nur für das EVO-Template.

Filteroptionen bei Seitenaufruf anzeigen?
    Im EVO-Template wird hiermit entschieden, ob die Filteroptionen beim Aufruf einer Artikellistenansicht
    ausgeklappt dargestellt werden oder zusammengeklappt.

.. hint::

    Diese Einstellung gilt nur für das EVO-Template.

Anzahl der sichtbaren Filteroptionen in Boxen
    Dieser Wert bestimmt, wie viele Filter maximal in den jeweiligen Filterboxen angezeigt werden.

Position des Overlays
    Diese Option legt die Position der verschiedenen Artikel-Overlays (wie "auf Lager", "Ausverkauft" usw.)
    fest. |br|
    Diese Overlays sind an den vier Ecken eines Artikelbildes positionierbar.

.. hint::

    Diese Einstellung gilt nur für das NOVA-Template.

Entwickler-Einstellungen
""""""""""""""""""""""""

LiveStyler aktivieren
    Diese Option aktiviert den "*EVO-LiveStyler*". Er wird vorwiegend für Theme-Anpassungen eingesetzt.

.. hint::

    Diese Einstellung gilt nur für das EVO-Template. |br|
    Wenn Sie die Möglichkeiten des EVO-LiveStylers nutzen wollen, installieren Sie bitte ebenfalls das
    Plugin "*Theme-Editor*"!

Eine ausführlichere Beschreibung zu diesem Thema finden Sie im Abschnitt :doc:`hier </shop_templates/theme_edit>`.
