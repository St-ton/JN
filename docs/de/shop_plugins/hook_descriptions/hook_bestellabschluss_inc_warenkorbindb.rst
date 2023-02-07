HOOK_BESTELLABSCHLUSS_INC_WARENKORBINDB (ab Version 4.06) (228)
===============================================================

Triggerpunkt
""""""""""""

Unmittelbar vor dem Speichern des Warenkorbs im Bestellabschluß

Beschreibung
""""""""""""

Der Hook wird unmittelbar vor dem Speichern des Warenkorbs im Bestellabschluß getriggert. Der Warenkorb wird als Referenz
übergeben und kann durch das Plugin modifiziert werden.

Parameter
"""""""""

``Warenkorb`` **oWarenkorb**
    In **oWarenkorb** wird ein ``Warenkorb``-Objekt als Referenz übergeben.
    In **oBestellung** wird ein ``Bestellung``-Objekt als Referenz übergeben. (ab Version 5.1.0)

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr;

    foreach ($args_arr['oWarenkorb']->PositionenArr as $i => $Position) {
        if ($Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
            // mache irgendwas mit der Warenkorbposition wenn es ein Artikel ist...
            //...
        }
    }

