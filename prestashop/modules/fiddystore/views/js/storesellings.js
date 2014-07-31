var CONTROLLER = "AdminStoresellings";
var URL = currentIndex + "&token=" + token;
var ENTER_KEYCODE = 13;

var invokeSearchProductsRequest = function(event) {
	if(event.which === ENTER_KEYCODE) {
		event.preventDefault();
    	searchProducts(event);
    }
};

var calculateDiscount = function(event) {
	var discountValue = $('input[name=discount]').attr('value'),
		total_price;

	if(event.which === ENTER_KEYCODE) {
		event.preventDefault();
		
		setDiscount();
	}
};

var searchProducts = function(event) {
	var barcodeValue = $('input[name=barcode]').attr('value'), 
		attributes_html, 
		data;
	
	$('input[name=barcode]').attr('value', '');
	
	event.preventDefault();
	
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "addProduct",
			barcode: barcodeValue
		},
		type: 'POST',
		success : function(jsonData) {
			ajaxSuccess(jsonData);
			
			data = JSON.parse(jsonData);
			
			attributes_html = '<select class="id_product_attribute" id="ipa_' + data.product.id_product + '" name="ipa_' + data.product.id_product + '">';
			$.each(data.product.combinations, function() {
					attributes_html += '<option ' + (this.default_on == 1 ? 'selected="selected"' : '') + ' value="' + this.id_product_attribute + '">' + this.attributes + ' - ' + this.formatted_price + '</option>';
					});
			attributes_html += '</select>';
			
			$('.variante').html(attributes_html);
		}
	});
};

var sell = function() {
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "test"
		},
		type: 'POST',
		success : function(jsonData){
			location.reload();
		}	
	});
};

var renderProducts = function(products) {
	var tbody = $('#productList tbody'),
		i, 
		product,
		row;
		
	tbody.html('');
	for(i = 0; i < products.length; i++) {
		product = products[i];
		
		row = "<tr id='product_" + product.id_product + "'><td>" + product.id_product + "</td><td><span class='badge'>" + product.cart_quantity + "</span></td><td>" + product.ean13 + "</td><td>" + product.name + "</td><td>" + formatCurrency(parseFloat(product.total_wt), currency_format, currency_sign, currency_blank)
 + "</td><td><button type='button' class='btn btn-default' onclick='javascript:decreaseQuantity(\"" + product.id_product + "\")'>-</button>&nbsp;<button type='button' class='btn btn-default' onclick='javascript:increaseQuantity(\"" + product.id_product + "\")'>+</button></td></tr>";
		
		tbody.append(row);
	}
};

var renderTotalPrice = function(total_price) {
	var priceElement = $('#total');
	
	priceElement.html('<p>' + formatCurrency(parseFloat(total_price), currency_format, currency_sign, currency_blank) + '</p>');	
};


var renderDiscounts = function(discounts) {
	var tbody = $('#discountsList tbody'),
		i, 
		discount,
		row;
		
	tbody.html('');
	for(i = 0; i < discounts.length; i++) {
		discount = discounts[i];
		
		row = "<tr id='discount_" + discount.id_cart_rule + "'><td>" + discount.name + "</td><td>" + formatCurrency(parseFloat(discount.reduction_amount), currency_format, currency_sign, currency_blank) + "</td><td><button type='button' class='btn btn-default' onclick='javascript:removeDiscount(\"" + discount.id_cart_rule + "\")'>Entfernen</button></td></tr>";
		
		tbody.append(row);
	}
};

var increaseQuantity = function(id_product) {
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "increaseQuantity",
			id_product: id_product
		},
		type: 'POST',
		success : ajaxSuccess	
	});
};

var decreaseQuantity = function(id_product) {
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "decreaseQuantity",
			id_product: id_product
		},
		type: 'POST',
		success : ajaxSuccess	
	});
};

var setDiscount = function() {
	var discount = $('#discount').val();
	
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "setDiscount",
			discount: discount
		},
		type: 'POST',
		success : ajaxSuccess	
	});
};

var removeDiscount = function(id_cart_rule) {
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "removeDiscount",
			id_cart_rule: id_cart_rule
		},
		type: 'POST',
		success : ajaxSuccess	
	});
};

var ajaxSuccess = function(jsonData) {
	var data, products;
	
	data = JSON.parse(jsonData);
	if(data.products) {
		products = data.products;
		renderProducts(products);
		renderTotalPrice(data.total_price);
		renderDiscounts(data.discounts);
	}
};

$(document).ready( function () {
	$('#searchProductsButton').click(searchProducts);
	$('input[name=barcode]').on('keydown', invokeSearchProductsRequest);
	$('input[name=discount]').on('keydown', calculateDiscount);
	$('#closeCart').click(sell);
});
