<form>
<div class="panel">	
	<div class="form-group">
		<div class="col-lg-3">
			<labelfor="product">Produkt</label>
		</div>
		<div class="col-lg-9">
			<input type="text" name="barcode" />
		</div>
	</div>
	
	<button class="btn btn-primary" name="search_product" id="searchProductsButton">Produkt suchen</button>
</div>
</form>

<div class="panel" id="productList">	
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
				<td>3</td>
				<td>&nbsp;</td>
				<td>15.50</td>
				<td>&nbsp;</td>
			</tr>
		</tfoot>
	</table>
</div>