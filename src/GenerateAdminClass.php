<?php

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaLibs\SimpleList;

class GenerateAdminClass {

    public $name_model='';

    public $list;
    
    public $arr_fields=array();
    
    public $arr_fields_edit=array();
    
    public function __construct($name_model)
    {
    
        $list=new SimpleList($name_model);
    
    }
    
    public function show()
    {
    
        settype($_GET['op'], 'integer');
        
        switch($_GET['op'])
        {
            
            //List
            
            default:
            
                
            
            break;
    
            //Create new item
            
            case 'create_item':
            
                
            
            break;
        
        //Update item
        
        //Delete item
        
        }
    
    }

}

?>