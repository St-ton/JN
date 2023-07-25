Alerts
======

.. |br| raw:: html

   <br />

All alerts (messages like error messages or warnings) starting from JTL-Shop version 5.0 will be generated via the alert service.

You can obtain the *alert* service via the shop class:

.. code-block:: php

    <?php

    use JTL\Services\JTL\AlertServiceInterface;

    $alertHelper = Shop::Container()->getAlertService();

A message will be generated using the ``addAlert()`` method. |br|
This method recognises the three parameters *type*, *message*, and *key*. For the *type* parameter, you can use the *alert* class
constants:

+--------------------+--------------------------------------------+
| Constants          | Value                                      |
+====================+============================================+
| ``TYPE_PRIMARY``   | *primary*                                  |
+--------------------+--------------------------------------------+
| ``TYPE_SECONDARY`` | *secondary*                                |
+--------------------+--------------------------------------------+
| ``TYPE_SUCCESS``   | *success*                                  |
+--------------------+--------------------------------------------+
| ``TYPE_DANGER``    | *danger*                                   |
+--------------------+--------------------------------------------+
| ``TYPE_WARNING``   | *warning*                                  |
+--------------------+--------------------------------------------+
| ``TYPE_INFO``      | *info*                                     |
+--------------------+--------------------------------------------+
| ``TYPE_LIGHT``     | *light*                                    |
+--------------------+--------------------------------------------+
| ``TYPE_DARK``      | *dark*                                     |
+--------------------+--------------------------------------------+
| ``TYPE_ERROR``     | *error* (previously used for ``$cFehler``) |
+--------------------+--------------------------------------------+
| ``TYPE_NOTE``      | *note* (previously used for ``$cHinweis``) |
+--------------------+--------------------------------------------+

The *value* column represents the respective Bootstrap 4 CSS class.

.. code-block:: php

    <?php

    $alertHelper->addAlert(Alert::TYPE_INFO, 'This is just test info!', 'testInfo');

The type is added as a CSS class ``alert-type`` for the respective *alert*, which corresponds to a
Bootstrap 4 *alert-info* class in this particular example. |br|
The last *key* parameter represents a key string, through which an alert can be identified and overwritten, if
necessary. This *key* is also displayed in HTML (data-Attribut ``data-key``) and is addressable via
Javascript/CSS.

Options
-------

Furthermore, options can be assigned to the *alert*. |br|
The *dismissable* option can be used, for example, to force the *alert* to be closed only through user interaction.

.. code-block:: php

    <?php

    $alertHelper->addAlert(
        Alert::TYPE_INFO,
        'This is just test info!',
        'testInfo',
        ['dismissable' => true]
    );

All possible options:

+-----------------------------+--------+---------+-----------------------------------------------------------------+
| Option                      | Type    | Default | Description                                                    |
+=============================+========+=========+=================================================================+
| ``dismissable``             | bool   | false   | alert can be clicked away                                       |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``fadeOut``                 | int    |  0      | fadeOut (e.g. via the constant: ``Alert::FADE_SLOW`` =9000,     |
|                             |        |         | which corresponds to 9 seconds. Alternatively, type in any      |
|                             |        |         | integer)                                                        |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``showInAlertListTemplate`` | bool   | true    | Display alert in the centre of the header                       |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``saveInSession``           | bool   | false   | save alert in the *SESSION* (e.g. for redirects)                |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``linkHref``                | string |         | entire alert as a link                                          |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``linkText``                | string |         | If ``linkHref`` and ``linkText`` set, then                      |
|                             |        |         | text in the message will be attached as a link                  |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``icon``                    | string |         | *Font Awesome* icon                                             |
+-----------------------------+--------+---------+-----------------------------------------------------------------+
| ``id``                      | string |         | attaches the value of the ``id`` to the HTML                    |
+-----------------------------+--------+---------+-----------------------------------------------------------------+

Display in the front end
------------------------

Alerts will be saved as a collection in the Smarty variable ``alertList``. All alerts for which
``showInAlertListTemplate === true`` gesetzt ist, werden zentral im Header ausgegeben.

.. code-block:: html+smarty

    {include file='snippets/alert_list.tpl'}

In the event that you wish to display the alert in a certain place within the template, as opposed to the header,
simply set the option ``showInAlertListTemplate`` to ``false``. Then, display *alert* wherever desired as
follows:

.. code-block:: html+smarty

    {$alertList->displayAlertByKey('testInfo')}

