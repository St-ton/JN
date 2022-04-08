HOOK_CART_DELETE_PARENT_CART_ITEM (337)
=======================================

Triggerpunkt
""""""""""""

Vor Löschen einer Warenkorb-Position die ein Vaterartikel ist (Vaterartikel dürfen nicht gekauft werden)


Parameter
"""""""""
``CartItem`` **positionItem**
    **positionItem** Warenkorb-Position die ein Vaterartikel ist

``bool`` **&delete**
    **&delete** Flag, das positionItem enntfernt wenn true
