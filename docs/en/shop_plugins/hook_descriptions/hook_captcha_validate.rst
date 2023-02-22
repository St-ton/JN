HOOK_CAPTCHA_VALIDATE (272)
===========================

Triggerpunkt
""""""""""""

Die Captcha-Hooks (ab Version 5.0) werden vom ``CaptchaService`` in ``src/Services/JTL/CaptchaService.php`` bei der Ausgabe des Markup und
der Validierung getriggert.

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

Beschreibung
""""""""""""

Der Hook ``HOOK_CAPTCHA_VALIDATE`` wird zur Validierung des Captcha getriggert.

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
