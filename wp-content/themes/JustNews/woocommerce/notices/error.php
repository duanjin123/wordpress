<?php
/**
 * Show error messages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/notices/error.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $messages ) {
	return;
}
if(count($messages)>1){ ?>
<ul class="wc alert alert-danger alert-dismissible fade in">
	<?php foreach ( $messages as $message ) : ?>
		<li><?php echo wp_kses_post( $message ); ?></li>
	<?php endforeach; ?>
    <span class="close" data-dismiss="alert">&times;</span>
</ul>
<?php }else{ ?>
    <div class="wc alert alert-danger alert-dismissible fade in">
        <?php echo wp_kses_post( $messages[0] ); ?>
        <span class="close" data-dismiss="alert">&times;</span>
    </div>
<?php } ?>