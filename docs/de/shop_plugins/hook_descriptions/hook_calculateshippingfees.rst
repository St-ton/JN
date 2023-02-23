HOOK_CALCULATESHIPPINGFEES (307)
================================

Triggerpunkt
""""""""""""

Nach der Versandkostenberechnung durch die Standardmodule, aber - im Gegensatz zu
:doc:`HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS <hook_toolsglobal_inc_berechneversandpreis>` -
vor der Berechnung weitergehender Kosten durch abhängige Versandarten, Zuschläge oder einer möglichen Deckelung.

Parameter
"""""""""

``float`` **price**
    **price** Rückgabe des berechneten Versandpreises

``JTL\Checkout\Versandart|object`` **shippingMethod**
    **shippingMethod** Versandart

``string`` **iso**
    **iso** ISO-Code der Währung

``JTL\Catalog\Product\Artikel|stdClass`` **additionalProduct**
    **additionalProduct** Zusatzartikel

``JTL\Catalog\Product\Artikel|null`` **product**
    **product** Artikel für artikelabhängige Versandkosten
