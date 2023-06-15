.. |br| raw:: html

      <br />

HOOK_BOXEN_INC_UMFRAGE (95)
===========================

Dieser Hook wurde entfernt in: Shop 5.0.0-rc.2
(Grund: https://issues.jtl-software.de/issues/SHOP-3408)

Triggerpunkt
""""""""""""

In ``includes/src/Boxes/Items/Poll.php``, |br|
vor der Anzeige der Umfrage.

Parameter
"""""""""

``JTL\Boxes\Items\Poll`` **&box**
    **&box** Umfrage-Objekt

``array`` **&cache_tags**
    **&cache_tags** Umfang des Zwischenspeicherns

``bool`` **cached**
    **cached** Flag "wurde vom Zwischenspeicher gelesen?"
