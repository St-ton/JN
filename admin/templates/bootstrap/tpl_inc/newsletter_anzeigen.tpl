<div id="page">
    <div id="content" class="container-fluid">
        <form method="post" action="newsletter.php">
            {$jtl_token}
            <div id="welcome" class="post">
                <h2 class="title"><span>{__('newsletterhistory')}</span></h2>

                <div class="content">
                    <p>{__('newsletterdesc')}</p>
                </div>
            </div>
            <table class="newsletter table">
                <tr>
                    <td><strong>{__('newsletterdraftsubject')}</strong>:</td>
                    <td>{$oNewsletterHistory->cBetreff}</td>
                </tr>
                <tr>
                    <td><strong>{__('newsletterdraftdate')}</strong>:</td>
                    <td>{$oNewsletterHistory->Datum}</td>
                </tr>
            </table>
            <h3>{__('newsletterHtml')}:</h3>
            <p>{$oNewsletterHistory->cHTMLStatic}</p>
            <p class="submit-wrapper">
                <button class="btn btn-primary" name="back" type="submit" value="{__('newsletterback')}"><i class="fa fa-angle-double-left"></i> {__('newsletterback')}</button>
            </p>
        </form>
    </div>
</div>