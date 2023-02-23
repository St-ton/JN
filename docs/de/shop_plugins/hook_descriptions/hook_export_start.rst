HOOK_EXPORT_START (351)
=======================

Triggerpunkt
""""""""""""

Vor dem Start eines Export-Durchlaufs.

Parameter
"""""""""
``JTL\Export\FormatExporter`` **exporter**
    **exporter** Instanz der FormatExporter-Klasse selbst
``int`` **exportID**
    **exportID** Die ID (kExportformat) des aktuell laufenden Exports
``int`` **max**
    **max** Maximale Anzahl an zu exportierenden Produkten für diesen Durchlauf
``bool`` **isAsync**
    **isAsync** Gibt an, ob der aktuelle Export aynchron aus dem Shopbackend heraus gestartet wurde
``bool`` **isCron**
    **isCron** Gibt an, ob der aktuelle Export als Cronjob gestartet wurde
