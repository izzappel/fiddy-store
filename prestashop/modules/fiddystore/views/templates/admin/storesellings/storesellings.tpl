<script>
	var currency_format = {$currency->format|intval};
	var currency_sign = '{$currency->sign|addslashes}';
	var currency_blank = {$currency->blank|intval};
	var priceDisplayPrecision = 2;
</script>

<div class="panel">	
	<div class="form-group">
		<div class="col-lg-3">
			<label for="product">Produkt</label>
		</div>
		<div class="col-lg-9">
			<input type="text" name="barcode" />
		</div>
	</div>
	
	<div class="form-group">
		<div class="col-lg-3">
			<label for="product">Variante</label>
		</div>
		<div class="col-lg-9 variante">
			
		</div>
	</div>
	
	
	
	<div id="productList">	
		<table class="table">
			<thead>
				<tr>
					<th>Id</th>
					<th>Anzahl</th>
					<th>Barcode</th>
					<th>Produkt</th>
					<th>Preis</th>
					<th>+/-</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$products item=p}
			    <tr>
			    	<td>{$p['id_product']}</td>
					<td><span class="badge">{$p['cart_quantity']}</span></td>
					<td>{$p['ean13']}</td>
					<td>{$p['name']}</td>
					<td>{displayPrice price=$p['total_wt'] currency=$currency->id}</td>
					<td>
						<button type='button' class='btn btn-default' onclick='javascript:decreaseQuantity({$p.id_product})'>-</button>&nbsp;
						<button type='button' class='btn btn-default' onclick='javascript:increaseQuantity({$p.id_product})'>+</button>
					</td>
			    </tr>
			    		
			{/foreach}
			</tbody>
		</table>
	</div>
</div>
<div class="panel">
	<div class="row top-spacer">
		<div class="form-group">
			<div class="col-lg-3">
				<label for="discount">Rabatt</label>
			</div>
			<div class="col-lg-9">
				<input type="text" name="discount" id="discount" />
			</div>
		</div>
	</div>
	
	<div id="discountsList">	
		<table class="table">
			<thead>
				<tr>
					<th>Bezeichnung</th>
					<th>Wert</th>
					<th>Entfernen</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$discounts item=d}
			    <tr>
			    	<td>{$d['name']}</td>
					<td>{displayPrice price=$d['reduction_amount'] currency=$currency->id}</td>
					<td>
						<button type='button' class='btn btn-default' onclick='javascript:removeDiscount({$d['id_cart_rule']})'>Entfernen</button>
					</td>
			    </tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>
<div class="panel">
	<div class="row top-spacer">
		<div class="col-lg-9">
			<p>Total</p>
		</div>
		<div class="col-lg-3" id="total">
			<p><span class='label label-success'>{displayPrice price=$total_price currency=$currency->id}</span></p>
		</div>
	</div>
	
	
	<div class="row top-spacer">
		<div class="col-lg-offset-9 col-lg-3">
			<button class="btn btn-success btn-lg" id="closeCart">Kauf abschliessen</button>
		</div>
	</div>

</div>