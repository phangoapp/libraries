<?php

/**
*
* @author  Antonio de la Rosa <webmaster@web-t-sys.com>
* @file
*
*
*/

namespace PhangoApp\PhaLibs;
use PhangoApp\PhaUtils\SimpleTable;
use PhangoApp\PhaUtils\Pages;
use PhangoApp\PhaUtils\Utils;
use PhangoApp\PhaModels\Webmodel;
use PhangoApp\PhaI18n\I18n;
use PhangoApp\PhaRouter\Routes;
use PhangoApp\PhaView\View;

class SimpleList
{

	public $arr_options=array();
	public $yes_options=1;
	public $arr_fields=array();
	public $arr_fields_showed=array();
	public $arr_fields_no_showed=array();
	public $arr_fields_search=array();
	public $default_field_search='';
	public $arr_extra_fields=array();
	public $arr_extra_fields_func=array();
	public $arr_cell_sizes=array();
	public $model_name;
	public $where_sql='';
	public $options_func='PhangoApp\PhaLibs\SimpleList::BasicOptionsListModel';
	public $options_func_extra_args=array();
	public $url_options='';
	public $separator_element='<br />';
	public $limit_rows=10;
	public $raw_query=0;
	public $yes_pagination=1;
	public $num_by_page=20;
	public $begin_page=0;
	public $initial_num_pages=20;
	public $order_by='';
	public $variable_page='begin_page';
	public $yes_search=0;
	public $order_field='';
	public $order=0;
	public $yes_id=1;
	
	function __construct($model)
	{
		settype($_GET['begin_page'], 'integer');
		
		$this->model=$model;
		
		$this->model_name=$this->model->name;
		
		$this->begin_page=$_GET['begin_page'];
		
		if( count($this->model->forms)==0)
		{	
			$this->model->create_forms();
		}
		
		$this->arr_fields_search=$this->arr_fields_showed;
		
		$this->order_field=$this->model->idmodel;
		
	}
	
	public function set_yes_id($bool)
	{
	
        if($bool)
        {
        
            $this->yes_id=1;
        
        }
        else
        {
        
            $this->yes_id=0;
            $this->yes_options=0;
        
        }
	
	}
	
	public function set_fields_showed($arr_fields=array())
	{
	
        $this->arr_fields_showed=$arr_fields;
        $this->arr_fields_search=$arr_fields;
	
	}
	
	public function load_fields_showed($arr_fields_no_showed=array())
	{
	
        $arr_fields_show=array();
        
        if(count($this->arr_fields)==0)
        {
            
            $this->arr_fields=array_keys($this->model->components);
        
        }
        
        
        if(!in_array($this->model->idmodel, $this->arr_fields) && $this->yes_id==1)
        {
        
            //$this->arr_fields[]=$this->model->idmodel;
            array_unshift($this->arr_fields, $this->model->idmodel);
            
        
        }
        
        if(count($this->arr_fields_showed)==0)
        {
        
            $this->arr_fields_showed=$this->arr_fields;
        
        }
        
        $this->arr_fields_showed=array_intersect($this->arr_fields_showed, $this->arr_fields);
        $arr_fields_showed=array_diff($this->arr_fields_showed, $arr_fields_no_showed);

        
        foreach($arr_fields_showed as $field)
        {
        
            $arr_fields_show[$field]=$this->model->forms[$field]->label;
        
        }
        
        return $arr_fields_show;
	
	}
	
	public function obtain_list()
	{
        
        $this->model->set_conditions($this->where_sql);
            
        $this->model->set_order($this->order_by);
        
        $this->model->set_limit('limit '.$this->begin_page.', '.$this->num_by_page);
        
        return $this->model->select($this->arr_fields, $this->raw_query);
	
	}
	
	public function obtain_row($arr_row, $options_method)
	{
	
        $arr_row_final=array();
            
        foreach($this->arr_fields_showed as $field)
        {
        
            $arr_row_final[$field]=Webmodel::$model[$this->model_name]->components[$field]->show_formatted($arr_row[$field],  $arr_row);
        
        }
        
        //extra arr_extra_fields
        
        foreach($this->arr_extra_fields_func as $name_func)
        {
            
            $arr_row_final[]=$name_func($arr_row);
            
        }
        
        $arr_row_final=$this->$options_method($arr_row_final, $arr_row, $this->options_func, $this->url_options, $this->model_name, webmodel::$model[$this->model_name]->idmodel, $this->separator_element, $this->options_func_extra_args);
        
        return $arr_row_final;
	
	}
	
	public function show()
	{
        settype($_GET['ajax'], 'integer');
        
        $arr_fields_show=$this->load_fields_showed($this->arr_fields_no_showed);
            
        //Extra fields name_field
        
        foreach($this->arr_extra_fields as $extra_key => $name_field)
        {
        
            $arr_fields_show[$extra_key]=$name_field;
        
        }
        
        $options_method='no_add_options';
        
        if($this->yes_options)
        {
            
            $arr_fields_show[]=I18n::lang('common', 'options', 'Options');
            $options_method='yes_add_options';
        
        }
        
        /*if($_GET['ajax']==0)
        {*/
            
            SimpleTable::top_table_config($arr_fields_show, $this->arr_cell_sizes);
            
            $query=$this->obtain_list();
            
            while($arr_row=$this->model->fetch_array($query))
            {
            
                $arr_row_final=$this->obtain_row($arr_row, $options_method);
            
                SimpleTable::middle_table_config($arr_row_final, $cell_sizes=array());
            
            }
            
            SimpleTable::bottom_table_config();
            
        /*}
        else
        {
        
            //Obtain table ajax
            $query=$this->obtain_list();
            
            while($arr_row=$this->model->fetch_array($query))
            {
            
                $arr_row_final[]=$this->obtain_row($arr_row, $options_method);
            
            
            }
            
            echo json_encode($arr_row_final);
        
        }*/
        
        if($this->yes_pagination==1)
        {
        
            //Utils::load_libraries(array('pages'));
            
            $this->model->set_conditions($this->where_sql);
            
            $total_elements=$this->model->select_count();
            
            echo '<p>'.I18n::lang('common', 'pages', 'Pages')
            .': '.Pages::show( $this->begin_page, $total_elements, $this->num_by_page, $this->url_options ,$this->initial_num_pages, $this->variable_page, $label='', $func_jscript='').'</p>';
        
        }
	
	}
	
	private function yes_add_options($arr_row, $arr_row_raw, $options_func, $url_options, $model_name, $model_idmodel, $separator_element, $options_func_extra_args)
	{
		
		$arr_row[]=implode($separator_element, call_user_func_array($options_func, array($url_options, $model_name, $arr_row_raw[$model_idmodel], $arr_row_raw, $options_func_extra_args) ) );
		
		return $arr_row;

	}



	private function no_add_options($arr_row, $arr_row_raw, $options_func, $url_options, $model_name, $model_idmodel, $separator_element, $options_func_extra_args)
	{

		return $arr_row;

	}
	
	public function change_order_by_url()
    {
    
        //Get: by/field/order/0
        
        //settype($_GET['order'], 'integer');
        
        if(!isset($_GET['order']))
        {
            
            $_GET['order']=$this->order;
        
        }
        
        $arr_order[$_GET['order']]='ASC';
        $arr_order[0]='ASC';
        $arr_order[1]='DESC';
        
        if(isset($_GET['field_search']))
        {
            
            $_GET['field_search']=Utils::slugify($_GET['field_search'], 1);
            
            if(isset($this->model->components[$_GET['field_search']]))
            {
            
                $this->order_by='order by `'.$_GET['field_search'].'` '.$arr_order[$_GET['order']];
                
                //$this->url_options=Routes::add_get_parameters($this->url_options, array('field_search' => $_GET['field_search'], 'order' => $_GET['order']));
            
            }
        
        }
    
    }
    
    public function search_by_url()
    {
        
        if(!isset($_GET['field_search']))
        {
        
            $_GET['field_search']=$this->order_field;
            
        }
    
        if(isset($_GET['search']) && isset($_GET['field_search']))
        {
            
            $_GET['field_search']=Utils::slugify($_GET['field_search'], 1);
            
            if(isset($this->model->components[$_GET['field_search']]))
            {
            
                //$_GET['search']=trim($this->model->components[$_GET['field_search']]->check($_GET['search']));
                //$_GET['search']=trim(Utils::form_text($_GET['search']));
                
                if($_GET['search']!=false)
                {
            
                    $this->where_sql=['WHERE `'.$_GET['field_search'].'` LIKE ?', ['%'.$_GET['search'].'%']];
                    
                }
            
            }
        }
        else
        {
        
            $_GET['search']='';
            
        }
        
        $this->change_order_by_url();
        
        $this->url_options=Routes::add_get_parameters($this->url_options, array('field_search' => $_GET['field_search'], 'order' => $_GET['order']));
                
        if($_GET['search']!='')
        {
        
            $this->url_options=Routes::add_get_parameters($this->url_options, array('search' => $_GET['search']));
        
        }
    
    }
	
	static public function BasicOptionsListModel($url_options, $model_name, $id)
    {

        if(!isset(View::$header['delete_model']))
        {
            ob_start();
        
            ?>
            <script language="javascript">
                function warning()
                {
                    if(confirm('<?php echo I18n::lang('common', 'delete_model', 'Delete element'); ?>'))
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
            </script>
            <?php
            
            View::$header['delete_model']=ob_get_contents();
            
            ob_end_clean();
        }

        $url_options_edit=Routes::add_get_parameters($url_options, array('op_admin' =>2, Webmodel::$model[$model_name]->idmodel => $id));
        $url_options_delete=Routes::add_get_parameters($url_options, array('op_admin' =>3, Webmodel::$model[$model_name]->idmodel => $id));

        $arr_options=array('<a href="'.$url_options_edit.'">'.I18n::lang('common', 'edit', 'Edit').'</a>', '<a href="'.$url_options_delete.'" onclick="javascript: if(warning()==false) { return false; }">'.I18n::lang('common', 'delete', 'Delete').'</a>');

        return $arr_options;

    }
    
    static public function NoDeleteOptionsListModel($url_options, $model_name, $id)
    {

        

        $url_options_edit=Routes::add_get_parameters($url_options, array('op_admin' =>2, Webmodel::$model[$model_name]->idmodel => $id));

        $arr_options=array('<a href="'.$url_options_edit.'">'.I18n::lang('common', 'edit', 'Edit').'</a>');

        return $arr_options;

    }


}

?>
