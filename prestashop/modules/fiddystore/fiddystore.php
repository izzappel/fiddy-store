<?php
if(!defined('_PS_VERSION_'))
	exit;
	
class FiddyStore extends Module 
{
	public function __construct() 
	{
		$this->name = 'fiddystore';
		$this->tab = '';
		$this->version = '0.1';
		$this->author = 'Isabel Züger';
		$this->push_filename = _PS_CACHE_DIR_.'push/activity';
		$this->allow_push = true;
		$this->push_time_limit = 180;


		parent::__construct();
		
		$this->displayName = $this->l('Fiddy Store');
		$this->description = $this->l('Ladenverkauf Modul für Fiddy Store');
		
		$this->confirmUninstall = $this->l('Willst du das Fiddy-Store Modul wirklich löschen?');
		
		if(!Configuration::get('FIDDYSTORE_NAME'))
			$this->warning = $this->l('Keine Name angegeben.');
			
			
		$this->admin_tpl_path = _PS_MODULE_DIR_.$this->name.'/views/templates/admin/';
		$this->hooks_tpl_path = _PS_MODULE_DIR_.$this->name.'/views/templates/hooks/';
	}
	
	public function install()
	{
		$tab = new Tab();
		foreach (Language::getLanguages() as $language)
            $tab->name[$language['id_lang']] = $this->l('Ladenverkauf'); 
		$tab->class_name = 'StoreSellings';
		$tab->module = 'fiddystore';
		$tab->id_parent = 0; // Root tab
		$tab->add();
		
		if(parent::install() == false || !$this->registerHook('displayBackOfficeHeader') || !Configuration::updateValue('FIDDYSTORE_NAME', 'Fiddy Store Plugin'))
			return false;
		return true;
	}
	
	public function uninstall() 
	{
		// uninstall DB
		// Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'mymodule`');
		$tab = new Tab((int)Tab::getIdFromClassName('StoreSellings'));
		$tab->delete();
		
		if(!parent::uninstall() || !Configuration::deleteByName('FIDDYSTORE_NAME')) 
			return false;
		return true;
	}
	
	public function hookDisplayBackOfficeTop($params) 
	{
		$this->context->smarty->assign(
			array(
				'fiddy_store_name' => Configuration::get('FIDDYSTORE_NAME'),
				'fiddy_store_link' => $this->context->link->getModuleLink('fiddystore', 'display')
			)
		);
		return $this->display(__FILE__, 'fiddystore.tpl');
	}
	
}

?>