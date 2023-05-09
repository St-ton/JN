Breaking changes
================

.. |rarr| raw:: html

   &rArr;

.. |br| raw:: html

   <br />

JTL-Shop 4.x |rarr| JTL-Shop 5.x
--------------------------------


- **System requirements upgraded to PHP 7.3**

    PHP 7.3 is a requirement for the operation of JTL-Shop 5.x.

- **jQuery version upgraded to 3.0**

    For JTL-Shop 5.x, the Javascript framework *jQuery* was updated from version 1.12 to
    Version 3.0. |br|
    You can find more information in section ":doc:`/shop_templates/jquery_update`".

- **Bootstrap updated to version 4.1**

    The CSS-Framework *Bootstrap* has also received an update along with JTL-Shop 5.x and is now version 4.1.3
    in the online shop.

- **The outdated libraries "xajax" and "PclZip" were removed**

- **The unused page types "News archive" and "RMA" were removed**

- **The versioning was changed**

    "Semantic versioning" : for JTL-Shop, |br|
    "API versioning" : internally for the synchronisation with JTL-Wawi**

    With JTL-Shop 5.x, the version numbering of the online shop will be changed to the generally accepted method
    `SemVer <http://semver.org/>`_. |br|
    For the purpose of connecting to JTL-Wawi, the previous versioning will continue to be maintained as the internal API version.

- **The option to upgrade the JTL-Shop from the *earlier* version 4.02 to version 5.x was removed**

    Users of previous versions, like version 3.0 and earlier, must upgrade to JTL-Shop version 4.06
    , in order to then upgrade to version 5.x.

- **As of version 5.x, the "Evo" template from JTL-Shop 4 will be continued as a separate project and will no
  longer be provided in newer shop versions.**

    You can find the "Evo" template in the JTL gitlab repository at
    `Evo <https://gitlab.com/jtl-software/jtl-shop/templates>` and on the JTL Builds server at
    `build.jtl-shop.de <https://build.jtl-shop.de/get/template_evo-5-0-0-rc-3.zip/template>`.

- **UTF8 migration for the entire online shop**

    + For string manipulation, it is recommended to use the PHP *Multibyte String Function* (``mb_``).
    + The ``utf8_encode()`` function should not be used in plug-ins anymore.
    + The database of JTL-Shop 5.x was overhauled with regard to its collations and the respective table engines,
      and changed to UTF8. |br|

      +-------------------+---------------------+
      | default collation | ``utf8_unicode_ci`` |
      +-------------------+---------------------+
      | default engine    | ``InnoDB``          |
      +-------------------+---------------------+

- **The display and creation of the menu structure in the back end was altered**

    The dynamic creation of the menu structure of the back end has been overhauled. As of JTL-Shop 5.0 it is no longer based on
    database tables, but rather on the structure in the ``admin/includes/admin_menu.php`` file. |br|
    The layout of all menu items has also been greatly modernized in the course of this change.

- **Multilingual capability of the back end was changed to "gettext"**

    The multilingual capability of all menus in the back end of JTL-Shop as of version 5.0 are managed via
    `gettext/gettext <https://github.com/php-gettext/Gettext>`_. |br|

- **Plug-ins will no longer come in installation packages**

    Plug-ins can instead be obtained from the JTL-Extension Store. |br|
    For this reason, plug-ins will no longer come along with the installation package of the online shop.

    The following plug-ins will be no longer be available, nor will they be replaced:

    - JTL Backend User Extension
    - JTL Themebar

- **Tools for compiling themes have been overhauled**

    To compile your own theme in JTL-Shop 4.x, you can use the
    `Evo Editor <https://gitlab.com/jtl-software/jtl-shop/legacy-plugins/evo-editor>`_ |br|
    In JTL-Shop 5.x themes were compiled with the
    `JTL Theme Editor <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_theme_editor>`_

    You can find more information on the application of these plug-ins in section ":ref:`label_eigenestheme_kompilieren`".

- **VAT ID number validation has been expanded from domestic to EU-wide**

    The previous validation process of VAT ID numbers, which was only valid for Germany, has now been replaced
    with an EU-wide validation process using the VAT information exchange system, (VIES) from the European Union.

    You can find further information about this system here:
    EU VIES <https://europa.eu/youreurope/business/taxation/vat/check-vat-number-vies/index_de.htm>`_

- **The table `tpreise`, including its contents provided by dbeS were removed**

    In JTL-Shop 4.x, for compatibility reasons with JTL-Wawi 0.9, prices were kept in several tables in the
    database (``tpreise`` and ``tpreis``/``tpreisdetail``). |br|
    This repetitive data storage has been removed altogether from JTL-Shop 5.x. All price data are now only found in 
    the tables ``tpreis`` und ``tpreisdetail``.

- **The "Show financing proposals" setting (1324) has been removed**

    This setting was first introduced with JTL-Shop 3.x as part of the financing module "Dresdner Cetelem" / "Commerz Finanz"
    . It is no longer available in JTL-Shop 4.x or JTL-Shop 5.x.

- **Data type for media files tabs changed**

    The item properties ``$cMedienTyp_arr`` are no longer an associative array, but rather now in JTL-Shop 5.x
    an array of arrays.

- **Several payment method integrations were removed**

    JTL-Shop will no longer come with the following payment modules, as they have been altogether
    removed from the core of JTL-Shop 5.x: |br|

    - EOS
    - Wirecard
    - UT
    - ipayment
    - PaymentPartner
    - PostFinance
    - SafetyPay
    - WorldPay
    - Sofort
    - Billpay
    - Moneybookers
    - UOS

    The old core payment method "PayPal" has been removed. The plug-in *JTL PayPal* will be available from now on instead.

- **Hooks that have been extended, complemented or removed**

    Over the course of the modifications and changes mentioned here, various hooks of the plug-in system
    have also been either changed, supplemented or completely removed. |br|
    You can find a complete list of all available hooks and their respective parameters in the developer documentation at
    ":doc:`/shop_plugins/hook_list`".

- **The "Imanee Image Manipulation Lib" was removed**

    The Imanee project for image processing has not been maintained by the provider for several years now and has
    been removed from the core of JTL-Shop 5.x.

- **The "product tags" feature was removed**

    This feature was seldom used by customers and is no longer up to date. |br|
    This feature was removed from the core of JTL-Shop with JTL-Shop 5.x.

- **URL generation has been overhauled**

    SEO URLs will no longer be dealt with using ``iso2ascii()``, but rather with its own testing and coding
    procedures all centralised in the SEO assistant.

- **Settings (1142) and (1130) for the number of thumbnails displayed for parent-child relationships have both been removed**

    Due to the more efficient display of item details in the NOVA template, these two settings
    are obsolete and have been removed from the core of JTL-Shop with JTL-Shop 5.x.

- **Duplication of pictures in multilingual shops has been deactivated**

    In multilingual shops, all item images were previously generated and loaded for each language. This overhead on
    computation time and data transfer is relativised in JTL store 5.x, in that only a single image set is kept in the
    default language. |br|
    The foreign language image attribute from JTL-Wawi are no longer being considered, as these attributes
    are only of verbal quality. JTL-Wawi also only stores one image set for the default language.

- **Outdated modules were removed**

    The following outdated modules were removed from the core of JTL-Shop:

    - Price radar
    - Price graphics
    - Surveys

- **The "Do You Know" ("DUK") widget was removed**

    Until now, these features were rarely used and are no longer up to date. |br|
    They were, therefore, removed from the core of JTL-Shop with the release of JTL-Shop 5.x.

- **Dynamic price calculation now allows consistent pricing for orders abroad**

    The dynamic calculation of net prices was set as the default setting in JTL-Shop 5.x.

    As of JTL-Shop 4.06, this calculation can be activated by means of a configuration setting in the
    ``includes/config.JTL-Shop.ini.php``:

    .. code-block:: php

       define('CONSISTENT_GROSS_PRICES', true);

- **The Yatego export format was removed**

    The outdated and defected export format "Yatego" was removed from the core of the JTL-Shop.

    This format will instead be made available, if necessary, from Yatego directly as a plug-in.

- **Export formats of third-party providers were removed:**

    The following export formats of third-party providers were removed from the core of the JTL-Shop:

    - Hardwareschotte
    - Kelkoo
    - Become Europe (become.eu)
    - Europe
    - Billiger
    - Geizhals
    - Preisauskunft
    - Preistrend
    - Shopboy
    - Idealo
    - Preisroboter
    - Milando
    - Channelpilot
    - Preissuchmaschine
    - Elm@r Produktdatei
    - Yatego Neu
    - LeGuide.com
    - Twenga

- **Old Shop3 back end templates were removed**

- **Support for a separate mobile template was removed**

- **The following outdated core features were removed:**

    - The "scale up" image function
    - Function and box "Global characteristics"
    - VCard Upload
    - Google Analytics
    - News-Widget
    -Customer referral programme
    - The old JTL-Shop 3.0 image interface
    - Internal word linking system

- **In the meta tag "robots" of special pages, the "content" is now set to "nofollow, noindex"**

    From a SEO perspective, indexing these page types in particular brings no value whatsoever. |br|
    Furthermore, if there are errors in the legal texts, indexing can lead to these pages being easily found by
 cease and desist lawyers via Google search.

    Therefore, the special pages were set to "nofollow, noindex" in the meta tag parameter "content" in JTL-Shop 5.x.

- **Faster shipping methods have been prioritised**

    As of JTL-Shop 5.x shipping methods will be displayed and sorted not only based on price. |br|

    For example, if two shipping methods have the same price, the shipping method with the lower
 sorting number, which corresponds to higher priority, is now displayed before the shipping method with higher sorting number. |br|
    This way, faster shipping methods will be displayed higher on the list of available shipping options.

- **Basket consistency check**

    As of JTL-Shop 4.05, checksums will be carried out with the help of the basket consistency check. |br|
    You can find more information on this in section ":ref:`label_hinweise_wkchecksum`".

- **The favicon upload feature has been overhauled**

    With JTL-Shop 5.x, the upload feature for the online shop *favicon* has been overhauled.

    The following paths display the directories, where favicon is searched for: |br|
    (sorted from top to bottom)

    * Front end:

    .. code-block:: console

       [Shop-root]/[Templates-Pfad]/themes/base/images/favicon.ico
       [Shop-root]/[Templates-Pfad]/favicon.ico
       [Shop-root]/favicon.ico
       [Shop-root]/favicon-default.ico

    * Back end:

    .. code-block:: console

       [Shop-root]/[admin-Pfad]/favicon.ico
       [Shop-root]/[admin-Pfad]/favicon-default.ico

    As soon as *favicon* is found in one of the paths, the search will end and the located *favicon*
    is then used.

- **Google Analytics tracking was removed from the core of JTL-Shop**

    Due to extensive changes in "Google Analytics", the previously used implementation
    (``ga.js``) was removed from JTL-Shop 5.x.

    In the future, tracking will also be managed by separate plug-ins that comply with current GDPR
 regulations.

- **Google-Recaptcha and Gravatar were removed from the core of JTL-Shop**

    According to the requirements of the GDPR, for the data transfer to third-party providers, consent must be explicitly obtained from each
    and every individual customer. Which is why modules from third-party providers have been removed from
    JTL-Shop 5.x.

    The JTL-Shop is by default released in a way that no data is passed on to third-party providers.

- **GDPR compliance established**

    As the GDPR came into effect, the online shop was modified in a number of ways.

    Obtaining customer consent for marketing-relevant emails is now taken care of by a new double opt-in procedure
    (see ``includes/src/Optin/``). |br|
    Furthermore, in JTL-Shop 5.x a “clean-up”, or encryption, of personal data of individual customers
 has been implemented, which is regularly triggered by Chronjobs. See ``includes/src/GeneralDataProtection/``.

- **Cryptographic functions have been overhauled**

    Cryptographic functions, as well as ID generation functions, rely heavily on the generation of
    random numbers, which are not always truly random once they are machine generated. |br|
    The PHP default function for this purpose is also no exception. |br|

    In order to tackle this issue effectively, improved libraries have been integrated into JTL-Shop 5.x for random number
    generation.

    This revision of the cryptographic functions of the online shop also entailed the replacement of the
    hashing functions that are called up before passwords are stored.

