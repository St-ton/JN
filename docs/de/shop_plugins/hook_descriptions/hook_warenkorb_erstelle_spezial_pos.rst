HOOK_WARENKORB_ERSTELLE_SPEZIAL_POS (310)
=========================================

Triggerpunkt
""""""""""""

Nach dem Anlegen einer Spezialposition im Warenkorb.

Parameter
"""""""""

``int`` **productID**
    **productID** Artikel-ID

``\JTL\Cart\CartItem[]`` **&positionItems**
    **&positionItems** Positionen-array

``float`` **&qty**
    **&qty** hinzuzufügende Anzahl
