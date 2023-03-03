Lizenzierung
============

.. |br| raw:: html

   <br />

Plugin- und Template-Lizensierung (ab Shop 5.0.0)
-------------------------------------------------

**Voraussetzung:**

Als Erstes müssen Sie Ihr neues Plugin/Template im JTL-Kundencenter anlegen und eine ``EsxID`` für dieses
Plugin/Template generieren.

Loggen Sie sich hierzu in Ihr Kundencenter in den
Bereich `Erweiterungen verwalten <https://kundencenter.jtl-software.de/sellerprogramm/erweiterungen-verwalten>`_ ein
und legen Sie dort eine neue Erweiterung des gewünschten Typs (JTL-Shop 5 Plugin oder JTL-Shop 5 Template) an. (Bitte
Beachten Sie, dass Sie das Onboarding für Seller durchlaufen haben müssen, um Ihre Plugins und Templates im
JTL-Extension Store anbieten zu können)

.. image:: /_images/lic_cust_centre_login.png

Mit Erstellung der neuen Erweiterung wird auch sofort eine dazugehörige ExsID generiert.

.. image:: /_images/lic_exs_id.png

ExsID
"""""

Die ``ExsID`` tragen Sie in der ``info.xml`` Ihres Plugins, bzw. in der ``template.xml`` Ihres Templates, ein.

**Beispiel für ein Plugin:**

.. code-block:: xml
   :emphasize-lines: 12

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


**Beispiel für ein Template:**

.. code-block:: xml
   :emphasize-lines: 11

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

Falls Sie Ihr Plugin oder Template kostenlos zur Verfügung stellen wollen, sind ab hier keine weiteren Schritte
erforderlich. |br|
Das Plugin ist nun über das Backend von JTL-Shop installierbar und updatebar.

Falls Sie Testlizenzen ausgestellt haben, werden Plugins mit abgelaufenen Testlizenzen automatisch deaktiviert.

Die PluginID beim Updaten
-------------------------

Mit der korrekten Pflege der Plugin ID können Sie sicherstellen, dass Ihr Plugin korrekt geupdatet wird. Eine
versionsübergreifend identische Benennung der ``PluginID`` und des dazugehörigen Installationsordners gewährleisten,
dass Plugins sich aktualisieren können.

Wenn die Benennungen der PluginID und des dazugehörigen Installationsordners zwischen 2 Versionen Unterschiede
aufweisen, wird JTL-Shop keine Aktualisierung des bestehenden Plugins vornehmen, sondern eine separate Neuinstallation
vornehmen, so dass am Ende 2 unterschiedliche Versionen desselben Plugins installiert sind.

Stellen Sie daher sicher, dass über alle Versionen Ihres Plugins hinweg Die ``PluginID`` in der ``info.xml``, der
Installationsorder des Plugins sowie das Feld ``PluginID`` bei der Pflege der Erweiterung im Kundencenter immer exakt
identisch benannt sind, um derartige Fehler zu vermeiden.

Lizenzprüfung
-------------

Für den Fall dass die Lizenz/Subscription manuell geprüft werden soll, bietet der Shop einige Möglichkeiten.

Bootstrapping
"""""""""""""

In der ``Bootstrap.php`` des Plugins oder Templates kann die Methode
``BootstrapperInterface::licenseExpired(ExsLicense $license): void`` implementiert werden. Diese Methode wird immer
dann aufgerufen, wenn JTL-Shop auf abgelaufene Extensions prüft. |br|
Dies findet via Cronjob alle 4 Stunden statt, sowie bei jeder Aktualisierung der Lizenzübersicht im Backend.


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

Um an beliebigen Stellen die Lizenz für eine beliebige Extension zu erhalten (insbesondere hilfreich bei "*InApp
Purchases*") existiert der License-Manager.

.. code-block:: php

    $manager      = new JTL\License\Manager(\JTL\Shop::Container()->getDB(), \JTL\Shop::Container()->getCache());
    $subscription = $manager->getLicenseByExsID('some_exs_id');


Komplexe Beispiele
------------------

Die verschiedenen Möglichkeiten in der ``Bootstrap.php`` eines (Child-)Templates zeigt das folgende Codebeispiel.

.. code-block:: php

    <?php declare(strict_types=1);

    namespace Template\mychildtemplate;

    use JTL\License\Manager;
    use JTL\License\Struct\ExsLicense;
    use JTL\Shop;

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
                // FALLBACK to default template
                Shop::Container()->getTemplateService()->setActiveTemplate('NOVA');
                die('Bitte erwerben Sie eine neue Lizenz!');
            } elseif ($license->getLicense()->getSubscription()->isExpired()) {
                die('Bitte erwerben Sie eine neue Subscription!');
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
            // FALLBACK to default template
            Shop::Container()->getTemplateService()->setActiveTemplate('NOVA');
        }
    }


Analog dazu funktionieren die Methoden aus der ``Bootstrap.php`` eines Plugins. |br|
Hier besteht zusätzlich die Möglichkeit, auch Plugins über den Aufruf von ``JTL\Plugin\Plugin::selfDescruct()`` hart
zu deaktivieren.


.. code-block:: php
   :emphasize-lines: 15,16

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
                $this->getPlugin()->selfDestruct(State::EXS_SUBSCRIPTION_EXPIRED, $this->getDB(), $this->getCache());
            }
        }
    }
