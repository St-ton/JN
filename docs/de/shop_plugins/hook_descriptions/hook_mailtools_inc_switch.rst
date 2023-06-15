HOOK_MAILTOOLS_INC_SWITCH (100)
===============================

Triggerpunkt
""""""""""""

Nach dem Mail-Template-Wechsel, beim Senden einer Mail

Parameter
"""""""""

``MailSmarty\JTLSmarty`` **mailsmarty**
    **mailsmarty** JTLSmarty- / bzw. MailSmarty-Objekt

``JTL\Mail\Renderer\SmartyRenderer`` **renderer**
    **renderer** SmartyRenderer-Objekt

``mail`` ab Version 5.0: Objekt der Email
   default ``=null``

``int`` **kEmailvorlage**
    **kEmailvorlage** ID der Emailvorlage

``int`` **kSprache**
    **kSprache** Sprach-ID

``string`` **cPluginBody**
    **cPluginBody** default ``=''``

``JTL\Mail\Template\TemplateInterface`` **cPluginBody**
    **cPluginBody** TemplateInterface

``JTL\Mail\Template\Model`` **model**
    **model** Vorlagendaten (Model)

``JTL\Mail\Template\Model`` **Emailvorlage**
    **Emailvorlage** Emailvorlage
