
<div class="panel">	
	<div class="form-group">
		<div class="col-lg-3">
			<labelfor="product">Produkt</label>
		</div>
		<div class="col-lg-9">
			<input type="text" name="barcode" />
		</div>
	</div>
	
	<!--<button class="btn btn-primary" name="search_product" id="searchProductsButton">Produkt suchen</button>-->

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
			</tbody>
			<tfoot>
				<tr class="info">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
<div class="panel">
	<div class="row top-spacer">
		<div class="form-group">
			<div class="col-lg-9">
				<labelfor="discount">Rabatt</label>
			</div>
			<div class="col-lg-3">
				<input type="text" name="discount" id="discount" />
			</div>
		</div>
	</div>
	
	<div class="row top-spacer">
		<div class="col-lg-9">
			<p>Total</p>
		</div>
		<div class="col-lg-3" id="total">
			<p>0</p>
		</div>
	</div>
	
	
	<div class="row top-spacer">
		<div class="col-lg-offset-9 col-lg-3">
			<button class="btn btn-success">Kauf abschliessen</button>
		</div>
	</div>

</div>