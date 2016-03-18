<?php
	if(!function_exists("readfile_chunked"))
	{
		// from: http://cn2.php.net/manual/en/function.readfile.php#48683
		// Read a file and display its content chunk by chunk
		function readfile_chunked($filename, $retbytes = TRUE) {
			$buffer = '';
			$cnt =0;
			// $handle = fopen($filename, 'rb');
			$handle = fopen($filename, 'rb');
			if ($handle === false) {
				return false;
			}
			while (!feof($handle)) {
				$buffer = fread($handle, CHUNK_SIZE);
				echo $buffer;
				ob_flush();
				flush();
				if ($retbytes) {
					$cnt += strlen($buffer);
				}
			}
			$status = fclose($handle);
			if ($retbytes && $status) {
				return $cnt; // return num. bytes delivered like readfile() does.
			}
			return $status;
		}
	}

	if(!function_exists("enclose"))
	{
		function enclose($s)
		{
			return "\"" . str_replace("\"", "\\\"", $s) . "\"";
		}
	}

	function ssd_getOption($s)
	{
		if(isset($_REQUEST[$s]) && $_REQUEST[$s])
			return $_REQUEST[$s];
		elseif(get_option("ssd_" . $s))
			return get_option("ssd_" . $s);
		else
			return "";
	}

	function ssd_setOption($s, $v = NULL)
	{
		//no value is given, set v to the request var
		if($v === NULL)
			$v = $_REQUEST[$s];

		return update_option("ssd_" . $s, $v);
	}

// templating functions //
$ssd_template_folder='templates/';

/**
 * Retrieves a template part
 *
 * Taken from bbPress
 *
 * @param string $slug
 * @param string $name Optional. Default null
 *
 * @uses  ssd_locate_template()
 * @uses  load_template()
 * @uses  get_template_part()
 */
function ssd_get_template_part( $slug, $name = null, $atts=array(), $load = true ) {
	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'ssd_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return ssd_locate_template( $templates, $load, false, $atts );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *                            Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function ssd_locate_template( $template_names, $load = false, $require_once = true, $atts=array() ) {
	global $ssd_template_folder;

	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// Check child theme first
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $ssd_template_folder . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . $ssd_template_folder . $template_name;
			break;

		// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $ssd_template_folder . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . $ssd_template_folder . $template_name;
			break;

		// Check theme compatibility last
		} elseif ( file_exists( trailingslashit( ssd_get_templates_dir() ) . $template_name ) ) {
			$located = trailingslashit( ssd_get_templates_dir() ) . $template_name;
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )
		ssd_load_template( $located, $require_once, $atts );

	return $located;
}

/**
 * Require the template file with WordPress environment.
 *
 * The globals are set up for the template file to ensure that the WordPress
 * environment is available from within the function. The query variables are
 * also available.
 *
 * Mirrors the WP load_template() function.
 * However, we remove $require_once in place of an include_once to allow our variables
 *
 * @global array      $posts
 * @global WP_Post    $post
 * @global bool       $wp_did_header
 * @global WP_Query   $wp_query
 * @global WP_Rewrite $wp_rewrite
 * @global wpdb       $wpdb
 * @global string     $wp_version
 * @global WP         $wp
 * @global int        $id
 * @global WP_Comment $comment
 * @global int        $user_ID
 *
 * @param string $_template_file Path to template file.
 * @param bool   $include_once   Whether to include_once or include. Default true.
 */
function ssd_load_template( $_template_file, $include_once = true, $atts=array() ) {
  global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

  if ( is_array( $wp_query->query_vars ) ) {
          extract( $wp_query->query_vars, EXTR_SKIP );
  }

  if ( isset( $s ) ) {
          $s = esc_attr( $s );
  }

  if ( $include_once ) {
          include_once( $_template_file );
  } else {
          include( $_template_file );
  }
}

/**
 * ssd_get_templates_dir function.
 *
 * @access public
 * @return void
 */
function ssd_get_templates_dir() {
	global $ssd_template_folder;

	return plugin_dir_path(dirname(__FILE__)).$plugin_template_folder;
}
?>