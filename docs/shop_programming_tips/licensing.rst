Breaking Changes
================

.. |rarr| raw:: html

   &rArr;

.. |br| raw:: html

   <br />

Plugin- und Template-Lizensierung (ab Shop 5.0.0)
-------------------------------------------------


**Voraussetzung:**

Das Plugin muss im JTL-Kundencenter angelegt worden sein und eine exs_id wurde generiert.

ExsID
"""""

Als erstes muss die exs_id in der info.xml des Plugins bzw. der template.xml des Templates hinterlegt werden.
Beispiel für ein Plugin:

.. code-block:: php

    <?xml version="1.0" encoding="UTF-8"?>
    <jtlshopplugin>
        <Name>Mein Beispielplugin</Name>
        <Description>Tut überhaupt rein gar nichts</Description>
        <Author>Max Mustermann</Author>
        <URL>https://www.example.com</URL>
        <XMLVersion>100</XMLVersion>
        <ShopVersion>5.0.0</ShopVersion>
        <PluginID>my_example</PluginID>
        <CreateDate>2020-05-18</CreateDate>
        <Version>1.2.3</Version>
        <ExsID>175a1eb9-1234-4f87-b0e3-63bf782d37ba</ExsID>
        <Install>
            <empty></empty>
        </Install>
    </jtlshopplugin>


Beispiel für ein Template:

.. code-block:: php

    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <Template isFullResponsive="true">
        <Name>MeinTemplate</Name>
        <Description>Beispiel</Description>
        <Author>Max Mustermann</Author>
        <URL>https://www.example.com</URL>
        <MinShopVersion>5.0.0</MinShopVersion>
        <Version>1.0.0</Version>
        <Framework>Bootstrap4</Framework>
        <Parent>NOVA</Parent>
        <ExsID>175a1eb9-1234-4f87-b0e3-63bf782d37ba</ExsID>
        <Boxes>
            <Container Position="left" Available="1"></Container>
            <Container Position="right" Available="0"></Container>
            <Container Position="top" Available="0"></Container>
            <Container Position="bottom" Available="1"></Container>
        </Boxes>
    </Template>

Falls das Plugin/Template stets kostenlos ist oder dem Shopbenutzer vertraut wird, müssen keine weiteren Schritte unternommen werden.
Das Plugin ist nun über das Shopbackend installier- und updatebar. Falls Testlizenzen ausgestellt werden, werden Plugins mit abgelaufenen Testlizenzen automatisch deaktiviert.

Lizenzprüfung
-------------

Für den Fall dass die Lizenz/Subscription manuell geprüft werden soll, bietet der Shop einige Möglichkeiten.

Bootstrapping
"""""""""""""

In der Bootstrap.php des Templates oder Plugins kann die Methode BootstrapperInterface::licenseExpired(ExsLicense $license): void implementiert werden.
Diese Methode wird immer dann aufgerufen, wenn der Shop auf abgelaufene Extensions prüft. Dies findet via Cronjob alle 4 Stunden statt sowie bei jeder Aktualisierung der Lizenzübersicht im Backend.


Getter für Plugins
""""""""""""""""""

Am License-Objekt von Plugin-Instanzen gibt es stets einen Getter für die zugehörige Lizenz.

.. code-block:: php
    /** @var \JTL\Plugin\Plugin $plugin */
    $subscription = $plugin->getLicense()->getExsLicense()->getLicense()->getSubscription();


Getter für Templates
""""""""""""""""""""

Auch an Templatemodel-Instanzen gibt es einen entsprechenden Getter.

.. code-block:: php
    /** @var \JTL\Template\Model $template */
    $subscription = $template->getExsLicense()->getLicense()->getSubscription()


License-Manager
"""""""""""""""

Um an beliebigen Stellen die Lizenz für eine beliebige Extension zu erhalten (insbesondere hilfreich bei InApp Purchases) existiert der License-Manager.

.. code-block:: php
    $manager      = new JTL\License\Manager(\JTL\Shop::Container()->getDB(), \JTL\Shop::Container()->getCache());
    $subscription = $manager->getLicenseByExsID('some_exs_id');


Komplexbeispiele
""""""""""""""""

Die verschiedenen Möglichkeiten in der Bootstrap.php eines (Child-)Templates zeigt das folgende Codebeispiel.

.. code-block:: php
    <?php declare(strict_types=1);

    namespace Template\mychildtemplate;

    use JTL\License\Manager;
    use JTL\License\Struct\ExsLicense;

    class Bootstrap extends \Template\NOVA\Bootstrap
    {
        public function boot(): void
        {
            parent::boot();
            $this->customLicenseCheck();
            $this->checkViaManager();
        }

        private function customLicenseCheck(): void
        {
            $license = $this->getTemplate()->getExsLicense();
            if ($license === null) {
                die('Nanu? Keine Lizenz.');
            }
            if ($license->getLicense()->getSubscription()->getDaysRemaining() < 14) {
                echo 'Achtung! Subscription läuft bald aus!';
            } elseif ($license->getLicense()->getDaysRemaining() < 14) {
                echo 'Achtung! Lizenz läuft bald aus!';
            } elseif ($license->getLicense()->isExpired()) {
                die('Kauf einne neue Lizenz!');
            } elseif ($license->getLicense()->getSubscription()->isExpired()) {
                die('Kauf einne neue Subscription!');
            }
        }

        private function checkViaManager(): void
        {
            $manager = new Manager($this->getDB(), $this->getCache());
            $license = $manager->getLicenseByItemID('some_item_id');
            if ($license !== null && $license->getLicense()->getSubscription()->isExpired()) {
                // do something
            }
            $otherLicense = $manager->getLicenseByExsID('exsidOfAnotherPlugin');
            if ($license !== null && $license->getLicense()->getSubscription()->isExpired()) {
                // do something else
            }
        }

        public function licenseExpired(ExsLicense $license): void
        {
            echo 'Argh! Meine Lizenz ist abgelaufen!';
        }
    }

Analog dazu funktionieren die Methoden aus der Bootstrap.php eines Plugins.
Hier besteht zusätzlich die Möglichkeit, auch Plugins über den Call von JTL\Plugin\Plugin::selfDescruct() hart zu deaktivieren.

.. code-block:: php
    <?php declare(strict_types=1);

    namespace Plugin\my_example;

    use JTL\Events\Dispatcher;
    use JTL\Plugin\Bootstrapper;
    use JTL\Plugin\State;

    class Bootstrap extends Bootstrapper
    {
        public function boot(Dispatcher $dispatcher)
        {
            parent::boot($dispatcher);
            $license = $this->getPlugin()->getLicense()->getExsLicense();
            if ($license === null || $license->getLicense()->getSubscription()->isExpired()) {
                $this->getPlugin()->selfDestruct(State::ESX_SUBSCRIPTION_EXPIRED, $this->getDB(), $this->getCache());
            }
        }
    }
