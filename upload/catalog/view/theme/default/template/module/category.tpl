<div class="box">

  <div class="">

<?php


function makeList($array) {

        //Base case: an empty array produces no list
        if (empty($array)) return '';

        //Recursive Step: make a list with child lists
        $output = '<ul id="catnavleft" class="sf-menu sf-vertical">';
        foreach ($array as $key => $subArray) {
        	if(!empty($subArray['children'])){
            	$output .= '<li><a href="'. $subArray['href'] .'">'. $subArray['name'] .'</a>'. makeList($subArray['children']) .'</li>';
            } else {
            	$output .= '<li><a href="'. $subArray['href'] .'">'. $subArray['name'] .'</a></li>';
            }
        }
        $output .= '</ul>';
        
        return $output;
    } 

echo makeList($categories);

?>

  </div>
</div>
