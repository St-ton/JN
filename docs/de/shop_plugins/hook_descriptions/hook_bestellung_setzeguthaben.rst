HOOK_BESTELLUNG_SETZEGUTHABEN (335)
===================================

Triggerpunkt
""""""""""""

Direkt bei der Ermittlung des abzuziehenden Guthabens, wenn der Kunde die Option zur Verwendung seines Guthabens gewählt hat.
Mit diesem Hook kann das zu verwendende Guthaben manipuliert werden, um anderweitige Zahlungen wie z.B. "echte" Gutscheine zu berücksichtigen.


Parameter
"""""""""
``float`` **&creditToUse**
    **&creditToUse** Fließkommazahl mit dem Wert der aktuell als Guthaben verrechnet werden würde. Durch Veränderung des Parameter kann die Wertänderung zurückgegeben werden.
``float`` **cartTotal**
    **cartTotal** Fließkommazahl mit dem aktuellen Betrag des Warenkorbs.
``float`` **customerCredit**
    **customerCredit** Fließkommazahl mit dem aktuell für den Kunden verfügbaren Guthaben.
