HOOK_PAYMENT_METHOD_ADDINCOMINGPAYMENT (401)
============================================

Triggerpunkt
""""""""""""

Nach dem Speichern des Zahlungseingangs durch eine Plugin-Zahlungsart

Parameter
"""""""""

``JTL\Checkout\Bestellung`` **oBestellung**
    **oBestellung** Instanz der Bestellung

``stdClass`` **oZahlungseingang**
    **oZahlungseingang** Model des soeben gespeicherten Zahlungseingangs
