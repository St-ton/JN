{include file='tpl_inc/seite_header.tpl' cTitel=__('preview') cBeschreibung=__('newsletterdesc')}
<div id="content">
    <form method="post" action="newsletter.php">
        {$jtl_token}
        <input name="tab" type="hidden" value="newslettervorlagen" />
        <table class="table newsletter">
            <tr>
                <td><b>{__('subject')}</b>:</td>
                <td>{$oNewsletterVorlage->cBetreff}</td>
            </tr>

            <tr>
                <td style="vertical-align: middle;"><b>{__('newsletterdraftdate')}</b>:</td>
                <td>{$oNewsletterVorlage->Datum}</td>
            </tr>
        </table>

        <h3>{__('newsletterHtml')}:</h3>
        <div style="text-align: center;">
            <iframe src="{$cURL}" width="100%" height="500"></iframe>
        </div>
        <br />

        <h3>{__('newsletterText')}:</h3>
        <div style="text-align: center;">
            <textarea class="form-control" style="width: 100%; height: 300px;" readonly>{$oNewsletterVorlage->cInhaltText}</textarea></div>
        <br />
        <button class="btn btn-primary" name="back" type="submit" value="{__('back')}">{__('goBack')}</button>
    </form>
</div>
