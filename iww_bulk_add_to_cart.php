<?php
/*
Plugin Name: iWantWorkwear Bulk Add to Cart
Plugin URI:
Description: Allows for bulk purchasing of products; easy.
Version: 0.1
Author: iWantWorkwear
Author URI: http:dev.iwantworkwear.com
*/
// TODO: Bulk add to cart, cart page variations
add_action( 'wp_enqueue_scripts', 'iww_bulk_add_scripts', 99);

function iww_bulk_add_scripts(){
	wp_enqueue_script( 'iww_bulk_add', plugins_url( 'iww_bulk_add_to_cart.js', __FILE__ ), array('jquery'), 1.07 );
}

add_filter( 'woocommerce_before_calculate_totals', 'custom_cart_items_prices', 10, 1 );
function custom_cart_items_prices( $cart_object ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    // Iterating through cart items
    foreach ( $cart_object->get_cart() as $cart_item ) {

        // Get an instance of the WC_Product object
        $wc_product = $cart_item['data'];

        // Get the product name (WooCommerce versions 2.5.x to 3+)
        $nicename = ( !empty( get_post_meta( $wc_product->get_id(), 'wccaf_nice_name' ) ) ) ? get_post_meta( $wc_product->get_id(), 'wccaf_nice_name' ) : $wc_product->get_name();

        // SET THE NEW NAME
        $new_name = $nicename[0];

        // Set the new name (WooCommerce versions 2.5.x to 3+)
        if( method_exists( $wc_product, 'set_name' ) )
            $wc_product->set_name( $new_name );
        else
            $wc_product->post->post_title = $new_name;
    }
}

add_action( 'iww_bulk_tab', 'iww_bulk_form' );

function iww_bulk_form(){
	global $product;
	if( $product->is_type( 'variable' ) ){
		$child_vars = [];
		$children = $product->get_children();
		foreach( $children as $id ){
			$child = wc_get_product( $id );
			$child_vars[$id] = array(
				'id' => $id,
				'sku' => $child->get_sku(),
				'attr_str' => wc_get_formatted_variation( $child->get_variation_attributes(), true, false, true ),
				'price' => number_format((float)$child->get_price(), 2, '.', ''),
				'instock' => $child->get_stock_status(),
				'stock' => $child->get_stock_quantity(),
			);
		}
		?>
		<img src="" />
		<div id="current_discount"></div>
		<div>
			<div id="var_bulk_form">
				<?php
				// TODO: Make price show discount go up or down
				//<span class=" pl-2 price iww-bulk-price" style="font-size: 18px">$' . $var['price'] . ' </span>
				foreach( $child_vars as $var ){
					if( $var['instock'] == 'instock' ){
						echo '<div class="row">';
						echo '<div class="col-3"><input id="'. $var['id'] .'" type="number" min="0" placeholder="0" class="my-2 var-bulk-update qty" /></div>';
						echo '<div class="col-9 my-2">' . $var['attr_str'] . '<br><label class="d-inline">SKU: ' . $var['sku'] . '<span class="d-none iww-base-price">' . $var['price'] . ' </span></span></label>';
						echo '<label>Stock: '. $var['stock'].'</label>';
						echo '</div>';
						echo '</div>';
					}
				}
				?>
			</div>
			<a href="#" id="iww_bulk_form_submit" class="single_add_to_cart_button button alt w-100 my-2">Bulk Add to Cart</a>
		</div>
		<?php
	}
}

add_action( 'woocommerce_after_add_to_cart_button', 'iww_bulk_discount', 99  );

add_action( 'wp_ajax_ajax_discount_ranges', 'ajax_discount_ranges' );
add_action( 'wp_ajax_nopriv_ajax_discount_ranges', 'ajax_discount_ranges' );

/* get product type and fill table header accordingly, takes in $product->get_type() */

function ajax_discount_ranges(){
	$id = $_POST['id'];
	$ranges = array(
		get_post_meta( $id, '_bulkdiscount_quantity_1', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_2', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_3', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_4', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_5', true ),
	);
	echo json_encode( $ranges );
}

function get_discount_ranges( $id ){
	$ranges = array(
		get_post_meta( $id, '_bulkdiscount_quantity_1', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_2', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_3', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_4', true ),
		get_post_meta( $id, '_bulkdiscount_quantity_5', true ),
	);
	// var_dump( get_post_meta( $id ) );
		?>
		<th id="range_0"><?php if( ! empty( $ranges[0] ) ) echo '1 - ' . ( $ranges[0] - 1 ); ?></th>
		<th id="range_1"><?php if( ! empty( $ranges[1] ) ) echo $ranges[0] . ' - ' . ( $ranges[1] - 1 ); ?></th>
		<th id="range_2"><?php if( ! empty( $ranges[2] ) ) echo $ranges[1] . ' - ' . ( $ranges[2] - 1 ); ?></th>
		<th id="range_3"><?php if( ! empty( $ranges[2] ) ) echo $ranges[2] . '+' ?></th>
		<?php
}

function iww_bulk_discount(){
	global $product;
	$type = $product->get_type();
	// TODO: conflict with show_per_unit
		// var_dump( get_post_meta( $product->get_id() ) );

		$id = $product->get_id();
		?>
		<div id="iww-price-table" class="iww-price-table">
			<h3 class="my-2">Bulk Pricing:</h3>
			<table class="table table-bordered">
				<thead class="price-ranges">
					<?php get_discount_ranges( $id ); ?>
				</thead>
				<tbody>
	<?php

	if( $product->is_type( 'variable' ) ){
		// variable
			?>
			<script>

			function fill_table_headers( $ ){
				var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		    var id = '<?php echo $product->get_id(); ?>';
				var ranges = new Array();
		    var data = {
					'action'   : 'ajax_discount_ranges',
					'id'		   : id,
					'dataType' : 'json'
				};
				$.post(ajaxurl, data, function(response){
					console.log(id, response);

					var ranges = response.match( /\d+/g );
					$('#range_0').text('1 - ' + (+ranges[0] - 1) );
					$('#range_1').text((ranges[0]) + ' - ' + (+ranges[1] - 1) );
					$('#range_2').text((ranges[1]) + ' - ' + (+ranges[2] - 1) );
					$('#range_3').text(ranges[2] + '+' );
				});

		  }

			jQuery( document ).ready( function( $ ){
				$( 'form' ).on( 'change', 'select', debounce( function() {
					var options = $( 'table.variations select' );
					var ready = [];
					var price;

					// check the values of each of the selections
					options.each( function() {
						ready.push( this.value.length );
					});

					// all selections have been made
					if ( ready.every( readyCheck ) ){
						// return table via js
						var table_header = fill_table_headers( $ );

						<?php if ( $product->get_variation_price('min') != $product->get_variation_price('max') ) : ?>
						// TODO: DIY, find a way to check if on sale once
							<?php if( ! $product->is_on_sale() ) : ?>
								price = $( '.woocommerce-variation-price' ).find( '.price' ).find( 'span.amount' ).text().replace('$', ''); // find price where woocommerce puts variation prices
							<?php else : ?>
								price = $( '.woocommerce-variation-price' ).find( '.price' ).find('ins').find( 'span.amount' ).text().replace('$', ''); // find price where woocommerce puts variation prices
							<?php endif; ?>

							$( '#price_range' ).css( 'height', '0px' ); // hide range

						<?php else : ?>

							<?php if( ! $product->is_on_sale() ) : ?>
								price = $( '#price_range' ).find( 'span.iww-price' ).text().replace('$', '');
							<?php else : ?>
								price = $( '#price_range' ).find( 'span.iww-price' ).find('ins').text().replace('$', '');
							<?php endif; ?>

						<?php endif; ?>


						$( '.iww-no-discount' ).html( price ); // make a copy of price for the quantity change

						// fill table - discount( price, discount ) - Pass the percent off for discount
						$('.iww-price-table tbody').html(`
						    <tr>
									<td class="price-tier-0 table-success click-price">$${ price }</td>
						      <td class="price-tier-1 click-price">${ discount( price, 5 ) }</td>
									<td class="price-tier-2 click-price">${ discount( price, 10 ) }</td>
									<td class="price-tier-3 click-price">${ discount( price, 15 ) }</td>
						    </tr>`
							);

						if( price.length ){
							$( '#iww-price-table' ).show(); // table display set to none, show when ready
						}

					}
				} , 250, false ) );
			});
			</script>
			<?php
	} else {
		?>
		<script>
		// show price table
			document.getElementById('iww-price-table').style.display = 'block';
		</script>
		<?php
		$price = $product->get_price();
		$count = 1;
		$discount = 1;
		?>
		<tr>
			<?php
			// fill table body
			echo '<td class="price-tier-0  click-price">'. get_woocommerce_currency_symbol() . number_format( $price, 2 ) . '</td>';
			while( $count <= 3 ){
				$discount = $discount - .05;
				$new_price = ( $price * $discount );
				echo '<td class="price-tier-' . $count . ' click-price">' . get_woocommerce_currency_symbol() . number_format( $new_price, 2 ) . '</td>';
				$count++;
			}
			?>
		</tr>
		<?
	}
	?>
	</tbody>
			</table>
		</div>
		<?php
}

function woocommerce_maybe_add_multiple_products_to_cart( $url = false ) {
	// Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
	if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
		return;
	}

	// Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
	remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

	$product_ids = explode( ',', $_REQUEST['add-to-cart'] );
	$count       = count( $product_ids );
	$number      = 0;

	foreach ( $product_ids as $id_and_quantity ) {
		// Check for quantities defined in curie notation (<product_id>:<product_quantity>)
		// https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/#comment-12236
		$id_and_quantity = explode( ':', $id_and_quantity );
		$product_id = $id_and_quantity[0];

		$_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;

		if ( ++$number === $count ) {
			// Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
			$_REQUEST['add-to-cart'] = $product_id;

			return WC_Form_Handler::add_to_cart_action( $url );
		}

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			continue;
		}

		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		// Variable product handling
		if ( 'variable' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );

		// Grouped Products
		} elseif ( 'grouped' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );

		// Custom Handler
		} elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
			do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

		// Simple Products
		} else {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
		}
	}
}

// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'woocommerce_maybe_add_multiple_products_to_cart', 15 );


/**
 * Invoke class private method
 *
 * @since   0.1.0
 *
 * @param   string $class_name
 * @param   string $methodName
 *
 * @return  mixed
 */
function woo_hack_invoke_private_method( $class_name, $methodName ) {
	if ( version_compare( phpversion(), '5.3', '<' ) ) {
		throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
	}

	$args = func_get_args();
	unset( $args[0], $args[1] );
	$reflection = new ReflectionClass( $class_name );
	$method = $reflection->getMethod( $methodName );
	$method->setAccessible( true );

	$args = array_merge( array( $class_name ), $args );
	return call_user_func_array( array( $method, 'invoke' ), $args );
}
