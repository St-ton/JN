HOOK_MAILER_PRE_SEND (292)
==========================

Triggerpunkt
""""""""""""

Direkt nach dem Senden einer Email via PHPMailer

Parameter
"""""""""

``\JTL\Mail\Mailer`` **mailer**
    **mailer** Mailer-Objekt

``\JTL\Mail\Mail\MailInterface`` **mail**
    **mail** Mail-Objekt

``\PHPMailer\PHPMailer\PHPMailer`` **phpmailer**
    **phpmailer** PHPMailer-Instanz

``bool`` **status**
    **status** TRUE wenn erfolgreich versendet, FALSE sonst
