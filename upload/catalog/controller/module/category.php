<?php
class ControllerModuleCategory extends Controller {

	public function buildTree(array $elements, $counts, $parentId, $chain) {
	// Takes AllCategories and Counts arrays, builds a Tree structure
		
    	$branch = array();
		$getcounts = $this->config->get('config_product_count');
		
    	foreach ($elements as $key=>$element) {
        	if ($element['parent_id'] == $parentId) {

				// >> Chain + URL-Path : Cat path (id_id_id etc.)
				if(empty($chain) || !in_array($element['parent_id'], $chain)){
					if($parentId == 0){
						$chain[]= '';
					}else{
						$chain[]= $element['parent_id'];
					}
				}
				$element['chain'] = $chain;
				$urlpath = (implode('_',$chain)).'_'.$element['category_id'];
				$urlpath = ltrim($urlpath,'_');

				
				// >> Product Counts
				$element['thistotal'] = '0';

				// If using Counts, and we have them!
				if($getcounts && is_array($counts) && !empty($counts)){
					
					// If the CatID is in the Count array
					if(array_key_exists($element['category_id'], $counts)){
						$element['thistotal'] = $counts[$element['category_id']];
					}else{
						// Not in the array, thus no products in that category
						$element['thistotal'] = '0';
					}
				}
				
				$element['href'] = $this->url->link('product/category', 'path=' . $urlpath);
				$element['name'] = $element['name'] . ' [ ' .$element['thistotal']  .' ]';
				
				$children = $this->buildTree($elements, $counts, $element['category_id'], $element['chain']);
            	if ($children) {
	                $element['children'] = $children;
					$chain = '';
        	    }

				
            	$branch[$element['category_id']] = $element;
				//$this->pruneTree($branch);
			
        	}
    	}
		
    	return $branch;
	}	
	// END FUNC


	public function pruneTree(&$nodes) {
		// Func removes Categories from category tree if
		// it has no children AND no products.
		// It will leave Categories with no products if it has a child,
		// (or grandchild etc.), that has a product
		
    	foreach ($nodes as $key => $node) {
        	if (!isset($node['children']) && $node['thistotal'] == 0) {
            	unset($nodes[$key]);
	        } elseif (!empty($node['children'])) {
    	        $this->pruneTree($nodes[$key]['children']);
        	    // This line checks if all the children have been pruned away:
            	if (empty($nodes[$key]['children']) && $nodes[$key]['thistotal'] == 0) {
                	unset($nodes[$key]);
	            }
    	    }
	    }
	}
	// END FUNC

	protected function index($setting) {

		$this->language->load('module/category');

		$this->data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$this->data['category_id'] = $parts[0];
		} else {
			$this->data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$this->data['child_id'] = $parts[1];
		} else {
			$this->data['child_id'] = 0;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->data['categories'] = array();


		
		$categories = $this->model_catalog_category->getAllCategories();

		
		$productcounts = $this->model_catalog_product->getAllTotalProducts();
		$productcounts2 = '';
		foreach($productcounts as $proddata){
			$productcounts2[$proddata['category_id']] = $proddata['COUNT(*)'];
		}
		unset($productcounts);



		$chain = array();

		$category_tree_data = $this->buildTree($categories, $productcounts2, 0, $chain); 
		
		$nostubs = true;
		if($this->config->get('config_product_count') && $nostubs){
			$this->pruneTree($category_tree_data);
		}
		
		 
		$this->data['categories'] = $category_tree_data;


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/category.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/category.tpl';
		} else {
			$this->template = 'default/template/module/category.tpl';
		}

		$this->render();
	}
}
?>
