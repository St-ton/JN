<?php

/**
 * Class Migration_20200508095000
 */
class Migration_20200508095000 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Update data protection lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'newsletter', 'unsubscribeAnytime', 'Bitte senden Sie mir entsprechend Ihrer ' .
            '<a href="%s" target="_blank">Datenschutzerklärung</a> regelmäßig [ALTERNATIV: alle [X] Wochen] und jederzeit ' .
            'widerruflich Informationen zu folgendem Produktsortiment per E-Mail zu: [AUFZÄHLUNG DER VON IHNEN VERTRIEBENEN WARENGRUPPEN].');
        $this->setLocalization('eng', 'newsletter', 'unsubscribeAnytime', 'Please email me the latest information on ' .
            'the product ranges listed below in regular intervals [ALTERNATIVELY: EVERY [X] WEEKS] in accordance with ' .
            'your <a href="%s" target="_blank">Privacy notice</a>. I recognise that I can revoke my permission to ' .
            'receive said emails at any time. [ADD LIST OF PRODUCT GROUPS SOLD BY YOU]');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->setLocalization('ger', 'newsletter', 'unsubscribeAnytime', 'Abonnieren Sie jetzt den Newsletter und verpassen Sie keine Angebote. Die Abmeldung ist jederzeit möglich.');
        $this->setLocalization('eng', 'newsletter', 'unsubscribeAnytime', 'Subscribe to the newsletter now and never miss the latest offers again! You can unsubscribe at any time.');
    }
}
