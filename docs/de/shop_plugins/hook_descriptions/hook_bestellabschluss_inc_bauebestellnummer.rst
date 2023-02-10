HOOK_BESTELLABSCHLUSS_INC_BAUEBESTELLNUMMER (ab Version 4.06.15) (231)
======================================================================

Triggerpunkt
""""""""""""

Beim Speichern eine Bestellung in der Datenbank

Beschreibung
""""""""""""

Mit diesem Hook ist es möglich, die vom Shop generierte Bestellnummer, via Plugin, permanent zu ändern.

Parameter
"""""""""

``int`` **&orderNo**
    In **orderNo** befindet sich der numerische Teil der, vom Shop generierten, Bestellnummer

``string`` **&prefix**
    In **prefix** befindet sich das Bestellnummern-Präfix, welches im Backend einstellbar ist

``string`` **&suffix**
    In **suffix** befindet sich das Bestellnummern-Suffix, welches im Backend einstellbar ist
