HOOK_BACKEND_ACCOUNT_EDIT (ab Version 4.05) (222)
=================================================

Triggerpunkt
""""""""""""

Beim Validieren, Speichern, Sperren, Entsperren und Löschen von Backend-Benutzern

Beschreibung
""""""""""""

Dieser Hook ist ein Backend-Hook und wird in ``admin/includes/benutzerverwaltung_inc.php`` beim Validieren, Speichern, Sperren,
Entsperren und Löschen von Backend-Benutzern getriggert.

Parameter
"""""""""

``Account`` **oAccount**
    In **oAccount** wird ein ``Account``-Objekt mit den Grunddaten des zu bearbeiteten Nutzers übergeben. Die Felder entsprechen
    den Spalten der Tabelle ``tadminlogin``.

``string`` **type**
    Die möglichen Werte in **type** sind ``VALIDATE``, ``SAVE``, ``LOCK``, ``UNLOCK`` und ``DELETE``, je nach konkret auszuführender Aktion.

``&array`` **attribs**
    Dieses Array wird als Referenz übergeben und enthält die erweiterten Attribute zum Nutzeraccount. Das Array enthält Werte
    in der Form ``feldName => feldWert``.

    Im Validieren-Context (``VALIDATE``) können die erweiterten Attribute vor dem Speichern manipuliert werden.
    Der Shop-Core unterstützt für jedes erweiterte Attribut einen einfachen Wert (``VARCHAR(512)``) und einen erweiterten Text (``TEXT``).
    Um einen erweiterten Text zu speichern, kann das Attrbut in der Form ``feldName => [feldWertNormal, feldWertLang]`` zurückgegeben werden.

    .. note::

        Dieser Parameter ist nur im Validieren- und Speichern-Context (``VALIDATE|SAVE``) gültig und ansonsten ``NULL``.

``&array`` **messages**
    Ist eine Referenz auf ein Array der Form ``[ 'error' => string, 'notice' => string ]``. In diesem können Fehler und Hinweise
    an die GUI des Backends zurückgegeben werden.

``&(bool|array)`` **result**
    Dies ist der Rückgabewert des Aufrufes. Er sollte ``true`` im Erfolgsfall und ansonsten ein Array mit Feldnamen (als Schlüssel)
    sein, die zu Fehlern geführt haben. Dieses Array wird an die GUI des Backends zurückgegeben, um z.B. fehlerhafte Felder farblich markieren zu können.

.. note::

    Der Shop-Core besitzt einen Standardmechanismus zum Speichern von erweiterten Attributen. Solange keine weiteren Abhängigkeiten
    vorhanden sind muss der Speichern-Context (``SAVE``) nicht implementiert werden!

Beispiel für eine Implementierung
"""""""""""""""""""""""""""""""""

.. code-block:: php

    global $args_arr;

    switch ($args_arr['type']) {
        case 'VALIDATE':
            if (empty($args_arr['attribs']['myField'])) {
                $args_arr['messages']['error'] = 'myField darf nicht leer sein!';
                $args_arr['result'] = ['myField' => 1];
            } else {
                $args_arr['attribs']['myField'] = [
                    strip_tags(substr($args_arr['attribs']['myField'], 0, 250)),
                    $args_arr['attribs']['myField']
                ];
                $args_arr['result'] = true;
            }
            break;
        case 'SAVE':
            // do something to save my own dependencies
            $args_arr['result'] = MyPluginHelper::SaveDependencies(
                $args_arr['oAccount'],
                $args_arr['attribs'],
                $args_arr['messages']);
            break;
        default:
            $args_arr['result'] = true;
    }
