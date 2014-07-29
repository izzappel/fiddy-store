(function(global) {
	var products = [];
	
	var addProduct = function(product) {
		var i;
		
		for(i = 0; i < products.length; i++) {
			if(products[i].id_product === product.id_product) {
				products[i].amount++;
				return products[i];
			}
		}
		
		product.amount = 1;
		product.guid = generateUUID();
		products.push(product);
		return product;
	}
	
	var removeProduct = function(guid) {
		var i;
		
		for(i = 0; i < products.length; i++) {
			if(products[i].guid === guid) {
				products[i].amount--;
				if(products[i].amount === 0) {
					products.splice(i, 1);
					return null;
				}
				return products[i];
			}
		}
		
		return null;
	}
	
	var getProductByGuid = function(guid) {
		var i;
		
		for(i = 0; i < products.length; i++) {
			if(products[i].guid === guid) {
				return products[i];
			}
		}
		
		return null;

	}
	
	var getAmount = function() {
		var i, amount = 0;
		
		for(i = 0; i < products.length; i++) {
			amount += products[i].amount;
		}
		
		return amount;
	}
	
	var generateUUID = function() {
    	var d = new Date().getTime();
	    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
	        var r = (d + Math.random()*16)%16 | 0;
	        d = Math.floor(d/16);
	        return (c=='x' ? r : (r&0x7|0x8)).toString(16);
	    });
	    return uuid;
	};
	
	global.storesellings = global.storeselligs || {};
	global.storesellings.addProduct = addProduct;
	global.storesellings.removeProduct = removeProduct;
	global.storesellings.getProductByGuid = getProductByGuid; 
	global.storesellings.products = products;
	global.storesellings.getAmount = getAmount;
	
})(window);

var CONTROLLER = "StoreSellings";
var URL = currentIndex + "&token=" + token;

var searchProducts = function(event) {
	var barcodeValue = $('input[name=barcode]').attr('value');
	
	event.preventDefault();
		$.ajax({
			url : URL,
			data : {
				ajax : "1",
				controller : CONTROLLER,
				action : "searchProducts",
				barcode: barcodeValue
			},
			type: 'POST',
			success : function(jsonData){
				var data, product, row;
				
				data = JSON.parse(jsonData);
				if(data.product) {
					product = data.product;
					
					addProduct(product);
				}
			}	
		});
};

var getTotalPrice = function() {
	$.ajax({
		url : URL,
		data : {
			ajax : "1",
			controller : CONTROLLER,
			action : "getPriceForProducts",
			products: storesellings.products
		},
		type: 'POST',
		success : function(jsonData){
			var data, total_price, row;
			
			data = JSON.parse(jsonData);
			$('#productList .info').html('<td>&nbsp;</td><td>' + storesellings.getAmount() + '</td><td>&nbsp;</td><td>&nbsp;</td><td>' + data.total_price + '</td><td>&nbsp;</td>');
		}	
	});
};

var addProduct = function(product) {
	product = storesellings.addProduct(product);
					
	if($('#product_' + product.guid).length <= 0) {
		row = "<tr id='product_" + product.guid + "'><td>" + product.id_product + "</td><td class='amount'> " + product.amount + "</td><td>" + product.ean13 + "</td><td>" + product.name + "</td><td>" + product.price_tax_incl + "</td><td><button type='button' class='btn btn-default' onclick='javascript:removeProduct(\"" + product.guid + "\")'>-</button>&nbsp;<button type='button' class='btn btn-default' onclick='javascript:multiplyProduct(\"" + product.guid + "\")'>+</button></tr>";
		
		$('#productList tbody').append(row);
	} else {
		row = $('#product_' + product.guid);
		$('.amount', row).text(product.amount);
	}
	
	getTotalPrice();
};


var multiplyProduct = function(guid) {
	var product = storesellings.getProductByGuid(guid);
	if(product !== null) {
		addProduct(product);
	} else {
		console.log("product " + guid + " not found");
	}
};

var removeProduct = function(guid) {
	var product = storesellings.removeProduct(guid);
	if(product !== null) {
		row = $('#product_' + product.guid);
		$('.amount', row).text(product.amount);
	} else {
		$('#product_' + guid).remove();
	}
	
	getTotalPrice();
};

$(document).ready( function () {
	$('#searchProductsButton').click(searchProducts);
});
