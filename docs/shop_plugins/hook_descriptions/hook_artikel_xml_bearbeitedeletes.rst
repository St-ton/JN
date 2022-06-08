HOOK_ARTIKEL_XML_BEARBEITEDELETES (152)
=======================================

Triggerpunkt
""""""""""""

In ``bearbeiteDeletes()``, in dbeS, nach dem Löschen eines Artikels aus der Datenbank (in dbeS)

Parameter
"""""""""

``int`` **kArtikel**
    **kArtikel** Artikel-ID

``int`` **kVaterArtikel** (ab 5.1.3)
    **kVaterArtikel** Artikel-ID des Vaterartikels
