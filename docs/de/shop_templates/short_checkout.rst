Template-Änderungen - Verkürzter Checkout (ab 4.06)
===================================================

.. |br| raw:: html

   <br />

Bis einschließlich Version 4.05 Build 3 besteht der Checkout in JTL-Shop mit EVO- oder EVO-Child-Template aus den
folgenden Schritten:

1. Warenkorb
2. Login, Registrieren oder Gast
3. Rechnungsadresse
4. Lieferadresse
5. Versandart
6. Zahlungsart
7. Bestellübersicht und Abschluss

Ab Version 4.06 werden die Schritte 2, 3 und 4, sowie 5 und 6 zu jeweils einem Schritt zusammengefasst, so dass sich
nur noch diese Schritte ergeben:

1. Warenkorb
2. Login, Registrieren oder Gast, Rechnungsadresse und Lieferadresse
3. Versandart und Zahlungsart
4. Bestellübersicht und Abschluss

Bei der Umstellung wurde großer Wert darauf gelegt, die Änderungen so gering wie möglich zu halten und möglichst über
Template-Anpassungen zu realisieren. |br|
Die internen Abläufe im Shop-Core sind – je nach Umfang der Änderungen - auch noch mit einem angepassten oder
Drittanbieter-Template in weiten Teilen kompatibel.

.. attention::

    Testen Sie in jedem Fall Drittanbieter-Templates und EVO-Child-Templates, die Anpassungen im Bereich des Checkouts
    enthalten, vor einem Update ausgiebig!


.. _outdatedTemplateFiles:

Nicht mehr verwendete Template-Dateien
--------------------------------------

Die Template-Dateien ``checkout/step1_proceed_as_guest.tpl`` und ``checkout/step2_delivery_address.tpl`` wurden
als `@deprecated` markiert und werden nicht mehr vom Shop-Core oder von EVO referenziert. Beide Dateien sind jedoch
noch ohne Inhalt in EVO vorhanden.

.. attention::

    Wenn ``checkout/step1_proceed_as_guest.tpl`` und/oder ``checkout/step2_delivery_address.tpl`` in Child-Templates
    von EVO referenziert aber selbst nicht ersetzt werden, dann müssen diese Dateien aus einem EVO-Template der Version
    4.05 kopiert oder die referenzierenden Template-Dateien an die neue Struktur angepasst werden. Ansonsten kann es
    zu fehlendem Inhalt kommen.

Die Template-Datei ``checkout/step4_payment_options.tpl`` ist lediglich noch als Stub vorhanden und lädt
die (neuen) Template-Dateien für Zahlungsarten (``checkout/inc_payment_methods.tpl``) und Trustedshops
(``checkout/inc_payment_trustedshops.tpl``).

Da die Datei ``checkout/step4_payment_options.tpl`` von verschiedenen Zahlungs-Plugins als Triggerpunkt für eigene
Ausgaben verwendet wird, wird sie weiterhin vom Shop-Core geladen. Dort wird sie nachbearbeitet, um Form-in-Form-Tags
zu vermeiden und die Kompatibilität mit Plugins zu gewährleisten, die nicht direkt für den *3-Step-Checkout* entwickelt
wurden.

.. caution::

    Die Datei ``checkout/step4_payment_options.tpl`` sollte nicht mehr direkt von anderen Template-Dateien
    per *include* eingebunden werden! |br|
    Als Alternative stellt der Shop-Core im Checkout-Prozess die Template-Variable ``{$step4_payment_content}``
    zur Verfügung, die den aufbereiteten Inhalt von ``checkout/step4_payment_options.tpl`` enthält.


.. _newTemplateFiles:

Neue Template-Dateien
---------------------

Die Template-Dateien ``checkout/inc_payment_methods.tpl``, ``checkout/inc_payment_trustedshops.tpl`` und
``checkout/inc_shipping_address.tpl`` sind neu und werden ausschließlich von anderen EVO-Template-Dateien
referenziert und per `include` eingebunden.

Zur strukturellen Vereinfachung und besseren Wiederverwendbarkeit wurden die Template-Dateien
``register/form/customer_account.tpl``, ``register/form/customer_login.tpl``,
``register/form/customer_shipping_address.tpl`` und ``register/form/customer_shipping_contact.tpl`` neu eingeführt.
Diese Dateien werden ausschließlich von EVO per *include* geladen und müssen in Drittanbieter-Templates nicht
zwangsläufig enthalten sein.

Die Template-Datei ``checkout/step1_edit_customer_address.tpl`` ist ebenfalls neu, wird aber vom Shop-Core
in Ersetzung von ``checkout/step1_proceed_as_guest.tpl`` und ``checkout/step2_delivery_address.tpl`` verwendet.

.. important::

    Die Template-Datei ``checkout/step1_edit_customer_address.tpl`` ist neu und muss von Drittanbieter-Templates
    implementiert werden, da sie vom Shop-Core ab Version 4.06 verwendet wird!

Änderungen
----------

In der Template-Datei ``checkout/step3_shipping_options.tpl`` werden ab Version 4.06 neben den Versandarten auch die
Zahlungsarten der aktuell gewählten Versandart mit gerendert.

.. hint::

    Wenn ``checkout/step3_shipping_options.tpl`` in EVO-Child-Templates überschrieben wurde, dann muss diese Datei an
    die neuen Strukturen angepasst werden, da sonst keinerlei Möglichkeit zur Wahl der Zahlungsart mehr besteht!

Die in der Template-Variable ``{$step}`` enthaltenen Werte spiegeln keine optische, sondern nur noch eine logische
Trennung der einzelnen Steps wider, da aus Kompatibilitätsgründen intern alle Steps des alten Checkouts erhalten
geblieben sind. |br|
So stehen die Steps *Versand* und *Zahlung* optisch für den gleichen Step - „Versand- und Zahlungsart“.
Logisch unterscheiden sich beide darin, dass im Step *Versand* Versandart und Zahlungsart noch nicht
ausgewählt sind. Beim Step *Zahlung* ist jedoch bereits eine Versandart vom Nutzer ausgewählt worden. |br|
Neu hinzugekommen ist der Step ``edit_customer_address``, der sich hinter der Anzeige von „Rechnungs- und Lieferadresse“
verbirgt.
