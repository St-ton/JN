HOOK_MAILTOOLS_SENDEMAIL_ENDE (153)
===================================

Triggerpunkt
""""""""""""

Vor dem tatsächlichen Senden einer Email

Parameter
"""""""""

``JTL\Smarty\JTLSmarty|JTL\Smarty\MailSmarty`` **mailsmarty**
    **mailsmarty** MailSmarty-Objekt

``JTL\Mail\Mail\MailInterface`` **mail**
    **mail** (ab Version 5.0) MailInterface

``int`` **kEmailvorlage**
    **kEmailvorlage** (ab Version 5.0) Emailvorlage

``int`` **kSprache**
    **kSprache** Sprach-ID

``string`` **cPluginBody**
    **cPluginBody** (ab Version 5.0) default ``= ''``

``object`` **Emailvorlage**
    **Emailvorlage** (ab Version 5.0) default ``= null``

``JTL\Mail\Template\TemplateInterface`` **template**
    **template** (ab Version 5.0) TemplateInterface
