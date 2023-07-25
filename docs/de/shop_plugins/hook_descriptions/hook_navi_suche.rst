HOOK_NAVI_SUCHE (161)
=====================

Triggerpunkt
""""""""""""

Bevor die Anzahl der Artikel ermittelt wird

Parameter
"""""""""

``bool`` **bExtendedJTLSearch**
    **bExtendedJTLSearch** Flag, welches die erweiterte Suche anzeigt

``mixed`` **&oExtendedJTLSearchResponse**
    **&oExtendedJTLSearchResponse** default ``= null``

``array`` **&cValue**
    **&cValue** Suchbegriff

``int`` **&nArtikelProSeite**
    **&nArtikelProSeite** Anzahl der anzuzeigenden Ergebisse pro Seite

``int`` **nSeite**
    **nSeite** aktuelle Seite

``string`` **nSortierung**
    **nSortierung** vom Benutzer gewählte Sortierung der Ergebnisse

``int`` **bLagerbeachten**
    **bLagerbeachten** Flag, welches signalisiert, ob Lagerbestände beachtet werden sollen
