<?php
$cat_options = get_option('_category_'.$cat);
$tpl = isset($cat_options['tpl']) ? $cat_options['tpl'] : get_option('cat_tpl_'.$cat);

if ( $tpl && locate_template('cat-tpl-' . $tpl . '.php') != '' ) {
    get_template_part( 'cat-tpl', $tpl );
} else {
    get_template_part( 'cat-tpl', 'default' );
}