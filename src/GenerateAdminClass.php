<?php

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaLibs\SimpleList;
use PhangoApp\PhaView\View;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaUtils\Utils;

class GenerateAdminClass {

    public $model_name='';

    public $list;
    
    public $arr_fields_edit=array();
    
    public $yes_search=1;
    
    public function __construct($model_name, $url)
    {
    
        $this->model_name=$model_name;
        
        $this->list=new SimpleList($model_name);
        
        $this->set_url_admin($url);
    
    }
    
    public function show()
    {
        
        settype($_GET['op_admin'], 'integer');
        
        switch($_GET['op_admin'])
        {
            
            //List
            
            default:
                
                //$this->list->show();
                echo View::load_view(array($this), 'admin/adminlist');
            
            break;
    
            //Create new item
            
            case 1:
            
                
            
            break;
        
        //Update item
        
        //Delete item
        
        }
    
    }
    
    public function set_url_admin($url)
    {
    
        $this->list->url_options=$url;
        $this->url=$url;
    
    }

}

?>