<?php
/*
Plugin Name: SyntaxHighlighter++
Plugin URI: http://leo108.com/pid-1304.asp
Description: 支持Bash/shell, C#, C++, CSS, Delphi, Diff, Groovy, JavaScript, Java, Perl, PHP, Plain Text, Python, Ruby, Scala, SQL, Visual Basic and XML等语言，并在编辑器下方增加一个代码输入框，直接将相关代码贴入编辑器中。
Version: 2.5.0
Author: leo108
Author URI: http://leo108.com/
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
function highlighter_init() {
    $plugin_dir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain( 'sh', false , $plugin_dir.'/lang' );
}
function highlighter_activate() {
    $options['highlighter_style'] = "Default";
    $options['highlighter_tagName'] = "pre";
    $options['highlighter_autolinks'] = "true";
    $options['highlighter_collapse'] = "false";
    $options['highlighter_firstline'] = 1;
    $options['highlighter_gutter'] = "true";
    $options['highlighter_smarttabs'] = "true";
    $options['highlighter_tabsize'] = 4;
    $options['highlighter_toolbar'] = "true";
    add_option( 'highlighter_options', $options );
}
function highlighter_footer() {
    $options = get_option( 'highlighter_options' );
    $current_path = get_option('siteurl') .'/wp-content/plugins/' . basename(dirname(__FILE__)) .'/';
    ?>
    <script type="text/javascript" src="<?php echo $current_path; ?>scripts/shCore.js"></script>
    <script type="text/javascript" src="<?php echo $current_path; ?>scripts/shAutoloader.js"></script>
    <script type="text/javascript">
    function path()
    {
      var args = arguments,
          result = []
          ;
      for(var i = 0; i < args.length; i++)
          result.push(args[i].replace('@', '<?php echo $current_path; ?>scripts/'));
      return result
    };
    SyntaxHighlighter.autoloader.apply(null, path(
      'applescript            @shBrushAppleScript.js',
      'actionscript3 as3      @shBrushAS3.js',
      'bash shell             @shBrushBash.js',
      'coldfusion cf          @shBrushColdFusion.js',
      'cpp c                  @shBrushCpp.js',
      'c# c-sharp csharp      @shBrushCSharp.js',
      'css                    @shBrushCss.js',
      'delphi pascal          @shBrushDelphi.js',
      'diff patch pas         @shBrushDiff.js',
      'erl erlang             @shBrushErlang.js',
      'groovy                 @shBrushGroovy.js',
      'java                   @shBrushJava.js',
      'jfx javafx             @shBrushJavaFX.js',
      'js jscript javascript  @shBrushJScript.js',
      'perl pl                @shBrushPerl.js',
      'php                    @shBrushPhp.js',
      'text plain             @shBrushPlain.js',
      'ps                     @shBrushPowerShell.js',
      'py python              @shBrushPython.js',
      'ruby rails ror rb      @shBrushRuby.js',
      'sass scss              @shBrushSass.js',
      'scala                  @shBrushScala.js',
      'sql                    @shBrushSql.js',
      'vb vbnet               @shBrushVb.js',
      'xml xhtml xslt html    @shBrushXml.js',
      'other                  @shBrushOther.js'
    ));
    SyntaxHighlighter.defaults['auto-links'] = <?php echo $options['highlighter_autolinks']?$options['highlighter_autolinks']:'true';?>;
    SyntaxHighlighter.defaults['collapse'] = <?php echo $options['highlighter_collapse']?$options['highlighter_collapse']:'false';?>;
    SyntaxHighlighter.defaults['first-line'] = <?php echo $options['highlighter_firstline']?$options['highlighter_firstline']:'0';?>;
    SyntaxHighlighter.defaults['gutter'] = <?php echo $options['highlighter_gutter']?$options['highlighter_gutter']:'true';?>;
    SyntaxHighlighter.defaults['smart-tabs'] = <?php echo $options['highlighter_smarttabs']?$options['highlighter_smarttabs']:'true';?>;
    SyntaxHighlighter.defaults['tab-size'] = <?php echo $options['highlighter_tabsize']?$options['highlighter_tabsize']:'4';?>;
    SyntaxHighlighter.defaults['toolbar'] = <?php echo $options['highlighter_toolbar']?$options['highlighter_toolbar']:'true';?>;
    SyntaxHighlighter.config.tagName = "<?php echo $options['highlighter_tagName']?$options['highlighter_tagName']:'pre';?>";
	SyntaxHighlighter.config.clipboardSwf = '<?php echo $current_path; ?>scripts/clipboard.swf';
    SyntaxHighlighter.all();
    </script>
    <?php
}
function highlighter_head() {
    $options = get_option( 'highlighter_options' );
    $current_path = get_option('siteurl') .'/wp-content/plugins/' . basename(dirname(__FILE__)) .'/';
    ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $current_path."styles/shCore".$options['highlighter_style'].".css"; ?>" />
    <?php
}
function codebox_init(){
    $options = get_option( 'highlighter_options' );
?>
<div id="codebox" class="meta-box-sortables ui-sortable" style="position: relative;"><div class="postbox">
<div class="handlediv" title="Click to toggle"></div>
<h3 class="hndle"><span><?php echo __('Syntax-highlighter++','sh'); ?></span></h3>
<div class="inside">
<?php echo __('Language:','sh'); ?>
<select id="language">
    <option value="other"><?php echo __('Other','sh'); ?></option>
    <option value="applescript">AppleScript</option>
    <option value="as3">AS3</option>
    <option value="bash">Bash</option>
    <option value="c">C</option>
    <option value="cpp">C++</option>
    <option value="csharp">C#</option>
    <option value="cf">ColdFusion</option>
    <option value="css">CSS</option>
    <option value="delphi">Delphi</option>
    <option value="diff">Diff</option>
    <option value="erl">Erlang</option>
    <option value="groovy">Groovy</option>
    <option value="php">HTML</option>
    <option value="java">Java</option>
    <option value="jfx">JavaFX</option>
    <option value="javascript">Javascript</option>
    <option value="perl">Perl</option>
    <option value="php">PHP</option>
    <option value="plain">Plain</option>
    <option value="ps">PowerShell</option>
    <option value="python">Python</option>
    <option value="ruby">Ruby</option>
    <option value="sass">Sass</option>
    <option value="scala">Scala</option>
    <option value="sql">SQL</option>
    <option value="vb">VisualBasic</option>
    <option value="vb">VB.NET</option>
    <option value="xml">XML</option>
</select>
<br>
<?php echo __('Code:','sh'); ?><br><textarea id="code" rows="8" cols="70" style="width:97%;"></textarea><br>
<input type="button" value="<?php echo __('Insert','sh'); ?>" onclick="javascript:settext();">

<script>
function settext()
{
    var str='<<?php echo $options['highlighter_tagName']?$options['highlighter_tagName']:'pre';?> class="brush:';
    var lang=document.getElementById("language").value;
    var code=document.getElementById("code").value;
    str=str+lang;
    str=str+'">';
    str=str+filter(code)+"</<?php echo $options['highlighter_tagName']?$options['highlighter_tagName']:'pre';?>><p>&nbsp;</p>";
    var win = window.dialogArguments || opener || parent || top;
    if((typeof win.send_to_editor) != "undefined") {
        win.send_to_editor(str);
    } else if((typeof CKEDITOR.instances.content.insertHtml) != "undefined") {
        CKEDITOR.instances.content.insertHtml(str);
    } else if((typeof KindEditor.instances[0].insertHtml) != "undefined") {
        KindEditor.instances[0].insertHtml(str);
    } else if((typeof UE.instants.ueditorInstant0.execCommand) != "undefined") {
        UE.instants.ueditorInstant0.execCommand("insertHtml",str);
    } else {
        alert("<?php echo __('This plugin can not insert code to your editor','sh'); ?>");
    }
    document.getElementById("code").value="";
}
function filter (str) {
    str = str.replace(/&/g, '&amp;');
    str = str.replace(/</g, '&lt;');
    str = str.replace(/>/g, '&gt;');
    str = str.replace(/'/g, '&#39;');
    str = str.replace(/"/g, '&quot;');
//    str = str.replace(/\|/g, '&brvbar;');
    return str;
}
</script>
</div></div></div>
<script>
    var dom = document.getElementById("postdivrich");
    //bugfix, only display in article edit page
    if(dom){
        dom.appendChild(document.getElementById("codebox"));
    }else{
        document.getElementById("codebox").remove();
    }
</script>
<?php
}
function highlighter_options_page() {
        $options = get_option( 'highlighter_options' );
?>
    <div class="wrap">
        <h2><?php echo __('Syntax-highlighter++','sh'); ?></h2>
        <div class="narrow">
            <form action="options.php" method="post">
                <p><?php echo __('SyntaxHighlighter is a fully functional self-contained code syntax highlighter developed in JavaScript.','sh'); ?></p>
                <?php settings_fields('highlighter_options'); ?>
                <?php do_settings_sections('highlighter'); ?>
                <p class="submit">
                    <input name="submit" type="submit" class="button-primary" value="<?php echo __('Save Changes','sh') ?>" />
                </p>
            </form>
        </div>
    </div>
<?php
}
add_action('admin_init', 'highlighter_admin_init');
function highlighter_admin_init(){
    register_setting( 'highlighter_options', 'highlighter_options', 'highlighter_options_validate' );
    add_settings_section('highlighter_main', __('Settings','sh'), 'highlighter_section', 'highlighter');
    add_settings_field('style', __('style','sh'), 'highlighter_style', 'highlighter', 'highlighter_main');
    add_settings_field('tagName', __('tagName','sh'), 'highlighter_tagName', 'highlighter', 'highlighter_main');
    add_settings_field('autolinks', __('autolinks','sh'), 'highlighter_autolinks', 'highlighter', 'highlighter_main');
    add_settings_field('collapse', __('collapse','sh'), 'highlighter_collapse', 'highlighter', 'highlighter_main');
    add_settings_field('firstline', __('firstline','sh'), 'highlighter_firstline', 'highlighter', 'highlighter_main');
    add_settings_field('gutter', __('gutter','sh'), 'highlighter_gutter', 'highlighter', 'highlighter_main');
    add_settings_field('smarttabs', __('smarttabs','sh'), 'highlighter_smarttabs', 'highlighter', 'highlighter_main');
    add_settings_field('tabsize', __('tabsize','sh'), 'highlighter_tabsize', 'highlighter', 'highlighter_main');
    add_settings_field('toolbar', __('toolbar','sh'), 'highlighter_toolbar', 'highlighter', 'highlighter_main');
}
function highlighter_section() {
    echo __('<p>Please enter your config.</p>','sh');
}
function highlighter_style()
{
$options = get_option( 'highlighter_options' );
?>
    <select name="highlighter_options[highlighter_style]" id="highlighter_style" />
        <option value="Default" <?php if("Default"==$options['highlighter_style']) echo "selected='selected'"; ?>>Default</option>
        <option value="Django" <?php if("Django"==$options['highlighter_style']) echo "selected='selected'"; ?>>Django</option>
        <option value="Emacs" <?php if("Emacs"==$options['highlighter_style']) echo "selected='selected'"; ?>>Emacs</option>
        <option value="FadeToGrey" <?php if("FadeToGrey"==$options['highlighter_style']) echo "selected='selected'"; ?>>FadeToGrey</option>
        <option value="Midnight" <?php if("Midnight"==$options['highlighter_style']) echo "selected='selected'"; ?>>Midnight</option>
        <option value="RDark" <?php if("RDark"==$options['highlighter_style']) echo "selected='selected'"; ?>>RDark</option>
    </select><br />
<?php
}
function highlighter_tagName()
{
$options = get_option( 'highlighter_options' );
?>
<input type="text" name="highlighter_options[highlighter_tagName]" id="highlighter_tagName" value="<?php echo $options['highlighter_tagName']; ?>" /><br />
<?php
}
function highlighter_autolinks()
{
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_autolinks]" id="highlighter_autolinks" />
    <option value="true" <?php if("true"==$options['highlighter_autolinks']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_autolinks']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_collapse()
{
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_collapse]" id="highlighter_collapse" />
    <option value="true" <?php if("true"==$options['highlighter_collapse']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_collapse']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_firstline()
{
$options = get_option( 'highlighter_options' );
?>
 <input type="text" name="highlighter_options[highlighter_firstline]" id="highlighter_firstline" value="<?php echo $options['highlighter_firstline']; ?>" /><br />
<?php
}
function highlighter_gutter()
{
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_gutter]" id="highlighter_gutter" />
    <option value="true" <?php if("true"==$options['highlighter_gutter']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_gutter']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_smarttabs()
{
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_smarttabs]" id="highlighter_smarttabs" />
    <option value="true" <?php if("true"==$options['highlighter_smarttabs']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_smarttabs']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_tabsize()
{
$options = get_option( 'highlighter_options' );
?>
 <input type="text" name="highlighter_options[highlighter_tabsize]" id="highlighter_tabsize" value="<?php echo $options['highlighter_tabsize']; ?>" /><br />
<?php
}
function highlighter_toolbar()
{
$options = get_option( 'highlighter_options' );
?>
<select name="highlighter_options[highlighter_toolbar]" id="highlighter_toolbar" />
    <option value="true" <?php if("true"==$options['highlighter_toolbar']) echo "selected='selected'"; ?>>Yes</option>
    <option value="false" <?php if("false"==$options['highlighter_toolbar']) echo "selected='selected'"; ?>>No</option>
</select><br />
<?php
}
function highlighter_options_validate($input) {
    $input['highlighter_tagName'] = $input['highlighter_tagName'] ? $input['highlighter_tagName'] : "pre";
    $input['highlighter_firstline'] = is_int($input['highlighter_firstline']) ? $input['highlighter_firstline'] : 1;
    $input['highlighter_tabsize'] = is_int($input['highlighter_tabsize']) ? $input['highlighter_tabsize'] : 4;
    return $input;
}
function highlighter_menu() {
    add_options_page('Syntax-highlighter++ Settings', __('Syntax-highlighter++','sh'), 'manage_options', 'Syntaxhighlighterpp', 'highlighter_options_page');
}
function highlighter_action_links( $links, $file ) {
    if ( $file != plugin_basename( __FILE__ ))
        return $links;
    $settings_link = '<a href="options-general.php?page=Syntaxhighlighterpp">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
register_activation_hook(__FILE__, 'highlighter_activate');
add_action( 'init' , 'highlighter_init');
add_filter( 'plugin_action_links', 'highlighter_action_links',10,2);
add_action('admin_menu','highlighter_menu');
add_action('dbx_post_sidebar','codebox_init');
add_action('wp_footer','highlighter_footer');
add_action('wp_head','highlighter_head');
?>
