Pagination
==========

.. |br| raw:: html

   <br />

Mit der *Pagination* können Sie große Listen von Items (Tabelleneinträge, News-Beiträge, Artikel-Reviews) auf mehrere
Seiten aufteilen, durch die der Betrachter blättern kann. Dabei können ihm Einstellungen zur Seitengröße und zur
Sortierung angeboten werden. Alle festgelegten Einstellungen werden in der Browser-*SESSION* festgehalten.

Die *Pagination* kann auf zwei verschiedene Arten verwendet werden:

    - Sie übergeben der *Pagination* ein fertiges Array als Eingabe. |br|
      Dieses Array wird sortiert und der momentan anzuzeigende Bereich wird ausgeschnitten.
    - Sie übergeben der *Pagination* die Gesamtanzahl der Einträge als Eingabe. |br|
      Das *Paginations*-Objekt liefert dann lediglich anhand der gewählten Optionen eine SQL ``LIMIT``- und
      eine ``ORDER BY``-Klausel, die Sie in Ihre eigene SQL-Abfrage einbauen können.

Dateien der *Pagination*
------------------------

+------------------------------------------------------+-------------------------------------------------+
| Datei                                                | Funktion                                        |
+======================================================+=================================================+
| ``includes/src/Pagination/Pagination.php``           | Paginations-Klasse                              |
+------------------------------------------------------+-------------------------------------------------+
| ``admin/templates/bootstrap/tpl_inc/pagination.tpl`` | Template-Datei für das Backend                  |
+------------------------------------------------------+-------------------------------------------------+
| ``templates/Evo/snippets/pagination.tpl``            | Template-Datei für das Frontend (EVO-Template)  |
+------------------------------------------------------+-------------------------------------------------+
| ``templates/NOVA/snippets/pagination.tpl``           | Template-Datei für das Frontend (NOVA-Template) |
+------------------------------------------------------+-------------------------------------------------+

Quick-Start
-----------

Erzeugen Sie eine Instanz der Pagination. |br|
Weisen Sie der neuen Pagination einen ID-String zu, mit dem diese Pagination und ihre in der *SESSION* gespeicherten
Einstellungen von anderen Instanzen unterschieden werden können.

.. code-block:: php

   $oPaginationStandard = new Pagination('standard');

Übergeben Sie der *Pagination* ein Array aller Items, durch die geblättert werden soll: |br|
(Für Daten-Listen, die sich rein aus Datenbankabfragen erzeugen lassen, siehe: :ref:`label_sql_optimized_pagination`.)

.. code-block:: php

   $oPaginationStandard = (new Pagination('standard'))
        ->setItemArray($oKuponStandard_arr)
        ->setSortByOptions([
            ['cName', 'Name'],
            ['cCode', 'Code'],
            ['nVerwendungenBisher', 'Verwendungen'],
            ['dLastUse', 'Zuletzt verwendet']
        ]);

Wie hier zu sehen ist, sind alle Methoden der Pagination *chainable*. |br|
Zum Schluss stellen Sie die Pagination mit ``assemble()`` fertig:

.. code-block:: php
   :emphasize-lines: 9

   $oPaginationStandard = (new Pagination('standard'))
        ->setItemArray($oKuponStandard_arr)
        ->setSortByOptions([
            ['cName', 'Name'],
            ['cCode', 'Code'],
            ['nVerwendungenBisher', 'Verwendungen'],
            ['dLastUse', 'Zuletzt verwendet']
        ])
        ->assemble();

.. important::

    Danach sollten keine *Setter* mehr aufgerufen werden!

Übergeben Sie nun das Paginations-Objekt an Smarty.

.. code-block:: php

   $smarty->assign('oPaginationStandard', $oPaginationStandard);

Die Einträge der momentan gewählten Blätter-Seite erhalten Sie durch ``$oPaginationStandard->getPageItems()``. |br|
Durch diese Liste können Sie dann entsprechend iterieren und die darin enthaltenen Elemente im Frontend ausgeben.

.. code-block:: smarty

    {foreach $oPaginationStandard->getPageItems() as $oKupon}
        ...
    {/foreach}

Einbindung der Templates
------------------------

Die Templates enthalten die Seitennavigation und die Kontrollelemente zum Sortieren und Einstellen der Seitengröße. |br|
Es gibt zwei getrennte Templates für das Backend und für das Frontend.

Backend
"""""""

.. code-block:: smarty

   {include file='tpl_inc/pagination.tpl'
        oPagination=$oPagination
        cParam_arr=['tab'=>$tab]
        cAnchor=$tab}

**Parameter:**

+---------------------------+------------------------------------------------------------------------------------+
| Parameter                 | Verwendung                                                                         |
+===========================+====================================================================================+
| ``oPagination``           | das Paginations-Objekt                                                             |
+---------------------------+------------------------------------------------------------------------------------+
| ``cParam_arr`` (optional) | assoziatives Array von GET-Parametern, welche von der Pagination                   |
|                           | beim Seitenblättern oder Ändern von Optionen mit durchgeschleift werden sollen     |
+---------------------------+------------------------------------------------------------------------------------+
| ``cAnchor`` (optional)    | ein zusätzlicher Ziel-Anker, der mit an die URL angehängt wird (Form: ``#foobar``) |
+---------------------------+------------------------------------------------------------------------------------+

Frontend
""""""""

.. code-block:: smarty

   {include file='snippets/pagination.tpl'
        oPagination=$oPagination
        cParam_arr=['tab'=>$tab]
        cThisUrl='/target/path'
        cParam_arr=['key1' => 'val1', 'key2' => 'val2', ...]
        parts=['pagi', 'label']}

**Parameter:**

+---------------------------+----------------------------------------------------------------+
| Parameter                 | Verwendung                                                     |
+===========================+================================================================+
| ``oPagination``           | das Pagination-Objekt                                          |
+---------------------------+----------------------------------------------------------------+
| ``cParam_arr`` (optional) | siehe oben (Backend)                                           |
+---------------------------+----------------------------------------------------------------+
| ``cThisUrl`` (optional)   | eigener Pfad der einbindenden Seite                            |
+---------------------------+----------------------------------------------------------------+
| ``parts`` (optional)      | Mit diesem Parameter kann die Anzeige auf einzelne Komponenten |
|                           | des Templates eingeschränkt werden. |br|                       |
|                           | Übergeben Sie hier eine Liste von Komponenten-Bezeichnern:     |
|                           |                                                                |
|                           | - ``label`` Label für die Anzahl der Einträge                  |
|                           | - ``pagi`` Seitennavigation                                    |
|                           | - ``count`` Selectbox für Einträge pro Seite                   |
|                           | - ``sort`` Selectbox für die Sortierung                        |
+---------------------------+----------------------------------------------------------------+

Methoden des *Paginations*-Objekts
----------------------------------

+------------------------------------------------------+----------------------------------------------------------------+
| Methode                                              | Funktion                                                       |
+======================================================+================================================================+
| ``setRange($nRange)``                                | Da bei sehr großen Listen auch große Seitenzahlen              |
|                                                      | entstehen können, |br|                                         |
|                                                      | die die Navigation zu lang werden                              |
|                                                      | lassen, werden Auslassungspunkte (``...``) eingefügt. |br|     |
|                                                      | Auf der linken und rechten Seite vom gerade aktiven Seitenlink |
|                                                      | werden dann jeweils |br|                                       |
|                                                      | maximal ``$nRange`` benachbarte Seitenlinks angezeigt.         |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemsPerPageOptions($nItemsPerPageOption_arr)`` | Legt die Auswahloptionen für "Einträge pro Seite" fest.        |
|                                                      | Diese werden in einer Selectbox zur Auswahl angeboten.         |
|                                                      |                                                                |
|                                                      | **Beispiel:**                                                  |
|                                                      |                                                                |
|                                                      | .. code-block:: php                                            |
|                                                      |                                                                |
|                                                      |      [5, 10, 20, 50]                                           |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setSortByOptions($cSortByOption_arr)``             | Legt die Auswahloptionen für die Sortierung fest. |br|         |
|                                                      | Jede Auswahloption ist ein Paar aus der Tabellenspalte         |
|                                                      | (dem *Property*, nach dem sortiert wird) |br|                  |
|                                                      | und einer zugehörigen Beschriftung.                            |
|                                                      | Diese werden in einer Selectbox jeweils für aufsteigende |br|  |
|                                                      | und absteigende Reihenfolge zur Auswahl angeboten.             |
|                                                      |                                                                |
|                                                      | **Beispiel:**                                                  |
|                                                      |                                                                |
|                                                      | .. code-block:: php                                            |
|                                                      |                                                                |
|                                                      |     [                                                          |
|                                                      |          ['cName', 'Name'],                                    |
|                                                      |          ['cCode', 'Code'],                                    |
|                                                      |          ['nVerwendungenBisher', 'Verwendungen'],              |
|                                                      |          ['dLastUse', 'Zuletzt verwendet']                     |
|                                                      |     ]                                                          |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemArray($oItem_arr)``                         | Legt das gesamte Array aller Items fest |br|                   |
|                                                      | (erste Verwendungsmethode)                                     |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemCount($nItemCount)``                        | Legt die gesamte Anzahl der Items fest |br|                    |
|                                                      | (zweite Verwendungsmethode)                                    |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setDefaultItemsPerPage($n)``                       | Setzt, wie viele Einträge standardmäßig                        |
|                                                      | pro Seite gezeigt werden                                       |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemsPerPage($nItemsPerPage)``                  | Übergeht die gewählte Option für "Einträge pro Seite" und      |
|                                                      | legt diese auf den Wert ``$nItemsPerPage`` fest. |br|          |
|                                                      | Dies ist nützlich, wenn Sie keine Auswahlmöglichkeiten         |
|                                                      | anbieten möchten, |br|                                         |
|                                                      | sondern einen festen Wert vorgeben wollen.                     |
+------------------------------------------------------+----------------------------------------------------------------+


.. _label_sql_optimized_pagination:

Eigene SQL-Abfrage
------------------

Oft müssen größere Datenmengen **direkt aus der Datenbank** dargestellt werden. |br|
Für diesen Zweck existiert eine weitere Verwendungsmöglichkeit, bei der dem Paginations-Objekt lediglich die
Gesamtanzahl der anzuzeigenden Elemente übergeben wird (mittels ``setItemCount()``). |br|

.. code-block:: php
   :emphasize-lines: 1

   $oPagination->setItemCount(
       Shop::Container()->getDB()->query(
          'SELECT count(*) AS count FROM tkunden',
          ReturnType::SINGLE_OBJECT
       )->count);

Das Paginations-Objekt ermittelt nun die Position im Listing, an der sich der Benutzer beim Blättern befindet.
Anschließend liest das Paginations-Object nur noch diesen "*Datenbereich*" aus der Datenbank, was die Mengen an Daten,
die übertragen werden müssen, erheblich reduziert.

Nach der Fertigstellung mit ``assemble()`` können Sie dann die gewünschten SQL-Klauseln für ``LIMIT``, und bei Bedarf
auch für ``ORDER``, vom Paginations-Objekt abrufen (mittels ``getLimitSQL()`` und ``getOrderSQL()``).

Diese SQL-Klauseln können Sie nun in einer eigenen SQL-Abfrage verwenden, um explizit nur diese Daten aus der Datenbank
abholen zu müssen:

.. code-block:: php
   :emphasize-lines: 4

   $pageOfData = Shop::Container()->getDB()->queryPrepared(
       'SELECT * FROM tredirect LIMIT :limitation ORDER BY :sorting',
       [
          'limitation' => $oPagination->getLimitSQL(),
          'sorting'    => $oPagination->getOrderSQL()
       ],
       ReturnType::ARRAY_OF_OBJECTS);

Abschließend übergeben Sie dann wieder das Paginations-Objekt an Smarty:

.. code-block:: php

   $smarty->assign('pageOfData', $pageOfData);

