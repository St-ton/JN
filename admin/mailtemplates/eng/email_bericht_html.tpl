{includeMailTemplate template=header type=html}

<font color="#313131" face="Helvetica, Arial, sans-serif" size="4" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 18px; line-height: 22px;">
	<strong>{$oMailObjekt->cIntervall}</strong>
</font><br>
<br>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
	{if $oMailObjekt->oAnzahlArtikelProKundengruppe != -1}
		<tr>
			<td colspan="2" class="column mobile-left" align="left" valign="top" style="border-bottom: 1px dotted #929292;">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Products per customer group:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		{foreach name=artikelprokgr from=$oMailObjekt->oAnzahlArtikelProKundengruppe item=oArtikelProKundengruppe}
			<tr>
				<td class="column mobile-left" width="25%" align="right" valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td align="right" valign="top">
								<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
									<strong>{$oArtikelProKundengruppe->cName}:</strong>
								</font>
							</td>
						</tr>
					</table>
				</td>
				<td class="column mobile-left" align="left" valign="top">
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td align="left" valign="top">
								<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
									{$oArtikelProKundengruppe->nAnzahl}
								</font>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		{/foreach}
	{/if}
	{if $oMailObjekt->nAnzahlNeukunden != -1}
		<tr style="border-top: 1px dotted #929292;">
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>New customer:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlNeukunden}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlNeukundenGekauft != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>New customers who purchased:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlNeukundenGekauft}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlBestellungen != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Orders:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlVersendeterBestellungen}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlBesucher != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Visitors:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlBesucherSuchmaschine}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlBesucherSuchmaschine != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Visitors from search engines:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlBesucherSuchmaschine}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlBewertungen != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Ratings:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlBewertungen}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Non-public ratings:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->oAnzahlGezahltesGuthaben != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Rating credit paid:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Rating credit total:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlTags != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Tags:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlTags}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlTagsNichtFreigeschaltet != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Tags not approved:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlTagsNichtFreigeschaltet}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlGeworbenerKunden != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Acquired customers:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlGeworbenerKunden}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlErfolgreichGeworbenerKunden != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Acquired customers who purchased:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlErfolgreichGeworbenerKunden}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlVersendeterWunschlisten != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Wish lists sent:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlVersendeterWunschlisten}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlDurchgefuehrteUmfragen != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Surveys conducted:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlDurchgefuehrteUmfragen}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlNewskommentare != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>New article comments:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlNewskommentare}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Article comments not published:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlProduktanfrageArtikel != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>New product questions:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlProduktanfrageArtikel}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>New availability questions:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlVergleiche != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Product comparisons:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlVergleiche}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
	{if $oMailObjekt->nAnzahlGenutzteKupons != -1}
		<tr>
			<td class="column mobile-left" width="25%" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								<strong>Coupons used:</strong>
							</font>
						</td>
					</tr>
				</table>
			</td>
			<td class="column mobile-left" align="left" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
								{$oMailObjekt->nAnzahlGenutzteKupons}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	{/if}
</table>

{includeMailTemplate template=footer type=html}