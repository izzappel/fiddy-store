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
		$this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/storesellings.js');
		$this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/storesellings.css');

		return parent::setMedia();
	}

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
		
		$tpl->assign($return);
		
		return $tpl->fetch();
	}
	
	public function ajaxProcessAddProduct()
	{
		$barcode = Tools::getValue('barcode');
		$product = $this->getProductByBarcode($barcode);
		//$this->addProductToCart($product['id_product']);
		
		$return = $this->createReturnValue();
		$return['product'] = $product;
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessIncreaseQuantity()
	{
		$id_product = Tools::getValue('id_product');
		$this->addProductToCart($id_product);
		
		$return = $this->createReturnValue();
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessDecreaseQuantity()
	{
		$id_product = Tools::getValue('id_product');
		$this->removeProductFromCart($id_product);
		
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
	
	private function getProduct($product) {
		$productObj = new Product((int)$product['id_product'], false, (int)$this->context->language->id);
		
		$combinations = array();
		$attributes = $productObj->getAttributesGroups((int)$this->context->language->id);
		
		foreach ($attributes as $attribute)
		{
			if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
				$combinations[$attribute['id_product_attribute']]['attributes'] = '';
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
				$combinations[$attribute['id_product_attribute']]['qty_in_stock'] = StockAvailable::getQuantityAvailableByProduct((int)$product['id_product'], $attribute['id_product_attribute'], (int)$this->context->shop->id);
		}

		foreach ($combinations as &$combination)
			$combination['attributes'] = rtrim($combination['attributes'], ' - ');
		$product['combinations'] = $combinations;
		
		return $product;
	}
	
	
	private function createReturnValue() {
		$cart = $this->getCart();
		$total_price = (float)Tools::ps_round((float)$cart->getOrderTotal(true), 2);
				
		$return = array(
			'total_price' => $total_price,
			'cart' => $cart,
			'discounts' => $cart->getCartRules(),
			'products' => $cart->getProducts()
		);
		
		return $return;
	}
	
	private function getProductByBarcode($barcode) 
	{
		$id_lang = $this->context->language->id;
		
		$matching_products = Product::searchByName($id_lang, $barcode);
		if(count($matching_products) > 0) 
		{
			return $this->getProduct($matching_products[0]);
		}
		return null;
	}
	
	private function calculateTotalPrice() 
	{
		$cart = $this->getCart();
		$total_price = (float)Tools::ps_round((float)$cart->getOrderTotal(true), 2);
		return $total_price;
	}
	
	private function addProductToCart($id_product) {
		$cart = $this->getCart();
		
		$cart->updateQty(1, $id_product);
	}
	
	private function removeProductFromCart($id_product) {
		$cart = $this->getCart();

		$cart->updateQty(1, $id_product, null, false, 'down');	
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

		$cartrule->reduction_amount = $discount;
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

		$id_order_state = 13;

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

		$order->invoice_date = '0000-00-00 00:00:00';
		$order->delivery_date = '0000-00-00 00:00:00';

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
		$anonymousArray = Customer::getCustomersByEmail('info@fiddy-store.ch');
		$anonymous = array_shift($anonymousArray);
		return $anonymous['id_customer'];
	}
	
	private function getAnonymousCustomerSecureKey() {
		$anonymousArray = Customer::getCustomersByEmail('info@fiddy-store.ch');
		$anonymous = array_shift($anonymousArray);
		return $anonymous['secure_key'];	
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