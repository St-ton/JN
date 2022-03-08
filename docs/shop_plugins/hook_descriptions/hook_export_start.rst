HOOK_EXPORT_START (241)
=======================

Triggerpunkt
""""""""""""

Nach dem Laden eines Artikels

Parameter
"""""""""

``JTL\Export\FormatExporter`` **exporter**
    **&exporter** Instanz des FormatExporters

``int`` **exportID**
    **exportID** ID des Exportformats

``int`` **max**
    **max** Maximale Anzahl an zu exportierenden Produkten für diesen Durchlauf

``bool`` **isAsync**
    **isAsync** Gibt an, ob Export asynchron gestartet wurde

``bool`` **isCron**
    **isCron** Gibt an, ob Cronjob aktiv ist
