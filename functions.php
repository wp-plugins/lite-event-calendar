<?php
function dpProEventCalendar_admin_url( $query = array() ) {
	global $plugin_page;

	if ( ! isset( $query['page'] ) )
		$query['page'] = $plugin_page;

	$path = 'admin.php';

	if ( $query = build_query( $query ) )
		$path .= '?' . $query;

	$url = admin_url( $path );

	return esc_url_raw( $url );
}

function dpProEventCalendar_plugin_url( $path = '' ) {
	global $wp_version;
	if ( version_compare( $wp_version, '2.8', '<' ) ) { // Using WordPress 2.7
		$folder = dirname( plugin_basename( __FILE__ ) );
		if ( '.' != $folder )
			$path = path_join( ltrim( $folder, '/' ), $path );

		return plugins_url( $path );
	}
	return plugins_url( $path, __FILE__ );
}

function dpProEventCalendar_parse_date( $date ) {
	
	$date = substr($date,0,10);
	if($date == "0000-00-00" || $date == "")
		return '';
	$date_arr = explode("-", $date);
	$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0];
	
	return $date ;
}

function dpProEventCalendar_parse_date_widget( $date, $date_format ) {
	if($date == "0000-00-00" || $date == "")
		return '';
		
	$date_arr = explode("-", substr($date, 0, 10));
	$time_arr = explode(":", substr($date, 11, 5));
	
	switch($date_format) {
		case 0: 
			$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0]." ".$time_arr[0].":".$time_arr[1];
			break;
		case 1: 
			$date = $date_arr[2]."/".$date_arr[1]."/".$date_arr[0]." ".$time_arr[0].":".$time_arr[1];
			break;
		case 2: 
			$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0];
			break;
		case 3: 
			$date = $date_arr[2]."/".$date_arr[1]."/".$date_arr[0];
			break;
		case 4: 
			$date = substr(dpProEventCalendar_translate_month($date_arr[1]), 0, 3)." ".$date_arr[2].", ".$date_arr[0];
			break;
		case 5: 
			$date = substr(dpProEventCalendar_translate_month($date_arr[1]), 0, 3)." ".$date_arr[2];
			break;
		default: 
			$date = $date_arr[1]."/".$date_arr[2]."/".$date_arr[0]." ".$time_arr[0].":".$time_arr[1];
			break;	
	}
	
	return $date ;
}

function dpProEventCalendar_translate_month($month) {
	global $dpProEventCalendar;
	
	switch($month) {
		case "01":
			$month_name = $dpProEventCalendar['lang_january'];
			break;
		case "02":
			$month_name = $dpProEventCalendar['lang_february'];
			break;
		case "03":
			$month_name = $dpProEventCalendar['lang_march'];
			break;
		case "04":
			$month_name = $dpProEventCalendar['lang_april'];
			break;
		case "05":
			$month_name = $dpProEventCalendar['lang_may'];
			break;
		case "06":
			$month_name = $dpProEventCalendar['lang_june'];
			break;
		case "07":
			$month_name = $dpProEventCalendar['lang_july'];
			break;
		case "08":
			$month_name = $dpProEventCalendar['lang_august'];
			break;
		case "09":
			$month_name = $dpProEventCalendar['lang_september'];
			break;
		case "10":
			$month_name = $dpProEventCalendar['lang_october'];
			break;
		case "11":
			$month_name = $dpProEventCalendar['lang_november'];
			break;
		case "12":
			$month_name = $dpProEventCalendar['lang_december'];
			break;
		default:
			$month_name = $dpProEventCalendar['lang_january'];
			break;
	}
	
	return $month_name;
}

function dpProEventCalendar_reslash_multi(&$val,$key) 
{
   if (is_array($val)) array_walk($val,'dpProEventCalendar_reslash_multi',$new);
   else {
      $val = dpProEventCalendar_reslash($val);
   }
}


function dpProEventCalendar_reslash($string)
{
   if (!get_magic_quotes_gpc())$string = addslashes($string);
   return $string;
}

function dpProEventCalendar_CutString ($texto, $longitud = 180) { 
	$str_len = function_exists('mb_strlen') ? mb_strlen($texto) : strlen($texto);
	if($str_len > $longitud) { 
		$strpos = function_exists('mb_strpos') ? mb_strpos($texto, ' ', $longitud) : strpos($texto, ' ', $longitud);
		$pos_espacios = $strpos - 1; 
		if($pos_espacios > 0) { 
			$substr1 = function_exists('mb_substr') ? mb_substr($texto, 0, ($pos_espacios + 1)) : substr($texto, 0, ($pos_espacios + 1));
			$caracteres = count_chars($substr1, 1); 
			if ($caracteres[ord('<')] > $caracteres[ord('>')]) { 
				$strpos2 = function_exists('mb_strpos') ? mb_strpos($texto, ">", $pos_espacios) : strpos($texto, ">", $pos_espacios);
				$pos_espacios = $strpos2 - 1; 
			} 
			$substr2 = function_exists('mb_substr') ? mb_substr($texto, 0, ($pos_espacios + 1)) : substr($texto, 0, ($pos_espacios + 1));
			$texto = $substr2.'...'; 
		} 
		if(preg_match_all("|(<([\w]+)[^>]*>)|", $texto, $buffer)) { 
			if(!empty($buffer[1])) { 
				preg_match_all("|</([a-zA-Z]+)>|", $texto, $buffer2); 
				if(count($buffer[2]) != count($buffer2[1])) { 
					$cierrotags = array_diff($buffer[2], $buffer2[1]); 
					$cierrotags = array_reverse($cierrotags); 
					foreach($cierrotags as $tag) { 
							$texto .= '</'.$tag.'>'; 
					} 
				} 
			} 
		} 
 
	} 
	return $texto; 
}

add_action( 'wp_ajax_nopriv_getDate', 'dpProEventCalendar_getDate' );
add_action( 'wp_ajax_getDate', 'dpProEventCalendar_getDate' );
 
function dpProEventCalendar_getDate() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!is_numeric($_POST['date'])) { die(); }
	
	$timestamp = $_POST['date'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	$is_admin = $_POST['is_admin'];
	if ($is_admin && strtolower($is_admin) !== "false") {
      $is_admin = true;
   } else {
      $is_admin = false;
   }
   
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( $is_admin, $calendar, $timestamp, null, '', $category, $event_id, $author );
	
	die($dpProEventCalendar->monthlyCalendarLayout());
}

add_action( 'wp_ajax_nopriv_getDaily', 'dpProEventCalendar_getDaily' );
add_action( 'wp_ajax_getDaily', 'dpProEventCalendar_getDaily' );
 
function dpProEventCalendar_getDaily() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!is_numeric($_POST['date'])) { die(); }
	
	$timestamp = $_POST['date'];
	$currDate = date("Y-m-d", $timestamp);
	
	$calendar = $_POST['calendar'];
	$is_admin = $_POST['is_admin'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	if ($is_admin && strtolower($is_admin) !== "false") {
      $is_admin = true;
   } else {
      $is_admin = false;
   }
   
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( $is_admin, $calendar, $timestamp, null, '', $category, $event_id, $author );
	
	echo "<!--".date_i18n(get_option('date_format'), $timestamp).">!]-->";
	
	die($dpProEventCalendar->dailyCalendarLayout());
}

add_action( 'wp_ajax_nopriv_getEvents', 'dpProEventCalendar_getEvents' );
add_action( 'wp_ajax_getEvents', 'dpProEventCalendar_getEvents' );
 
function dpProEventCalendar_getEvents() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!isset($_POST['date'])) { die(); }
	
	$date = $_POST['date'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', $category, $event_id, $author );
	
	die($dpProEventCalendar->eventsListLayout( $date ));
}

add_action( 'wp_ajax_nopriv_getEvent', 'dpProEventCalendar_getEvent' );
add_action( 'wp_ajax_getEvent', 'dpProEventCalendar_getEvent' );
 
function dpProEventCalendar_getEvent() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!isset($_POST['event'])) { die(); }
	
	$event = $_POST['event'];
	$calendar = $_POST['calendar'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar );
	
	echo '
		<div class="dp_pec_date_event_head dp_pec_date_event_daily dp_pec_isotope">
			<span></span><a href="" class="dp_pec_date_event_back"></a>
		</div>';
		
	$result = $dpProEventCalendar->getEventByID($event);
	echo $dpProEventCalendar->singleEventLayout($result, false);
	
	die();
}

add_action( 'wp_ajax_nopriv_getEventsMonth', 'dpProEventCalendar_getEventsMonth' );
add_action( 'wp_ajax_getEventsMonth', 'dpProEventCalendar_getEventsMonth' );
 
function dpProEventCalendar_getEventsMonth() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!isset($_POST['month'])) { die(); }
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', $category, $event_id, $author );
	
	$next_month_days = cal_days_in_month(CAL_GREGORIAN, str_pad(($month), 2, "0", STR_PAD_LEFT), $year);
	$month_number = str_pad($month, 2, "0", STR_PAD_LEFT);
	
	echo $dpProEventCalendar->upcomingCalendarLayout( false, 99, '', $year."-".$month_number."-01 00:00:00", $year."-".$month_number."-".$next_month_days." 23:59:59", true );
	die();
}

add_action( 'wp_ajax_nopriv_getEventsMonthList', 'dpProEventCalendar_getEventsMonthList' );
add_action( 'wp_ajax_getEventsMonthList', 'dpProEventCalendar_getEventsMonthList' );
 
function dpProEventCalendar_getEventsMonthList() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //   die ( 'Busted!');
		
	if(!is_numeric($_POST['month'])) { die(); }
	
	$month = $_POST['month'];
	$year = $_POST['year'];
	$calendar = $_POST['calendar'];
	$category = $_POST['category'];
	$event_id = $_POST['event_id'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', $category, $event_id, $author );
	
	$next_month_days = cal_days_in_month(CAL_GREGORIAN, str_pad(($month), 2, "0", STR_PAD_LEFT), $year);
	$month_number = str_pad($month, 2, "0", STR_PAD_LEFT);
	
	echo $dpProEventCalendar->eventsMonthList( $year."-".$month_number."-01 00:00:00", $year."-".$month_number."-".$next_month_days." 23:59:59" );
	
	die();
}

function dpProEventCalendar_wp_mail_from_name( $original_email_from )
{
	return get_bloginfo('name');
}

add_action( 'wp_ajax_nopriv_getSearchResults', 'dpProEventCalendar_getSearchResults' );
add_action( 'wp_ajax_getSearchResults', 'dpProEventCalendar_getSearchResults' );
 
function dpProEventCalendar_getSearchResults() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !isset($_POST['key'])) { die(); }
	
	$calendar = $_POST['calendar'];
	$key = $_POST['key'];
	$type = $_POST['type'];
	$author = $_POST['author'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar, null, null, '', '', '', $author );
	
	die($dpProEventCalendar->getSearchResults( $key, $type ));
}

add_action( 'wp_ajax_nopriv_getCategoryResults', 'dpProEventCalendar_getCategoryResults' );
add_action( 'wp_ajax_getCategoryResults', 'dpProEventCalendar_getCategoryResults' );
 
function dpProEventCalendar_getCategoryResults() {
	
    $nonce = $_POST['postEventsNonce'];
	//if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
    //    die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !isset($_POST['key'])) { die(); }
	
	$calendar = $_POST['calendar'];
	$key = $_POST['key'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( false, $calendar );
	
	die($dpProEventCalendar->getCategoryResults( $key ));
}

add_action( 'wp_ajax_setSpecialDates', 'dpProEventCalendar_setSpecialDates' );
 
function dpProEventCalendar_setSpecialDates() {

    $nonce = $_POST['postEventsNonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-get-events-nonce' ) )
        die ( 'Busted!');
		
	if(!isset($_POST['calendar']) || !isset($_POST['date'])) { die(); }
	
	$calendar = $_POST['calendar'];
	$sp = $_POST['sp'];
	$date = $_POST['date'];
	
	require_once('classes/base.class.php');

	$dpProEventCalendar = new DpProEventCalendar( true, $calendar );
	
	$dpProEventCalendar->setSpecialDates( $sp, $date );
	
	die();
}

function dpProEventCalendar_updateNotice(){
    echo '<div class="updated">
       <p>Updated Succesfully.</p>
    </div>';
}

if(@$_GET['settings-updated'] && 
($_GET['page'] == 'dpProEventCalendar-admin' 
|| $_GET['page'] == 'dpProEventCalendar-events' 
|| $_GET['page'] == 'dpProEventCalendar-special'
|| $_GET['page'] == 'dpProEventCalendar-payments')) {
	add_action('admin_notices', 'dpProEventCalendar_updateNotice');
}

function dpProEventCalendar_pro_event_calendar_init() {
  global $dpProEventCalendar;
  
  $labels = array(
    'name' => __('Lite Event Calendar', 'dpProEventCalendar'),
    'singular_name' => __('Events', 'dpProEventCalendar'),
    'add_new' => __('Add New', 'dpProEventCalendar'),
    'add_new_item' => __('Add New Event', 'dpProEventCalendar'),
    'edit_item' => __('Edit Event', 'dpProEventCalendar'),
    'new_item' => __('New Event', 'dpProEventCalendar'),
    'all_items' => __('All Events', 'dpProEventCalendar'),
    'view_item' => __('View Event', 'dpProEventCalendar'),
    'search_items' => __('Search Events', 'dpProEventCalendar'),
    'not_found' =>  __('No Events Found', 'dpProEventCalendar'),
    'not_found_in_trash' => __('No Events Found in Trash', 'dpProEventCalendar'), 
    'parent_item_colon' => '',
    'menu_name' => __('Events', 'dpProEventCalendar')
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
	'exclude_from_search' => ( $dpProEventCalendar['exclude_from_search'] ? true : false ),
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => ( $dpProEventCalendar['events_slug'] != "" ? $dpProEventCalendar['events_slug'] : 'pec-events') ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
	'show_in_menu' => 'dpProEventCalendar-admin',
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    'taxonomies' => array('pec_events_category', 'post_tag')
  ); 

  register_post_type( 'pec-events', $args );
  //flush_rewrite_rules();
  
}
add_action( 'init', 'dpProEventCalendar_pro_event_calendar_init' );

add_action( 'init', 'dpProEventCalendar_pro_event_calendar_taxonomies', 0 );

add_action( 'admin_init', 'flush_rewrite_rules' );

function dpProEventCalendar_pro_event_calendar_taxonomies() 
{
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name'                => _x( 'Categories', 'taxonomy general name' ),
    'singular_name'       => _x( 'Category', 'taxonomy singular name' ),
    'search_items'        => __( 'Search Categories' ),
    'all_items'           => __( 'All Categories' ),
    'parent_item'         => __( 'Parent Category' ),
    'parent_item_colon'   => __( 'Parent Category:' ),
    'edit_item'           => __( 'Edit Category' ), 
    'update_item'         => __( 'Update Category' ),
    'add_new_item'        => __( 'Add New Category' ),
    'new_item_name'       => __( 'New Category Name' ),
    'menu_name'           => __( 'Category' )
  ); 	

  $args = array(
    'hierarchical'        => true,
    'labels'              => $labels,
    'show_ui'             => true,
    'show_admin_column'   => true,
    'query_var'           => true,
    'rewrite'             => array( 'slug' => 'pec_events_category' )
  );

  register_taxonomy( 'pec_events_category', array( 'pec-events' ), $args );
}


add_action('admin_footer-edit.php', 'pec_custom_bulk_admin_footer');

function pec_custom_bulk_admin_footer() {

	global $post_type;
	if($post_type == 'pec-events') {
	  echo '
	  <script type="text/javascript">
	  	jQuery(document).ready(function() {
			jQuery("select[name=\'action\']").append("<option value=\'duplicate\'>Duplicate</option>");

		});
	  </script>';
	}
  
}

add_action('load-edit.php', 'pec_custom_bulk_action');

function pec_custom_bulk_action() {

	// 1. get the action
	$wp_list_table = _get_list_table('WP_Posts_List_Table');
	
	$action = $wp_list_table->current_action();
	
	// 2. security check
	if ($action != "" && check_admin_referer('bulk-posts')) {
		check_admin_referer('bulk-posts');
		
		$post_ids = $_GET['post'];
		
		switch($action) {
			
			// 3. Perform the action
			
			case 'duplicate':
							
				$duplicated = 0;
				
				foreach( $post_ids as $post_id ) {
					$my_post = get_post($post_id, "ARRAY_A" );
					unset($my_post['ID']);
					$my_post['post_category'] = array();
					$category = get_the_terms( $post_id, 'pec_events_category' ); 
					if(!empty($category)) {
						foreach ( $category as $cat){
							$my_post['post_category'][] =  $cat->term_id;
						}
					}

					if ( !$inserted = wp_insert_post( $my_post, false ) )
					
					wp_die( __('Error duplicating post.') );
					
					$meta_values = get_post_meta($post_id);
					
					foreach($meta_values as $key => $value) {
						foreach($value as $val) {
							add_post_meta($inserted, $key, $val);
						}
					}
					wp_set_post_terms( $inserted, $my_post['post_category'], 'pec_events_category' );
					$duplicated++;
				
				}
				
				// build the redirect url
				
				$sendback = add_query_arg( array( 'post_type' => 'pec-events', 'duplicated' => $duplicated, 'ids' => join(',', $post_ids) ), $sendback );
				
			break;
			
			default: return;
			
		}
		
		// 4. Redirect client
		
		wp_redirect($sendback);
		
		exit();
	}

}

add_action('admin_notices', 'pec_custom_bulk_admin_notices');

function pec_custom_bulk_admin_notices() {

	global $post_type, $pagenow;
	
	if($pagenow == 'edit.php' && $post_type == 'pec-events' &&
	
		isset($_REQUEST['duplicated']) && (int) $_REQUEST['duplicated']) {
		
		$message = sprintf( _n( 'Post duplicated.', '%s posts duplicated.', $_REQUEST['duplicated'] ), number_format_i18n( $_REQUEST['duplicated'] ) );
		
		echo "
		<div class='updated'><p>{$message}</p></div>
		";
	
	}

}

function pec_truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}

add_action('edit_post', 'dpProEventCalendar_editEvent');
add_action('publish_post', 'dpProEventCalendar_editEvent');
add_action('wp_trash_post', 'dpProEventCalendar_editEvent');
add_action('untrash_post', 'dpProEventCalendar_editEvent');
add_action('delete_post', 'dpProEventCalendar_editEvent');

function dpProEventCalendar_editEvent($post_ID) {
	global $current_user, $wpdb, $dpProEventCalendar_cache;
	
	$post_type = get_post_type( $_GET['post'] );
	
	if('pec-events' != $_POST['post_type'] && 'pec-events' != $post_type) return;
	
	$calendar_id = get_post_meta($post_ID, 'pec_id_calendar', true); 
	
	if(isset($dpProEventCalendar_cache['calendar_id_'.$calendar_id])) {
	   $dpProEventCalendar_cache['calendar_id_'.$calendar_id] = array();
	   update_option( 'dpProEventCalendar_cache', $dpProEventCalendar_cache );
   }
}

function dpProEventCalendar_contentFilter($content) {
	global $dpProEventCalendar;
	
	// assuming you have created a page/post entitled 'debug'
	if ($GLOBALS['post']->post_type == 'pec-events') {
		
		$content = '[dpProEventCalendar get="date"]'.
					'[dpProEventCalendar get="location"]'.
					'[dpProEventCalendar get="phone"]'.
					'[dpProEventCalendar get="link"]'.
					'<div class="dp_pec_clear"></div>'.
					$content.
					'[dpProEventCalendar get="map"]';
	}
	// otherwise returns the database content
	return $content;
}

add_filter( 'the_content', 'dpProEventCalendar_contentFilter' );
?>