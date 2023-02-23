HOOK_CARTHELPER_ADD_PRODUCT_ID_TO_CART (355)
============================================

Triggerpunkt
""""""""""""

Zu Beginn von addProductIDToCart(), vor dem addToCartCheck().


Parameter
"""""""""
``JTL\Catalog\Product\Artikel`` **product**
    **product** Instanz der Artikel-Klasse
``int|string|float`` **&qty**
    **&qty** Die Menge des Artikels, die in den Warenkorb gelegt werden soll
