<?php

class Baidu_map{
    public function __construct( $html='', $pos='', $scrollWheelZoom=0 ) {
        $this->html = $html;
        $this->pos = $pos ? $pos : '116.403963,39.915119';
        $this->scrollWheelZoom = $scrollWheelZoom;
        $rand1 = rand(100,999);
        $rand2 = rand(1000,9999);
        $rand3 = rand(10000,99999);
        $this->id = 'map-'.$rand1.$rand2.$rand3; //随机数ID避免重复
    }

    private function load_scripts(){
        global $options;
        $ak = isset($options['baidu_map_ak']) && $options['baidu_map_ak'] ? $options['baidu_map_ak'] : '0RisxUuPqPSBMWjZQ24ROEch4TQFrQXE';
        wp_enqueue_script( 'map', '//api.map.baidu.com/api?v=2.0&ak='.$ak, array('main'), '', true );
    }

    public function init_script(){
        echo '<script>baidu_map(\''.$this->id.'\', \''.addslashes($this->html).'\', ['.strip_tags($this->pos).'], '.$this->scrollWheelZoom.');</script>'."\r\n";
    }

    public function init_map(){
        $this->load_scripts();
        add_action('wp_footer', array($this, 'init_script'), 100);
        return '<div id="'.$this->id.'" style="height: 100%;"></div>';
    }
}

function baidu_map($html='', $pos='', $scrollWheelZoom=0, $echo=true){
    $map = new Baidu_map($html, $pos, $scrollWheelZoom);
    if($echo){
        echo $map->init_map();
    }else{
        return $map->init_map();
    }
}




