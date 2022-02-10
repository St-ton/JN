HOOK_EXPORT_PRE_RENDER (340)
============================

Triggerpunkt
""""""""""""

Vor dem Rendern eines Produktes innerhalb von Exporten.
Mit diesem Hook können Eigenschaftes des zu exportierenden Produktes modifiziert werden.


Parameter
"""""""""
``JTL\Export\Product`` **product**
    **product** Instanz des zu exportierenden Produktes
``JTL\Export\FormatExporter`` **exporter**
    **exporter** Instanz der FormatExporter-Klasse selbst
``int`` **exportID**
    **exportID** Die ID (kExportformat) des aktuell laufenden Exports
