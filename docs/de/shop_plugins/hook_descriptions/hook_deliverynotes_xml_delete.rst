HOOK_DELIVERYNOTES_XML_DELETE (342)
===================================

Triggerpunkt
""""""""""""

Beim Löschen eines Lieferscheins. Wird von JTL-Wawi mittels Abgleich ausgelöst. Der Aufruf erfolgt direkt vor dem Löschen der zugehörigen Datenbankeinträge.

Parameter
"""""""""

``int`` **deliveryNoteID**
    **deliveryNoteID** Die ID entspricht einem Schlüsselwert (kLieferschein) in tLieferschein.
