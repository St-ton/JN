HOOK_BACKEND_ACCOUNT_PREPARE_EDIT (ab Version 4.05) (223)
=========================================================

Triggerpunkt
""""""""""""

Erweiterte Attribute in das Formular zur Bearbeitung von Backend-Nutzern

Beschreibung
""""""""""""

Dieser Hook ist ein Backend-Hook und wird in ``admin/includes/benutzerverwaltung_inc.php`` zur Integration der erweiterten
Attribute in das Formular zur Bearbeitung von Backend-Nutzern getriggert.

Parameter
"""""""""

``Account`` **oAccount**
    In **oAccount** wird ein ``Account``-Objekt mit den Grunddaten des zu bearbeiteten Nutzers übergeben. Die Felder entsprechen
    den Spalten der Tabelle ``tadminlogin``.

``JTLSmarty`` **smarty**
    Der Parameter verweist auf das aktuelle JTLSmarty-Objekt des Backend-Templates.

``array`` **attribs**
    Dieses Array enthält die erweiterten Attribute zum Nutzeraccount. Das Array enthält Werte
    in der Form ``feldName => attributObject``, wobei jedes ``attributObject`` Felder enthält, die den Spalten der Tabelle
    ``tadminloginattribut`` entsprechen.

``&string`` **content**
    In **content** wird das komplette Markup für alle unterstützen Attribute zum Einsetzen in das Formular als Rückgabewert
    erwartet. Hierfür kann auf Funktionen des JTLSmarty-Objekts in **smarty** zurückgegriffen werden.

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr, $oPlugin;

    $args_arr['content'] = $args_arr['smarty']
        ->assign('attribValues', $args_arr['attribs'])
        ->fetch($oPlugin->cAdminmenuPfad . 'templates/myuserextension_index.tpl');

