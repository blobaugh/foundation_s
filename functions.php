	<?php
/**
 * foundation_s functions and definitions
 *
 * @package foundation_s
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 640; /* pixels */

if ( ! function_exists( 'foundation_s_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function foundation_s_setup() {

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on foundation_s, use a find and replace
	 * to change 'foundation_s' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'foundation_s', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support for Post Thumbnails on posts and pages
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	//add_theme_support( 'post-thumbnails' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'foundation_s' ),
		'grid' => __('Grid Menu', 'foundation_s'),
	) );


/* --------------------------------------------
		Walker classes
-------------------------------------------- */



// Using the Walker class to add the dropdown class to sub-menus per Foundation requirements

class foundation_s_walker extends Walker_Nav_Menu {
    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ){
        $GLOBALS['dd_children'] = ( isset($children_elements[$element->ID]) )? 1:0;
        $GLOBALS['dd_depth'] = (int) $depth;
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }

     function start_lvl(&$output, $depth) {
    $indent = str_repeat("\t", $depth);
    $output .= "<ul class=\"dropdown\">";
  }
}


// walker class to turn nav menus into grid elements

// Add class to the tile navigation for blocks
class tile_walker extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth, $args)
    {	

    	// variables for class names - change these.
    	$largeGrid = '';
    	$smallGrid = '';

    	// add 'tight' to variable to add the smaller margin class
    	$tight = ''; 


    	$attributes  = '';
        !empty( $item->attr_title ) and $attributes .= ' title="'  . esc_attr( $item->attr_title ) .'"';
        !empty( $item->target ) and $attributes .= ' target="' . esc_attr( $item->target     ) .'"';
        !empty( $item->xfn ) and $attributes .= ' rel="'    . esc_attr( $item->xfn        ) .'"';
        !empty( $item->url ) and $attributes .= ' href="'   . esc_attr( $item->url        ) .'"';
        $classes = empty($item->classes) ? array () : (array) $item->classes;
        $class_names = join(' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
        !empty ( $class_names ) and $class_names = ' class="' . $largeGrid . ' ' ' ' . $smallGrid . '  columns ' . $tight . '"';
        $output .= "<a" . $attributes . " class='tile-menu-item'>";
        $output .= "<div id='ms-menu-item-$item->ID' $class_names>";
        $output .= "<div class='panel table' data-height-watch>";
        
        $title = apply_filters( 'the_title', $item->title, $item->ID );
        $item_output = $args->before
        . "<h2>"
        . $args->link_before
        . $title
        . '</h2></div></div>'
        . '</a>'
        . $args->link_after
        . $args->after;
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}



add_filter('nav_menu_css_class','add_parent_css',10,2);
function  add_parent_css($classes, $item){
     global  $dd_depth, $dd_children;
     $classes[] = 'depth'.$dd_depth;
     if($dd_children)
         $classes[] = 'parent';
    return $classes;
}

//Add class to parent pages to show they have subpages (only for automatic wp_nav_menu)

function add_parent_class( $css_class, $page, $depth, $args )
{
   if ( ! empty( $args['has_children'] ) )
       $css_class[] = 'parent';
   return $css_class;
}
add_filter( 'page_css_class', 'add_parent_class', 10, 4 );

	/**
	 * Setup the WordPress core custom background feature.
	 */
	add_theme_support( 'custom-background', apply_filters( 'foundation_s_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );
}
endif; // foundation_s_setup
add_action( 'after_setup_theme', 'foundation_s_setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 */
function foundation_s_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'foundation_s' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'foundation_s_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function foundation_s_scripts() {
	wp_enqueue_style( 'foundation_s-style', get_stylesheet_uri() );

	wp_enqueue_script( 'foundation_s-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'foundation_s-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );
	wp_enqueue_script('zepto', get_template_directory_uri() . '/js/zepto.js', array(), '01', true);
	wp_enqueue_script('foundation', get_template_directory_uri() . '/js/foundation.min.js', array('jquery'), '01', true);
		wp_enqueue_script('myapp', get_template_directory_uri(). '/js/foundation_s.js', array(), '01', true);
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'foundation_s-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20120202' );
	}
}
add_action( 'wp_enqueue_scripts', 'foundation_s_scripts' );

/* --------------------------------------------
		Custom Functions
-------------------------------------------- */

// uncomment this filter to remove the admin bar when logged in.

	// add_filter( 'show_admin_bar', '__return_false' );

// this is used to create a custom login screen - feel free to change the
// parameters as you see fit 
	function foundation_s_login_logo() {  
		echo 
	    '<style  type="text/css"> h1 a {  
	    		background-image:url('.get_template_directory_uri().'/images/LOGO_NAME_GOES_HERE)  !important;
			    background-position: left top !important;
			    background-repeat: no-repeat;
			    background-size: 100% !important;
			    display: block;
			    height: 105px !important;
			    outline: 0 none;
			    overflow: hidden;
			    padding-bottom: 15px;
			    text-indent: -9999px;
			    width: 335px !important;
			}
	    </style> '; 
	}  
	add_action('login_head',  'my_custom_login_logo');


// uncomment any of the below sections to remove admin menus
	function foundation_s_remove_menu_items() {
	    if( !current_user_can( 'manage_options' ) ):
	    	// remove_menu_page( 'edit.php?post_type=page' ); // pages
		    // remove_menu_page('link-manager.php');
		    // remove_menu_page('index.php');
		    // remove_menu_page('users.php');
		    // remove_menu_page('upload.php');
		    // remove_menu_page('tools.php');
		    // remove_menu_page('edit.php');
		    // remove_menu_page('edit-comments.php');
		    // remove_menu_page('post-new.php');
		    // remove_submenu_page('themes.php','themes.php');
		    // remove_submenu_page('themes.php','theme-editor.php');
		    // remove_submenu_page('themes.php','widgets.php');
	    endif;
	}
	add_action( 'admin_menu', 'foundation_s_remove_menu_items' );


	function the_page_name(){
		// allows you to use the name of the current page.
			global $post;
			return $post->post_name;
		}
	function get_page_name(){
		// allows you to display the name of the current page.
			global $post;
			echo $post->post_name;
		}

// Add a nicename to body class for page.  
// Use this for custom CSS based on pagename
	function foundation_s_body_page_name_class( $classes ) {
	    global $post;
	        $classes[] = $post->post_name;
	    return $classes;
	}
	add_filter('body_class', 'foundation_s_body_page_name_class');

/* --------------------------------------------
		Functions in Progress
-------------------------------------------- */




/* --------------------------------------------
		Includes
-------------------------------------------- */

/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
// require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
// require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
// require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
// require get_template_directory() . '/inc/jetpack.php';
