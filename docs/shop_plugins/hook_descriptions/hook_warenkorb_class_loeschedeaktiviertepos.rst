HOOK_WARENKORB_CLASS_LOESCHEDEAKTIVIERTEPOS (ab Version 4.06.18) (230)
======================================================================

Triggerpunkt
""""""""""""

Für jede Position des Warenkorbs

Beschreibung
""""""""""""

Der Hook wird für jede Position des Warenkorbs getriggert, um zwischenzeitlich deaktivierte, gelöschte oder anderweitig ungültige Artikel aus dem Warenkorb zu entfernen.

Parameter
"""""""""

``WarenkorbPos`` **oPosition**
    In **oPosition** wird ein ``WarenkorbPos``-Objekt übergeben.

``bool`` **&delete**
    In **delete** wird ein Boolean als Referenz übergeben. Dieser Wert dient als Rückgabe, ob die Position aus dem Warenkorb gelöscht werden muß (``true``) oder weiterhin im Warenkorb gültig ist (``false``).

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr;

    if ($args_arr['oPosition']->cLieferstatus === 'ausverkauft') {
        $args_arr['delete'] = true;
    } else {
        $args_arr['delete'] = false;
    }

