<?php

//A serie of links, link 0 is the big father, and have an array with children. 

//The distinction is with the link. I can create a index with the link how key and the father how value. 

// [link_father]=array('link_son' => text);

/**
* A class for create simple hierarchy links. 
*/

namespace PhangoApp\Phalibs;

class HierarchyLinks {


	public $arr_links=array();
	
	public $arr_indexes=array();
	
	public function __construct($arr_links)
	{
	
		$this->arr_links=$arr_links;
	
	}
	
	public function update_links($link_father, $link_son, $text)
	{
	
        $this->arr_links[$link_father][$link_son]=$text;
	
	}
	
	public function calculate_indexes()
	{
	
		foreach($this->arr_links as $father_link => $arr_child_links)
		{	
			
			foreach($arr_child_links as $link => $text)
			{
                
				$this->arr_indexes[$link]=$father_link;
				
			}
		
		}
	
	}
	
	public function result($last_link, $arr_result=array(), $yes_last_link=0)
	{
	
		$this->calculate_indexes();
		
		if(isset($this->arr_indexes[$last_link]))
		{
            $father=$this->arr_indexes[$last_link];
            
            $arr_last_link[1]='yes_link';
            
            $arr_last_link[0]='no_link';
            
            $yes_link_func=$arr_last_link[$yes_last_link];
            
            if($father!='')
            {		
                
                $arr_result[]=$this->$yes_link_func($last_link, $this->arr_links[$father][$last_link]);
                
                $yes_last_link=1;
                
                $arr_result=$this->result($father, $arr_result, $yes_last_link);
                
                return $arr_result;
            
            }
            else
            {
                
                $arr_result[]=$this->$yes_link_func($last_link, $this->arr_links[$father][$last_link]);
                
                return $arr_result;
            
            }
        }
        
        return $arr_result;
	
	}
	
	
	public function show($link, $separator='&gt;&gt;', $class_link='')
	{
	
		$arr_result=$this->result($link);
		
		$arr_result=array_reverse($arr_result);
		
		return implode(' '.$separator.' ', $arr_result);
	
	}
	
	private function yes_link($link, $text)
	{
	
		return '<a href="'.$link.'">'.$text.'</a>';
	
	}
	
	private function no_link($link, $text)
	{
	
		return $text;
	
	}
	

}

?>