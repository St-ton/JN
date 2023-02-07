HOOK_CAPTCHA_MARKUP (271)
=========================

Triggerpunkt
""""""""""""

Die Captcha-Hooks (ab Version 5.0) werden vom ``CaptchaService`` in ``src/Services/JTL/CaptchaService.php`` bei der Ausgabe des Markup und
der Validierung getriggert.

Parameter
"""""""""

``bool`` **getBody**
    Wird **getBody** mit ``true`` übergeben, sollte das Markup für den Body-Teil erzeugt werden, ansonsten wird Markup für
    den Head-Teil erwartet.

``&string`` **markup**
    In **markup** wird das Markup als String zurückgegeben.

Beschreibung
""""""""""""

Über diesen Hook wird das Captcha-Markup im Template ausgegeben. Über den Parameter ``getBody`` wird unterschieden ob das
Markup für den Body- oder den Head-Teil erzeugt werden soll.

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr, $oPlugin;

    if ($args_arr['getBody']) {
        $args_arr['markup'] = Shop::Smarty()
            ->assign('mycaptcha_sitekey', $oPlugin->oPluginEinstellungAssoc_arr['mycaptcha_sitekey'])
            ->fetch($oPlugin->cFrontendPfad . '/templates/captcha.tpl');
    } else {
        $args_arr['markup'] = '<script type="text/javascript">jtl.load("' . $oPlugin->cFrontendPfadURL . 'js/mycaptcha.js");</script>';
    }
