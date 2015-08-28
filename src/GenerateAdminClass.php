<?php

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaLibs\SimpleList;
use PhangoApp\PhaView\View;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaModels\ModelForm;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaRouter\Routes;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaLibs\HierarchyLinks;

class GenerateAdminClass {

    public $model_name='';

    public $list;
    
    //For the future
    
    public $arr_fields_insert=array();
    
    public $arr_fields_edit=array();
    
    public $enctype='';
    
    public $url='';
    
    public $safe=0;
    
    public $arr_links=array();
    
    public $hierarchy;
    
    public $text_add_item='';
    
    public $no_insert=0;
    
    public $no_delete=0;
    
    public function __construct($model, $url)
    {
    
        $this->model=&$model;
    
        $this->model_name=$this->model->name;
        
        $this->list=new SimpleList($this->model);
        
        $this->set_url_admin($url);
        
        $this->arr_links['']=array($url => I18n::lang('common', 'home', 'Home'));
        
        $this->hierarchy=new HierarchyLinks($this->arr_links);
        
        $this->text_add_item=I18n::lang('common', 'add_new_item', 'Add new item');
        
        $this->text_add_item_success=I18n::lang('common', 'add_new_item_success', 'Added new item succesfully');
        
        $this->text_update_item=I18n::lang('common', 'update_item', 'Update item');
        
        $this->text_updated_item=I18n::lang('common', 'item_updated', 'Item update succesfully');
        
        $this->text_deleted_item=I18n::lang('common', 'item_deleted', 'Item deleted succesfully');
        
        $this->text_deleted_item_error=I18n::lang('common', 'item_deleted_error', 'Error, cannot delete the field. Please, check for errors');
        
        if(count($this->model->forms)==0)
        {
        
            $this->model->create_forms($this->arr_fields_edit);
        }
    }
    
    public function show()
    {
        
        settype($_GET['op_admin'], 'integer');
        
        if($this->no_delete==1)
        {
        
            if($this->list->options_func=='PhangoApp\PhaLibs\SimpleList::BasicOptionsListModel')
            {
        
                $this->list->options_func='PhangoApp\PhaLibs\SimpleList::NoDeleteOptionsListModel';
            }
            
        }
        
        switch($_GET['op_admin'])
        {
            
            //List
            
            default:
            
                echo $this->hierarchy->show($this->url);
                
                //$this->list->show();
                echo View::load_view(array($this), 'admin/adminlist');
            
            break;
    
            //Create new item
            
            case 1:
                
                if(!$this->no_insert)
                {
                
                    $action=Routes::add_get_parameters($this->url, array('op_admin' => 1));
                        
                    //$this->arr_links[$this->url]=array($action => I18n::lang('common', 'add_new_item', 'Add new item'));
                    
                    $this->hierarchy->update_links($this->url, $action, $this->text_add_item);
                    
                    echo $this->hierarchy->show($action);
                    
                    echo '<h2>'.$this->text_add_item.'</h2>';
                    
                    $this->insert_model($action);
                }
                
            break;

            case 2:
            
                settype($_GET[$this->model->idmodel], 'integer');
            
                $action=Routes::add_get_parameters($this->url, array('op_admin' => 2, $this->model->idmodel => $_GET[$this->model->idmodel]));
                    
                $this->hierarchy->update_links($this->url, $action, $this->text_update_item);
            
                echo $this->hierarchy->show($action);
                
                echo '<h2>'.$this->text_update_item.'</h2>';
                
                $this->update_model($action);
            
            break;
            
            case 3:
            
                if(!$this->no_delete)
                {
                
                    settype($_GET[$this->model->idmodel], 'integer');
                    
                    $id=$_GET[$this->model->idmodel];
                    
                    $idmodel=$this->model->idmodel;
                    
                    $this->model->set_conditions('WHERE '.$idmodel.'='.$id);
                    
                    if($this->model->delete($_POST, $this->safe))
                    {
                    
                        View::set_flash($this->text_deleted_item);
                        
                        Routes::redirect($this->url);
                        
                    }
                    else
                    {
                    
                        echo '<p>'.$this->text_deleted_item_error.'</p>';
                    
                    }
                }
                
            break;
        
        }
    
    }
    
    public function insert_model($action)
    {
    
        if(Routes::$request_method=='GET')
        {
        
            $id=$this->model->idmodel;
        
            if(isset($this->model->forms[$id]))
            {
                unset($this->model->forms[$id]);
            }
            
            $this->form(array(), $action);
            
        }
        elseif(Routes::$request_method=='POST')
        {
        
            if(!$this->model->insert($_POST, $this->safe))
            {
                echo '<p><span class="error">'.$this->model->std_error.'</span></p>';
                
                $this->form($_POST, $action, 1);
            
            }
            else
            {
            
                View::set_flash($this->text_add_item_success);
                
                Routes::redirect($this->url);
            
            }
        
        }
    
    }
    
    public function update_model($action)
    {
    
        $id=$_GET[$this->model->idmodel];
        
        settype($id, 'integer');
        
        $idmodel=$this->model->idmodel;
        
        $arr_row=$this->model->select_a_row($id);
        
        settype($arr_row[$idmodel], 'integer');
        
        if($arr_row[$idmodel]>0)
        {
        
            if(Routes::$request_method=='GET')
            {
        
                $this->form($arr_row, $action, 1);
                
            }
            else
            if(Routes::$request_method=='POST')
            {
                $this->model->set_conditions('WHERE '.$idmodel.'='.$id);
                
                if(!$this->model->update($_POST, $this->safe))
                {
                
                    echo '<p><span class="error">'.$this->model->std_error.'</span></p>';
                
                    $this->form($_POST, $action, 1);
                
                }
                else
                {
                
                    View::set_flash($this->text_updated_item);
            
                    Routes::redirect($this->url);
                
                }
            
            }
            
        }
    
    }
    
    public function form($post, $action, $show_error=0)
    {
    
        //ModelForm::pass_errors_to_form($this->model);
    
        ModelForm::set_values_form($this->model->forms, $post, $show_error);
        
        $fields=$this->arr_fields_edit;
        
        $method='post';
        
        
        echo View::load_view(array($this->model->forms, $fields, $method, $action, $this->enctype), 'forms/updatemodelform');
    
    }
    
    public function set_url_admin($url)
    {
    
        $this->list->url_options=$url;
        $this->url=$url;
    
    }

}

?>