<?php

namespace PhangoApp\PhaLibs;

/**
* Simple static method for create father and son links using an array
*
*/

class FatherLinks {
    
    /**
    * Static method for show a series of hierarchy links defined by fathers and sons
    * 
    * @param string $switch This element define the key of arr_links where the page is placed
    * @param string $arr_links The links ordered by father to son define by the key father
    */

    static public function show($switch, $arr_links)
    {
    
        $arr_final=[];
    
        foreach($arr_links as $key => $links)
        {
        
            if($key==$switch)
            {
            
                $arr_final[]=$links[0];
                
                break;
            
            }
            
            $arr_final[]='<a href="'.$links[1].'">'.$links[0].'</a>';
        
        }
        
        return $arr_final;
    
    }

}

?>
