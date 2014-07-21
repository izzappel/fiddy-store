<?php
if(!defined('_PS_VERSION_'))
	exit;
	
class StoreSellingsController extends ModuleAdminController 
{
	public function __construct()    
    {    
        parent::__construct();        
         
		$this->action = 'view';
		$this->display = 'view';
		$this->meta_title = $this->l('Ladenverkauf');
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
			'module_name' => "test"
		));
		
		return $tpl->fetch();
	}

}

?>