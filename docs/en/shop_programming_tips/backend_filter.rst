Back end filter
===============

.. |br| raw:: html

   <br />

With a configurable amount of text fields, filters allow you to limit selectboxes or Date Range Picker entries
of a table overview in the back end. You can find examples of application and more at 
*Campaigns-> Coupons* or *SEO -> Redirection*.

A filter is comprised of a variety of different types of filter fields (text, selection, date range). |br|
These filter fields are offered with the template on the desired page for searching and filtering.
The conditions created by each field are both related AND conditional and made available for a subsequent database query as
a ``WHERE`` clause. |br|
This means that every defined field filter further restricts the result set.

*Back end filter* files
-----------------------

All the classes that belong to the back end filters can be found in the ``includes/src/Pagination/`` directory,
and the corresponding template file in the ``tpl_inc/`` directory.

+-------------------------------------------------------+----------------------------------------------------+
| File                                                 | Function                                            |
+=======================================================+====================================================+
| ``Filter.php``                                        | filter class                                       |
+-------------------------------------------------------+----------------------------------------------------+
| ``FilterField.php``                                   | abstract base class for every type of filter field |
+-------------------------------------------------------+----------------------------------------------------+
| ``FilterTextField.php``                               | free text filter field class                       |
+-------------------------------------------------------+----------------------------------------------------+
| ``FilterSelectField.php``                             | selectbox filter field class                       |
+-------------------------------------------------------+----------------------------------------------------+
| ``FilterSelectOption.php``                            | option class for a selectbox filter field          |
+-------------------------------------------------------+----------------------------------------------------+
| ``FilterDateRangeField.php``                          | filter field class "Date Range Picker"             |
+-------------------------------------------------------+----------------------------------------------------+
| ``admin/templates/bootstrap/tpl_inc/filtertools.tpl`` | template for the back end                          |
+-------------------------------------------------------+----------------------------------------------------+

Quick start
-----------

First, create a filter instance and assign it a string ID. This string ID can be used to distinguish
the filter and its settings stored in the session from those of other instances:

.. code-block:: php

   $oFilterStandard = new Filter('standard');

Add a free text search field to the filter to search, for example, ``cName`` in a table
column. |br|
This text field is labelled as "Name":

.. code-block:: php

   $oFilterStandard->addTextfield('Name', 'cName');

Add a drop-down list to the filter whose value, for example, corresponds to a ``cAktiv`` column. |br|
The selection field is now labelled as "Status". |br|
The ``addSelectfield()`` method will return the newly created selection field:

.. code-block:: php

   $oAktivSelect = $oFilterStandard->addSelectfield('Status', 'cAktiv');

You may now add any selection options to the selection field.

.. code-block:: php

    $oAktivSelect->addSelectOption('alle', '', 0);
    $oAktivSelect->addSelectOption('aktiv', 'Y', Operation::EQUALS);
    $oAktivSelect->addSelectOption('inaktiv', 'N', Operation::EQUALS);

The``addSelectOption($cTitle, $cValue, $nTestOp = 0)`` method adds an additional option to a given selection
field. |br|
This option is then labelled as the first parameter (here ``$cTitle``) and the corresponding value as the second parameter
 (here ``$cValue``). The third parameter defines the *comparison method* when searching in the table
columns. Here you can decide whether the value must match exactly, appear as a prefix or postfix
, be numerically larger or smaller, etc. |br|


.. _label_backend_filter_compare_const:

All possible *comparison method* values are listed in the following table:

+-----------------------------------+------+--------------------------------------------------+
| Constant                         | Value | SQL compliance                                   |
+===================================+======+==================================================+
| ``Operation::CUSTOM``             | 0    | User defined: the selected method can be         |
|                                   |      | selected from the filter list in the selectbox   |
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

The value ``0`` (or ``Operation::CUSTOM``) generates a selection with a blank filter condition. This means that this option
no longer restricts the result set.

Now, the filter can be finalised with ``assemble()``.

.. code-block:: php

   $oFilterStandard->assemble();

Now, the filter parameters are saved in the *SESSION* and a SQL ``WHERE`` clause has been generated, which you can
retrieve via ``getWhereSQL()`` and use in your SQL query.

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

So that your filter can also be shown in the back end, assign the filter object to Smarty:

.. code-block:: php

   $smarty->assign('oFilterStandard', $oFilterStandard);

Finally, add the filter template to the desired page:

.. code-block:: smarty

   {include file='tpl_inc/filtertools.tpl' oFilter=$oFilterStandard}

Filter object methods
---------------------

``addTextfield($cTitle, $cColumn, $nTestOp = 0, $nDataType = 0)``
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

With this method, you can add a new text field to the filter object with the label ``$cTitle``, which will then be compared
with the ``$cColumn`` table column. |br|
Instead of containing only one string, ``$cTitle`` can contain a pair of 2 strings that consist of a simple label and
longer text, displayed as a tooltip:

.. code-block:: php

    ['Search term', 'Searches in order number, amount, customer first name, email address, note'];

``$nTestOp`` determines the comparison method. |br| Therefore, all values that are listed
under :ref:`Comparison methods <label_backend_filter_compare_const>` are possible.

With the fourth parameter, ``$nDataType``, you can specify if the text field contains a string (``0``) or a
scalar value (``1``). |br|
Accordingly, for ``$nDataType = 0`` the comparison methods 5 to 8 are hidden, as well as methods
1 to 3 for ``$nDataType = 0``.

This method then returns a ``FilterTextField`` object that corresponds to the added text field object.

``addSelectfield($cTitle, $cColumn)``
"""""""""""""""""""""""""""""""""""""

With this method, you can add a new drop-down list to the filter object with the label ``$cTitle``, which will then be compared
with the ``$cColumn`` table column.

You can add other options to the field by using ``addSelectOption()``
(See: :ref:`label_backend_filter_select_field`).

The return value of this method is a ``FilterSelectField`` which corresponds to the added drop-down list object.


``addDaterangefield($cTitle, $cColumn)``
""""""""""""""""""""""""""""""""""""""""

By using this method, you are adding a new Date Range Picker with the label ``$cTitle`` to the filter object.
The table column ``$cColumn`` contains the date , which must be in the selected range.

The ``filtertools.tpl`` template provides a Date Range Picker for this purpose.
(See also: :ref:`label_backend_filter_template`)

``assemble()``
""""""""""""""

``assemble()`` finalises the filter. |br|
This method assembles a SQL ``WHERE`` clause and saves the filter settings made
in the *SESSION*.

Call up this function after you have configured all the filter fields.


.. _label_backend_filter_template:

Template integration
--------------------

The template provides all filter fields in one bar and can be included with the following directive in
the back end:

.. code-block:: smarty

    {include file='tpl_inc/filtertools.tpl'
        oFilter=$oFilter
        cParam_arr=[
            'var1' => val1, 'var2' => val2, ...
        ]}

+---------------------------+----------------------------------------------------------------------------------------------+
| Parameter                 | Meaning                                                                                      |
+===========================+==============================================================================================+
| ``oFilter``               | the filter object                                                                            |
+---------------------------+----------------------------------------------------------------------------------------------+
| ``cParam_arr`` (optional) | an associative array of GET parameters, which are to be looped through during filtering      |
+---------------------------+----------------------------------------------------------------------------------------------+


.. _label_backend_filter_select_field:

The selection field object ``FilterSelectField``
------------------------------------------------

``Filter::addSelectField()`` can generate a drop-down list and add this to a filter
object.

The ``addSelectOption($cTitle, $cValue, $nTestOp = 0)`` method adds a new option with the label ``$cTitle`` and
corresponding value ``$cValue`` to the selection field.

The third parameter of this method, ``$nTestOp``, corresponds with the third parameter of ``Filter::addTextfield()``.
