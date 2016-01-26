<?php

namespace PhangoApp\PhaLibs;

class FatherLinks {

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