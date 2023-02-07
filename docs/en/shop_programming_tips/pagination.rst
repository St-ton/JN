Pagination
==========

.. |br| raw:: html

   <br />

With *pagination*, you can divide large lists of items (table items, news articles, item reviews) across multiple pages, through
which the reader can flip through. Settings like page size and sorting options can
also be offered to the reader. All established settings will be stored in the browser *SESSION*.

*Pagination* can be used in two different ways:

    - You can assign the *pagination* a complete array as input. |br|
      This array is sorted and the currently displayed area is excluded.
    - You can assign the *pagination* the total number of items as input. |br|
      Based on the selected options, a *pagination* object can deliver an SQL ``LIMIT`` and
      a ``ORDER BY`` clause that you can then implement in your SQL query.

*Pagination* files
------------------------

+------------------------------------------------------+-------------------------------------------------+
| File                                                | Function                                        |
+======================================================+=================================================+
| ``includes/src/Pagination/Pagination.php``           | pagination class                              |
+------------------------------------------------------+-------------------------------------------------+
| ``admin/templates/bootstrap/tpl_inc/pagination.tpl`` | template file for the back end                  |
+------------------------------------------------------+-------------------------------------------------+
| ``templates/Evo/snippets/pagination.tpl``            | Template-Datei für das Frontend (EVO-Template)  |
+------------------------------------------------------+-------------------------------------------------+
| ``templates/NOVA/snippets/pagination.tpl``           | template file for the front end (NOVA template) |
+------------------------------------------------------+-------------------------------------------------+

Quick start
-----------

Create a pagination instance. |br|
Assign the new pagination a string ID, with which the pagination and its settings saved in the *SESSION* can be differentiated
from other instances.

.. code-block:: php

   $oPaginationStandard = new Pagination('default');

Assign the *pagination* an array of all items that can be flipped through. |br|
(For lists of data that can be generated from database queries, see: :ref:`label_sql_optimized_pagination`.)

.. code-block:: php

   $oPaginationStandard = (new Pagination('default'))
        ->setItemArray($oKuponStandard_arr)
        ->setSortByOptions([
            ['cName', 'name'],
            ['cCode', 'code'],
            ['nVerwendungenBisher', 'application'],
            ['dLastUse', 'last used']
        ]);

As you can see here, all pagination methods are *chainable*. |br|
Lastly, finalise the pagination with ``assemble()``:

.. code-block:: php
   :emphasize-lines: 9

   $oPaginationStandard = (new Pagination('default'))
        ->setItemArray($oKuponStandard_arr)
        ->setSortByOptions([
            ['cName', 'name'],
            ['cCode', 'code'],
            ['nVerwendungenBisher', 'application'],
            ['dLastUse', 'last used']
        ])
        ->assemble();

.. important::

    After finalisation, *setters* should not be called up again!

Assign the pagination object to Smarty.

.. code-block:: php

   $smarty->assign('oPaginationStandard', $oPaginationStandard);

You can get items from the currently selected pages using ``$oPaginationStandard->getPageItems()``. |br|
With this list, you can then iterate accordingly and output the elements it contains in the front end.

.. code-block:: smarty

    {foreach $oPaginationStandard->getPageItems() as $oKupon}
        ...
    {/foreach}

Template integration
------------------------

The templates all include page navigation and controls used for sorting and page size settings. |br|
There are two separate templates, one for the back end and one for the front end.

Back end
"""""""

.. code-block:: smarty

   {include file='tpl_inc/pagination.tpl'
        oPagination=$oPagination
        cParam_arr=['tab'=>$tab]
        cAnchor=$tab}

**Parameter:**

+---------------------------+------------------------------------------------------------------------------------+
| Parameter                 | Application|
+===========================+====================================================================================+
| ``oPagination``           | the pagination object                                                             |
+---------------------------+------------------------------------------------------------------------------------+
| ``cParam_arr`` (optional) | an associative array of GET parameters, which should be looped through from the pagination                   |
|                           |  when scrolling pages or changing options     |
+---------------------------+------------------------------------------------------------------------------------+
| ``cAnchor`` (optional)    | an additional destination anchor, which is attached to the URL (Form: ``#foobar``) |
+---------------------------+------------------------------------------------------------------------------------+

Front end
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
| Parameter                 | Application                                                     |
+===========================+================================================================+
| ``oPagination``           | the pagination object                                          |
+---------------------------+----------------------------------------------------------------+
| ``cParam_arr`` (optional) | See above (Back end)                                           |
+---------------------------+----------------------------------------------------------------+
| ``cThisUrl`` (optional)   | Pathway to the integrate pages                            |
+---------------------------+----------------------------------------------------------------+
| ``parts`` (optional)      | This parameter can be used to limit the display |
|                           | to individual components of the template. |br|                       |
|                           | Specify a list of component identifiers here:     |
|                           |                                                                |
|                           | - ``label`` Label for the number of items                  |
|                           | - ``pagi`` Page navigation                                    |
|                           | - ``count`` Select box for items per page                   |
|                           | - ``sort`` Select box for sorting                        |
+---------------------------+----------------------------------------------------------------+

*Pagination* object methods
----------------------------------

+------------------------------------------------------+----------------------------------------------------------------+
| Methods                                              | Function                                                       |
+======================================================+================================================================+
| ``setRange($nRange)``                                | Since with long lists, page numbers              |
|can be quite large,  |br|                                         |
|                                                      |which would make navigation too long,                              |
|                                                      | an ellipsis is simply inserted (``...``)...``) eingefügt. |br|     |
|                                                      | To both the left and the right of the active page link|
|                                                      | is a maximal ``$nRange`` |br|                                       |
|                                                      | of neighbouring page links displayed.         |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemsPerPageOptions($nItemsPerPageOption_arr)`` | Sets the "items per page" option.        |
|                                                      | These will then be displayed for selection in a select box.         |
|                                                      |                                                                |
|                                                      | **Example:**                                                  |
|                                                      |                                                                |
|                                                      | .. code-block:: php                                            |
|                                                      |                                                                |
|                                                      |      [5, 10, 20, 50]                                           |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setSortByOptions($cSortByOption_arr)``             | Establishes the selection options for sorting. |br|         |
|                                                      | Every selection option is comprised of a pair of values from the table column          |
|                                                      | (that of *property*, which is then sorted) |br|                  |
|                                                      | and a corresponding label.                            |
|                                                      | These are offered for selection in a select box |br|  |
|                                                      | for ascending and descending order, respectively.             |
|                                                      |                                                                |
|                                                      | **Example:**                                                  |
|                                                      |                                                                |
|                                                      | .. code-block:: php                                            |
|                                                      |                                                                |
|                                                      |     [                                                          |
|                                                      |          ['cName', 'name'],                                    |
|                                                      |          ['cCode', 'code'],                                    |
|                                                      |          ['nPreviousapplications', 'applications'],              |
|                                                      |          ['dLastused', 'Last used']                     |
|                                                      |     ]                                                          |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemArray($oItem_arr)``                         | Sets the array of all items |br|                   |
|                                                      | (first application method)                                     |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemCount($nItemCount)``                        | Sets the total item count |br|                    |
|                                                      | (second application method)                                    |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setDefaultItemsPerPage($n)``                       | Sets the number of items                        |
|                                                      | that are to be shown by default per page                                       |
+------------------------------------------------------+----------------------------------------------------------------+
| ``setItemsPerPage($nItemsPerPage)``                  | Overrides the selected option for "Entries per page" and      |
|                                                      | sets this to the value ``$nItemsPerPage``. |br|          |
|                                                      | This is particularly useful if you don’t want to offer         |
|                                                      | any selection options, |br|                                         |
|                                                      | but rather just want to define a fixed value instead.                     |
+------------------------------------------------------+----------------------------------------------------------------+


.. _label_sql_optimized_pagination:

Your own SQL query
------------------

Often, large amounts of data have to be displayed **directly from the database**. |br|
For this reason, there is another option in which the
total number of items to be displayed is sent to the pagination object (via ``setItemCount()``. |br|

.. code-block:: php
   :emphasize-lines: 1

   $oPagination->setItemCount(
       Shop::Container()->getDB()->query(
          'SELECT count(*) AS count FROM tkunden',
          ReturnType::SINGLE_OBJECT
       )->count);

The pagination object now determines the position in the listing where the user is located when scrolling.
Then the pagination object reads just the "*data range*" from the data base, which considerably reduces the amount of data that
needs to be transferred.

After finalising with ``assemble()``, you can then call up the desired SQL clause for ``LIMIT`` and, when necessary, the
clause for ``ORDER`` from the pagination object using ``getLimitSQL()`` and ``getOrderSQL()``.

You can now use these SQL clauses in your own SQL query to only retrieve this data from the database
:

.. code-block:: php
   :emphasize-lines: 4

   $pageOfData = Shop::Container()->getDB()->queryPrepared(
       'SELECT * FROM tredirect LIMIT :limitation ORDER BY :sorting',
       [
          'limitation' => $oPagination->getLimitSQL(),
          'sorting'    => $oPagination->getOrderSQL()
       ],
       ReturnType::ARRAY_OF_OBJECTS);

Finally, assign the pagination object to Smarty again.

.. code-block:: php

   $smarty->assign('pageOfData', $pageOfData);

