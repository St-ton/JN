HOOK_DELIVERYNOTES_XML_SHIPPING (341)
=====================================

Triggerpunkt
""""""""""""

Bei Änderung (Insert, Update) von Versandinformationen. Wird von JTL-Wawi mittels Abgleich ausgelöst. Der Aufruf erfolgt nach dem Aktualisieren des zugehörigen Datenbankeintrages.

Parameter
"""""""""

``object`` **shipping**
    **shipping** stdClass-Instanz einer Versandinformation. Enthält Angaben zu tVersand.
