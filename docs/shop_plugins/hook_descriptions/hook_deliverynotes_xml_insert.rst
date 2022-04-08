HOOK_DELIVERYNOTES_XML_INSERT (340)
===================================

Triggerpunkt
""""""""""""

Bei Änderung (Insert, Update) eines Lieferscheins. Wird von JTL-Wawi mittels Abgleich ausgelöst. Der Aufruf erfolgt nach dem Aktualisieren der zugehörigen Datenbankeinträge.

Parameter
"""""""""

``object`` **deliveryNote**
    **$deliveryNote** stdClass-Instanz eines Lieferscheins. Enthält Angaben zu tLieferschein, tLieferscheinPos, tlieferscheinPosInfo und tVersand.
