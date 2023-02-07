Zahlungs-Plugins
================

.. |br| raw:: html

   <br />

Ein Zahlungs-Plugin definiert über den Knoten ``<PaymentMethod>`` in der ``info.xml`` eine oder mehrere
Zahlungsmethoden, die dann über eine Zuordnung zu Versandarten im Onlineshop für Bezahlvorgänge genutzt werden
können. |br|
Die grundsätzliche XML-Struktur einer Zahlungsmethode finden Sie im Abschnitt :ref:`label_infoxml_paymentmethode`
unter :doc:`infoxml`.

Grundlegendes
-------------

Jede Zahlungsmethode wird durch eine Payment-Klasse repräsentiert. Der Klassenname und die zugehörige Klassendatei
werden in der ``info.xml`` mit den Knoten ``<ClassName>`` und ``<ClassFile>`` festgelegt. Die Klassendatei muss sich
für eine erfolgreiche Validierung der Zahlungsmethode im Unterverzeichnis ``paymentmethod`` innerhalb des
Plugin-Verzeichnisses befinden. Bis einschließlich Version 4.x können die Bezeichner für Klassenname und Klassendatei
frei gewählt werden, während diese ab Version 5.0 der PSR-4-Spezifikation folgen müssen. |br|
Jede Payment-Klasse muss ab Version 5.0 das Interface ``JTL\Plugin\Payment\MethodInterface`` implementieren oder von
``JTL\Plugin\Payment\Method`` abgeerbt werden. Bis einschließlich Version 4.x müssen alle Payment-Klassen Unterklassen
von ``PaymentMethod`` (``/includes/modules/PaymentMethod.class.php``) sein. |br|
Über die Methoden der Payment-Klasse wird standardmäßig der komplette Zahlungsvorgang abgedeckt. Die Registrierung
weiterer Hooks für den Zahlungsprozess ist normalerweise nur notwendig, wenn durch die Zahlungsmethode weitergehende
Eingriffe in den Ablauf des Zahlungsvorganges oder des gesamten Bestellprozesses notwendig sind.

Implementation einer Payment-Klasse bis einschl. JTL-Shop Version 4.x
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

.. code-block:: php
   :emphasize-lines: 2

    <?php
    require_once PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php';

    /**
     * Class SimplePayment.
     */
    class SimplePayment extends PaymentMethod
    {
        // ...
    }

Implementation einer Payment-Klasse ab JTL-Shop Version 5.0
"""""""""""""""""""""""""""""""""""""""""""""""""""""""""""

.. hint::

    Im Weiteren wird von einer **Implementation für JTL-Shop Version 5.x** ausgegangen und nur soweit dies nicht auch
    sinngemäß für JTL-Shop Version 4.x gilt, explizit auf die Unterschiede eingegangen.

.. code-block:: php
   :emphasize-lines: 4

    <?php
    namespace Plugin\jtl_example_payment\paymentmethod;

    use JTL\Plugin\Payment\Method;

    /**
     * Class SimplePayment.
     */
    class SimplePayment extends Method
    {
        // ...
    }

Die Basis-Payment-Klasse ``JTL\Plugin\Payment\Method`` implementiert das benötigte Interface und stellt alle
grundlegenden Funktionen einer Zahlungsmethode zur Verfügung. Die eigene Payment-Klasse sollte deshalb immer von dieser
Basis-Klasse abgeerbt werden. |br|
Eine einfache Zahlungsmethode, die lediglich Informationen für eine Banküberweisung versendet, muss damit lediglich die
Methode ``preparePaymentProcess`` überschreiben.

.. code-block:: php

    <?php
    namespace Plugin\jtl_example_payment\paymentmethod;

    use JTL\Alert\Alert;
    use JTL\Mail\Mail\Mail;
    use JTL\Mail\Mailer;
    use JTL\Plugin\Payment\Method;
    use JTL\Session\Frontend;
    use JTL\Shop;
    use PHPMailer\PHPMailer\Exception;
    use stdClass;

    /**
     * Class SimplePayment
     * @package Plugin\jtl_example_payment\paymentmethod\src
     */
    class SimplePayment extends Method
    {
        protected const MAILTEMPLATE_SIMPLEPAYMENT = 'kPlugin_%d_SimplePaymentTransferData';

        /**
         * @inheritDoc
         */
        public function preparePaymentProcess($order): void
        {
            parent::preparePaymentProcess($order);

            $obj              = new stdClass();
            $obj->tkunde      = Frontend::getCustomer();
            $obj->tbestellung = $order;
            $tplKey           = \sprintf(self::MAILTEMPLATE_SIMPLEPAYMENT, $this->plugin->getID());

            /** @var Mailer $mailer */
            $mailer = Shop::Container()->get(Mailer::class);
            $mailer->getHydrator()->add('Bestellung', $order);

            $mail = new Mail();
            try {
                $mailer->send($mail->createFromTemplateID($tplKey, $obj));
            } catch (Exception $e) {
            } catch (\SmartyException $e) {
                Shop::Container()->getAlertService()->addAlert(
                    Alert::TYPE_ERROR,
                    __('Payment mail for Simple payment cant be send'),
                    'simplePaymentCantSendMail'
                );
            }
        }
    }

Die Methode ``preparePaymentProcess`` wird durch den Bestellabschluss nach Finalisierung der Bestellung aufgerufen und
startet den Bezahlvorgang der Zahlungsmethode. |br|
Im Beispiel wird das über die ``info.xml`` definierte E-Mail-Template für die Zahlungsmethode geladen und über den
Mailer-Service von JTL-Shop versendet.

Zahlung vor Bestellabschluss
----------------------------

Im Modus "Zahlung vor Bestellabschluss" wird beim Abschließen des Bestellvorganges durch den Kunden die Bestellung
nicht festgeschrieben, sondern lediglich in der aktuellen Kundensession gehalten, wenn der Bezahlvorgang gestartet wird.
Die Zahlungsmethode muss bei erfolgreicher Zahlung über einen Aufruf von ``/includes/modules/notify.php`` dafür sorgen,
dass der Kunde zum Bestellabschluss gelangt und die Bestellung festgeschrieben wird. Dies kann z. B. über eine
URL-Weiterleitung erfolgen. Die dafür notwendige URL kann mittels
:ref:`getNotificationURL <label_public-function-method-getNotificationURL>` ermittelt werden. |br|
Im Fehlerfall muss der Kunde zurück in den Bestellprozess geleitet werden, um die Bezahlung ggf. zu wiederholen oder
den Checkout mit einer anderen Zahlungsart fortsetzen zu können.

.. hint::

   Bei Zahlungsmethoden, die eine zeitversetzte Bestätigung der Zahlung via Webhook versenden, kann es passieren, dass
   die Bestellung nicht mehr festgeschrieben werden kann, da diese aufgrund einer abgelaufenen Kundensession bereits
   verfallen ist. In diesem Fall existiert dann eine Zahlung, zu der es keine Bestellung gibt! |br|
   Für solche Zahlungsmethoden sollte besser nur der Modus "Zahlung nach Bestellabschluss" gewählt werden.

Die "Zahlung vor Bestellabschluss" kann für die Zahlungsmethode über den XML-Parameter ``<PreOrder>1</PreOrder>``
voreingestellt werden. Dieser Wert lässt sich jedoch in den Einstellungen der Zahlungsmethode vom Betreiber des
Onlineshops nachträglich ändern.

Zahlung nach Bestellabschluss
-----------------------------

Im Modus "Zahlung nach Bestellabschluss" wird die Bestellung komplett abgeschlossen und in der Datenbank gespeichert,
bevor der Bezahlvorgang gestartet wird. Die Zahlungsmethode muss hier dafür sorgen, dass bei erfolgreicher Zahlung
die Bestellung per :ref:`setOrderStatusToPaid <label_public-function-method-setOrderStatusToPaid>` auf den Status
"bezahlt" gesetzt und mittels :ref:`addIncomingPayment <label_public-function-method-addIncomingPayment>` der
Zahlungseingang gespeichert wird. |br|
Ein Zahlvorgang, der in diesen Modus läuft, kann normalerweise auch neu gestartet werden falls Fehler aufgetreten sind.
Die Zahlungsmethode sollte dies dann auch entsprechend signalisieren. |br|
Siehe hierzu auch :ref:`canPayAgain <label_public-function-method-canPayAgain>` |br|
Ein Rücksprung in den Bestellvorgang und die Auswahl einer anderen Zahlungsmethode durch den Kunden ist jedoch nicht
möglich.

Die "Zahlung nach Bestellabschluss" kann für die Zahlungsmethode über den XML-Parameter ``<PreOrder>0</PreOrder>``
voreingestellt werden. Dieser Wert lässt sich jedoch in den Einstellungen der Zahlungsmethode vom Betreiber des
Onlineshops nachträglich ändern.

.. hint::

   Sollte die Zahlungsmethode nur einen der beiden Modi unterstützen, dann sollte bei geänderter Einstellung über
   :doc:`HOOK_PLUGIN_SAVE_OPTIONS <hook_descriptions/hook_plugin_save_options>` ein entsprechender Hinweis ausgegeben
   und die Zahlungsmethode über :ref:`isValidIntern <label_public-function-method-isValidIntern>` als "nicht verfügbar"
   markiert werden.

   .. code-block:: php

      /**
       * @inheritDoc
       */
      public function isValidIntern($args_arr = []): bool
      {
        if ($this->duringCheckout) {
            return false;
        }

        return parent::isValidIntern($args_arr);
      }

.. _label_public-function-method-init:

public function init()
""""""""""""""""""""""

Wird bei jedem Instanziieren der Zahlungsmethode aufgerufen. In der Payment-Basisklasse werden die Properties
``caption`` und ``duringCheckout`` initialisiert. Als Rückgabewert wird die Klasseninstanz selbst erwartet. |br|
Diese Methode sollte überschrieben werden, wenn eigene Initialisierungen vorgenommen werden müssen. Z. B. können hier
die ab JTL-Shop Version 5.0 notwendigen Sprachdateien des Plugins geladen werden, um eine saubere Trennung von Code und
Sprache zu ermöglichen.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function init(int $nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $pluginID = PluginHelper::getIDByModuleID($this->moduleID);
        $plugin   = PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID);
        Shop::Container()->getGetText()->loadPluginLocale(
            'simple_payment',
            $plugin
        );
        Shop::Smarty()->assign('pluginLocale', $plugin->getLocalization());

        return $this;
    }

.. _label_public-function-method-getOrderHash:

public function getOrderHash()
""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-getReturnURL:

public function getReturnURL()
""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-getNotificationURL:

public function getNotificationURL()
""""""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-updateNotificationID:

public function updateNotificationID()
""""""""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-getShopTitle:

public function getShopTitle()
""""""""""""""""""""""""""""""

Liefert den Namen des Onlineshops, der ggf. an einen Payment-Provider übergeben wird. In der Payment-Basisklasse wird
hier der Name des Onlineshops aus der Konfiguration ermittelt. Diese Methode muss normalerweise nicht überschrieben
werden.

.. _label_public-function-method-preparePaymentProcess:

public function preparePaymentProcess()
"""""""""""""""""""""""""""""""""""""""

Die Methode ``preparePaymentProcess`` wird durch den Bestellabschluss nach Finalisierung der Bestellung aufgerufen und
startet den Bezahlvorgang der Zahlungsmethode. |br|
Je nachdem, ob die Zahlungsmethode im Modus "Zahlung vor Bestellabschluss" oder "Zahlung nach Bestellabschluss"
ausgeführt wird, ist zum Zeitpunkt des Aufrufs die zugrundeliegende Bestellung bereits in der Tabelle ``tbestellung``
persistiert oder sie existiert nur innerhalb der aktiven Kundensession.

.. hint::

   Im Modus "Zahlung vor Bestellabschluss" muss diese Methode dafür sorgen, dass mittels Aufruf von
   ``/includes/modules/notify.php`` der Bestellabschluss ausgeführt und damit die Bestellung festgeschrieben wird.
   Die URL für diesen Aufruf kann über :ref:`label_public-function-method-getNotificationURL` ermittelt werden.

Die Payment-Basisklasse definiert diese Methode ohne Funktionalität, so dass diese in jedem Fall überschrieben werden
muss!

Beispiel für eine Implementation im Modus "Zahlung nach Bestellabschluss".

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function preparePaymentProcess($order): void
    {
        parent::preparePaymentProcess($order);

        $credentials     = Frontend::get(self::USERCREDENTIALS, []);
        $serviceProvider = new ServiceProvider($this->getSetting('prepaid_card_provider_url'));
        try {
            $payStatus = self::PAYSTATUS_FAILED;
            $payValue  = $order->fGesamtsumme;

            if ($payValue <= 0) {
                $this->setOrderStatusToPaid($order);

                return;
            }

            $hash    = $this->generateHash($order);
            $payment = $serviceProvider->payPrepaidTransaction(
               'PrepaidPayment: ' . $hash,
               $this->getSetting('prepaid_card_merchant_login'),
               $this->getSetting('prepaid_card_merchant_secret'),
               $credentials['token'],
               '',
               $payValue,
               $forcePay
            );

            $payStatus = $payment->payment_value >= $payValue
               ? self::PAYSTATUS_SUCCESS
               : self::PAYSTATUS_PARTIAL;

            if ($payStatus === self::PAYSTATUS_PARTIAL
               || $payStatus === self::PAYSTATUS_SUCCESS
            ) {
               $this->deletePaymentHash($hash);
               $this->addIncomingPayment($order, (object)[
                  'fBetrag'  => $payment->payment_value,
                  'cZahler'  => $credentials['name'],
                  'cHinweis' => $payment->payment_key,
               ]);
            }
            if ($payStatus === self::PAYSTATUS_SUCCESS) {
               $this->setOrderStatusToPaid($order);
            }
        } catch (ServiceProviderException $e) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                $e->getMessage(),
                'paymentFailed'
            );
        }
    }

.. _label_public-function-method-sendErrorMail:

public function sendErrorMail()
"""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-generateHash:

public function generateHash()
""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-deletePaymentHash:

public function deletePaymentHash()
"""""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-addIncomingPayment:

public function addIncomingPayment()
""""""""""""""""""""""""""""""""""""

Über ``addIncomingPayment`` wird ein Zahlungseingang angelegt. Die Methode der Payment-Basisklasse legt dazu in der
Tabelle ``tzahlungseingang`` einen entsprechenden Eintrag an. Diese Methode muss normalerweise nicht überschrieben
werden.

.. _label_public-function-method-setOrderStatusToPaid:

public function setOrderStatusToPaid()
""""""""""""""""""""""""""""""""""""""

Mit ``setOrderStatusToPaid`` wird die übergebene Bestellung in den Status "bezahlt" versetzt. Die Methode der
Payment-Basisklasse führt dazu ein Update der Tabelle ``tbestellung`` durch. Diese Methode muss normalerweise nicht
überschrieben werden.

.. _label_public-function-method-sendConfirmationMail:

public function sendConfirmationMail()
""""""""""""""""""""""""""""""""""""""

Ein Aufruf von ``sendConfirmationMail`` der Payment-Basisklasse versendet über die Methode
:ref:`sendMail <label_public-function-method-sendMail>` die Standard-E-Mail für "Bestellung bezahlt". Diese Methode
muss normalerweise nicht überschrieben werden.

.. _label_public-function-method-handleNotification:

public function handleNotification()
""""""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-finalizeOrder:

public function finalizeOrder()
"""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-redirectOnCancel:

public function redirectOnCancel()
""""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-redirectOnPaymentSuccess:

public function redirectOnPaymentSuccess()
""""""""""""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-doLog:

public function doLog()
"""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-getCustomerOrderCount:

public function getCustomerOrderCount()
"""""""""""""""""""""""""""""""""""""""

Mit dieser Methode der Payment-Basisklasse wird zu einem bestehenden Kunden die Anzahl an Bestellungen ermittelt, die
"in Bearbeitung", "bezahlt" oder "versandt" sind. Diese Methode muss normalerweise nicht überschrieben
werden.

.. _label_public-function-method-loadSettings:

public function loadSettings()
""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-getSetting:

public function getSetting()
""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-isValid:

public function isValid()
"""""""""""""""""""""""""

Diese Methode gibt die Validität der Zahlungsmethode im aktuellen Zahlvorgang - also abhängig von Kunde und / oder
Warenkorb - an. |br|
Bei Rückgabe von ``false`` wird die Zahlungsmethode im Bestellprozess nicht angeboten bzw. als ungültig
zurückgewiesen.  Der Rückgabewert ``true`` zeigt dagegen an, dass die Zahlungsart verwendet werden kann. |br|
In der Payment-Basisklasse wird hier das Ergebnis von :ref:`isValidIntern <label_public-function-method-isValidIntern>`
und zusätzlich die Erfüllung der Bedingungen für die Mindestanzahl an Bestellungen durch den Kunden sowie der
Mindestbestellwert im aktuellen Warenkorb geprüft. |br|
Diese Methode muss nur überschrieben werden, wenn eigene kunden- und warenkorbabhängige Bedingungen geprüft werden
müssen.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function isValid(object $customer, Cart $cart): bool
    {
        return parent::isValid($customer, $cart) && !$this->isBlacklisted($customer->cMail);
    }

.. _label_public-function-method-isValidIntern:

public function isValidIntern()
"""""""""""""""""""""""""""""""

Mit dieser Methode wird die grundsätzliche (interne) Validität der Zahlungsmethode geprüft. |br|
Ein Rückgabewert ``true`` signalisiert hierbei, dass die Zahlungsmethode gültig ist und verwendet werden kann.
Bei Rückgabe von ``false`` wird die Zahlungsmethode als ungültig angesehen und im Bestellprozess nicht zur Auswahl
angezeigt. |br|
Im Gegensatz zu :ref:`isValid <label_public-function-method-isValid>` erfolgt die Prüfung unabhängig vom
aktuellen Zahlvorgang. Die Implementation der Payment-Basisklasse liefert immer ``true``. Diese Methode muss also
überschrieben werden, wenn die Zahlungsmethode aufgrund "interner" Gründe wie fehlender oder fehlerhafter
Konfiguration nicht verfügbar ist.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function isValidIntern($args_arr = []): bool
    {
        if (empty($this->getSetting('postpaid_card_provider_url'))
            || empty($this->getSetting('postpaid_card_login_url'))
            || empty($this->getSetting('postpaid_card_merchant_login'))
            || empty($this->getSetting('postpaid_card_merchant_secret'))
        ) {
            $this->state = self::STATE_NOT_CONFIGURED;

            return false;
        }

        return parent::isValidIntern($args_arr);
    }

.. _label_public-function-method-isSelectable:

public function isSelectable()
""""""""""""""""""""""""""""""

Mit ``isSelectable`` steht eine Möglichkeit zur Verfügung, die Zahlungsmethode im Bestellprozess auszublenden. |br|
Im Unterschied zu :ref:`isValid <label_public-function-method-isValid>` und
:ref:`isValidIntern <label_public-function-method-isValidIntern>` wird diese Methode für reine Frontend-Bedingungen
genutzt. |br|
Dies ist z. B. dann der Fall, wenn eine grundsätzlich zulässige Zahlungsmethode nicht in der Liste zur Auswahl der
Versand- und Zahlungsart aufgeführt werden soll, weil diese nur für einen Expresskauf-Button oder für ein direktes
Bezahlen am Artikel oder aus dem Warenkorb heraus genutzt wird. |br|
In der Payment-Basisklasse liefert diese Methode immer das Ergebnis von
:ref:`isValid <label_public-function-method-isValid>`.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function isSelectable(): bool
    {
        return parent::isSelectable() && !$this->isExpressPaymentOnly();
    }

.. note::

    Die Methoden ``isValidIntern()``, ``isValid()`` und ``isSelectable()`` bedingen einander. Dabei hat
    ``isValidIntern()`` die höchste und ``isSelectable()`` die geringste Wertigkeit. Eine Zahlungsmethode, die über
    ``isValidIntern()`` ``false`` liefert, ist auch nicht valide und auch nicht auswählbar. Eine nicht auswählbare
    Zahlungsmethode kann aber durchaus valide sein. |br| Durch den Aufruf der geerbten Methoden aus der
    Payment-Basisklasse kann diese Abhängigkeit einfach sichergestellt werden.

.. _label_public-function-method-handleAdditional:

public function handleAdditional()
""""""""""""""""""""""""""""""""""

Wird im Bestellprozess aufgerufen, um zu prüfen, ob der Zusatzschritt im Bestellprozess angezeigt werden soll.
Ist der Zwischenschritt aus Plugin-Sicht notwendig, muss ``false`` zurückgegeben werden. |br|
Dies kann z. B. genutzt werden, um zusätzliche, für die Zahlungsart relevante Daten wie Kreditkartendaten vom Kunden
abzufragen.  Sind diese Daten z. B. bereits in der Kundensession vorhanden, kann der Schritt mit Rückgabe von ``true``
übersprungen werden. |br|
In der Payment-Basisklasse liefert diese Methode immer ``true`` und muss deshalb nur überschrieben werden, wenn ein
eigener Zwischenschritt (siehe: :ref:`<AdditionalTemplateFile> <label_AdditionalTemplateFile>`) vorhanden ist.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function handleAdditional($post): bool
    {
        $credentials = Frontend::get(self::USERCREDENTIALS, []);

        if (empty($credentials['name']) || empty($credentials['token'])) {
            Shop::Smarty()
                ->assign('credentials_loginName', empty($credentials['name'])
                    ? Frontend::getCustomer()->cMail
                    : $credentials['name'])
                ->assign('credentials_secret', '')
                ->assign('additionalNeeded', true);

            return false;
        }

        return parent::handleAdditional($post);
    }

.. _label_public-function-method-validateAdditional:

public function validateAdditional()
""""""""""""""""""""""""""""""""""""

Diese Methode wird im Bestellprozess aufgerufen und entscheidet im Zusammenspiel mit
:ref:`handleAdditional <label_public-function-method-handleAdditional>`, ob das Zusatzschritt-Template
(siehe: :ref:`<AdditionalTemplateFile> <label_AdditionalTemplateFile>`) nach der Auswahl der Zahlungsart angezeigt
werden muss. Können die Daten aus dem Zwischenschritt nicht validiert werden, wird ``false`` zurückgegeben,
ansonsten ``true``.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function validateAdditional(): bool
    {
        $credentials     = Frontend::get(self::USERCREDENTIALS, []);
        $postCredentials = Request::postVar('credentials', []);

        if (Request::getInt('editZahlungsart') > 0 || Request::getInt('editVersandart') > 0) {
            $this->resetToken();

            return false;
        }

        if (isset($postCredentials['post'])) {
            if (!Form::validateToken()) {
                Shop::Container()->getAlertService()->addAlert(
                    Alert::TYPE_ERROR,
                    Shop::Lang()->get('invalidToken'),
                    'invalidToken'
                );

                return false;
            }

            $secret               = StringHandler::filterXSS($postCredentials['secret']);
            $credentials['name']  = StringHandler::filterXSS($postCredentials['loginName']);
            $credentials['token'] = $this->validateCredentials($credentials['name'], $secret);

            Frontend::set(self::USERCREDENTIALS, $credentials);

            return !empty($credentials['token']);
        }

        if (!empty($credentials['token'])) {
            return parent::validateAdditional();
        }

        return false;
    }

.. _label_public-function-method-addCache:

public function addCache()
""""""""""""""""""""""""""

Mit ``addCache`` wird ein Key-Value-Pair zwischengespeichert. Die Payment-Basisklasse benutzt für die Methoden
:ref:`addCache <label_public-function-method-addCache>`, :ref:`unsetCache <label_public-function-method-unsetCache>`
und :ref:`getCache <label_public-function-method-getCache>` die aktuelle Kunden-Session als Zwischenspeicher. |br|
Diese Methode muss überschrieben werden, wenn eine andere Cache-Methode verwendet werden soll.

.. _label_public-function-method-unsetCache:

public function unsetCache()
""""""""""""""""""""""""""""

Mit ``unsetCache`` wird ein Key-Value-Pair aus dem Zwischenspeicher entfernt. Die Payment-Basisklasse benutzt für die
Methoden :ref:`addCache <label_public-function-method-addCache>`,
:ref:`unsetCache <label_public-function-method-unsetCache>` und :ref:`getCache <label_public-function-method-getCache>`
die aktuelle Kunden-Session als Zwischenspeicher. |br|
Diese Methode muss überschrieben werden, wenn eine andere Cache-Methode verwendet werden soll.

.. _label_public-function-method-getCache:

public function getCache()
""""""""""""""""""""""""""

Mit ``getCache`` wird ein Key-Value-Pair aus dem Zwischenspeicher gelesen. Die Payment-Basisklasse benutzt für die
Methoden :ref:`addCache <label_public-function-method-addCache>`,
:ref:`unsetCache <label_public-function-method-unsetCache>` und :ref:`getCache <label_public-function-method-getCache>`
die aktuelle Kunden-Session als Zwischenspeicher. |br|
Diese Methode muss überschrieben werden, wenn eine andere Cache-Methode verwendet werden soll.

.. _label_public-function-method-createInvoice:

public function createInvoice()
"""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-reactivateOrder:

public function reactivateOrder()
"""""""""""""""""""""""""""""""""

(Beschreibung folgt)

.. _label_public-function-method-cancelOrder:

public function cancelOrder()
"""""""""""""""""""""""""""""

Diese Methode wird vom JTL-Shop-Core bei der Synchronisation mit JTL-Wawi aufgerufen, wenn eine Bestellung storniert
wurde. Die Payment-Basisklasse setzt den Status der zugeordneten Bestellung auf "storniert" und versendet über
:ref:`sendMail <label_public-function-method-sendMail>` die "Bestellung storniert"-E-Mail. |br|
Diese Methode muss überschrieben werden, wenn weitergehende Operationen notwendig sind. Das kann z. B. die Stornierung
der Zahlung beim Payment-Provider sein.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function cancelOrder(int $orderID, bool $delete = false): bool
    {
        parent::cancelOrder($orderID, $delete);

        $serviceProvider = new ServiceProvider($this->getSetting('prepaid_card_provider_url'));
        try {
            $payment = Shop::Container()->getDB()->queryPrepared(
                'SELECT cHinweis
                    FROM tzahlungseingang
                    WHERE kBestellung = :orderID',
                [
                    'orderID' => (int)$order->kBestellung
                ],
                ReturnType::SINGLE_OBJECT
            );
            if ($payment && !empty($payment->cHinweis)) {
                $serviceProvider->cancelPayment($payment->cHinweis);
            }
        } catch (ServiceProviderException $e) {
            $this->doLog($e->getMessage(), \LOGLEVEL_ERROR);
        }
    }

.. _label_public-function-method-canPayAgain:

public function canPayAgain()
"""""""""""""""""""""""""""""

Hier wird festgelegt, ob die Bezahlung über das Plugin erneut gestartet werden kann. Gibt diese Methode ``true``
zurück, dann wird bei einer unbezahlten Bestellung im Kundenaccount ein "Jetzt bezahlen"-Link angezeigt. Wird dieser
Link angeklickt, dann wird der Bezahlvorgang neu gestartet. Die :ref:`Init-Methode <label_public-function-method-init>`
für die Zahlungsmethode wird dann mit dem Parameter ``$nAgainCheckout = 1`` aufgerufen. |br|
Die Methode der Payment-Basisklasse liefert immer ``false`` und muss überschrieben werden, wenn die Zahlungsmethode
einen erneuten Zahlungsvorgang unterstützt.

.. _label_public-function-method-sendMail:

public function sendMail()
""""""""""""""""""""""""""

Die ``sendMail``-Methode der Payment-Basisklasse unterstützt die E-Mail-Templates für "Bestellbestätigung",
"Bestellung teilversandt", "Bestellung aktualisiert", "Bestellung versandt", "Bestellung bezahlt",
"Bestellung storniert" und "Bestellung reaktiviert" mit dem ``$type``-Parameter. Für die unterstützten Templates
werden die notwendigen Daten ermittelt und die jeweilige E-Mail versendet. |br|
Diese Methode muss überschrieben werden, wenn weitere oder eigene E-Mail-Templates unterstützt werden sollen.

.. code-block:: php

    /**
     * @inheritDoc
     */
    public function sendMail(int $orderID, string $type, $additional = null)
    {
        $order = new Bestellung($orderID);
        $order->fuelleBestellung(false);
        $mailer = Shop::Container()->get(Mailer::class);

        switch ($type) {
            case self::MAILTEMPLATE_PAYMENTCANCEL:
                $data = (object)[
                    'tkunde'      => new Customer($order->kKunde),
                    'tbestellung' => $order,
                ];
                if ($data->tkunde->cMail !== '') {
                    $mailer->getHydrator()->add('Bestellung', $order);
                    $mailer->send((new Mail())->createFromTemplateID(\sprintf($type, $this->plugin->getID()), $data));
                }
                break;
            default:
                return parent::sendMail($orderID, $type, $additional);
        }

        return $this;
    }


Template-Selectoren (JTL PayPal Checkout)
-----------------------------------------

Nachfolgende Selectoren werden im "*JTL PayPal Checkout*"-Plugin verwendet. |br|
Bitte stellen Sie sicher, dass diese Selektoren im Template enthalten sind und die adäquaten Bereiche
wie im NOVA-Template referenzieren, um eine korrekte Funktion des "JTL PayPal Checkout"-Plugins zu gewährleisten.

Selectoren in der: **CheckoutPage.php** (phpQuery)

.. code-block:: php

    - \*_phpqSelector
    - #complete-order-button
    - body
    - .checkout-payment-method
    - .checkout-shipping-form
    - #fieldset-payment
    - #result-wrapper
    - meta[itemprop="price"]



Selectoren in der: **CheckoutPage.php** (phpQuery)


.. code-block:: php

    - #miniCart-ppc-paypal-standalone-button
    - #cart-ppc-paypal-standalone-button
    - #\*-ppc-\*-standalone-button
    - #productDetails-ppc-paypal-standalone-button
    - #cart-checkout-btn
    - #add-to-cart button[name="inWarenkorb"]
    - meta[itemprop="price"]
    - #buy_form
    - #complete-order-button
    - #paypal-button-container
    - #complete_order
    - #comment
    - #comment-hidden
    - form#complete_order
    - .checkout-payment-method
    - #za_ppc_\*_input
    - input[type=radio][name=Zahlungsart]
    - #fieldset-payment .jtl-spinner

