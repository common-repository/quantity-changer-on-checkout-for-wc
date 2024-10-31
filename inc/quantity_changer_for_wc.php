<?php
/**
 * Quantity Changer On Checkout For WC
 *
 * @package Frontend
 * @version 1.0
 */
class Quantity_Changer_On_Checkout_For_WC {

  /**
  * Construct for the class
  *
  * @param empty
  * @return mixed
  *
  */
  public function __construct() {
    //Check plugin dependency
    add_action( 'admin_notices', array($this, 'check_plugin_dependency' ) );

    //Remove checkout quantity text
    add_filter ('woocommerce_checkout_cart_item_quantity', array($this, 'remove_checkout_quantity_text'), 10, 2 );

    //Add quantity inputs and add remove item in the checkout page
    add_filter ('woocommerce_cart_item_name', array($this, 'checkout_add_quantity_inputs') , 10, 3 );

    //Add plugin js
    add_action('wp_footer', array($this, 'checkout_quntity_js'), 10);

    //Add ajax function for quantity update  
    add_action('wp_ajax_nopriv_order_update_quantity', array($this, 'order_update_quantity'));
    
    //Add ajax function for quantity update 
    add_action('wp_ajax_order_update_quantity', array($this, 'order_update_quantity'));

  }


  /**
  * @uses Checks plugin dependency
  *
  * @since 1.0
  *
  * @param Empty
  * @return Bool
  *
  */
  public function check_plugin_dependency() {
    if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

      echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Quantity Changer On CheckOut For WooCommerce requires WooCommerce to be installed and active. You can download %s here.', 'quantity_changer_on_checkout_for_wc' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';

        deactivate_plugins( '/quantity-changer-on-checkout-for-wc/quantity-changer-on-checkout-for-wc.php' );
    } 
  }


  /**
  * @uses Remove checkout quantity
  *
  * @since 1.0
  *
  * @param Object $cart_item  | Cart Object
  * @param String $cart_item_key  | Cart item key
  * @return bool
  *
  */
  public function remove_checkout_quantity_text($cart_item, $cart_item_key) {
    $product_quantity = '';
    return $product_quantity;
  }


  /**
  * @uses Adds quantity inputs and remove product button in the checkout page
  *
  * @since 1.0
  *
  * @param String $product_title  | Product Title
  * @param Object $cart_item  | Cart item object
  * @param String $cart_item_key  | Cart item key string
  * @return mixed
  *
  */
  public function checkout_add_quantity_inputs($product_title, $cart_item, $cart_item_key) {

    if( is_checkout() ) {
      $cart = WC()->cart->get_cart();

      if( is_array($cart) && !empty($cart) ) {
        foreach( $cart as $cart_key => $cart_value ) {
          if( $cart_key == $cart_item_key ) {
            $product_id = $cart_item['product_id'];
            $product = $cart_item['data'];

            //Add Delete Icon
            $return = sprintf(
              '<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                  esc_url( WC()->cart->get_remove_url( $cart_key ) ),
                  __( 'Remove this item', 'quantity_changer_on_checkout_for_wc' ),
                  esc_attr( $product_id ),
                  esc_attr( $product->get_sku() )
            );

            //Add Product Name
            $return .= '&nbsp; <span class = "product_name" >' . $product_title . '</span>' ;

            //Add Quantity Selector
            if ( $product->is_sold_individually() ) {
              $return .= sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_key );
            } else {
              $return .= woocommerce_quantity_input( array(
                      'input_name'  => "cart[{$cart_key}][qty]",
                      'input_value' => $cart_item['quantity'],
                      'max_value'   => $product->backorders_allowed() ? '' : $product->get_stock_quantity(),
                      'min_value'   => '1'
                      ), $product, false );
            }
          }
        }
      }
    }
    else {
      $product = $cart_item['data'];
      $product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';

      if( !$product_permalink ) {
        $return = $product->get_title() . '&nbsp;';
      }
      else {
        $return = sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $product->get_title());

      }
    }
    return $return;
  }


  /**
  * @uses Adds js for quantity changer
  *
  * @since 1.0
  *
  * @param empty
  * @return mixed
  *
  */
  public function checkout_quntity_js() {
    if( is_checkout() ) {
      wp_enqueue_script( 'qcfwc_checkout_script', plugins_url( '/assets/js/quantity-changer.js', __FILE__ ), array('jquery'), '1.0.0', true );

      wp_localize_script('qcfwc_checkout_script', 'QCFWC', 
        array(
          'ajax_url'    => admin_url( 'admin-ajax.php' ),
          "ajax_nonce"  => wp_create_nonce('order_update_quantity_nonce'),
        )
      );
    }
  }


  /**
  * @uses Updates product quantity with price
  *
  * @since 1.0
  *
  * @param empty
  * @return mixed
  *
  */
  public function order_update_quantity() {
    
    if ( ! wp_doing_ajax() ) {
      wp_die();
    }

    check_ajax_referer( 'order_update_quantity_nonce', 'security' );

    $values = array();
    wp_parse_str($_POST['post_data'], $values);
    $cart = isset($values['cart']) ? $values['cart'] : array();

    if( is_array($cart) && !empty($cart) ) {
      foreach ( $cart as $cart_key => $cart_value ){
        WC()->cart->set_quantity( $cart_key, $cart_value['qty'], false );
        WC()->cart->calculate_totals();
        woocommerce_cart_totals();
      }
    }
    wp_die();
  }

}