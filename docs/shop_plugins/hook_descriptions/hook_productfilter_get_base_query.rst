HOOK_PRODUCTFILTER_GET_BASE_QUERY (253)
=======================================

Triggerpunkt
""""""""""""

Beim Erzeugen des Artikelfilter-Basisabfrage-Objektes

Parameter
"""""""""

``array`` **&select**
    **&select** für die Abfrage verwendete SELECTs

``array`` **&joins**
    **&joins** für die Abfrage verwendete JOINs

``array`` **&conditions**
    **&conditions** für die Abfrage verwendete WHEREs

``array`` **&groupBy**
    **&groupBy** für die Abfrage verwendete GROUP BYs

``array`` **&having**
    **&having** für die Abfrage verwendete HAVINGs

``array`` **&order**
    **&order** für die Abfrage verwendete ORDERs

``array`` **&limit**
    **&limit** für die Abfrage verwendete LIMITs

``JTL\Filter\ProductFilter`` **productFilter**
    **productFilter** aktuelles Artikelfilter-Objekt
