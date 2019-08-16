HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI (76)
==========================================================

Triggerpunkt
""""""""""""

Plausibilitätsprüfung für die unregistrierte Bestellung, im Bestellvorgang

Parameter
"""""""""

``int`` **&nReturnValue**
    **&nReturnValue** Resultat der Plausibilitätsprüfung

``array`` **&fehlendeAngaben**
    **&fehlendeAngaben** Liste der fehlenden Angaben

``JTL\Customer\Kunde`` **&Kunde**
    **&Kunde** Kundenobjekt

``array`` **cPost_arr**
    **cPost_arr** HTTP-POST-Variablen
