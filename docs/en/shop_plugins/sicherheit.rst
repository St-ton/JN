Writing secure plug-ins
=======================

.. |br| raw:: html

   <br />

Plug-ins have complete access to the online shop. |br|
It is, therefore, essential that every plug-in developer places the utmost importance on the security of the plug-ins that they write.

This guide is intended to assist developers in making their plug-ins according to the most up-to-date safety standards,
and to strengthen the security of the entire JTL-Shop ecosystem.

Validation
----------

First, all input values for SQL queries should be validated. |br|
Data validation is the first step in the right direction to avoid *SQL injections* and other issues.
 You can find general tips and information about this at
"`Testing for SQL Injection (OTG-INPVAL-005) <https://www.owasp.org/index.php/Testing_for_SQL_Injection_(OTG-INPVAL-005)>`_"
.

A good example might be the validation functions provided by PHP:

.. code-block:: php

    <?php
    // validates that the variable is an integer.
    $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
    if (!$productId || $productId < 0) {
        // Value is invalid. Processing should be stopped
        exit();
    }
    // otherwise it is possible to continue working with the value


Prepared statements
-------------------

The only truly sure way to prevent *SQL injections* is to only use *prepared statements* for parameterization of SQL queries.
 |br|
When *prepared statements* are used, *SQL injections* are impossible to create. When you only depend on validation of the data,
you will eventually forget to validate a value sufficiently.
Moreover, free text fields cannot be validated adequately at all.

JTL-Shop provides an easy way to execute *prepared statements*. |br|
The recommended variant:

.. code-block:: php

    <?php

    $db = JTL\Shop::Container()->getDB();

    // validates that the variable is an integer.
    $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
    if (!$productId || $productId < 0) {
        // Value is invalid. Processing should be stopped
        exit();
    }

    $query = "
        SELECT cArtNr, cName, cBeschreibung
        FROM tartikel
        WHERE kArtikel = :productId
    ";
    $productInfo = $db->queryPrepared(
        $query,
        ['productId' => $productId],
        JTL\DB\ReturnType::ARRAY_OF_OBJECTS
    );

A note on plug-in certification
-------------------------------

.. important::

    JTL will only **certify** plug-ins that exclusively use *prepared statements*

We, therefore, recommend all plug-in developers to adapt and write their own codes using *prepared statements* only.

