<?php

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class StoreSellings extends AdminTab
{
	public function __construct()
	{	
		parent::__construct();
	}

	public function display() 
	{
		echo $this->l('This is my tab! FIDDY STORE!');
	}
}	

?>