<?php

add_filter( 'woocommerce_enqueue_styles', '__return_false' );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

add_action( 'wp_enqueue_scripts', 'wpcom_woo_scripts' );
function wpcom_woo_scripts(){
    // 载入主样式
    if (function_exists('WC')) {
        wp_enqueue_style('wpcom-woo', get_template_directory_uri() . '/css/woocommerce.css', array(), THEME_VERSION);
        wp_enqueue_style('wpcom-woo-smallscreen', get_template_directory_uri() . '/css/woocommerce-smallscreen.css', array(), THEME_VERSION, 'only screen and (max-width: 768px)');
    }
}

add_filter('woocommerce_format_sale_price', 'woo_format_sale_price', 10, 3);
function woo_format_sale_price($price, $regular_price, $sale_price ) {
    $price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del>' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';
    return $price;
}

add_filter( 'woocommerce_product_get_rating_html', 'woo_product_get_rating_html', 10, 2 );
function woo_product_get_rating_html($rating_html, $rating){
    if($rating<=0){
        $rating_html  = '<div class="star-rating"></div>';
    }
    return $rating_html;
}

add_action( 'wpcom_woo_cart_icon', 'wpcom_woo_cart_icon' );
function wpcom_woo_cart_icon() {
    global $options;
    if ( isset($options['show_cart']) && $options['show_cart']=='1' && function_exists('WC') ) {
        $count = WC()->cart->cart_contents_count;
        ?>
        <div class="shopping-cart woocommerce">
            <a class="cart-contents fa fa-shopping-cart" href="<?php echo wc_get_cart_url(); ?>">
                <?php if ( $count > 0 ) { ?>
                    <span class="shopping-count"><?php echo esc_html( $count ); ?></span>
                <?php } ?>
            </a>
        </div>
    <?php
    }
}

/**
 * Ensure cart contents update when products are added to the cart via AJAX
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'wpcom_woo_icon_add_to_cart_fragment' );
function wpcom_woo_icon_add_to_cart_fragment( $fragments ) {
    ob_start();
    $count = WC()->cart->cart_contents_count;
    ?>
    <a class="cart-contents fa fa-shopping-cart" href="<?php echo wc_get_cart_url(); ?>">
        <?php if ( $count > 0 ) { ?>
            <span class="shopping-count"><?php echo esc_html( $count ); ?></span>
        <?php } ?>
    </a>

    <?php
    $fragments['a.cart-contents'] = ob_get_clean();

    return $fragments;
}

add_filter( 'woocommerce_product_reviews_tab_title', 'wpcom_reviews_tab_title' );
function wpcom_reviews_tab_title( ) {
    global $product;
    return sprintf( __( 'Reviews (%d)', 'wpcom' ), $product->get_review_count() );
}

add_filter( 'woocommerce_checkout_fields' , 'wpcom_override_checkout_fields', 10, 1 );
function wpcom_override_checkout_fields( $fields ) {
    unset( $fields['billing']['billing_last_name'] );
    unset( $fields['billing']['billing_company'] );
    unset( $fields['billing']['billing_address_2'] );
    unset( $fields['billing']['billing_postcode'] );
    unset($fields['billing']['billing_email']);

    // billing address order
    $billing_order = array(
        "billing_first_name",
        "billing_phone",
        "billing_country",
        "billing_state",
        "billing_city",
        "billing_address_1",
    );

    $i=1;
    $billing_ordered_fields = array();
    foreach($billing_order as $field) {
        $fields["billing"][$field]['priority'] = $i*10;
        $billing_ordered_fields[$field] = $fields["billing"][$field];
        $i++;
    }

    $fields["billing"] = $billing_ordered_fields;
    $fields['billing']['billing_first_name']['label'] = __('Name', 'wpcom');

    return $fields;
}

add_filter( 'woocommerce_billing_fields', 'wpcom_woo_address_to_edit', 10, 2);
function wpcom_woo_address_to_edit( $address, $country ) {
    if($country=='CN') {
        unset($address['billing_last_name']);
        unset($address['billing_company']);
        unset($address['billing_address_2']);
        unset($address['billing_postcode']);
        unset($address['billing_email']);

        // billing address order
        $billing_order = array(
            "billing_first_name",
            "billing_phone",
            "billing_country",
            "billing_state",
            "billing_city",
            "billing_address_1",
        );

        $i = 1;
        $billing_ordered_fields = array();
        foreach ($billing_order as $field) {
            $address[$field]['priority'] = $i * 10;
            $billing_ordered_fields[$field] = $address[$field];
            $i++;
        }

        $address = $billing_ordered_fields;
        $address['billing_first_name']['label'] = __('Name', 'wpcom');
        $address['billing_address_1']['placeholder'] = __('Address', 'wpcom');
    }
    return $address;
}

add_filter( 'woocommerce_get_country_locale', 'woocommerce_default_address_fields_reorder', 10, 1 );
function woocommerce_default_address_fields_reorder( $fields ) {
    $fields['CN']['state']['priority'] = 50;
    $fields['CN']['state']['label'] = __('Province', 'wpcom');
    $fields['CN']['city']['priority'] = 60;
    $fields['CN']['city']['label'] = __('City', 'wpcom');
    $fields['CN']['address_1']['priority'] = 70;
    $fields['CN']['address_2']['priority'] = 80;

    return $fields;
}


add_filter('woocommerce_localisation_address_formats', 'wpcom_woo_address_formats');
function wpcom_woo_address_formats($format){
    $format['CN'] = "{name}, {phone}\n{state}, {city}, {address_1}";
    return $format;
}

add_filter( 'woocommerce_formatted_address_replacements', 'wpcom_woo_formatted_address_replacements', 10, 2 );
function wpcom_woo_formatted_address_replacements($formatted_address, $arg){
    $formatted_address['{phone}'] = isset($arg['phone'])?$arg['phone']:'';
    return $formatted_address;
}

add_filter( 'woocommerce_account_menu_items', 'wpcom_woo_account_menu_items' );
function wpcom_woo_account_menu_items( $items ){
    if(function_exists('um_user')){
        $items['orders'] = __('Orders', 'wpcom');
        $items['downloads'] = __('Downloads', 'wpcom');
        $items['edit-address'] = __('Addresses', 'wpcom');
        unset($items['dashboard']);
        unset($items['edit-account']);
        unset($items['customer-logout']);
    }
    return $items;
}

add_filter('loop_shop_columns', 'wpcom_woo_shop_columns');
function wpcom_woo_shop_columns(){
    global $options;
    return isset($options['shop_list_col']) && $options['shop_list_col'] ? $options['shop_list_col'] : 4;
}

add_filter( 'body_class', 'wpcom_woo_body_class' );
function wpcom_woo_body_class( $classes ){
    if(!function_exists('is_woocommerce')) return $classes;
    global $options;
    $classes = (array) $classes;
    $class = '';
    if(is_singular( 'product' )) {
        $class = isset($options['related_col']) && $options['related_col'] ? 'columns-'.$options['related_col'] : 'columns-4';
    }else if(is_post_type_archive( 'product' ) || is_woocommerce()){
        $class = isset($options['shop_list_col']) && $options['shop_list_col'] ? 'columns-'.$options['shop_list_col'] : 'columns-4';
    }
    $classes[] = $class;
    return $classes;
}

add_filter( 'woocommerce_output_related_products_args', 'wpcom_woo_related_products_args');
function wpcom_woo_related_products_args( $args ){
    global $options;
    $args['columns'] = isset($options['related_col']) ? $options['related_col'] : 4;
    $args['posts_per_page'] = isset($options['related_posts_per_page']) ? $options['related_posts_per_page'] : 4;
    return $args;
}

add_filter( 'loop_shop_per_page', 'wpcom_woo_shop_per_page');
function wpcom_woo_shop_per_page( $posts ){
    global $options;
    return isset($options['shop_posts_per_page']) ? $options['shop_posts_per_page'] : $posts;
}

remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
add_action( 'woocommerce_archive_description', 'wpcom_woo_archive_description', 10 );
function wpcom_woo_archive_description(){
    if ( is_search() ) {
        return;
    }

    if ( is_post_type_archive( 'product' ) ) {
        $shop_page   = get_post( wc_get_page_id( 'shop' ) );
        if ( $shop_page ) {
            $description = wc_format_content( $shop_page->post_content );
            if ( $description ) {
                echo '<div class="page-description">' . $description . '</div>';
            }
        }
    }
}

add_filter( 'woocommerce_is_account_page', 'wpcom_wc_is_account_page' );
function wpcom_wc_is_account_page(){
    global $ultimatemember;
    if( isset($ultimatemember) && is_page($ultimatemember->permalinks->core[ 'account' ]))
        return true;
}

add_action( 'after_setup_theme', 'wpcom_woo_um_api' );
function wpcom_woo_um_api(){
    $show_profile = apply_filters( 'um_show_profile', true );
    if(!$show_profile && function_exists('is_woocommerce')){
        add_filter( 'um_account_page_default_tabs_hook', 'wpcom_um_add_tabs', 20 );
        add_filter( 'woocommerce_get_myaccount_page_permalink', 'wpcom_woo_myaccount_page_permalink' );
        add_action( 'um_account_tab__orders', 'um_account_tab__orders' );
        add_action( 'um_account_tab__downloads', 'um_account_tab__downloads' );
        add_action( 'um_account_tab__edit-address', 'um_account_tab__address' );
        add_action( 'um_account_tab__view-order', 'um_account_tab__view_order' );
        add_action( 'woocommerce_before_edit_account_address_form', 'wc_print_notices', 10 );
    }
}

function wpcom_um_add_tabs( $tabs ){
    $tabs[120] = array(
        'orders' => array(
            'icon' => 'fa fa-shopping-cart',
            'title' => __('Orders', 'wpcom'),
            'custom' => 1
        )
    );
    $tabs[140] = array(
        'downloads' => array(
            'icon' => 'fa fa-cloud-download',
            'title' => __('Downloads', 'wpcom'),
            'custom' => 1
        )
    );
    $tabs[160] = array(
        'edit-address' => array(
            'icon' => 'fa fa-map-marker',
            'title' => __('Addresses', 'wpcom'),
            'custom' => 1
        )
    );
    $tabs[10000] = array(
        'view-order' => array(
            'title' => '',
            'custom' => 1,
        )
    );
    return $tabs;
}

function um_account_tab__orders( $info ) {
    $um_tab = get_query_var('um_tab');
    if($um_tab=='orders') {
        $page = get_query_var('um_val');
    }
    $page = isset($page) && $page ? $page : 1;
    extract( $info );
    ?>

    <div class="um-account-heading uimob340-hide uimob500-hide"><i class="<?php echo $icon; ?>"></i><?php echo $title; ?></div>

    <div class="um-col-alt um-col-alt-b">
        <?php do_action( 'woocommerce_account_orders_endpoint', $page ); ?>
    </div>
<?php
}

function um_account_tab__downloads( $info ) {
    extract( $info );
    ?>

    <div class="um-account-heading uimob340-hide uimob500-hide"><i class="<?php echo $icon; ?>"></i><?php echo $title; ?></div>

    <div class="um-col-alt um-col-alt-b">
        <?php do_action( 'woocommerce_account_downloads_endpoint' ); ?>
    </div>
<?php
}

function um_account_tab__address( $info ) {
    extract( $info );
    ?>

    <div class="um-account-heading uimob340-hide uimob500-hide"><i class="<?php echo $icon; ?>"></i><?php echo $title; ?></div>

    <div class="um-col-alt um-col-alt-b woocommerce">
        <?php do_action( 'woocommerce_account_edit-address_endpoint', 'billing' ); ?>
    </div>
<?php
}

function um_account_tab__view_order( $info ) {
    $um_tab=get_query_var('um_tab');
    if($um_tab && $um_tab=='view-order') {
        $order_id = get_query_var('um_val');
        extract($info);
        ?>
        <?php woocommerce_order_details_table($order_id);?>
    <?php
    }
}

function wpcom_woo_myaccount_page_permalink( $link ){
    if(function_exists('um_edit_profile_url')){
        $link = um_get_core_page('account');
    }
    return $link;
}
