HOOK_SYNC_SEND_AVAILABILITYMAILS (336)
======================================

Triggerpunkt
""""""""""""

In ``sendAvailabilityMails()``, in dbeS, vor dem Versenden von Verfügbarkeitsbenachrichtigungen

Parameter
"""""""""

``bool`` **&sendMails**
    **sendMails** Wenn auf FALSE gesetzt, werden keine Verfügbarkeitsbenachrichtigungen versendet

``object`` **product**
    **product** Der Artikel, für den Benachrichtigung versendet werden

``array`` **&subscriptions**
    **subscriptions** Referenz auf Liste von Einträgen aus tverfuegbarkeitsbenachrichtigung
