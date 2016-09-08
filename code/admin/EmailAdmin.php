<?php

class EmailAdmin extends ModelAdmin {
        
    private static $allowed_actions = array(
        "ImportForm"
    );
    
    private static $menu_priority = 6;

    private static $url_segment = 'emails';

    private static $menu_title = 'Emails';

    private static $managed_models = array(
        'EmailMessage'
    );

    private static $model_importers = array();
    
    public function getEditForm($id = null, $fields = null)
    {
		$form = parent::getEditForm($id, $fields);
		
		$class = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($class);            
        $config = $gridField->getConfig();
        
        $config
            ->removeComponentsByType("GridFieldExportButton")
            ->removeComponentsByType("GridFieldPrintButton")
            ->removeComponentsByType("GridFieldAddNewButton");
		
		return $form;
	}
}
