<?php

/*
Plugin Name: aHOT Helper
Plugin URI: http://reedwebservice.com
Description: Integrate Woocommerce with Google Trusted Stores.
Version: 0.1
Author: Ryan Reed
Author URI: 
Requires at least: 3.0
Tested up to: 4.3.1
Text Domain: sosm
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('plugins_loaded', 'init_hot_helper', 0);

function init_hot_helper() {

    add_action('admin_menu', 'hot_menu_page');
    add_action( 'woocommerce_single_product_summary', 'hot_next_avail', 12 );
   
}

function hot_menu_page() {

	global $efyp_settings_page;

	$page_title = 'HOT';
	$menu_title = 'HOT';
	$capability = 'manage_options';
	$menu_slug  = 'hot_options';
	$function   = 'hot_options';
    // $icon_url   = 'plugins_url( 'efyp/images/icon.png' )';
    $position   = 11;

    $efyp_settings_page = add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, plugins_url( '' ), $position );	
}

function hot_options() {
?>
    <div class="wrap">
    <h2>HOT Helper</h2>
    <form method="post" action="" id="hot_settings">
    <input type="hidden" name="hot_options_submitted" value="submitted">
    
    <?php
    // Settings Saved Confirmation 
    
    if ( isset($_POST['hot_options_submitted'] ) && $_POST['hot_options_submitted'] == 'submitted') { 
        
        hot_get_file();

        ?>
		<div id="message" class="updated fade"><p><strong><?php _e('Your settings have been saved.', 'efyp'); ?></strong></p></div>
        <?php
	}
?>
<p><label for="server">Server Name: <input type="text" value="" size="40" /></label></p>

<p><label for="server">User Name: <input type="text" value="" size="40" /></label></p>

<p><label for="server">Password: <input type="password" value="" size="40" /></label></p>

<p><label for="server">Directory Name: <input type="text" value="" size="40" /></label></p>

<p><label for="server">Remote File: <input type="text" value="" size="40" /></label></p>

<p><label for="server">Local File: <input type="text" value="" size="40" /></label></p>
<p>PUSH BUTTON ONLY WHEN READY!</p>
<input type="submit" value="Get HOT File" />
    </div> <!--.wrap-->
    
<?php    
}

function hot_get_file() {
    echo 'Trying to connect....<br /><br />';
    $ftp_server = 'ftp2.houseoftroy.com';
    
    $conn_id = ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server"); 
    
    $ftp_user = 'public@ftp2.houseoftroy.com';
    $ftp_pass = 'WJf95#hm12';
    
    // try to login
    if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
    echo "Connected as $ftp_user@$ftp_server\n";
    } else {
    echo "Couldn't connect as $ftp_user\n";
    }
    
    // get the file list for /
    //$buff = ftp_rawlist($conn_id, '/');

    
    
    // try to change the directory to somedir
    if (ftp_chdir($conn_id, "/House of Troy/Inventory")) {
        echo "<br />Current directory is now: " . ftp_pwd($conn_id) . "\n";
        } else { 
    echo "<br />Couldn't change directory\n";
}
    //$buff2 = ftp_rawlist($conn_id, '/House of Troy/Inventory');
    // print current directory
    echo '<br />';
    echo ftp_pwd($conn_id);
    
    $remote_file = 'Houseoftroyinventory.csv';
    $local_file = '/home/efypiano/public_html/wp-content/plugins/hot-helper/inventory-today.csv';
    
    // try to download $server_file and save to $local_file
    if (ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY)) {
    echo "Successfully written to $local_file\n";
} else {
    echo "There was a problem\n";
}
    
    
    // close the connection
    ftp_close($conn_id);
    
    // output the buffer
    echo '<br />';
    //var_dump($buff);
    echo '<br />';
    //var_dump($buff2);
echo '<br /><br />';

//$csv = array_map('str_getcsv', file('/home/efypiano/gamma/wp-content/plugins/hot-helper/inventory-today.csv'));
//print_r($csv);

echo '<br /><br />**************************************';

//print_r(csv_to_array('/home/efypiano/gamma/wp-content/plugins/hot-helper/inventory-today.csv'));

//csv_to_array('/home/efypiano/gamma/wp-content/plugins/hot-helper/inventory-today.csv');

hot_query_site_products();

echo '**************************************';


$row = 1;
if (($handle = fopen("/home/efypiano/public_html/wp-content/plugins/hot-helper/inventory-today.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        
        $num = count($data);
        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
            // big test
            /*if (( $data[$c] == 'D100-AB' ) && ( $c = 2 )) {
                echo 'yep!';
            }*/
            
            
        }
    }
    fclose($handle);
}



}

function csv_to_array($filename='', $delimiter=',')
{
	if(!file_exists($filename) || !is_readable($filename))
		return FALSE;
	
	$header = NULL;
	$csv = array();
	if (($handle = fopen($filename, 'r')) !== FALSE)
	{
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
		{
			if(!$header)
				$header = $row;
			else
				$csv[] = array_combine($header, $row);
		}
		fclose($handle);
	}
	return $csv;
}
/*
 * Gives Array of House of Troy Brand Products
 *
 */
function hot_query_site_products() {
    global $post, $product, $woocommerce, $variation_id;
       
    $loop = array(
      'post_type'           => array ( 'product','product_variation' ),
      'post_status'         => 'publish',
      'nopaging' => true,
      'posts_per_page'      => 8000,
      'post_parent'         => get_the_ID(),
      'meta_key'            => $variation_id,
      'meta_query'          => array(
        array(
          'key'             => '_visibility',
          'value'       => array('catalog', 'visible'),
          'compare'         => 'IN'
        )
      ),
      'tax_query'           => array(
          array(
            'taxonomy'      => 'product_brand',
            'terms'         => 'house-of-troy',
            'field'         => 'slug',
            'operator'      => 'IN',
            'include_children' => true,
          )
      )
    );
   
    #get all products based on args for that brand
    $instore_items = new WP_Query( $loop );

    echo '<p style="text-decoration:underline;font-weight:bold;">Just a Query Only. QUERY SITE PRODUCTS. Shows all products in the HOT Brand.</p>';

        if ( $instore_items->have_posts()) :

            while ( $instore_items->have_posts()) : $instore_items->the_post();
                $theid = get_the_ID();
                $product = wc_get_product($theid);
        
                    if ( $product->is_type( 'variable') ) {
                        echo '<p>This is VARIABLE Product. Parent ID is: ' . $theid;
                        
                        $variations = $product->get_available_variations();
                        $all_variationz = array();
                        //echo '<br />Variable Parent Id: ' . $product;

                            foreach ( $variations as $variation ) {
                                $all_variationz[] = $variation['variation_id'];
                
                                $prodz = new WC_Product($variation['variation_id']);
                                $var_sku = $prodz->get_sku();
                                $the_stock[] = $var_sku;
                                $the_variation_id_and_sku[] = array('var_id' => $variation['variation_id'], 'sku' => $var_sku);
                                echo '<br /><span style="text-decoration:underline;">Var SKU:</span> ' . $var_sku . ' <span style="text-decoration:underline;">Var Id:</span> ' . $variation['variation_id'];
                
                                //more_next_steps( $the_stock_var );
                                update_post_meta( $theid->variation_id, '_var_next_avail', '12/34/56' );        
                            }
            
                        // Just dumps all variation Ids
                        //print_r($all_variationz);
            
                    } // if type variable
        
                    if( $product->get_sku() ) echo '<ol>' . $product->get_sku() . '</ol>';
                        //$the_stock = array();    
                        $chk = $product->get_sku();
            
                        if ( !empty($chk) ) {
                            $the_stock[] = $product->get_sku();
                        } 
         
            endwhile;
    
            echo '<p style="text-decoration:underline;font-weight:bold;">Variation SKUS and VAR_IDs</p>';
            print_r($the_variation_id_and_sku);
            echo '<p></p>';
    
            echo '<p style="text-decoration:underline;font-weight:bold;">The STOCK array</p>';
            print_r($the_stock);
            echo '<p>END Stock array.</p>';
     
            wp_reset_query();
        endif;
    next_steps( $the_stock, $the_variation_id_and_sku );
//return $the_stock;    
}

function next_steps( $the_stock, $the_variation_id_and_sku ) {
global $product;
    $csv = csv_to_array('/home/efypiano/public_html/wp-content/plugins/hot-helper/inventory-today.csv');
    
    echo '<br />CSV: CSV: <br />';
    print_r($csv);
    
    if ( $csv ) { echo '<p style="text-decoration:underline;font-weight:bold;">CSV exists!</p>'; }
    
    echo '<p style="text-decoration:underline;font-weight:bold;">Looping Through $stock array</p>';
    
    foreach ( $the_stock as $stock ) {
        for ($i=1;$i<count($csv);$i++) {
            if ($csv[$i][Item] == $stock ) {
                
                echo '<p>We have a Winner!!! <span style="text-decoration:underline;color:red;">Our Stock #:</span> ' . $stock . ' <span style="text-decoration:underline;color:green;">HOT Stock #:</span> ' . $csv[$i][Item] . '<br />';
                echo '<span style="text-decoration:underline;">HOT Qty on Hand:</span> ' . $csv[$i]['Quantity on Hand'] . '<br />';
                echo '<span style="text-decoration:underline;">Next Avail:</span> ' . $csv[$i]['Next Availability'] . '<br />';
                $when = $csv[$i]['Next Availability'];
                              
                $stock_id = wc_get_product_id_by_sku( $stock );
                                  
                hot_update_avail( $stock_id, $when );
                $today = date("l, F j, Y", strtotime($when));
                echo '<span style="text-decoration:underline;">Product Id: ' . $stock_id . '<br />';
                echo '<span style="text-decoration:underline;">When Avail Date: ' . $today . '<p><hr />';
            } else {
                //echo '<br />No winner';
            }
        }
    }    
    
}

//wp_get_post_parent_id( $post_ID );
function more_next_steps( $the_stock_var ) {
    
    $csv = csv_to_array('/home/efypiano/public_html/wp-content/plugins/hot-helper/inventory-today.csv');
    
    foreach ( $the_stock_var as $the_stock_va ) {
        for ($i=1;$i<count($csv);$i++) {
            if ($csv[$i][Item] == $stock ) {
              
                $when = $csv[$i]['Next Availability'];  
                
            }
            
        }
        
        
    }
    
}
function hot_next_avail() {
    global $post;
    
    //$when_avail = strtotime($when);
    //$today = date("l, F j, Y", strtotime($when));
    
    $date_from_product = get_post_meta( $post->ID, '_next_avail', true );
    
    $today = date('m/d/y');
    $today_time = strtotime($today);
    $prod_time = strtotime($date_from_product);
    
    if ( ($date_from_product) && ($prod_time > $today_time) ) {
    
    $when_available = date('l, F j, Y', strtotime($date_from_product));
        
    echo '<p style="color:maroon;font-weight:bold;">In stock on ' . $when_available . '</p>';
    
    }
}
function hot_update_avail( $stock_id, $when ) {
    global $post, $product, $woocommerce, $variation;
    
    $_product = wc_get_product( $stock_id );
    $the_parent = wp_get_post_parent_id( $stock_id );
    
    
    if ( $_product->is_type( 'simple' ) ) {
        update_post_meta( $stock_id, '_next_avail', $when );
        echo 'UPDATING SIMPLE';
    } elseif ( $_product->is_type( 'variation' )) {
        update_post_meta( $stock_id, '_var_next_avail', $when );
    }
/*        $more_args = array(
            'post_type' => array( 'product_variation', 'product' ),
            'post_in'   => $the_parent,
    
        );
    
        $loop = new WP_Query($more_args);
    
            while ($loop->have_posts() ) {
                $loop->the_post();
                $produckt = new WC_Product_Variable(get_the_ID());
                $variatiens = $produckt->get_available_variations();
                echo 'VARIATIENS: ';
                print_r($variatiens);
                //echo 'Produckt: ' . $produckt;
                    foreach ($variatiens as $variatien){
                        update_post_meta( $variatien['variation_id'], '_var_next_avail', $when );
                    }
            }
    



        update_post_meta( $stock_id['variation_id'], '_var_next_avail', $when );
        echo 'Single Var: ' . $stock_id->post_id;
        echo 'UPDATING VARIABLE. Parent ID: ' . $the_parent . 'stock id: ' . $stock_id;
    }
					
    //update_post_meta( $stock_id, '_next_avail', $when ); */
    
    return;    
} 
