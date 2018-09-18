<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $product;
$product_images_class  	= woodmart_product_images_class();
$product_summary_class 	= woodmart_product_summary_class();
$single_product_class  	= woodmart_single_product_class();
$content_class 			= woodmart_get_content_class();
$product_design 		= woodmart_product_design();

$container_summary = 'container';

if( woodmart_get_opt( 'single_full_width' ) ) {
	$container_summary = 'container-fluid';
}


?>

<?php if ( $product_design == 'alt' ): ?>
	<div class="single-breadcrumbs-wrapper">
		<div class="container">
			<?php woocommerce_breadcrumb(); ?>
			<?php if ( woodmart_get_opt( 'products_nav' ) ): ?>
				<?php woodmart_products_nav(); ?>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>

<div class="container">
	<?php
		/**
		 * woocommerce_before_single_product hook
		 *
		 * @hooked wc_print_notices - 10
		 */
		 do_action( 'woocommerce_before_single_product' );
		 if ( post_password_required() ) {
		 	echo get_the_password_form();
		 	return;
		 }

	?>
</div>
<div id="product-<?php the_ID(); ?>" <?php post_class( $single_product_class ); ?>>

	<div class="<?php echo esc_attr( $container_summary ); ?>">
		<div class="single-breadcrumbs" style="padding-left: 1rem;">
			<?php woocommerce_breadcrumb(); ?>
		</div>
		<div class="row product-image-summary-wrap">
			<div class="product-image-summary col-md-12 col-sm-12">
				<div class="row product-image-summary-inner">
					<div class="col-sm-6 col-xs-12 product-images">
						<div class="product-images-inner">
							<?php
								/**
								 * woocommerce_before_single_product_summary hook
								 *
								 * @hooked woocommerce_show_product_sale_flash - 10
								 * @hooked woocommerce_show_product_images - 20
								 */
								do_action( 'woocommerce_before_single_product_summary' );
							?>
						</div>
					</div>
					<div class="<?php echo esc_attr( $product_summary_class ); ?> summary entry-summary">
						<div class="summary-inner">
							<h1 class="product_title entry-title"><?php echo get_the_title(); ?></h1>
							<?php echo woocommerce_template_single_rating(); ?>

							<ul class="nav nav-tabs my-3" id="single-product-tabs" role="tablist">
							  <li class="nav-item">
							    <a class="nav-link active" id="product-simple" data-toggle="tab" href="#simple" role="tab" aria-controls="simple" aria-selected="true">Simple</a>
							  </li>
								<?php if($) ?>
							  <li class="nav-item">
							    <a class="nav-link" id="product-bulk" data-toggle="tab" href="#bulk" role="tab" aria-controls="bulk" aria-selected="false">Bulk</a>
							  </li>
							  <li class="nav-item">
							    <a class="nav-link" id="product-custom" data-toggle="tab" href="#custom" role="tab" aria-controls="custom" aria-selected="false">Custom</a>
							  </li>
							</ul>
							<div class="tab-content" id="product-content">
							  <div class="tab-pane fade show active" id="simple" role="tabpanel" aria-labelledby="product-simple">
									<?php
										/**
										 * woocommerce_single_product_summary hook
										 * @hooked woocommerce_template_single_rating - 10
										 * @hooked woocommerce_template_single_price - 15
										 * @hooked woocommerce_template_single_add_to_cart - 30
										 * @hooked woocommerce_template_single_excerpt - 31
										 */
										do_action( 'woocommerce_single_product_summary' );
									?>
								</div>
							  <div class="tab-pane fade" id="bulk" role="tabpanel" aria-labelledby="product-bulk">
									<?php do_action( 'iww_bulk_tab' ); ?>
								</div>
							  <div class="tab-pane fade" id="custom" role="tabpanel" aria-labelledby="product-custom">
									<?php do_action( 'iww_custom_tab' ); ?>
								</div>
							</div>
						</div>
					</div>
				</div><!-- .summary -->
			</div>

			<?php
				/**
				 * woocommerce_sidebar hook
				 *
				 * @hooked woocommerce_get_sidebar - 10
				 */
				do_action( 'woocommerce_sidebar' );
			?>

		</div>
		<?php
			/**
			 * woodmart_after_product_content hook
			 *
			 * @hooked woodmart_product_extra_content - 20
			 */
			do_action( 'woodmart_after_product_content' );
			?>

	</div>
	<div class="container-fluid">
		<?php do_action( 'iww_cross_sell' ); ?>
	</div>


	<div class="product-tabs-wrapper">
		<div class="container">
			<div class="row">
				<div class="col-sm-12 poduct-tabs-inner">
					<?php
						/**
						 * woocommerce_after_single_product_summary hook
						 *
						 * @hooked woocommerce_output_product_data_tabs - 10
						 * @hooked woocommerce_upsell_display - 15
						 * @hooked woocommerce_output_related_products - 20
						 */
						do_action( 'woocommerce_after_single_product_summary' );
					?>
				</div>
			</div>
		</div>
	</div>

	<?php
		do_action( 'woodmart_after_product_tabs' );
	?>

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
