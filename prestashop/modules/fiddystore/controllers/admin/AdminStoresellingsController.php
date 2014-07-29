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

		$tpl->assign(array(
			'product' => "test"
		));
		
		return $tpl->fetch();
	}
	
	public function ajaxProcessSearchProducts()
	{
		$barcode = Tools::getValue('barcode');
	
		$product = $this->getProductByBarcode($barcode);
		
		$total_price = $this->calculateTotalPrice();
	
		print_r($this->productsInBasket);
	
		$return = array(
					'barcode' => $barcode,
					'product' => $product
					);
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessGetTotalPrice() {
		$products = Tools::getValue('products');
		$discount = Tools::getValue('discount');

		$price_for_products = $this->calculateTotalPrice($products);

		if(strpos($discount, '%') !== FALSE) {
			$discount = str_replace("%", "", $discount);
			$discount = ($price_for_products * ($discount / 100));
		}
		
		$total_price = $price_for_products - $discount;

		$return = array(
					'total_price' => $total_price,
					'price_for_products' => $price_for_products
					);
		die(Tools::jsonEncode($return));
	}
	
	private function getProductByBarcode($barcode) 
	{
		$id_lang = $this->context->language->id;
		
		$matching_products = Product::searchByName($id_lang, $barcode);
		if(count($matching_products) > 0) 
		{
			return $matching_products[0];
		}
		return null;
	}
	
	private function calculateTotalPrice($products) 
	{
		$price = 0;
		for($i = 0; $i < count($products); $i++) 
		{
			$product = $products[$i];
			$price += $product['amount'] * $product['price_tax_incl'];
		}
		return $price;
	}

}

?>