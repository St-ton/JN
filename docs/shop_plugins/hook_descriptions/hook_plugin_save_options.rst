HOOK_PLUGIN_SAVE_OPTIONS (280)
==============================

Triggerpunkt
""""""""""""

Nach dem Speichern der Plugin-Optionen

Parameter
"""""""""

``JTL\Plugins\PluginInterface`` **plugin**
    **plugin** Pluginobjekt

``bool`` **&hasError**
    **&hasError** Flag, welches anzeigt, ob das Plugin fehlerfrei geladen werden konnte

``string`` **&msg**
    **&msg** Meldung, die beim Laden aufgetreten ist

``string`` **error**
    **error** (ab Version 5.0) Fehlermeldung

``array`` **options**
    **options** Liste von Konfigurationsoptionen
