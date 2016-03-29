<?php

namespace PhangoApp\PhaLibs;

use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaView\View;
use PhangoApp\PhaI18n\I18n;

class ParentLinks {

    public $model_name;
    public $parentfield_name;
    public $field_name;
    public $idmodel;

    public function __construct($url, $model_name, $parentfield_name, $field_name, $idmodel, $last_link=0, $arr_parameters=[], $arr_pretty_parameters=[])
    {
    
        $this->url=$url;
        $this->model_name=$model_name;
        $this->parentfield_name=$parentfield_name;
        $this->field_name=$field_name;
        $this->idmodel=$idmodel;
        $this->last_link=$last_link;
        $this->arr_parameters=$arr_parameters;
        $this->arr_pretty_parameters=$arr_pretty_parameters;
    
    }

    public function hierarchy_links()
    {

        //Get the father and its father, and the father of its father
        
        //Obtain all id and fathers
        
        //Cache system?
        
        $arr_id_father=array(0 => 0);
        $arr_id_name=array(0 => I18n::lang('common', 'home', 'Home'));
        $arr_hierarchy=array();
        
        $query=Webmodel::$model[$this->model_name]->select(array(Webmodel::$model[$this->model_name]->idmodel, $this->parentfield_name, $this->field_name), 1);
        
        while(list($id, $father, $name)=Webmodel::$model[$this->model_name]->fetch_row($query))
        {
            
            $arr_id_father[$id]=$father;
            $arr_id_name[$id]=Webmodel::$model[$this->model_name]->components[$this->field_name]->show_formatted($name);
        
        }
        
        $arr_hierarchy=$this->recursive_obtain_father($arr_id_father, $this->idmodel, $arr_id_name, $arr_hierarchy);
        
        $arr_hierarchy=array_reverse($arr_hierarchy);
        
        return $arr_hierarchy;
        
        //echo load_view(array($arr_hierarchy), 'common/utilities/hierarchy_links');

    }
    
    public function recursive_obtain_father($arr_id_father, $id, $arr_id_name, $arr_hierarchy)
    {

        $arr_hierarchy[]=array('name' => $arr_id_name[$id], 'id' => $id);

        if($id!=0)
        {
        
            $arr_hierarchy=$this->recursive_obtain_father($arr_id_father, $arr_id_father[$id], $arr_id_name, $arr_hierarchy);
        
        }
        
        return $arr_hierarchy;

    }

    public function show()
    {
    
        $arr_hierarchy_links=$this->hierarchy_links();
    
        //'common/utilities/hierarchy_links_standard'
    
        return View::load_view(array($arr_hierarchy_links, $this->url, $this->parentfield_name, $this->arr_parameters, $this->last_link), 'common/utils/parentlinks');
    
    }

}
    
?>
