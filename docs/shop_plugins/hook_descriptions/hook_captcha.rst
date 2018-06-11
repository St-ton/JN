Captcha Hooks (ab Version 5.0)
==============================

Die Captcha-Hooks werden vom ``CaptchaService`` in ``src/Services/JTL/CaptchaService.php`` bei der Ausgabe des Markup und
der Validierung getriggert.

.. _captcha_hooks_hook_captcha_configured:

HOOK_CAPTCHA_CONFIGURED
-----------------------

Dieser Hook wird getriggert, um zu prüfen ob das Captcha komplett und korrekt konfiguriert ist. Ein Plugin sollte hier
über den Parameter ``isConfigured`` den Zustand der Konfiguration signalisieren.

Parameter
"""""""""

``&bool`` **isConfigured**
    In **isConfigured** übermittelt das Plugin den Status der Captcha-Konfiguration. Dies ist notwendig, um Captchas zu
    vermeiden die nie validiert werden können, weil z.B. Website- oder Secret-Keys nicht konfiguriert sind.

    * ``true`` - Captcha ist konfiguriert und kann verwendet werden
    * ``false`` - Captcha ist nicht konfiguriert und kann nicht verwendet werden

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr, $oPlugin;

    $args_arr['isConfigured'] = !empty($oPlugin->oPluginEinstellungAssoc_arr['mycaptcha_sitekey'])
        && !empty($oPlugin->oPluginEinstellungAssoc_arr['mycaptcha_secretkey']);

.. _captcha_hooks_hook_captcha_markup:

HOOK_CAPTCHA_MARKUP
-------------------

Über diesen Hook wird das Captcha-Markup im Template ausgegeben. Über den Parameter ``getBody`` wird unterschieden ob das
Markup für den Body- oder den Head-Teil erzeugt werden soll.

Parameter
"""""""""

``bool`` **getBody**
    Wird **getBody** mit ``true`` übergeben, sollte das Markup für den Body-Teil erzeugt werden, ansonsten wird Markup für
    den Head-Teil erwartet.

``&string`` **markup**
    In **markup** wird das Markup als String zurückgegeben.

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

.. _captcha_hooks_hook_captcha_validate:

HOOK_CAPTCHA_VALIDATE
---------------------

Der Hook ``HOOK_CAPTCHA_VALIDATE`` wird zur Validierung des Captcha getriggert.

Parameter
"""""""""

``array`` **requestData**
    **requestData** enthält die Get- bzw. Post-Daten des aktuellen Requests.

``&bool``  **isValid**
    Über **isValid** wird vom Plugin der Status der Validierung zurückgegeben.

    * ``true`` - Das Captcha wurde validiert und ist gültig
    * ``false`` - Das Captcha konnte nicht validiert werden oder ist ungültig

.. note::

    Für die Validierung sollten nur die Werte aus dem übergebenen ``requestData``-Parameter verwendet werden und
    nicht direkt ``$_GET``, ``$_POST``, etc.

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr, $oPlugin;

    $secret = $oPlugin->oPluginEinstellungAssoc_arr['mycaptcha_secretkey'];
    $url    = 'https://captchaservice.com/mycaptcha/api/siteverify';

    $json = http_get_contents($url, 30, [
        'secret'   => $secret,
        'response' => $requestData['mycaptcha-response']
    ]);

    if (is_string($json)) {
        $result = json_decode($json);
        if (json_last_error() === JSON_ERROR_NONE) {
            $args_arr['isValid'] = isset($result->success) && $result->success;
        }
    } else {
        $args_arr['isValid'] = false;
    }