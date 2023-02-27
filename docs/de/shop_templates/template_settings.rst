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

Theme
"""""

Theme
    Für das NOVA-Template gibt es aktuell die Themes clear (Standard), midnight und blackline.

Sliderposition / Full-Width Slider |br| Bannerposition / Full-Width Banner
    Diese beiden Optionen entscheiden, ob Slider und Banner über die gesamte Bildschirmbreite hinweg dargestellt
    werden oder nur über dem Content-Bereich.

Mitlaufendes Megameü im Header
    Das Megamenü wird bim Scrollen permanent angezeigt.

Favicon
    Ein Favicon ist ein kleines Bild (32x32, 16x16) welches in den Browser-Tabs neben dem Titel der Seite angezeigt
    wird.

Warenkorb-Mengen-Optionen in Dropdown anzeigen?
    Im NOVA-Template gibt es eine Plus- und eine Minus-Schaltfläche neben der Menge. |br|
    Wird diese Option deaktiviert, kann im Template die Artikelmenge als Ziffer eingegeben werden.

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
    Skype-Link      :
    TicToc-Link     :*

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

Anzahl der sichtbaren Filteroptionen in Boxen
    Dieser Wert bestimmt, wie viele Filter maximal in den jeweiligen Filterboxen angezeigt werden.

Position des Overlays
    Diese Option legt die Position der verschiedenen Artikel-Overlays (wie "auf Lager", "Ausverkauft" usw.)
    fest. |br|
    Diese Overlays sind an den vier Ecken eines Artikelbildes positionierbar.

.. hint::

    Diese Einstellung gilt nur für das NOVA-Template.

