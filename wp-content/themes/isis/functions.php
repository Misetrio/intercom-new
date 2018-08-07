<?php	

/**
 * isis functions and definitions
 *
 * For more information on hooks, actions, and filters, @link http://codex.wordpress.org/Plugin_API
 */

/*
 * Set up the content width value based on the theme's design.
 *
 */

if ( ! isset( $content_width ) )
	$content_width = 630;



//Load Other CSS files

function isis_other_css() { 
if ( !is_admin() ) {	
wp_enqueue_style( 'isis_other', get_template_directory_uri() . '/css/foundation.css' );
wp_enqueue_style( 'isis_other', get_template_directory_uri() . '/css/nivo-slider.css' );
wp_enqueue_style( 'isis_other', get_template_directory_uri() . '/fonts/awesome/css/font-awesome.min.css' );


}  }
add_action('wp_enqueue_scripts', 'isis_other_css');	

function isis_other1_css() { 
if ( !is_admin() ) {	
wp_enqueue_style( 'isis_other1', get_template_directory_uri() . '/css/nivo-slider.css' );
wp_enqueue_style( 'isis_other1', get_template_directory_uri() . '/fonts/awesome/css/font-awesome.min.css' );


}  }
add_action('wp_enqueue_scripts', 'isis_other1_css');	

function isis_other2_css() { 
if ( !is_admin() ) {	
wp_enqueue_style( 'isis_other2', get_template_directory_uri() . '/fonts/awesome/css/font-awesome.min.css' );


}  }
add_action('wp_enqueue_scripts', 'isis_other2_css');	



 
 



function isis_fonts_css() { 
if ( !is_admin(
) ) {
{ ?>
<?php wp_enqueue_style('customfont',get_template_directory_uri().'/fonts/'.$os_fonts = of_get_option('font_select', 'raleway' ).'.css'); }
	}
}
add_action('wp_enqueue_scripts', 'isis_fonts_css');	

//Load Custom CSS
function isis_customstyle() { ?>
<?php if(of_get_option('sldrtxt_checkbox') == "0"){ ?>
<style type="text/css">
body .nivo-caption {
	display: none!important;
}
</style>
<?php } ?>


<?php if(of_get_option('sldrtitle_checkbox') == "0"){ ?>
<style type="text/css">
.nivo-caption h3 {
	display: none!important;
}
</style>
<?php } ?>

<?php if(of_get_option('sldrdes_checkbox') == "0"){ ?>
<style type="text/css">
.nivo-caption p {
	display: none!important;
}
</style>
<?php } ?>


<style type="text/css">
/*Secondary Elements Color*/



.postitle, .postitle a,.postitle2 a, .widgettitle,.widget-title,#searchsubmit, .entry-title a, .widgettitle2, #reply-title, #comments span, .catag_list a, .lay2 h2, .nivo-caption a, .nivo-caption,.entry-title,#sub_banner h1,.content_blog .post_title a,.title h2.blue,.title h2.green ,.post_content a{
color:<?php echo of_get_option('title_colorpicker');
?>!important;
border-color:<?php echo of_get_option('title_colorpicker');
?>!important;
}
 #copyright, #navmenu ul li ul li,  #today,#menu_wrap2{
background-color:<?php echo of_get_option('menu_colorpicker');
?>!important;
}

.view a.info:hover,#navmenu ul > li ul li:hover,#submit:hover,.midbutton:hover,.midrow_blocks_wrap:hover {
background-color:<?php echo of_get_option('hover_colorpicker');
?>!important; background:<?php echo of_get_option('hover_colorpicker');?>!important;

}
.ch-info a:hover,.widget_tag_cloud a:hover,.post_info a:hover,.post_views a:hover,
.post_comments a:hover,.wp-pagenavi:hover, .alignleft a:hover, .wp-pagenavi:hover ,.alignright a:hover,.comment-form a:hover,.post_content a:hover,.port a:hover{
color:<?php echo of_get_option('hover_colorpicker');
?>!important;}

</style>
<?php }

add_action( 'wp_head', 'isis_customstyle' );



//Load Java Scripts to header
function isis_head_js() { 
if ( !is_admin() ) {
wp_enqueue_script('jquery');
wp_enqueue_script('isis_js',get_template_directory_uri().'/other2.js');
wp_enqueue_script('isis_other',get_template_directory_uri().'/js/other.js');



if(of_get_option('slider_select') == "nivo"){ wp_enqueue_script('isis_nivo',get_template_directory_uri().'/js/jquery.nivo.js');}
if(of_get_option('disslight_checkbox') == "0")
if ( is_singular() ) wp_enqueue_script( 'comment-reply' );
}
}
add_action('wp_enqueue_scripts', 'isis_head_js');

//Load Java Scripts to Footer
add_action('wp_footer', 'isis_load_js');

function isis_load_js() { ?>
<?php if(of_get_option('slider_select') == "nivo"){ ?>

<script type="text/javascript">
    jQuery(window).load(function() {
		// nivoslider init
		jQuery('#nivo').nivoSlider({
				effect: 'random',
				animSpeed:700,
				pauseTime:<?php echo of_get_option('sliderspeed_text'); ?>,
				startSlide:0,
				slices:10,
				directionNav:true,
				directionNavHide:true,
				controlNav:true,
				controlNavThumbs:false,
				keyboardNav:true,
				pauseOnHover:true,
				captionOpacity:0.8,
				afterLoad: function(){
						if (jQuery(window).width() < 480) {
					jQuery(".nivo-caption").animate({"opacity": "1", "right":"0"}, {easing:"easeOutBack", duration: 500});
						}else{
					jQuery(".nivo-caption").animate({"opacity": "1", "right":"11%"}, {easing:"easeOutBack", duration: 500});	
					jQuery(".nivo-caption").has('.sld_layout3').addClass('sld3wrap');
							}
				},
				beforeChange: function(){
					jQuery(".nivo-caption").animate({right:"-500px"}, {easing:"easeInBack", duration: 500});
					//jQuery(".nivo-caption").delay(400).removeClass('sld3wrap');
					jQuery('.nivo-caption').animate({"opacity": "0"}, 100);
					jQuery('.nivo-caption').delay(500).queue(function(next){
						jQuery(this).removeClass("sld3wrap");next();});

				},
				afterChange: function(){
						if (jQuery(window).width() < 480) {
					jQuery(".nivo-caption").animate({"opacity": "1", "right":"0"}, {easing:"easeOutBack", duration: 500});
						}else{
					jQuery(".nivo-caption").animate({"opacity": "1", "right":"11%"}, {easing:"easeOutBack", duration: 500});	
					jQuery(".nivo-caption").has('.sld_layout3').addClass('sld3wrap');	
							}
				}
			});
	});
</script>

<?php } ?>

<script type="text/javascript">
	/* <![CDATA[ */
		jQuery().ready(function() {

	jQuery('#navmenu').prepend('<div id="menu-icon"><?php _e('Menu', 'isis') ?></div>');
	jQuery("#menu-icon").on("click", function(){
		jQuery("#navmenu .menu").slideToggle();
		jQuery(this).toggleClass("menu_active");
	});

		});
	/* ]]> */
	</script>
    
<script type="text/javascript" charset="utf-8">
  
    
    
    jQuery(document).ready(function($) {
				jQuery('#work-carousel').carouFredSel({
					next : "#work-carousel-next",
					prev : "#work-carousel-prev",
					auto: false,
					circular: false,
					infinite: true,	
					width: '100%',		
					scroll: {
						items : 1					
					}		
				});
			});
  </script> 
<?php } 



 


/* isis welcome text */








//isis get the first image of the post Function
function isis_get_images($overrides = '', $exclude_thumbnail = false)
{
    return get_posts(wp_parse_args($overrides, array(
        'numberposts' => -1,
        'post_parent' => get_the_ID(),
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'order' => 'ASC',
        'exclude' => $exclude_thumbnail ? array(get_post_thumbnail_id()) : array(),
        'orderby' => 'menu_order ID'
    )));
}




//ADD FULL WIDTH BODY CLASS
add_filter( 'body_class', 'isis_fullwdth_body_class');
function isis_fullwdth_body_class( $classes ) {
     if(of_get_option('nosidebar_checkbox') == "1")
          $classes[] = 'isis_fullwdth_body';
     return $classes;
}

//Custom Excerpt Length
function isis_excerptlength_teaser($length) {
    return 30;
}
function isis_excerptlength_index($length) {
    return 12;
}
function isis_excerptmore($more) {
    return '...';
}

function isis_excerpt($length_callback='', $more_callback='') {
    global $post;
    if(function_exists($length_callback)){
        add_filter('excerpt_length', $length_callback);
    }
    if(function_exists($more_callback)){
        add_filter('excerpt_more', $more_callback);
    }
    $output = get_the_excerpt();
    $output = apply_filters('wptexturize', $output);
    $output = apply_filters('convert_chars', $output);
    $output = '<p>'.$output.'</p>';
    echo $output;
}




	


/* isis  first image */

function isis_catch_that_image() {
global $post, $posts;
$isisfirst_img = esc_url('');
ob_start();
ob_end_clean();
if(preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches)){
$isisfirst_img = $matches [1] [0];
return $isisfirst_img;
}
else {
$isisfirst_img = esc_url(get_template_directory_uri()."/images/blank1.jpg");
return $isisfirst_img;
}
}

//Custom Excerpt Length
function excerpt($limit_isis) {
  $excerpt = explode(' ', get_the_excerpt(), $limit_isis);
  if (count($excerpt)>=$limit_isis) {
    array_pop($excerpt);
    $excerpt = implode(" ",$excerpt).'...';
  } else {
    $excerpt = implode(" ",$excerpt);
  }	
  $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
  return $excerpt;
}
 
function content($limit_isis) {
  $content = explode(' ', get_the_content(), $limit_isis);
  if (count($content)>=$limit_isis) {
    array_pop($content);
    $content = implode(" ",$content).'...';
  } else {
    $content = implode(" ",$content);
  }	
  $content = preg_replace('/\[.+\]/','', $content);
  $content = apply_filters('the_content', $content); 
  $content = str_replace(']]>', ']]&gt;', $content);
  return $content;
}


//SIDEBAR
function isis_widgets_init(){
	register_sidebar(array(
	'name'          => __('Right Sidebar', 'isis'),
	'id'            => 'sidebar',
	'description'   => __('Right Sidebar', 'isis'),
	'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget_wrap">',
	'after_widget'  => '</div></div>',
	'before_title'  => '<h3 class="widgettitle">',
	'after_title'   => '</h3>'
	));
	
	register_sidebar(array(
	'name'          => __('Footer Widgets', 'isis'),
	'id'            => 'foot_sidebar',
	'description'   => __('Widget Area for the Footer', 'isis'),
	'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget_wrap">',
	'after_widget'  => '</div></div>',
	'before_title'  => '<h3 class="widgettitle">',
	'after_title'   => '</h3>'
	));



 
	
	
}

add_action( 'widgets_init', 'isis_widgets_init' );








//**************isis SETUP******************//
function isis_setup() {
//Custom Background
add_theme_support( 'custom-background', array(
	'default-color' => '',
	'default-image' => get_template_directory_uri() . ''
) );

add_theme_support('automatic-feed-links');

//Post Thumbnail	
   add_theme_support( 'post-thumbnails' );
   
   
//Register Menus
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation(Header)', 'isis' ),
		
	) );

 // Enables post and comment RSS feed links to head
    add_theme_support('automatic-feed-links');


// Localisation Support
    load_theme_textdomain('isis', get_template_directory() . '/languages');
	
	add_theme_support( "title-tag" );
        
    
/*
 * Loads the Options Panel
 *
 * If you're loading from a child theme use stylesheet_directory
 * instead of template_directory
 */

define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/admin/' );
require_once dirname( __FILE__ ) . '/admin/options-framework.php';




}
add_action( 'after_setup_theme', 'isis_setup' );


?>
<?php 






