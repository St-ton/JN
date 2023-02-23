Hinweise zum verkürzten Checkout (ab 4.06)
==========================================

.. |br| raw:: html

   <br />

Bis einschließlich Version 4.05 Build 3 besteht der Checkout in JTL-Shop mit Evo- oder Evo-Child-Template aus den
folgenden Schritten:

1. Warenkorb
2. Login, Registrieren oder Gast
3. Rechnungsadresse
4. Lieferadresse
5. Versandart
6. Zahlungsart
7. Bestellübersicht und Abschluss

Ab JTL-Shop Version 4.06 werden zum einen die Schritte 2, 3 und 4, sowie zum anderen die Schritte 5 und 6 zu jeweils
einem Schritt zusammengefasst, so dass sich nur noch diese Schritte ergeben:

1. Warenkorb
2. Login, Registrieren oder Gast, Rechnungsadresse und Lieferadresse
3. Versandart und Zahlungsart
4. Bestellübersicht und Abschluss

Bei der Umstellung wurde großer Wert darauf gelegt, die Änderungen so gering wie möglich zu halten und möglichst über
:doc:`Template-Anpassungen </shop_templates/short_checkout>` zu realisieren. |br|
Die internen Abläufe im JTL-Shop-Core sind nahezu gleich geblieben. Die bisherigen Steps wurden intern beibehalten und
wenn möglich Workarounds eingefügt, um bestehende Plugins möglichst kompatibel mit dem neuen Checkout zu halten.

.. caution::

    In jedem Falle sollten Plugins, die in den Checkout eingreifen, vor einem Update ausgiebig getestet werden.

Die intern verwendeten Steps sind erhalten geblieben. Sie stellen jedoch teilweise keine optische und zeitliche
Trennung, sondern nur noch eine logische Trennung dar!

* *Versand* und *Zahlung* spiegeln beide den optischen Step "Versand- und Zahlungsart" wider.
* Im Step *Versand* ist keine Versandart und keine Zahlungsart ausgewählt.
* Im Step *Zahlung* wurde bereits explizit eine Versandart ausgewählt.
* Der Step *edit_customer_address* ist neu und spiegelt "Rechnungs- und Lieferadresse" wider.

Template-Dateien
----------------

Einige Template-Dateien werden im Checkout :ref:`nicht mehr verwendet <outdatedTemplateFiles>`
und einige sind :ref:`neu hinzugekommen <newTemplateFiles>`. |br|
Bei der Ausgabe eigener Inhalte über Hook ``HOOK_SMARTY_OUTPUTFILTER`` (140) und in Abhängigkeit bestimmter
Template-Dateien sind Anpassungen im Plugin notwendig.

Die Vergabe von IDs für Inhaltsbereiche wurde weitestgehend vom bisherigen Checkout übernommen. Es ist jedoch möglich,
dass eine andere räumliche Anordnung notwendig war oder aus optischen Gründen spezielle Tags weggefallen sind. |br|

.. caution::

    Plugins, die eigene Inhalte mittels *phpQuery* und stark qualifizierte Selektoren in die Ausgabe injecten, müssen
    überprüft und an diesen Stellen überarbeitet werden.

Hooks
-----

Es gibt keine Hooks oder Änderungen, die speziell mit dem verkürzten Checkout zu tun haben.
