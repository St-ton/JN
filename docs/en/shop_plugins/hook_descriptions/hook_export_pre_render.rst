HOOK_EXPORT_PRE_RENDER (350)
============================

Triggerpunkt
""""""""""""

Vor dem Rendern eines einzelnen Artikels während eines Exportdurchgangs

Parameter
"""""""""

``JTL\Export\Product`` **product**
    **product** Instanz des aktuell exportierten Produkts

``JTL\Export\FormatExporter`` **exporter**
    **exporter** Instanz des aktiven Exporters

``int`` **exportID**
    **exportID** ID des aktiven Exportformats
