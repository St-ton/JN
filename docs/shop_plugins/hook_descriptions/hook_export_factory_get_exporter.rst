HOOK_EXPORT_FACTORY_GET_EXPORTER (342)
======================================

Triggerpunkt
""""""""""""

Vor dem Initialisieren der FormatExporter-Instanz.
Dieser Hook kann genutzt werden, um den Exporter gegen eine eigene Implementation des ExporterInterfaces auszutauschen.

Parameter
"""""""""
``JTL\Export\FormatExporter`` **exporter**
    **exporter** Instanz der FormatExporter-Klasse
``int`` **exportID**
    **exportID** Die ID (kExportformat) des aktuell laufenden Exports
``JTL\Export\Model`` **model**
    **model** Das DataModel des aktuellen Exports
