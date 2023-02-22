HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS (106)
===============================================

Triggerpunkt
""""""""""""

Nach der Berechnung der Versandkosten

Parameter
"""""""""

``float|int`` **fPreis**; ab 4.06: **&fPreis**
    **&fPreis** Preis

``object`` **versandart**
    **versandart** Versandart-Objekt

``string`` **cISO**
    **cISO** ISO-String

``oArtikel|stdClass`` **oZusatzArtikel**
    **oZusatzArtikel** Zusatzartikel

``JTL\Catalog\Product\Artikel|null`` **Artikel**
    **Artikel** Artikel-Objekt
