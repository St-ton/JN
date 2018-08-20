<?php
/**
 * remove old payment methods
 *
 * @author fm
 * @created Mon, 20 Aug 2018 12:50:00 +0200
 */

/**
 * Class Migration_20180820125000
 */
class Migration_20180820125000 extends Migration implements IMigration
{
    protected $author = 'fm';
    protected $description = 'Remove old payment methods';

    public function up()
    {
        $this->execute("DELETE FROM tzahlungsart WHERE cModulId IN (
            'za_paypal_jtl', 
            'za_worldpay_jtl',
            'za_ipayment_jtl',
            'za_safetypay',
            'za_paymentpartner_jtl',
            'za_postfinance_jtl',
            'za_saferpay_jtl',
            'za_iloxx_jtl',
            'za_iclear_jtl'
            'za_wirecard_jtl'
        )");
        $this->execute("DELETE FROM teinstellungenconf WHERE cModulId IN (
            'za_paypal_jtl', 
            'za_worldpay_jtl',
            'za_ipayment_jtl',
            'za_safetypay',
            'za_paymentpartner_jtl',
            'za_postfinance_jtl',
            'za_saferpay_jtl',
            'za_iloxx_jtl',
            'za_iclear_jtl',
            'za_wirecard_jtl'
        ) OR cModulId LIKE 'za_ut_%' OR cModulId LIKE 'za_uos_%'");
        $this->execute("DELETE FROM teinstellungen WHERE cModulId IN (
            'za_paypal_jtl', 
            'za_worldpay_jtl',
            'za_ipayment_jtl',
            'za_safetypay',
            'za_paymentpartner_jtl',
            'za_postfinance_jtl',
            'za_saferpay_jtl',
            'za_iloxx_jtl',
            'za_iclear_jtl',
            'za_wirecard_jtl'
        ) OR cModulId LIKE 'za_ut_%' OR cModulId LIKE 'za_uos_%'");
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf NOT IN (SELECT kEinstellungenConf FROM teinstellungenconf)");
        $this->removeLocalization('ipaymentDesc');
        $this->removeLocalization('payWithIpayment');
        $this->removeLocalization('payWithWorldpay');
        $this->removeLocalization('worldpayDesc');
        $this->removeLocalization('payWithPaymentPartner');
        $this->removeLocalization('payWithWirecard');
        $this->removeLocalization('iloxxDesc');
        $this->removeLocalization('payWithIclear');
        $this->removeLocalization('iclearError');
    }

    public function down()
    {
        $this->execute("
        INSERT INTO `tzahlungsart`
        (`cName`, `cModulId`, `cKundengruppen`, `cZusatzschrittTemplate`, `cPluginTemplate`, `cBild`, `nSort`, `nMailSenden`, `nActive`, `cAnbieter`, `cTSCode`, `nWaehrendBestellung`, `nCURL`, `nSOAP`, `nSOCKETS`, `nNutzbar`)
        VALUES 
        ('PayPal','za_paypal_jtl','','',NULL,'',0,1,1,'PayPal','PAYPAL',0,0,0,1,1),
        ('WorldPay','za_worldpay_jtl',NULL,'',NULL,'',0,1,1,'RBS','OTHER',0,0,0,0,1),
        ('iPayment','za_ipayment_jtl',NULL,'',NULL,'',0,1,1,'1&1','OTHER',0,0,0,0,1),
        ('Safetypay','za_safetypay','','',NULL,'',0,1,1,'Safetypay','OTHER',0,0,1,0,1),
        ('PaymentPartner','za_paymentpartner_jtl','','',NULL,'',0,0,1,'PaymentPartner','OTHER',0,0,0,1,1),
        ('PostFinance','za_postfinance_jtl','','',NULL,'',0,0,1,'PostFinance','OTHER',0,0,0,0,1),
        ('Saferpay','za_saferpay_jtl','','',NULL,'',0,0,0,'SIX Card Solutions','OTHER',0,0,0,1,0),
        ('Wirecard','za_wirecard_jtl','','',NULL,'',0,0,1,'Wirecard','OTHER',0,0,0,0,1);
        ");
    }
}