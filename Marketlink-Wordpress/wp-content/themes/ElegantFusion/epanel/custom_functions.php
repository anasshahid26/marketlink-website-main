<?php
if ( ! function_exists( 'get_custom_header' ) ) {
	// compatibility with versions of WordPress prior to 3.4.
	add_custom_background();
} else {
	add_theme_support( 'custom-background', apply_filters( 'et_custom_background_args', array() ) );
}

if (function_exists('add_post_type_support')) add_post_type_support( 'page', 'excerpt' );
add_theme_support( 'automatic-feed-links' );

add_action('init','et_activate_features');
function et_activate_features(){
	/* activate shortcodes */
	require_once(TEMPLATEPATH . '/epanel/shortcodes/shortcodes.php');

	/* activate page templates */
	require_once(TEMPLATEPATH . '/epanel/page_templates/page_templates.php'); 

	/* import epanel settings */
	require_once(TEMPLATEPATH . '/epanel/import_settings.php'); 
}
	
add_filter('widget_text', 'do_shortcode');
add_filter('the_excerpt', 'do_shortcode');

if ( ! function_exists( 'et_options_stored_in_one_row' ) ){
	function et_options_stored_in_one_row(){
		global $et_store_options_in_one_row;
		
		return isset( $et_store_options_in_one_row ) ? (bool) $et_store_options_in_one_row : false; 
	}
}

if ( ! function_exists( 'et_get_option' ) ){
	function et_get_option( $option_name, $default_value = '' ){
		global $et_theme_options, $shortname;

		if ( et_options_stored_in_one_row() ){
			$et_theme_options_name = 'et_' . $shortname;
			
			if ( ! isset( $et_theme_options ) ) $et_theme_options = get_option( $et_theme_options_name );
			$option_value = isset ( $et_theme_options[$option_name] ) ? $et_theme_options[$option_name] : false;
		} else {
			$option_value = get_option( $option_name );
		}
		
		if ( !$option_value && '' != $default_value ) $option_value = $default_value;
		
		return $option_value;
	}
}

if ( ! function_exists( 'et_update_option' ) ){
	function et_update_option( $option_name, $new_value ){
		global $et_theme_options, $shortname;
		
		if ( et_options_stored_in_one_row() ){
			$et_theme_options_name = 'et_' . $shortname;
			
			if ( ! isset( $et_theme_options ) ) $et_theme_options = get_option( $et_theme_options_name );
			$et_theme_options[$option_name] = $new_value;
			
			$option_name = $et_theme_options_name;
			$new_value = $et_theme_options;
		}
		
		update_option( $option_name, $new_value );
	}
}

if ( ! function_exists( 'et_delete_option' ) ){
	function et_delete_option( $option_name ){
		global $et_theme_options, $shortname;
		
		if ( et_options_stored_in_one_row() ){
			$et_theme_options_name = 'et_' . $shortname;
			
			if ( ! isset( $et_theme_options ) ) $et_theme_options = get_option( $et_theme_options_name );
			
			unset( $et_theme_options[$option_name] );
			update_option( $et_theme_options_name, $et_theme_options );
		} else {
			delete_option( $option_name );
		}
	}
}

add_filter('body_class','et_browser_body_class');
function et_browser_body_class($classes) {
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	if($is_lynx) $classes[] = 'lynx';
	elseif($is_gecko) $classes[] = 'gecko';
	elseif($is_opera) $classes[] = 'opera';
	elseif($is_NS4) $classes[] = 'ns4';
	elseif($is_safari) $classes[] = 'safari';
	elseif($is_chrome) $classes[] = 'chrome';
	elseif($is_IE) $classes[] = 'ie';
	else $classes[] = 'unknown';

	if($is_iphone) $classes[] = 'iphone';
	return $classes;
}

/*this function allows for the auto-creation of post excerpts*/
if ( ! function_exists( 'truncate_post' ) ){
	function truncate_post($amount,$echo=true,$post='') {
		global $shortname;
		
		if ( $post == '' ) global $post;
			
		$postExcerpt = '';
		$postExcerpt = apply_filters( 'the_excerpt', $post->post_excerpt );
		
		if (et_get_option($shortname.'_use_excerpt') == 'on' && $postExcerpt <> '') { 
			if ($echo) echo $postExcerpt;
			else return $postExcerpt;	
		} else {
			$truncate = $post->post_content;
			
			$truncate = preg_replace('@\[caption[^\]]*?\].*?\[\/caption]@si', '', $truncate);
			
			if ( strlen($truncate) <= $amount ) $echo_out = ''; else $echo_out = '...';
			$truncate = apply_filters('the_content', $truncate);
			$truncate = preg_replace('@<script[^>]*?>.*?</script>@si', '', $truncate);
			$truncate = preg_replace('@<style[^>]*?>.*?</style>@si', '', $truncate);
			
			$truncate = sanitize_text_field( $truncate ); 
			
			if ($echo_out == '...') $truncate = substr($truncate, 0, strrpos(substr($truncate, 0, $amount), ' '));
			else $truncate = substr($truncate, 0, $amount);

			if ($echo) echo $truncate,$echo_out;
			else return ($truncate . $echo_out);
		};
	}
}


/*this function truncates titles to create preview excerpts*/
if ( ! function_exists( 'truncate_title' ) ){
	function truncate_title($amount,$echo=true,$post='') {
		if ( $post == '' ) $truncate = get_the_title(); 
		else $truncate = $post->post_title; 
		if ( strlen($truncate) <= $amount ) $echo_out = ''; else $echo_out = '...';
		$truncate = mb_substr( $truncate, 0, $amount, 'UTF-8' );
		if ($echo) {
			echo $truncate;
			echo $echo_out;
		}
		else { return ($truncate . $echo_out); }
	}
}


/*this function allows users to use the first image in their post as their thumbnail*/
if ( ! function_exists( 'et_first_image' ) ){
	function et_first_image() {
		global $post;
		$img = '';
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		if ( isset($matches[1][0]) ) $img = $matches[1][0];

		return trim($img);
	}
}


/* this function gets thumbnail from Post Thumbnail or Custom field or First post image */
if ( ! function_exists( 'get_thumbnail' ) ){
	function get_thumbnail($width=100, $height=100, $class='', $alttext='', $titletext='', $fullpath=false, $custom_field='', $post='')
	{
		if ( $post == '' ) global $post;
		global $shortname;
		
		$thumb_array['thumb'] = '';
		$thumb_array['use_timthumb'] = true;
		if ($fullpath) $thumb_array['fullpath'] = ''; //full image url for lightbox
		
		$new_method = true;
		
		if ( has_post_thumbnail( $post->ID ) && !( '' != $custom_field && get_post_meta( $post->ID, $custom_field, true ) ) ) {
			$thumb_array['use_timthumb'] = false;
			
			$et_fullpath =  wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			$thumb_array['fullpath'] =  $et_fullpath[0];
			$thumb_array['thumb'] = $thumb_array['fullpath'];
		}

		if ($thumb_array['thumb'] == '') {
			if ($custom_field == '') $thumb_array['thumb'] = esc_attr( get_post_meta($post->ID, 'Thumbnail', $single = true) );
			else {
				$thumb_array['thumb'] = esc_attr( get_post_meta($post->ID, $custom_field, $single = true) );
				if ($thumb_array['thumb'] == '') $thumb_array['thumb'] = esc_attr( get_post_meta($post->ID, 'Thumbnail', $single = true) );
			}
			
			if (($thumb_array['thumb'] == '') && ((et_get_option($shortname.'_grab_image')) == 'on')) { 
				$thumb_array['thumb'] = esc_attr( et_first_image() );
				if ( $fullpath ) $thumb_array['fullpath'] = $thumb_array['thumb'];
			}
			
			#if custom field used for small pre-cropped image, open Thumbnail custom field image in lightbox
			if ($fullpath) {
				$thumb_array['fullpath'] = $thumb_array['thumb'];
				if ($custom_field == '') $thumb_array['fullpath'] = apply_filters('et_fullpath', et_path_reltoabs(esc_attr($thumb_array['thumb'])));
				elseif ( $custom_field <> '' && get_post_meta($post->ID, 'Thumbnail', $single = true) ) $thumb_array['fullpath'] = apply_filters( 'et_fullpath', et_path_reltoabs(esc_attr(get_post_meta($post->ID, 'Thumbnail', $single = true))) );
			}
		}
		
		return $thumb_array;
	}
}

/* this function prints thumbnail from Post Thumbnail or Custom field or First post image */
if ( ! function_exists( 'print_thumbnail' ) ){
	function print_thumbnail($thumbnail = '', $use_timthumb = true, $alttext = '', $width = 100, $height = 100, $class = '', $echoout = true, $forstyle = false, $resize = true, $post='') {
		global $shortname;
		if ( $post == '' ) global $post;
		
		$output = '';
		$thumbnail_orig = $thumbnail;
		
		$thumbnail = et_multisite_thumbnail( $thumbnail );
		
		$cropPosition = '';
		
		$allow_new_thumb_method = false;
		
		$new_method = true;
		$new_method_thumb = '';
		$external_source = false;
			
		$allow_new_thumb_method = !$external_source && $new_method && $cropPosition == '';
		
		if ( $allow_new_thumb_method && $thumbnail <> '' ){
			$et_crop = get_post_meta( $post->ID, 'et_nocrop', true ) == '' ? true : false; 
			$new_method_thumb =  et_resize_image( et_path_reltoabs($thumbnail), $width, $height, $et_crop );
			if ( is_wp_error( $new_method_thumb ) ) $new_method_thumb = '';
		}
		
		if ($forstyle === false) {
			$output = '<img src="' . esc_url( $new_method_thumb ) . '"';
			
			if ($class <> '') $output .= " class='" . esc_attr( $class ) . "' ";
			
			$dimensions = apply_filters( 'et_print_thumbnail_dimensions', " width='" . esc_attr( $width ) . "' height='" .esc_attr( $height ) . "'" );

			$output .= " alt='" . esc_attr( strip_tags( $alttext ) ) . "'{$dimensions} />";
			
			if (!$resize) $output = $thumbnail;
		} else {
			$output = $new_method_thumb;
		}
		
		if ($echoout) echo $output;
		else return $output;
	}
}

if ( ! function_exists( 'et_new_thumb_resize' ) ){
	function et_new_thumb_resize( $thumbnail, $width, $height, $alt='', $forstyle = false ){
		global $shortname;
			
		$new_method = true;
		$new_method_thumb = '';
		$external_source = false;
			
		$allow_new_thumb_method = !$external_source && $new_method;
		
		if ( $allow_new_thumb_method && $thumbnail <> '' ){
			$et_crop = true;
			$new_method_thumb = et_resize_image( $thumbnail, $width, $height, $et_crop );
			if ( is_wp_error( $new_method_thumb ) ) $new_method_thumb = '';
		}
		
		$thumb = esc_attr( $new_method_thumb );
		
		$output = '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $alt ) . '" width =' . esc_attr( $width ) . ' height=' . esc_attr( $height ) . ' />';
		
		return ( !$forstyle ) ? $output : $thumb;
	}
}

if ( ! function_exists( 'et_multisite_thumbnail' ) ){
	function et_multisite_thumbnail( $thumbnail = '' ) {
		// do nothing if it's not a Multisite installation or current site is the main one
		if ( is_main_site() ) return $thumbnail;
		
		# get the real image url
		preg_match( '#([_0-9a-zA-Z-]+/)?files/(.+)#', $thumbnail, $matches );
		if ( isset( $matches[2] ) ){
			$file = rtrim( BLOGUPLOADDIR, '/' ) . '/' . str_replace( '..', '', $matches[2] );
			if ( is_file( $file ) ) $thumbnail = str_replace( ABSPATH, trailingslashit( get_site_url( 1 ) ), $file );
			else $thumbnail = '';
		}

		return $thumbnail;
	}
}

if ( ! function_exists( 'et_is_portrait' ) ){
	function et_is_portrait($imageurl, $post='', $ignore_cfields = false){
		if ( $post == '' ) global $post;
		
		if ( get_post_meta($post->ID,'et_disable_portrait',true) == 1 ) return false;
		
		if ( !$ignore_cfields ) {
			if ( get_post_meta($post->ID,'et_imagetype',true) == 'l' ) return false;
			if ( get_post_meta($post->ID,'et_imagetype',true) == 'p' ) return true;
		}
		
		$imageurl = et_path_reltoabs(et_multisite_thumbnail($imageurl));
		
		$et_thumb_size = @getimagesize($imageurl);
		if ( empty($et_thumb_size) ) {
			$et_thumb_size = @getimagesize( str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $imageurl ) );
			if ( empty($et_thumb_size) ) return false;
		}
		$et_thumb_width = $et_thumb_size[0];
		$et_thumb_height = $et_thumb_size[1];
		
		$result = ($et_thumb_width < $et_thumb_height) ? true : false;
		
		return $result;
	}
}

if ( ! function_exists( 'et_path_reltoabs' ) ){
	function et_path_reltoabs( $imageurl ){
		if ( strpos(strtolower($imageurl), 'http://') !== false || strpos(strtolower($imageurl), 'https://') !== false ) return $imageurl;
		
		if ( strpos( strtolower($imageurl), $_SERVER['HTTP_HOST'] ) !== false )
			return $imageurl;
		else {
			$imageurl = esc_url( apply_filters( 'et_path_relative_image', site_url() . '/' ) . $imageurl );
		}
		
		return $imageurl;
	}
}

if ( ! function_exists( 'in_subcat' ) ){
	function in_subcat($blogcat,$current_cat='') {
		$in_subcategory = false;
		
		if (cat_is_ancestor_of($blogcat,$current_cat) || $blogcat == $current_cat) $in_subcategory = true;
			
		return $in_subcategory;
	}
}

if ( ! function_exists( 'show_page_menu' ) ){
	function show_page_menu($customClass = 'nav clearfix', $addUlContainer = true, $addHomeLink = true){
		global $shortname, $themename, $exclude_pages, $strdepth, $page_menu, $is_footer;
		
		//excluded pages
		if (et_get_option($shortname.'_menupages') <> '') $exclude_pages = implode(",", et_get_option($shortname.'_menupages'));
		
		//dropdown for pages
		$strdepth = '';
		if (et_get_option($shortname.'_enable_dropdowns') == 'on') $strdepth = "depth=".et_get_option($shortname.'_tiers_shown_pages');
		if ($strdepth == '') $strdepth = "depth=1";
		
		if ($is_footer) { $strdepth="depth=1"; $strdepth2 = $strdepth; }
		
		$page_menu = wp_list_pages("sort_column=".et_get_option($shortname.'_sort_pages')."&sort_order=".et_get_option($shortname.'_order_page')."&".$strdepth."&exclude=".$exclude_pages."&title_li=&echo=0");
		
		if ($addUlContainer) echo('<ul class="'.$customClass.'">');
			if (et_get_option($shortname . '_home_link') == 'on' && $addHomeLink) { ?> 
				<li <?php if (is_front_page() || is_home()) echo('class="current_page_item"') ?>><a href="<?php echo esc_url( home_url() ); ?>"><?php _e('Home',$themename); ?></a></li>
			<?php };
			
			echo $page_menu;
		if ($addUlContainer) echo('</ul>');
	}
}

if ( ! function_exists( 'show_categories_menu' ) ){
	function show_categories_menu($customClass = 'nav clearfix', $addUlContainer = true){
		global $shortname, $themename, $category_menu, $exclude_cats, $hide, $strdepth2, $projects_cat;
			
		//excluded categories
		if (et_get_option($shortname.'_menucats') <> '') $exclude_cats = implode(",", et_get_option($shortname.'_menucats')); 
		
		//hide empty categories
		if (et_get_option($shortname.'_categories_empty') == 'on') $hide = '1';
		else $hide = '0';
		
		//dropdown for categories
		$strdepth2 = '';
		if (et_get_option($shortname.'_enable_dropdowns_categories') == 'on') $strdepth2 = "depth=".et_get_option($shortname.'_tiers_shown_categories'); 
		if ($strdepth2 == '') $strdepth2 = "depth=1";
		
		$args = "orderby=".et_get_option($shortname.'_sort_cat')."&order=".et_get_option($shortname.'_order_cat')."&".$strdepth2."&exclude=".$exclude_cats."&hide_empty=".$hide."&title_li=&echo=0";
		
		$categories = get_categories( $args );
		
		if ( !empty($categories) ) {
			$category_menu = wp_list_categories($args);	
			if ($addUlContainer) echo('<ul class="'.$customClass.'">');
				echo $category_menu; 
			if ($addUlContainer) echo('</ul>');
		}	
	}
}

function head_addons(){
	global $shortname, $default_colorscheme;
	
	if ( apply_filters('et_get_additional_color_scheme',et_get_option($shortname.'_color_scheme')) <> $default_colorscheme ) { ?>
		<link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/style-' . et_get_option($shortname.'_color_scheme') . '.css' ); ?>" type="text/css" media="screen" />
	<?php }; 

	if ( et_get_option($shortname.'_child_css') == 'on' && et_get_option($shortname.'_child_cssurl') <> '' ) { //Enable child stylesheet  ?>
		<link rel="stylesheet" href="<?php echo esc_url( et_get_option($shortname.'_child_cssurl') ); ?>" type="text/css" media="screen" />
	<?php };
	
	//prints the theme name, version in meta tag
	if ( ! function_exists( 'get_custom_header' ) ){
		// compatibility with versions of WordPress prior to 3.4.
		$theme_info = get_theme_data(TEMPLATEPATH . '/style.css');	
		echo '<meta content="' . esc_attr( $theme_info['Name'] . ' v.' . $theme_info['Version'] ) . '" name="generator"/>';
	} else {
		$theme_info = wp_get_theme();
		echo '<meta content="' . esc_attr( $theme_info->display('Name') . ' v.' . $theme_info->display('Version') ) . '" name="generator"/>';
	}

	if ( et_get_option( $shortname . '_custom_colors' ) == 'on' ) et_epanel_custom_colors_css();
	
};// end function head_addons()
add_action('wp_head','head_addons',7);


function integration_head(){
	global $shortname;
	if (et_get_option($shortname.'_integration_head') <> '' && et_get_option($shortname.'_integrate_header_enable') == 'on') echo( et_get_option($shortname.'_integration_head') ); 
};
add_action('wp_head','integration_head',12);

function integration_body(){
	global $shortname;
	if (et_get_option($shortname.'_integration_body') <> '' && et_get_option($shortname.'_integrate_body_enable') == 'on') echo( et_get_option($shortname.'_integration_body') ); 
};
add_action('wp_footer','integration_body',12);

/*this function gets page name by its id*/
if ( ! function_exists( 'get_pagename' ) ){
	function get_pagename( $page_id )
	{
		$page_object = get_page( $page_id );
		
		return apply_filters( 'the_title', $page_object->post_title, $page_id );
	}
}

/*this function gets category name by its id*/
if ( ! function_exists( 'get_categname' ) ){
	function get_categname( $cat_id )
	{
		return get_cat_name( $cat_id );
	}
}

/*this function gets category id by its name*/
if ( ! function_exists( 'get_catId' ) ){
	function get_catId( $cat_name )
	{
		if ( is_int( $cat_name ) ) return $cat_name;
		
		$cat_name_id = get_cat_ID( html_entity_decode( $cat_name, ENT_QUOTES ) );
		return $cat_name_id;
	}
}

/*this function gets page id by its name*/
if ( ! function_exists( 'get_pageId' ) ){
	function get_pageId( $page_name )
	{
		global $wpdb;
		
		if ( is_numeric( $page_name ) ) return $page_name;
		
		$page_name = html_entity_decode( $page_name, ENT_QUOTES );
		$page = get_page_by_title( $page_name );
		
		//fix for qtranslate plugin
		if ( ! $page ){
			$qt_page_name = '%<!--:en-->' . $page_name . '<!--:-->%';
			$qt_page_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_type= %s", $qt_page_name, 'page' ) );

			if ( $qt_page_id ) return $qt_page_id;
			else return null;
		}

		return $page->ID;
	}
}

/*this function controls the meta titles display*/
if ( ! function_exists( 'elegant_titles' ) ){
	function elegant_titles() {
		global $shortname;
		
		$sitename = get_bloginfo('name');
		$site_description = get_bloginfo('description');
		
		#if the title is being displayed on the homepage
		if (is_home() || is_front_page()) {
			if (et_get_option($shortname.'_seo_home_title') == 'on') echo et_get_option($shortname.'_seo_home_titletext');  
			else {
				$seo_home_type = et_get_option( $shortname . '_seo_home_type' );
				$seo_home_separate = et_get_option($shortname.'_seo_home_separate');
				
				if ( $seo_home_type == 'BlogName | Blog description' ) echo $sitename . esc_html( $seo_home_separate ) . $site_description; 
				if ( $seo_home_type == 'Blog description | BlogName') echo $site_description . esc_html( $seo_home_separate ) . $sitename;
				if ( $seo_home_type == 'BlogName only') echo $sitename;
			}
		}
		#if the title is being displayed on single posts/pages
		if ( ( is_single() || is_page() ) && ! is_front_page() ) { 
			global $wp_query; 
			$postid = $wp_query->post->ID; 
			$key = et_get_option($shortname.'_seo_single_field_title');
			$exists3 = get_post_meta($postid, ''.$key.'', true);
					if (et_get_option($shortname.'_seo_single_title') == 'on' && $exists3 !== '' ) echo $exists3; 
					else {
						$seo_single_type = et_get_option($shortname.'_seo_single_type');
						$seo_single_separate = et_get_option($shortname.'_seo_single_separate');
						if ( $seo_single_type == 'BlogName | Post title' ) echo $sitename . esc_html( $seo_single_separate ) . wp_title('',false,''); 
						if ( $seo_single_type == 'Post title | BlogName' ) echo wp_title('',false,'') . esc_html( $seo_single_separate ) . $sitename;
						if ( $seo_single_type == 'Post title only' ) echo wp_title('',false,'');
					}
						
		}
		#if the title is being displayed on index pages (categories/archives/search results)
		if (is_category() || is_archive() || is_search()) {
			$seo_index_type = et_get_option($shortname.'_seo_index_type');
			$seo_index_separate = et_get_option($shortname.'_seo_index_separate');
			if ( $seo_index_type == 'BlogName | Category name' ) echo $sitename . esc_html( $seo_index_separate ) . wp_title('',false,''); 
			if ( $seo_index_type == 'Category name | BlogName') echo wp_title('',false,'') . esc_html( $seo_index_separate ) . $sitename;
			if ( $seo_index_type == 'Category name only') echo wp_title('',false,'');
		}	  
	}
}

/*this function controls the meta description display*/
if ( ! function_exists( 'elegant_description' ) ){
	function elegant_description() {
		global $shortname;
		
		#homepage descriptions
		if ( is_home() && et_get_option($shortname.'_seo_home_description') == 'on' ) echo '<meta name="description" content="' . esc_attr( et_get_option($shortname.'_seo_home_descriptiontext') ) .'" />';
		
		#single page descriptions
		global $wp_query; 
		if ( isset($wp_query->post->ID) ) $postid = $wp_query->post->ID; 
		$key2 = et_get_option($shortname.'_seo_single_field_description');
		if ( isset($postid) ) $exists = get_post_meta($postid, ''.$key2.'', true);
		if (et_get_option($shortname.'_seo_single_description') == 'on' && $exists !== '') {
			if (is_single() || is_page()) echo '<meta name="description" content="' . esc_attr( $exists ) . '" />';
		}
		
		#index descriptions
		remove_filter('term_description','wpautop');
		$cat = get_query_var('cat'); 
		$exists2 = category_description($cat);
		
		$seo_index_description = et_get_option($shortname.'_seo_index_description');
		
		if ($exists2 !== '' && $seo_index_description == 'on') {
			if (is_category()) echo '<meta name="description" content="'. esc_attr( $exists2 ) .'" />';
		}
		if (is_archive() && $seo_index_description == 'on') echo '<meta name="description" content="Currently viewing archives from'. esc_attr( wp_title('',false,'') ) .'" />';
		if (is_search() && $seo_index_description == 'on') echo '<meta name="description" content="'. esc_attr( wp_title('',false,'') ) .'" />';
	}
}

/*this function controls the meta keywords display*/
if ( ! function_exists( 'elegant_keywords' ) ){
	function elegant_keywords() {
		global $shortname;
		
		#homepage keywords
		if (is_home() && et_get_option($shortname.'_seo_home_keywords') == 'on') echo '<meta name="keywords" content="'.esc_attr( et_get_option($shortname.'_seo_home_keywordstext') ).'" />';
		
		#single page keywords
		global $wp_query; 
		if (isset($wp_query->post->ID)) $postid = $wp_query->post->ID; 
		$key3 = et_get_option($shortname.'_seo_single_field_keywords');
		if (isset($postid)) $exists4 = get_post_meta($postid, ''.$key3.'', true);
		if (isset($exists4) && $exists4 !== '' && et_get_option($shortname.'_seo_single_keywords') == 'on') {
			if (is_single() || is_page()) echo '<meta name="keywords" content="' . esc_attr( $exists4 ) . '" />';	
		}
	}
}

/*this function controls canonical urls*/
if ( ! function_exists( 'elegant_canonical' ) ){
	function elegant_canonical() {
		global $shortname;
		
		#homepage urls
		if (is_home() && et_get_option($shortname.'_seo_home_canonical') == 'on') echo '<link rel="canonical" href="'. esc_url( home_url() ).'" />';
		
		#single page urls
		global $wp_query; 
		if (isset($wp_query->post->ID)) $postid = $wp_query->post->ID; 
		if (et_get_option($shortname.'_seo_single_canonical') == 'on') {
			if (is_single() || is_page()) echo '<link rel="canonical" href="'.esc_url( get_permalink() ).'" />';	
		}
		
		#index page urls
		if (et_get_option($shortname.'_seo_index_canonical') == 'on') {
			if (is_archive() || is_category() || is_search()) echo '<link rel="canonical" href="'. esc_url( get_permalink() ).'" />';	
		}
	}
}

add_action('wp_head','add_favicon');
function add_favicon(){
	global $shortname;
	
	$faviconUrl = et_get_option($shortname.'_favicon');
	if ($faviconUrl <> '') echo('<link rel="shortcut icon" href="'.esc_url( $faviconUrl ).'" />');
}

add_action( 'init', 'et_create_images_temp_folder' );
function et_create_images_temp_folder(){
	#clean et_temp folder once per week
	if ( false !== $last_time = get_option( 'et_schedule_clean_images_last_time'  ) ){
		$timeout = 86400 * 7;
		if ( ( $timeout < ( time() - $last_time ) ) && '' != get_option( 'et_images_temp_folder' ) ) et_clean_temp_images( get_option( 'et_images_temp_folder' ) );
	}
	
	if ( false !== get_option( 'et_images_temp_folder' ) ) return;

	$uploads_dir = wp_upload_dir();
	$destination_dir = ( false === $uploads_dir['error'] ) ? path_join( $uploads_dir['basedir'], 'et_temp' ) : null;

	if ( ! wp_mkdir_p( $destination_dir ) ) update_option( 'et_images_temp_folder', '' );
	else {
		update_option( 'et_images_temp_folder', preg_replace( '#\/\/#', '/', $destination_dir ) );
		update_option( 'et_schedule_clean_images_last_time', time() );
	}
}

if ( ! function_exists( 'et_clean_temp_images' ) ){
	function et_clean_temp_images( $directory ){
		$dir_to_clean = @ opendir( $directory );
		
		if ( $dir_to_clean ) {
			while (($file = readdir( $dir_to_clean ) ) !== false ) {
				if ( substr($file, 0, 1) == '.' )
					continue;
				if ( is_dir( $directory.'/'.$file ) )
					et_clean_temp_images( path_join( $directory, $file ) );
				else
					@ unlink( path_join( $directory, $file ) );
			}
			closedir( $dir_to_clean );
		}
		
		#set last time cleaning was performed
		update_option( 'et_schedule_clean_images_last_time', time() );
	}
}

add_filter( 'update_option_upload_path', 'et_update_uploads_dir' );
function et_update_uploads_dir( $upload_path ){
	#check if we have 'et_temp' folder within $uploads_dir['basedir'] directory, if not - try creating it, if it's not possible $destination_dir = null
	
	$destination_dir = '';
	$uploads_dir = wp_upload_dir();
	$et_temp_dir = path_join( $uploads_dir['basedir'], 'et_temp' );
	
	if ( is_dir( $et_temp_dir ) || ( false === $uploads_dir['error'] && wp_mkdir_p( $et_temp_dir ) ) ){
		$destination_dir = $et_temp_dir;
		update_option( 'et_schedule_clean_images_last_time', time() );
	}
		
	update_option( 'et_images_temp_folder', preg_replace( '#\/\/#', '/', $destination_dir ) );

	return $upload_path;
}

if ( ! function_exists( 'et_resize_image' ) ){
	function et_resize_image( $thumb, $new_width, $new_height, $crop ){
		if ( is_ssl() ) $thumb = preg_replace( '#^http://#', 'https://', $thumb );
		$info = pathinfo($thumb);
		$ext = $info['extension'];
		$name = wp_basename($thumb, ".$ext");
		$is_jpeg = false;
		$site_uri = apply_filters( 'et_resize_image_site_uri', site_url() );
		$site_dir = apply_filters( 'et_resize_image_site_dir', ABSPATH );
		
		#get main site url on multisite installation 
		if ( is_multisite() ){
			switch_to_blog(1);
			$site_uri = site_url();
			restore_current_blog();
		}
		
		if ( 'jpeg' == $ext ) {
			$ext = 'jpg';
			$name = preg_replace( '#.jpeg$#', '', $name );
			$is_jpeg = true;
		}
		
		$suffix = "{$new_width}x{$new_height}";
		
		$destination_dir = '' != get_option( 'et_images_temp_folder' ) ? preg_replace( '#\/\/#', '/', get_option( 'et_images_temp_folder' ) ) : null;
		
		$matches = apply_filters( 'et_resize_image_site_dir', array(), $site_dir );
		if ( !empty($matches) ){
			preg_match( '#'.$matches[1].'$#', $site_uri, $site_uri_matches );
			if ( !empty($site_uri_matches) ){
				$site_uri = str_replace( $matches[1], '', $site_uri );
				$site_uri = preg_replace( '#/$#', '', $site_uri );
				$site_dir = str_replace( $matches[1], '', $site_dir );
				$site_dir = preg_replace( '#\\\/$#', '', $site_dir );
			}
		}
		
		#get local name for use in file_exists() and get_imagesize() functions
		$localfile = str_replace( apply_filters( 'et_resize_image_localfile', $site_uri, $site_dir, et_multisite_thumbnail($thumb) ), $site_dir, et_multisite_thumbnail($thumb) );
		
		$add_to_suffix = '';
		if ( file_exists( $localfile ) ) $add_to_suffix = filesize( $localfile ) . '_';
		
		#prepend image filesize to be able to use images with the same filename
		$suffix = $add_to_suffix . $suffix;
		$destfilename_attributes = '-' . $suffix . '.' . $ext;
		
		$checkfilename = ( '' != $destination_dir && null !== $destination_dir ) ? path_join( $destination_dir, $name ) : path_join( dirname( $localfile ), $name );
		$checkfilename .= $destfilename_attributes;
		
		if ( $is_jpeg ) $checkfilename = preg_replace( '#.jpeg$#', '.jpg', $checkfilename );
		
		$uploads_dir = wp_upload_dir();
		$uploads_dir['basedir'] = preg_replace( '#\/\/#', '/', $uploads_dir['basedir'] );
		
		if ( null !== $destination_dir && '' != $destination_dir && apply_filters('et_enable_uploads_detection', true) ){
			$site_dir = trailingslashit( preg_replace( '#\/\/#', '/', $uploads_dir['basedir'] ) );
			$site_uri = trailingslashit( $uploads_dir['baseurl'] );
		}
		
		#check if we have an image with specified width and height
		
		if ( file_exists( $checkfilename ) ) return str_replace( $site_dir, trailingslashit( $site_uri ), $checkfilename );

		$size = @getimagesize( $localfile );
		if ( !$size ) return new WP_Error('invalid_image_path', __('Image doesn\'t exist'), $thumb);
		list($orig_width, $orig_height, $orig_type) = $size;
		
		#check if we're resizing the image to smaller dimensions
		if ( $orig_width > $new_width || $orig_height > $new_height ){
			if ( $orig_width < $new_width || $orig_height < $new_height ){
				#don't resize image if new dimensions > than its original ones
				if ( $orig_width < $new_width ) $new_width = $orig_width;
				if ( $orig_height < $new_height ) $new_height = $orig_height;
				
				#regenerate suffix and appended attributes in case we changed new width or new height dimensions
				$suffix = "{$add_to_suffix}{$new_width}x{$new_height}";
				$destfilename_attributes = '-' . $suffix . '.' . $ext;
				
				$checkfilename = ( '' != $destination_dir && null !== $destination_dir ) ? path_join( $destination_dir, $name ) : path_join( dirname( $localfile ), $name );
				$checkfilename .= $destfilename_attributes;
				
				#check if we have an image with new calculated width and height parameters
				if ( file_exists($checkfilename) ) return str_replace( $site_dir, trailingslashit( $site_uri ), $checkfilename );
			}
			
			#we didn't find the image in cache, resizing is done here
			if ( ! function_exists( 'wp_get_image_editor' ) ) {
				// compatibility with versions of WordPress prior to 3.5.
				$result = image_resize( $localfile, $new_width, $new_height, $crop, $suffix, $destination_dir );
			} else {
				$et_image_editor = wp_get_image_editor( $localfile );
				
				if ( ! is_wp_error( $et_image_editor ) ) {
					$et_image_editor->resize( $new_width, $new_height, $crop );
					
					// generate correct file name/path
					$et_new_image_name = $et_image_editor->generate_filename( $suffix, $destination_dir );
					
					do_action( 'et_resize_image_before_save', $et_image_editor, $et_new_image_name );
					
					$et_image_editor->save( $et_new_image_name );
					
					// assign new image path
					$result = $et_new_image_name;
				} else {
					// assign a WP_ERROR ( WP_Image_Editor instance wasn't created properly )
					$result = $et_image_editor;
				}
			}

			if ( ! is_wp_error( $result ) ) {
				// transform local image path into URI
				
				if ( $is_jpeg ) $thumb = preg_replace( '#.jpeg$#', '.jpg', $thumb);
				
				$site_dir = str_replace( '\\', '/', $site_dir );
				$result = str_replace( '\\', '/', $result );
				$result = str_replace( '//', '/', $result );
				$result = str_replace( $site_dir, trailingslashit( $site_uri ), $result );
			}
			
			#returns resized image path or WP_Error ( if something went wrong during resizing )
			return $result;
		}
		
		#returns unmodified image, for example in case if the user is trying to resize 800x600px to 1920x1080px image
		return $thumb;
	}
}

add_action( 'pre_get_posts', 'et_custom_posts_per_page' );
function et_custom_posts_per_page( $query = false ) {
	global $shortname;

	if ( is_admin() ) return;
	
	if ( ! is_a( $query, 'WP_Query' ) || ! $query->is_main_query() ) return;
	
	if ( $query->is_category ) {
		$query->set( 'posts_per_page', (int) et_get_option( $shortname . '_catnum_posts', '10' ) );
	} elseif ( $query->is_tag ) {
		$query->set( 'posts_per_page', (int) et_get_option( $shortname . '_tagnum_posts', '10' ) );
	} elseif ( $query->is_search ) {
		if ( isset($_GET['et_searchform_submit']) ) {			
			$postTypes = array();
			if ( !isset($_GET['et-inc-posts']) && !isset($_GET['et-inc-pages']) ) $postTypes = array('post');
			if ( isset($_GET['et-inc-pages']) ) $postTypes = array('page');
			if ( isset($_GET['et-inc-posts']) ) $postTypes[] = 'post';
			$query->set( 'post_type', $postTypes );
			
			if ( isset( $_GET['et-month-choice'] ) && $_GET['et-month-choice'] != 'no-choice' ) {
				$et_year = substr($_GET['et-month-choice'],0,4);
				$et_month = substr($_GET['et-month-choice'], 4, strlen($_GET['et-month-choice'])-4);

				$query->set( 'year', absint($et_year) );
				$query->set( 'monthnum', absint($et_month) );
			}
			
			if ( isset( $_GET['et-cat'] ) && $_GET['et-cat'] != 0 )
				$query->set( 'cat', absint($_GET['et-cat']) );
		}
		$query->set( 'posts_per_page', (int) et_get_option( $shortname . '_searchnum_posts', '10' ) );
	} elseif ( $query->is_archive ) {
		$query->set( 'posts_per_page', (int) et_get_option( $shortname . '_archivenum_posts', '10' ) );
	}
}

add_filter('pre_set_site_transient_update_themes', 'et_check_themes_updates');
function et_check_themes_updates( $update_transient ){
	global $wp_version;
	
	if ( !isset($update_transient->checked) ) return $update_transient;
	else $themes = $update_transient->checked;
	
	$send_to_api = array(
		'action' => 'check_theme_updates',
		'installed_themes' => $themes
	);
	
	$options = array(
		'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3),
		'body'			=> $send_to_api,
		'user-agent'	=> 'WordPress/' . $wp_version . '; ' . home_url()
	);
	
	$theme_request = wp_remote_post( 'http://www.elegantthemes.com/api/api.php', $options );
	if ( !is_wp_error($theme_request) && wp_remote_retrieve_response_code($theme_request) == 200 ){
		$theme_response = unserialize( wp_remote_retrieve_body( $theme_request ) );
		if ( !empty($theme_response) ) {
			$update_transient->response = array_merge(!empty($update_transient->response) ? $update_transient->response : array(),$theme_response);
			$last_update->checked = $themes;
			$last_update->response = $theme_response;
		}
	}
	
	$last_update->last_checked = time();
	set_site_transient( 'et_update_themes', $last_update );
	
	return $update_transient;
}

add_filter('site_transient_update_themes', 'et_add_themes_to_update_notification');
function et_add_themes_to_update_notification( $update_transient ){
	$et_update_themes = get_site_transient( 'et_update_themes' );
	if ( !is_object($et_update_themes) || !isset($et_update_themes->response) ) return $update_transient;
	$update_transient->response = array_merge(!empty($update_transient->response) ? $update_transient->response : array(), $et_update_themes->response);
	
	return $update_transient;
}


add_filter( 'default_hidden_meta_boxes', 'et_show_hidden_metaboxes', 10, 2 );
function et_show_hidden_metaboxes( $hidden, $screen ){
	# make custom fields and excerpt meta boxes show by default
	if ( 'post' == $screen->base || 'page' == $screen->base )
		$hidden = array('slugdiv', 'trackbacksdiv', 'commentstatusdiv', 'commentsdiv', 'authordiv', 'revisionsdiv');
		
	return $hidden;
}

add_filter('widget_title','et_widget_force_title');
function et_widget_force_title( $title ){	
	#add an empty title for widgets ( otherwise it might break the sidebar layout )
	if ( $title == '' ) $title = ' ';
	
	return $title;
}

//modify the comment counts to only reflect the number of comments minus pings
if( version_compare( phpversion(), '4.4', '>=' ) ) add_filter('get_comments_number', 'et_comment_count', 0);
function et_comment_count( $count ) {
	if ( ! is_admin() ) {
		global $id;
		$get_comments = get_comments( array('post_id' => $id, 'status' => 'approve') );
		$comments_by_type = &separate_comments($get_comments);
		return count($comments_by_type['comment']);
	} else {
		return $count;
	}
}

add_action( 'admin_init', 'et_theme_check_clean_installation' );
function et_theme_check_clean_installation(){
	add_action( 'admin_notices', 'et_theme_epanel_reminder' );
}

if ( ! function_exists( 'et_theme_epanel_reminder' ) ){
	function et_theme_epanel_reminder(){
		global $shortname, $themename, $current_screen;
		
		if ( false === et_get_option( $shortname . '_logo' ) && 'appearance_page_core_functions' != $current_screen->id ){
			printf( __('<div class="updated"><p>This is a fresh installation of %1$s theme. Don\'t forget to go to <a href="%2$s">ePanel</a> to set it up. This message will disappear once you have clicked the Save button within the <a href="%2$s">theme\'s options page</a>.</p></div>',$themename), get_current_theme(), admin_url( 'themes.php?page=core_functions.php' ) );
		}
	}
}

add_filter( 'gettext', 'et_admin_update_theme_message', 20, 3 );
function et_admin_update_theme_message( $default_translated_text, $original_text, $domain ) {
	global $themename;
    $theme_page_message = 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%1$s">View version %3$s details</a>. <em>Automatic update is unavailable for this theme.</em>';
	$updates_page_message = 'Update package not available.';

    if ( is_admin() && $original_text === $theme_page_message ) {
        return __( 'There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%1$s">View version %3$s details</a>. <em>Auto-updates are not available for this theme. If this is an Elegant Themes theme, then you must re-download the theme from the member\'s area and <a href="http://www.elegantthemes.com/members-area/documentation.html#update" target="_blank">re-install it</a> in order to update it to the latest version.</em>', $themename );
    }
	
	if ( is_admin() && $original_text === $updates_page_message ){
		return __( 'Auto-updates are not available for this theme. If this is an Elegant Themes theme, then you must re-download the theme from the member\'s area and <a href="http://www.elegantthemes.com/members-area/documentation.html#update" target="_blank">re-install it</a> in order to update it to the latest version.', $themename );
	}

    return $default_translated_text;
}

add_filter( 'body_class', 'et_add_fullwidth_body_class' );
function et_add_fullwidth_body_class( $classes ){
	$fullwidth_view = false;
	
	if ( is_page_template('page-full.php') ) $fullwidth_view = true;
	
	if ( is_page() || is_single() ){
		$et_ptemplate_settings = get_post_meta( get_queried_object_id(),'et_ptemplate_settings',true );
		$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
		
		if ( $fullwidth ) $fullwidth_view = true;
	}
	
	if ( is_single() && 'on' == get_post_meta( get_queried_object_id(), '_et_full_post', true ) ) $fullwidth_view = true;

	$classes[] = apply_filters( 'et_fullwidth_view_body_class', $fullwidth_view ) ? 'et_fullwidth_view' : 'et_includes_sidebar';
	
	return $classes;
}

function et_add_responsive_shortcodes_css(){
	global $shortname;
	
	if ( 'on' == et_get_option( $shortname . '_responsive_shortcodes', 'on' ) )
		wp_enqueue_style( 'et-shortcodes-responsive-css', ET_SHORTCODES_DIR . '/css/shortcodes_responsive.css', false, ET_SHORTCODES_VERSION, 'all' );
}

/**
 * Loads theme settings
 *
 */
if ( ! function_exists( 'et_load_core_options' ) ) {
	function et_load_core_options() {
		global $shortname;
		require_once( get_template_directory() . esc_attr( "/epanel/options_{$shortname}.php" ) );
	}
}