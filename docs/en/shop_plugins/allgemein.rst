General information
===================

.. |br| raw:: html

    <br />

The JTL plug-in system makes it possible to add on various types of additional features,
without having to modify the core code of JTL-Shop.

Plug-ins are installed by the shop operator/administrator. |br|
Installation consists of uploading a plug-in to its respective directory, which is ``<Shop-Root>/plugins/`` for
shop versions 5 and higher, and the installation itself, which is carried out via the plug-in manager
in the admin area of the shop.
In the plug-in manager, you have the option to *temporarily deactivate* or *permanently uninstall* plug-ins
that have already been installed. The features of the plug-in manager can be used in normal shop operation. |br|
Additionally, you have the option to secure plug-ins via a licence check.

There are a number of different plug-ins for carrying out various tasks in the JTL-Shop:

* To execute features ("*front end links*") in the front end of the JTL-Shop, whether or not they are visible.
* Providing special features in the back end of the shop like analysis and
  statistics ("*custom links*")
* Providing new payment methods in the form of a "payment method plug-in"
* Providing new boxes ("box management") for the JTL-Shop front end
* Integration of new email templates in the JTL-Shop
* and much more

A plug-in can fulfil *one* or *a combination* of these tasks.

The plug-in system works by means of :doc:`hooks </shop_plugins/hooks>`, which are defined in various
locations throughout the JTL-Shop code. |br|
A plug-in can use one or more hooks to execute its own code in these locations.

.. hint::

    If multiple plug-ins that use the same hooks are installed, the code of *each* plug-in will be executed at this point,
    in the chronological order in which the plug-ins were installed.

Plug-ins can be updated by remaining *versioned*. |br|
Plug-in updates can contain new features and/or bug fixes.

A shop operator will have to carry out plug-in updates themselves. The procedure for this is identical to the installation process. |br|
After uploading a new plug-in version into the plug-in directory of the online shop, the plug-in manager automatically recognizes that a new version is available
and an update button will pop up. |br|
After clicking on the update button, the plug-in will automatically be updated to the new version. The updated plug-in
is then activated immediately after updating.

Plug-ins may require a minimum version of the online shop. |br|
Since the online shop system can be enhanced with new functions when the shop is updated, plug-ins
can extend or access these new functions. This would not function in an older version of the shop
and, consequently, could lead to errors.

At the heart of every plug-in is the XML file ``info.xml`` that describes the plug-in. |br|
This XML file has to specify a minimum XML structure version so that the functionality described by the
plug-in can also be interpreted by the JTL-Shop. The plug-in XML version thus keeps the plug-in system
itself extendable. |br|
For example, in JTL-Shop 3.04, this XML structure has been extended to include self-defined email templates that a plug-in can automatically create and send via
the XML version.

A JTL-Shop can be operated in multiple languages. |br|
Through the language variable manager integrated in the plug-in system, data can be delivered already localised in
any number of languages. |br|
The plug-in manager also allows the shop operator to customise all the language variables to suit their own needs.
Language variables can still be reset to the default setting by the shop operator at any time. |br|
If a plug-in includes more languages than are available in the online shop system, only the languages available in the system
will be installed. On the other hand, if a plug-in delivers language variables in fewer languages than are currently activated in the online shop
, the language variables of the other languages will be filled in with the default language.

Plug-in management in the back end of the JTL-Shop
--------------------------------------------------

Plug-in management is the central place in the back end of JTL-Shop where plug-ins can be installed/uninstalled,
activated/deactivated, updated or configured.
As of JTL-Shop 5, it is referred to as the "Plug-in manager".

When *uninstalling* a plug-in, plug-in settings and any additional tables written by the plug-in
will be deleted. However, the same does not apply to the *deactivation* of a plug-in: Here, plug-in settings
and tables remain, but the plug-in is no longer executed.

.. important::

    Uninstalled plug-ins not only lose all their own settings and all language variables, they also
    lose all their database tables! |br|
    Deactivated plug-ins will not be loaded by the online shop system and they will no longer consume any of the system’s resources.

Plug-in installation
""""""""""""""""""""

Plug-in installation is comprised of two steps. You can do this while the online shop
is operational.

1. Upload the plug-in: |br|
   **ab Shop Version 4.x** in das Verzeichnis ``includes/plugins/``, |br|
   **As of shop version 5.x** into the ``plugins/`` directory. |br|
   (The upload takes place in "unpacked" form.
   File archives like ``*.zip`` or ``*.tgz`` are not supported.)
2. Start the installation in the back end via the menu item "*Plug-in manager*" in the "*Available*" tab. |br|
   The installation will then take place automatically.

Plug-in configuration
"""""""""""""""""""""

Each plug-in in the JTL-Shop will then have their own entry in the plug-in manager. |br|
The name displayed here corresponds to the contents of the ``<Description>`` tag in the ``info.xml`` file of the respective plug-in
and therefore represents the textual name of this plug-in.

Each plug-in can define any number of *custom links* and *setting links*. |br|
*Custom links* are links that execute custom code and produce custom content.  |br|
*Setting links* include plug-in settings.

Plug-ins can query and store custom settings via a *custom link*.
However, settings can be stored and queried quicker and more securely
via *setting links*. |br|
Most notably, access to these settings in the plug-in's own code is considerably simplified while the look and feel
of the shop’s settings is maintained. Additionally, a lot of program code is spared, since the required settings
can simply be stored in the XML file of the plug-in via *setting links*.
No additional code is necessary here!
