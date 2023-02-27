HOOK_BESTELLUNGEN_XML_BEARBEITEUPDATE (209)
===========================================

Triggerpunkt
""""""""""""

Nach dem Update einer Bestellung in der Datenbank (in dbeS)

Parameter
"""""""""

``stdClass`` **&oBestellung**
    **&oBestellung** Objekt der neuen Bestellung

``stdClass`` **&oBestellungAlt**
    **&oBestellungAlt** Objekt der alten Bestellung

``JTL\Customer\Kunde`` **&oKunde**
    **&oKunde** Kundenobjekt
