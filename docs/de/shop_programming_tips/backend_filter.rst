Backend-Filter
==============

.. |br| raw:: html

   <br />

Filter ermöglichen es, mit einer konfigurierbaren Menge von Textfeldern, Selectboxen oder Date-Range-Pickern Einträge
einer Tabellenübersicht im Backend einzugrenzen. Einsatzbeispiele finden Sie unter anderem unter
*Aktionen -> Coupons* (in JTL-Shop 4.x unter *Kaufabwicklung -> Kupons*) oder unter *SEO -> Weiterleitungen* (in
JTL-Shop 4.x unter *Globale Einstellungen -> Weiterleitungen*).

Ein Filter besteht aus einer Sammlung von Filterfeldern unterschiedlichen Typs (Freitext, Auswahl, Date-Range). |br|
Diese Filterfelder werden mit dem Template auf der gewünschten Seite zum Suchen und Filtern angeboten.
Die Bedingungen, die jedes Feld erzeugt, werden konjunktiv (UND) verknüpft und für eine anschließende Datenbankabfrage
als ``WHERE``-Klausel zur Verfügung gestellt. |br|
Das bedeutet, dass jedes Filterfeld, falls es definiert ist, die Ergebnismenge weiter einschränkt.

Dateien der *Backend-Filter*
----------------------------

Alle Klassen, die zu den Backend-Filtern gehören, befinden sich im Verzeichnis ``includes/src/Pagination/``,
die Template-Datei entsprechend im ``tpl_inc/``-Verzeichnis.

+-------------------------------------------------------+------------------------------------------------+
| Datei                                                 | Funktion                                       |
+=======================================================+================================================+
| ``Filter.php``                                        | Filter-Klasse                                  |
+-------------------------------------------------------+------------------------------------------------+
| ``FilterField.php``                                   | Abstrakte Basisklasse für jeden Filterfeld-Typ |
+-------------------------------------------------------+------------------------------------------------+
| ``FilterTextField.php``                               | Freitext-Filterfeld-Klasse                     |
+-------------------------------------------------------+------------------------------------------------+
| ``FilterSelectField.php``                             | Selectbox-Filterfeld-Klasse                    |
+-------------------------------------------------------+------------------------------------------------+
| ``FilterSelectOption.php``                            | Optionsklasse für ein Selectbox-Filterfeld     |
+-------------------------------------------------------+------------------------------------------------+
| ``FilterDateRangeField.php``                          | Klasse des Filterfeldes "Date-Range-Picker"    |
+-------------------------------------------------------+------------------------------------------------+
| ``admin/templates/bootstrap/tpl_inc/filtertools.tpl`` | Template für das Backend                       |
+-------------------------------------------------------+------------------------------------------------+

Quick-Start
-----------

Erzeugen Sie als erstes eine Instanz des Filters und weisen Sie ihm einen ID-String zu. Mit diesem ID-String können
der Filter und seine in der Session gespeicherten Einstellungen von denen anderer Instanzen unterschieden werden:

.. code-block:: php

   $oFilterStandard = new Filter('standard');

Fügen Sie dem Filter nun ein Freitext-Suchfeld hinzu, um beispielsweise in einer Tabellenspalte ``cName`` zu
suchen. |br|
Dieses Textfeld bekommt hier die Beschriftung "Name":

.. code-block:: php

   $oFilterStandard->addTextfield('Name', 'cName');

Fügen Sie dem Filter ein Dropdown-Auswahlfeld hinzu, dessen Wert z. B. mit einer Spalte ``cAktiv`` verglichen wird. |br|
Das Auswahlfeld bekommt nun die Beschriftung "Status". |br|
Die Methode ``addSelectfield()`` gibt das neu erzeugte Auswahlfeld zurück:

.. code-block:: php

   $oAktivSelect = $oFilterStandard->addSelectfield('Status', 'cAktiv');

Diesem Auswahlfeld können Sie nun beliebige Auswahl-Optionen hinzufügen.

.. code-block:: php

    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', Operation::EQUALS);
    $oAktivSelect->addSelectOption('inaktiv', 'N', Operation::EQUALS);

Die Methode ``addSelectOption($cTitle, $cValue, $nTestOp = 0)`` fügt eine weitere Option zu einem Auswahlfeld
hinzu. |br|
Diese Option erhält als Beschriftung den ersten Parameter (hier ``$cTitle``) und als zugehörigen Wert
den zweiten Parameter (hier ``$cValue``). Der dritte Parameter bestimmt die *Vergleichsmethode* beim Suchen in der
Tabellenspalte. Hier können Sie entscheiden ob der Wert exakt übereinstimmen muss, als Prä- oder Postfix vorkommen
muss, numerisch größer oder kleiner sein soll etc. |br|


.. _label_backend_filter_compare_const:

Alle möglichen Werte für die *Vergleichsmethode* sind in der folgenden Tabelle aufgelistet:

+-----------------------------------+------+--------------------------------------------------+
| Konstante                         | Wert | SQL-Entsprechung                                 |
+===================================+======+==================================================+
| ``Operation::CUSTOM``             | 0    | Benutzerdefiniert: die gewünschte Methode kann   |
|                                   |      | in der Filterleiste per Selectbox gewählt werden |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::CONTAINS``           | 1    | ``LIKE '%foo%'``                                 |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::BEGINS_WITH``        | 2    | ``LIKE 'foo%'``                                  |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::ENDS_WITH``          | 3    | ``LIKE '%foo'``                                  |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::EQUALS``             | 4    | ``='``                                           |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::LOWER_THAN``         | 5    | ``<``                                            |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::GREATER_THAN``       | 6    | ``>``                                            |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::LOWER_THAN_EQUAL``   | 7    | ``<=``                                           |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::GREATER_THAN_EQUAL`` | 8    | ``>=``                                           |
+-----------------------------------+------+--------------------------------------------------+
| ``Operation::NOT_EQUAL``          | 9    | ``!=``                                           |
+-----------------------------------+------+--------------------------------------------------+

Der Wert ``0`` (oder ``Operation::CUSTOM``) erzeugt eine Auswahloption mit leerer Filterbedingung, d. h. diese Option
schränkt die Ergebnismenge nicht weiter ein.

Nun kann der Filter mit ``assemble()`` fertiggestellt werden.

.. code-block:: php

   $oFilterStandard->assemble();

Ab jetzt sind die gesetzten Filterparameter in der *SESSION* gespeichert und eine SQL ``WHERE``-Klausel wurde
erstellt, welche Sie mit ``getWhereSQL()`` abrufen und in Ihrer eigenen SQL-Abfrage einsetzen können.

.. code-block:: php
   :emphasize-lines: 1,6

   $cWhereSQL = $oFilterStandard->getWhereSQL();
   Shop::Container()->getDB()->query(
       "SELECT *
       FROM tkupon
       WHERE cKuponTyp = 'standard' " .
           ($cWhereSQL !== '' ? ' AND ' . $cWhereSQL : '') .
           ($cOrderSQL !== '' ? ' ORDER BY ' . $cOrderSQL : '') .
           ($cLimitSQL !== '' ? ' LIMIT ' . $cLimitSQL : ''),
       ReturnType::ARRAY_OF_OBJECTS);

Damit Ihr Filter auch im Backend angezeigt werden kann, übergeben Sie das Filterobjekt an Smarty:

.. code-block:: php

   $smarty->assign('oFilterStandard', $oFilterStandard);

Als Letztes binden Sie auf der gewünschten Seite noch das Filter-Template ein:

.. code-block:: smarty

   {include file='tpl_inc/filtertools.tpl' oFilter=$oFilterStandard}

Methoden des Filterobjekts
--------------------------

``addTextfield($cTitle, $cColumn, $nTestOp = 0, $nDataType = 0)``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

Mit dieser Methode können Sie ein neues Textfeld zum Filterobjekt mit der Beschriftung ``$cTitle`` hinzufügen, welches
mit der Tabellenspalte ``$cColumn`` verglichen wird. |br|
``$cTitle`` kann anstatt eines Strings auch ein Paar aus 2 Strings enthalten, das z. B. aus einer einfachen
Beschriftung und einem längerem Text, welcher als Tooltip angezeigt wird, besteht:

.. code-block:: php

    ['Suchbegriff', 'Sucht in Bestell-Nr., Betrag, Kunden-Vornamen, E-Mail-Adresse, Hinweis'];

``$nTestOp`` legt die Vergleichsmethode fest. |br| Dabei sind alle Werte möglich, die
unter :ref:`Vergleichsmethoden <label_backend_filter_compare_const>` gelistet sind.

Mit dem vierten Parameter ``$nDataType`` können Sie angeben, ob das Textfeld einen String (``0``) enthält oder einen
skalaren Wert (``1``). |br|
Dementsprechend werden für ``$nDataType = 0`` die Vergleichsmethoden 5 bis 8 ausgeblendet und für ``$nDataType = 0``
werden die Methoden 1 bis 3 ausgeblendet.

Die Methode gibt dann ein ``FilterTextField``-Objekt zurück, das dem hinzugefügten Textfeld-Objekt entspricht.

``addSelectfield($cTitle, $cColumn)``
"""""""""""""""""""""""""""""""""""""

Diese Methode ermöglicht es Ihnen, ein neues Dropdown-Auswahlfeld zum Filterobjekt hinzuzufügen, welches die
Beschriftung ``$cTitle`` trägt und mit der Tabellenspalte ``$cColumn`` verglichen wird.

Weitere Optionen können Sie dem Feld mit ``addSelectOption()`` hinzufügen
(siehe: :ref:`label_backend_filter_select_field`).

Der Rückgabewert dieser Methode ist ein ``FilterSelectField``-Objekt, welches dem hinzugefügten Auswahlfeld-Objekt
entspricht.

``addDaterangefield($cTitle, $cColumn)``
""""""""""""""""""""""""""""""""""""""""

Mithilfe dieser Methode fügen Sie dem Filterobjekt einen neuen Date-Range-Picker mit der Beschriftung ``$cTitle`` hinzu.
Die Tabellenspalte ``$cColumn`` enthält das Datum, welches im gewählten Bereich liegen muss.

Das Template ``filtertools.tpl`` stellt dazu einen Date-Range-Picker zur Verfügung.
(siehe auch: :ref:`label_backend_filter_template`)

``assemble()``
""""""""""""""

``assemble()`` stellt den Filter fertig. |br|
Diese Methode setzt eine SQL ``WHERE``-Klausel zusammen und speichert die getroffenen Filtereinstellungen in
der *SESSION*.

Rufen Sie diese Funktion auf, nachdem Sie alle Filterfelder konfiguriert haben.


.. _label_backend_filter_template:

Einbindung des Templates
------------------------

Das Template stellt alle Filterfelder in einer Leiste zur Verfügung und kann mit folgender Direktive im Backend
eingebunden werden:

.. code-block:: smarty

    {include file='tpl_inc/filtertools.tpl'
        oFilter=$oFilter
        cParam_arr=[
            'var1' => val1, 'var2' => val2, ...
        ]}

+---------------------------+----------------------------------------------------------------------------------------------+
| Parameter                 | Bedeutung                                                                                    |
+===========================+==============================================================================================+
| ``oFilter``               | das Filterobjekt                                                                             |
+---------------------------+----------------------------------------------------------------------------------------------+
| ``cParam_arr`` (optional) | assoziatives Array von GET-Parametern, welche beim Filtern mit durchgeschleift werden sollen |
+---------------------------+----------------------------------------------------------------------------------------------+


.. _label_backend_filter_select_field:

Das Auswahlfeld-Objekt ``FilterSelectField``
--------------------------------------------

Mittels ``Filter::addSelectField()`` können Sie ein Dropdown-Auswahlfeld erzeugen und dieses einem Filter-Objekt
hinzufügen.

Die Methode ``addSelectOption($cTitle, $cValue, $nTestOp = 0)`` fügt dem Auswahlfeld eine neue Option mit der
Beschriftung ``$cTitle`` und dem zugehörigen Wert ``$cValue`` hinzu.

Der dritte Parameter ``$nTestOp`` dieser Methode entspricht dem dritten Parameter von ``Filter::addTextfield()``.
