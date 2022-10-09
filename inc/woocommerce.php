<?php
/**
 * Add WooCommerce support
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'understrap_woocommerce_support' );
if ( ! function_exists( 'understrap_woocommerce_support' ) ) {
	/**
	 * Declares WooCommerce theme support.
	 */
	function understrap_woocommerce_support() {
		add_theme_support( 'woocommerce' );

		// Add Product Gallery support.
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-slider' );

		// Add Bootstrap classes to form fields.
		add_filter( 'woocommerce_form_field_args', 'understrap_wc_form_field_args', 10, 3 );
		add_filter( 'woocommerce_quantity_input_classes', 'understrap_quantity_input_classes' );
		add_filter( 'woocommerce_loop_add_to_cart_args', 'understrap_loop_add_to_cart_args' );

		// Wrap the add-to-cart link in `div.add-to-cart-container`.
		add_filter( 'woocommerce_loop_add_to_cart_link', 'understrap_loop_add_to_cart_link' );

		// Add Bootstrap classes to account navigation.
		add_filter( 'woocommerce_account_menu_item_classes', 'understrap_account_menu_item_classes' );
	}
}

// First unhook the WooCommerce content wrappers.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

// Then hook in your own functions to display the wrappers your theme requires.
add_action( 'woocommerce_before_main_content', 'understrap_woocommerce_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'understrap_woocommerce_wrapper_end', 10 );

if ( ! function_exists( 'understrap_woocommerce_wrapper_start' ) ) {
	/**
	 * Display the theme specific start of the page wrapper.
	 */
	function understrap_woocommerce_wrapper_start() {
		$container = get_theme_mod( 'understrap_container_type' );
		if ( false === $container ) {
			$container = '';
		}

		echo '<div class="wrapper" id="woocommerce-wrapper">';
		echo '<div class="' . esc_attr( $container ) . '" id="content" tabindex="-1">';
		echo '<div class="row">';
		get_template_part( 'global-templates/left-sidebar-check' );
		echo '<main class="site-main" id="main">';
	}
}

if ( ! function_exists( 'understrap_woocommerce_wrapper_end' ) ) {
	/**
	 * Display the theme specific end of the page wrapper.
	 */
	function understrap_woocommerce_wrapper_end() {
		echo '</main>';
		get_template_part( 'global-templates/right-sidebar-check' );
		echo '</div><!-- .row -->';
		echo '</div><!-- .container(-fluid) -->';
		echo '</div><!-- #woocommerce-wrapper -->';
	}
}

if ( ! function_exists( 'understrap_wc_form_field_args' ) ) {
	/**
	 * Filter hook function monkey patching form classes
	 * Author: Adriano Monecchi http://stackoverflow.com/a/36724593/307826
	 *
	 * @param array<string,mixed> $args  Form field arguments.
	 * @param string              $key   Value of the fields name attribute.
	 * @param string|null         $value Value of <select> option.
	 *
	 * @return array<string,mixed> Form field arguments.
	 */
	function understrap_wc_form_field_args( $args, $key, $value = null ) {
		$bootstrap4 = 'bootstrap4' === get_theme_mod( 'understrap_bootstrap_version', 'bootstrap4' );

		// Add margin to each form field's html element wrapper (<p></p>).
		if ( $bootstrap4 ) {
			$args['class'][] = 'form-group';
		}
		$args['class'][] = 'mb-3';

		// Start field type switch case.
		switch ( $args['type'] ) {
			// Targets all select input type elements, except the country and state select input types.
			case 'select':
				// Add a class to the form input itself.
				$args['input_class'][] = 'form-control';
				// Add custom data attributes to the form input itself.
				$args['custom_attributes'] = array(
					'data-plugin'      => 'select2',
					'data-allow-clear' => 'true',
					'aria-hidden'      => 'true',
				);
				break;

			/*
			 * By default WooCommerce will populate a select with the country names - $args
			 * defined for this specific input type targets only the country select element.
			 */
			case 'country':
				$args['class'][] = 'single-country';
				break;

			/*
			 * By default WooCommerce will populate a select with state names - $args defined
			 * for this specific input type targets only the country select element.
			 */
			case 'state':
				$args['custom_attributes'] = array(
					'data-plugin'      => 'select2',
					'data-allow-clear' => 'true',
					'aria-hidden'      => 'true',
				);
				break;
			case 'textarea':
				$args['input_class'][] = 'form-control';
				break;
			case 'checkbox':
					// Wrap the label in <span> tag.
					$args['label'] = isset( $args['label'] ) ? '<span class="custom-control-label">' . $args['label'] . '<span>' : '';
					// Add a class to the form input's <label> tag.
					$args['label_class'][] = 'custom-control custom-checkbox';
					$args['input_class'][] = 'custom-control-input';
				break;
			case 'radio':
				$args['label_class'][] = 'custom-control custom-radio';
				$args['input_class'][] = 'custom-control-input';
				break;
			default:
				$args['input_class'][] = 'form-control';
				break;
		} // End of switch ( $args ).
		return $args;
	}
}

if ( ! is_admin() && ! function_exists( 'wc_review_ratings_enabled' ) ) {
	/**
	 * Check if reviews are enabled.
	 *
	 * Function introduced in WooCommerce 3.6.0., include it for backward compatibility.
	 *
	 * @return bool
	 */
	function wc_reviews_enabled() {
		return 'yes' === get_option( 'woocommerce_enable_reviews' );
	}

	/**
	 * Check if reviews ratings are enabled.
	 *
	 * Function introduced in WooCommerce 3.6.0., include it for backward compatibility.
	 *
	 * @return bool
	 */
	function wc_review_ratings_enabled() {
		return wc_reviews_enabled() && 'yes' === get_option( 'woocommerce_enable_review_rating' );
	}
}

if ( ! function_exists( 'understrap_quantity_input_classes' ) ) {
	/**
	 * Add Bootstrap class to quantity input field.
	 *
	 * @param array $classes Array of quantity input classes.
	 * @return array
	 */
	function understrap_quantity_input_classes( $classes ) {
		$classes[] = 'form-control';
		return $classes;
	}
}

if ( ! function_exists( 'understrap_loop_add_to_cart_link' ) ) {
	/**
	 * Wrap add to cart link in container.
	 *
	 * @param string $html Add to cart link HTML.
	 * @return string Add to cart link HTML.
	 */
	function understrap_loop_add_to_cart_link( $html ) {
		return '<div class="add-to-cart-container">' . $html . '</div>';
	}
}

if ( ! function_exists( 'understrap_loop_add_to_cart_args' ) ) {
	/**
	 * Add Bootstrap button classes to add to cart link.
	 *
	 * @param array<string,mixed> $args Array of add to cart link arguments.
	 * @return array<string,mixed> Array of add to cart link arguments.
	 */
	function understrap_loop_add_to_cart_args( $args ) {
		if ( isset( $args['class'] ) && ! empty( $args['class'] ) ) {
			if ( ! is_string( $args['class'] ) ) {
				return $args;
			}

			// Remove the `button` class if it exists.
			if ( false !== strpos( $args['class'], 'button' ) ) {
				$args['class'] = explode( ' ', $args['class'] );
				$args['class'] = array_diff( $args['class'], array( 'button' ) );
				$args['class'] = implode( ' ', $args['class'] );
			}

			$args['class'] .= ' btn btn-outline-primary';
		} else {
			$args['class'] = 'btn btn-outline-primary';
		}

		if ( 'bootstrap4' === get_theme_mod( 'understrap_bootstrap_version', 'bootstrap4' ) ) {
			$args['class'] .= ' btn-block';
		}

		return $args;
	}
}

if ( ! function_exists( 'understrap_account_menu_item_classes' ) ) {
	/**
	 * Add Bootstrap classes to the
	 *
	 * @param string[] $classes Array of classes added to the account menu items.
	 * @return string[] Array of classes added to the account menu items.
	 */
	function understrap_account_menu_item_classes( $classes ) {
		$classes[] = 'list-group-item';
		$classes[] = 'list-group-item-action';
		if ( in_array( 'is-active', $classes, true ) ) {
			$classes[] = 'active';
		}
		return $classes;
	}
}
