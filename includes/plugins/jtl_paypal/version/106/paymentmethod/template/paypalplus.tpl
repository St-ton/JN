{if $error}
	<p class="alert alert-danger">{$error}</p>
	<a href="{get_static_route id='bestellvorgang.php'}?editZahlungsart=1" class="btn btn-primary btn-lg pull-right submit submit_once">
		{lang key="modifyPaymentOption" section="checkout"}
	</a>
{/if}
