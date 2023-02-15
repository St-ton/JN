JTL-Shop Consent Manager
========================

.. |br| raw:: html

    <br />

Der Consent Manager von JTL-Shop stellt die Verwaltung für die Einwilligungen betroffener Personen, hier Endverbraucher,
zur Weiterverarbeitung ihrer personenbezogenen Daten dar. |br|

Seit dem EuGH-Urteil vom 01.10.2019 müssen Website-Betreiber im Rahmen der *Cookie-Richtlinie* über gesetzte Cookies
umfassend informieren und eine Einwilligung über die Verarbeitung personenbezogener Daten von ihren Website-Besuchern
einholen.

.. image:: /_images/cm-banner.png

Mit dem JTL-Shop Consent Manager können die Einwilligungen ("Consents") der Website-Besucher unkompliziert erfragt
und abgespeichert werden.

.. image:: /_images/cm-banner_mark.png

.. image:: /_images/cm-mainscreen.png

Der Aufruf dieser Einstellungen ist im Frontend jedenzeit über ein entsprechendes Icon möglich:

.. image:: /_images/cm-icon.png

Consent Manager im Plugin
-------------------------

Auch Plugins können über den Consent Manager von JTL-Shop 5 Einverständniserklärungen einfordern.

Hierfür registriert ein Plugin über den EventDispatcher (":ref:`label_bootstrapping_eventdispatcher`")
einen Listener für das Event ``CONSENT_MANAGER_GET_ACTIVE_ITEMS``.

.. code-block:: php

    $dispatcher->listen('shop.hook.' . \CONSENT_MANAGER_GET_ACTIVE_ITEMS, [$this, 'addConsentItem']);

Wird nun das Event ``CONSENT_MANAGER_GET_ACTIVE_ITEMS`` ausgelöst, registriert die Lambda-Funktion
``addConsentItem()`` im Plugin die entsprechende Einverständniserklärung im JTL-Shop Consent Manager.

.. code-block:: php

    /**
     * @param array $args
     */
    public function addConsentItem(array $args): void
    {
        $lastID = $args['items']->reduce(static function ($result, Item $item) {
                $value = $item->getID();

                return $result === null || $value > $result ? $value : $result;
            }) ?? 0;
        $item   = new Item();
        $item->setName('JTL Example Consent');
        $item->setID(++$lastID);
        $item->setItemID('jtl_test_consent');
        $item->setDescription('Dies ist nur ein Test aus dem Plugin JTL Test');
        $item->setPurpose('Dieser Eintrag dient nur zu Testzwecken');
        $item->setPrivacyPolicy('https://www.jtl-software.de/datenschutz');
        $item->setCompany('JTL-Software-GmbH');
        $args['items']->push($item);
    }

Zur Einforderung der Einverständniserklärung wird nun ein entsprechender Schalter im JTL-Shop Consent Manager
angezeigt.

.. image:: /_images/cm-testcons.png

Im Plugin "`jtl-test <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_test>`_" können Sie sich diese Vorgehensweise
in ausführlicherer Form anschauen.


