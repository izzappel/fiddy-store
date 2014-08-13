<?php
if(!defined('_PS_VERSION_'))
	exit;
	
class AdminStoresellingsController extends ModuleAdminController 
{
	public function __construct()    
    {          
         
		$this->action = 'view';
		$this->display = 'view';
		$this->bootstrap = true;
		//$this->ajax = true;
		
		$this->meta_title = $this->l('Ladenverkauf');
		
        parent::__construct();  
    }
    
    public function setMedia()
	{	
		parent::setMedia();
		
		$this->addJqueryPlugin(array('autocomplete', 'typewatch'));
		
		$this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/storesellings.js');
		$this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/storesellings.css');	}

	public function initContent()
    {
        $this->initTabModuleList();
		$this->addToolBarModulesListButton();
		$this->toolbar_title = $this->l('Fiddy Store Ladenverkauf');
		$this->initPageHeaderToolbar();
		if ($this->display == 'view')
		{
			// Some controllers use the view action without an object
			if ($this->className)
				$this->loadObject(true);
			$this->content .= $this->renderView();
		}
		
		$this->content .= $this->displayFiddyStore();


		$this->context->smarty->assign(array(
			'content' => $this->content,
			'url_post' => self::$currentIndex.'&token='.$this->token,			
			'show_page_header_toolbar' => $this->show_page_header_toolbar,
			'page_header_toolbar_title' => $this->page_header_toolbar_title,
			'page_header_toolbar_btn' => $this->page_header_toolbar_btn
		));
	}

	public function initPageHeaderToolbar()
	{
		parent::initPageHeaderToolbar();
		unset($this->page_header_toolbar_btn['back']);
	}

	public function displayFiddyStore()
	{
		$tpl = $this->createTemplate('storesellings.tpl');

		$return = $this->createReturnValue();
		$return['currency'] = $this->context->currency;
		$return['order_states'] = $this->getOrderStates();
		$return['order_state'] = Configuration::get("FIDDYSTORE_ORDER_STATE");
		$tpl->assign($return);
		
		return $tpl->fetch();
	}
	
	public function ajaxProcessSearchCustomers() 
	{
		$customerSearchString = Tools::getValue('customer_search');
		$customers = $this->getCustomersBySearchString($customerSearchString);
		
		$return = array(
			'customers' => $customers
		);
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessSetCustomer() {
		$customer = Tools::getValue('customer');
		Configuration::updateValue('FIDDYSTORE_CUSTOMER', $customer['id_customer']);
	}
	
	private function getCustomersBySearchString($customerSearchString) 
	{
		$customers = Customer::searchByName(pSQL($customerSearchString));
		return $customers;
	}
	
	public function ajaxProcessSetOrderState() {
		$id_order_state = Tools::getValue('id_order_state');
		Configuration::updateValue('FIDDYSTORE_ORDER_STATE', $id_order_state);
	}
	
	public function ajaxProcessAddProduct()
	{
		$barcode = Tools::getValue('barcode');
		$product = $this->getProductByBarcode($barcode);
		$added = false;
		
		if(isset($product) && (isset($product['selected_combination']) || $product['combinations'] == [])) 
		{
			$this->addProductToCart($product['id_product'], $product['selected_combination']['id_product_attribute']);
			$added = true;
		}
		
		$return = $this->createReturnValue();
		$return['product'] = $product;
		$return['added'] = $added;
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessIncreaseQuantity()
	{
		$id_product = Tools::getValue('id_product');
		$id_product_attribute = Tools::getValue('id_product_attribute');

		if(isset($id_product_attribute) && $id_product_attribute != '0') {
			$this->addProductToCart($id_product, $id_product_attribute);
		} else {
			$this->addProductToCart($id_product);
		}
		
		$return = $this->createReturnValue();
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessDecreaseQuantity()
	{
		$id_product = Tools::getValue('id_product');
		$id_product_attribute = Tools::getValue('id_product_attribute');
		
		if(isset($id_product_attribute) && $id_product_attribute != '0') {
			$this->removeProductFromCart($id_product, $id_product_attribute);
		} else {
			$this->removeProductFromCart($id_product);
		}
		
		$return = $this->createReturnValue();
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessSetDiscount() {
		$discount = Tools::getValue('discount');
		
		$this->createDiscount($discount);
		
		$return = $this->createReturnValue();
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessRemoveDiscount() {
		$id_cart_rule = Tools::getValue('id_cart_rule');
		
		$this->removeDiscount($id_cart_rule);
		
		$return = $this->createReturnValue();
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessTest() {
		$this->sellCart();
		$return = array(
			'context' => ""
		);
		
		die(Tools::jsonEncode($return));
	}
	
	private function appendProductCombinations($product) {
		$productObj = new Product((int)$product['id_product'], false, (int)$this->context->language->id);
		
		$combinations = array();
		$attributes = $productObj->getAttributesGroups((int)$this->context->language->id);
			
		foreach ($attributes as $attribute)
		{
			$product_attribute = $this->getProductAttribute($attribute['id_product_attribute'])[0];
			
			if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
			{
				$combinations[$attribute['id_product_attribute']]['attributes'] = '';
			}
			
			$combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';
			$combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
			$combinations[$attribute['id_product_attribute']]['default_on'] = $attribute['default_on'];
			
			if (!isset($combinations[$attribute['id_product_attribute']]['price']))
			{
				$price_tax_incl = Product::getPriceStatic((int)$product['id_product'], true, $attribute['id_product_attribute']);
				$price_tax_excl = Product::getPriceStatic((int)$product['id_product'], false, $attribute['id_product_attribute']);
				$combinations[$attribute['id_product_attribute']]['price_tax_incl'] = Tools::ps_round(Tools::convertPrice($price_tax_incl, $currency), 2);
				$combinations[$attribute['id_product_attribute']]['price_tax_excl'] = Tools::ps_round(Tools::convertPrice($price_tax_excl, $currency), 2);
				$combinations[$attribute['id_product_attribute']]['formatted_price'] = Tools::displayPrice(Tools::convertPrice($price_tax_excl, $currency), $currency);				
			}
			
			if (!isset($combinations[$attribute['id_product_attribute']]['qty_in_stock']))
			{				
				$combinations[$attribute['id_product_attribute']]['qty_in_stock'] = StockAvailable::getQuantityAvailableByProduct((int)$product['id_product'],$attribute['id_product_attribute'], (int)$this->context->shop->id);
			}
			
			$combinations[$attribute['id_product_attribute']]['ean13'] = $product_attribute['ean13'];

		}

		foreach ($combinations as &$combination)
		{
			$combination['attributes'] = rtrim($combination['attributes'], ' - ');
		}
		
		$product['combinations'] = $combinations;
		
		return $product;
	}
	
	private function getOrderStates() {
		$id_lang = $this->context->language->id;
		$order_states = OrderState::getOrderStates($id_lang);
		return $order_states;
	}
	
	private function getProductAttribute($id_product_attribute) {
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT pa.*
			FROM '._DB_PREFIX_.'product_attribute pa
			WHERE pa.id_product_attribute='.(int)$id_product_attribute);
	}
	
	private function createReturnValue() {
		$cart = $this->getCart();
		$total_price = (float)Tools::ps_round((float)$cart->getOrderTotal(true), 2);
		
		$products = $cart->getProducts();
		
		foreach ($products as $k => &$product)
		{
			$image = array();
			if (isset($product['id_product_attribute']) && (int)$product['id_product_attribute'])
				$image = Db::getInstance()->getRow('SELECT id_image
																FROM '._DB_PREFIX_.'product_attribute_image
																WHERE id_product_attribute = '.(int)$product['id_product_attribute']);
			if (!isset($image['id_image']))
				$image = Db::getInstance()->getRow('SELECT id_image
																FROM '._DB_PREFIX_.'image
																WHERE id_product = '.(int)$product['id_product'].' AND cover = 1');
	
			$product['qty_in_stock'] = StockAvailable::getQuantityAvailableByProduct($product['id_product'], isset($product['id_product_attribute']) ? $product['id_product_attribute'] : null, (int)$cart->id_shop);
			
			$product_obj = new Product($product['id_product']);
	
			$image_product = new Image($image['id_image']);
			
			$product['image'] = (isset($image['id_image']) ? ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.$image_product->getExistingImgPath().'.jpg', 'product_mini_'.(int)$product['id_product'].(isset($product['id_product_attribute']) ? '_'.(int)$product['id_product_attribute'] : '').'.jpg', 45, 'jpg') : '--');
		}
		
		$id = $this->getAnonymousCustomerId();
		$customer = new Customer($id);
		
		$return = array(
			'total_price' => $total_price,
			'cart' => $cart,
			'discounts' => $cart->getCartRules(),
			'products' => $products,
			'customer' => $customer
		);
		
		return $return;
	}
	
	private function getProductByBarcode($barcode) 
	{
		$id_lang = $this->context->language->id;
		
		$matching_products = Product::searchByName($id_lang, $barcode);
		if(count($matching_products) == 1) 
		{
			$product = $matching_products[0];
			
			$product = $this->appendProductCombinations($product);
			
			if($product['ean13'] == $barcode) {
				return $product;
			}
			
			foreach ($product['combinations'] as $combination) {
				if($combination['ean13'] == $barcode) {
					$product['selected_combination'] = $combination;
					$product['id_product_attribute'] = $combination['id_product_attribute'];
					break;
				}
			}
		
			return $product;
		}
		return null;
	}
	
	private function calculateTotalPrice() 
	{
		$cart = $this->getCart();
		$total_price = (float)Tools::ps_round((float)$cart->getOrderTotal(true), 2);
		return $total_price;
	}
	
	private function addProductToCart($id_product, $id_product_attribute = null) {
		$cart = $this->getCart();
		
		$cart->updateQty(1, $id_product, $id_product_attribute);
	}
	
	private function removeProductFromCart($id_product, $id_product_attribute = null) {
		$cart = $this->getCart();

		$cart->updateQty(1, $id_product, $id_product_attribute, false, 'down');	
	}
	
	private function removeDiscount($id_cart_rule) {
		$cart = $this->getCart();
		$cart->removeCartRule($id_cart_rule);
	}
	
	private function createDiscount($discount) {
		$cartrule = new CartRule();
		
		$languages = Language::getLanguages();
		
		$cartrule->description = 'Ladenrabatt';
		foreach ($languages as $language)
		{
			$cartrule->name[$language['id_lang']] = 'Rabatt';
		}
		
		$cartrule->quantity = 0;
		$cartrule->quantity_per_user = 1;
		
		$cartrule->id_customer = 0;
		
		$now = time();
		$cartrule->date_from = date('Y-m-d H:i:s', $now);
		$cartrule->date_to = date('Y-m-d H:i:s', $now); 
				
		$cartrule->active = 1;
		$cartrule->highlight = 1;

		if (strpos($discount, '%') !== FALSE) {
			$discount = str_replace("%", "", $discount);
			$cartrule->reduction_percent = $discount;		
		} else {
			$cartrule->reduction_amount = $discount;		
		}
		
		$cartrule->reduction_tax = true;
		
		$cartrule->minimum_amount_currency = $this->context->currency->id;
		$cartrule->reduction_currency = $this->context->currency->id;

		$cartrule->add();
		
		$cart = $this->getCart();
		
		$cart->addCartRule($cartrule->id);
	}
	
	private function sellCart() {
		$cart = $this->getCart();
		$product_list = $cart->getProducts();
				
		do			
			$reference = Order::generateReference();
		while (Order::getByReference($reference)->count());

		$id_order_state = Configuration::get("FIDDYSTORE_ORDER_STATE");

		$order = new Order();
		$order->product_list = $product_list;
		
		$order->current_state = $id_order_state;
		
		$order->id_customer = $this->getAnonymousCustomerId();
		$order->id_address_invoice = $this->getAnonymousAddressId();
		$order->id_address_delivery = $this->getAnonymousAddressId();
		
		$order->id_currency = $this->context->currency->id;
		$order->id_lang = (int)$cart->id_lang;
		$order->id_cart = $cart->id;
		
		$order->reference = $reference;
		$order->id_shop = (int)$this->context->shop->id;
		$order->id_shop_group = (int)$this->context->shop->id_shop_group;

		$order->secure_key = pSQL($this->getAnonymousCustomerSecureKey());
		$order->payment = "Kartenzahlung im Laden";
		$order->module = "fiddystore";

		$order->conversion_rate = $this->context->currency->conversion_rate;

		$order->id_carrier = 0;
		$id_carrier = 0;	
		
		$order->total_products = (float)$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $id_carrier, false);
		$order->total_products_wt = (float)$cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $id_carrier, false);

		$order->total_discounts_tax_excl = (float)abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $id_carrier, false));
		$order->total_discounts_tax_incl = (float)abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $id_carrier, false));
		$order->total_discounts = $order->total_discounts_tax_incl;

		$order->total_shipping_tax_excl = (float)$cart->getPackageShippingCost((int)$id_carrier, false, null, $order->product_list);
		$order->total_shipping_tax_incl = (float)$cart->getPackageShippingCost((int)$id_carrier, true, null, $order->product_list);
		$order->total_shipping = $order->total_shipping_tax_incl;

		if (!is_null($carrier) && Validate::isLoadedObject($carrier))
			$order->carrier_tax_rate = $carrier->getTaxesRate(new Address($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));

		$order->total_wrapping_tax_excl = (float)abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $id_carrier));
		$order->total_wrapping_tax_incl = (float)abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $id_carrier));
		$order->total_wrapping = $order->total_wrapping_tax_incl;

		$order->total_paid_tax_excl = (float)Tools::ps_round((float)$cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $id_carrier), 2);
		$order->total_paid_tax_incl = (float)Tools::ps_round((float)$cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $id_carrier), 2);
		$order->total_paid = $order->total_paid_tax_incl;
		$order->total_paid_real = $order->total_paid;
		
		$order->invoice_date = date('Y-m-d H:i:s', $now);
		$order->delivery_date = date('Y-m-d H:i:s', $now);

		// Creating order
		$result = $order->add();
		
		// Insert new Order detail list using cart for the current order
		$order_detail = new OrderDetail(null, null, $this->context);
		$order_detail->createList($order, $cart, $id_order_state, $order->product_list, 0, true, 0); //$package_list[$id_address][$id_package]['id_warehouse']);		
		
		// Set the order status
		$new_history = new OrderHistory();
		$new_history->id_order = (int)$order->id;
		$new_history->changeIdOrderState((int)$id_order_state, $order, true);	
		$new_history->add();
	}
	
	private function getAnonymousCustomerId() {
		return Configuration::get('FIDDYSTORE_CUSTOMER');
	}
	
	private function getAnonymousCustomerSecureKey() {
		$id = $this->getAnonymousCustomerId();
		$customer = new Customer($id);
		return $customer->secure_key;	
	}
	
	private function getAnonymousAddressId() {
		return (int)  (Address::getFirstCustomerAddressId($this->getAnonymousCustomerId()));
	} 
	
	private function getCart() {
		$id_customer = $this->getAnonymousCustomerId();
		
		$cartId =	Cart::lastNoneOrderedCart($id_customer);
		
		if($cartId == false) {
			$cart = $this->createCart();
		} else {
			$cart = new Cart((int)$cartId);
		}
		
		return $cart;
	}
	
	private function createCart() {
	
		$context = Context::getContext();
	
		$carts = Cart::getCustomerCarts($this->getAnonymousCustomerId(), false);
		
		$cart = array_shift($carts);
		$cart = new Cart((int)$cart['id_cart']);
		
        $cart->id_customer = $this->getAnonymousCustomerId(); 
        $cart->id_address_delivery = $this->getAnonymousAddressId();
        $cart->id_address_invoice = $cart->id_address_delivery;
        $cart->id_lang = (int)($this->context->language->id);
        $cart->id_currency = (int)($this->context->currency->id);
        $cart->id_carrier = 1;
        $cart->recyclable = 0;
        $cart->gift = 0;
        
        $cart->add();
        
        return $cart;
	}
	
	

}

?>