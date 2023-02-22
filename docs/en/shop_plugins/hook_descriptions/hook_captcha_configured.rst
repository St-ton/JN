HOOK_CAPTCHA_CONFIGURED (270)
=============================

Triggerpunkt
""""""""""""

Die Captcha-Hooks (ab Version 5.0) werden vom ``CaptchaService`` in ``src/Services/JTL/CaptchaService.php`` bei der Ausgabe des Markup und
der Validierung getriggert.

Parameter
"""""""""

``&bool`` **isConfigured**
    In **isConfigured** übermittelt das Plugin den Status der Captcha-Konfiguration. Dies ist notwendig, um Captchas zu
    vermeiden die nie validiert werden können, weil z.B. Website- oder Secret-Keys nicht konfiguriert sind.

    * ``true`` - Captcha ist konfiguriert und kann verwendet werden
    * ``false`` - Captcha ist nicht konfiguriert und kann nicht verwendet werden

Beschreibung
""""""""""""

Dieser Hook wird getriggert, um zu prüfen ob das Captcha komplett und korrekt konfiguriert ist. Ein Plugin sollte hier
über den Parameter ``isConfigured`` den Zustand der Konfiguration signalisieren.

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr, $oPlugin;

    $args_arr['isConfigured'] = !empty($oPlugin->oPluginEinstellungAssoc_arr['mycaptcha_sitekey'])
        && !empty($oPlugin->oPluginEinstellungAssoc_arr['mycaptcha_secretkey']);
