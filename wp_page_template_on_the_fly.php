<?php
/**
 * Plugin Name: WP Page-Template on-the-fly
 * Plugin URI: http://github.com
 * Description: This plugin creates a page template, a test page and adds template to test page in one go. Saves time when testing/trying out new code
 * Version: 1.0.0
 * Author: Andreas Neuber
 * Author URI: https://github.com/andreasneuber
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'Dont be so direct..' );


class wp_pagetemplate_on_the_fly {


	public $page_template_name;


	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page_template_editor_page' ) );
	}


	public function add_page_template_editor_page(){

		add_management_page(
			'PageTemplate on-the-fly',
			'PageTemplate on-the-fly',
			'manage_options',
			'wp_page_template_on_the_fly',
			array( $this, 'page_template_editor_page' ) );

	}


	public function page_template_editor_page(){

		//TODO - the usual sanitize, validate, nounce etc.
		if($_POST){

			$page_template_name = filter_var( $_POST['page_template_name'], FILTER_SANITIZE_STRING);
			$page_template_name = strlen( $page_template_name ) < 1 ? 'Page template ' . time() : $page_template_name;
			$this->page_template_name = $page_template_name;

			$custom_code = trim( $_POST['custom_code'] );
			$custom_code = stripslashes( $custom_code );

			$page_file_name = 'page_template_' . time() . '.php';
			$file_path      = get_stylesheet_directory() . "/" . $page_file_name;
			$myfile         = fopen( $file_path, "w") or die("Unable to open file!");

			$txt = "<?php /* Template Name: " . $page_template_name ." */ ?>\n";
			fwrite($myfile, $txt);

			$txt = "<?php get_header(); ?>\n\n";
			fwrite($myfile, $txt);

			$txt = $custom_code . "\n\n";
			fwrite($myfile, $txt);


			$txt = "<?php get_sidebar(); ?>\n";
			fwrite($myfile, $txt);

			$txt = "<?php get_footer(); ?>\n";
			fwrite($myfile, $txt);

			fclose($myfile);


			// Now create a page and add page template to it
			$page_id = $this->create_test_page();
			$this->add_page_template_to_test_page( $page_id, $page_file_name );
		}
		?>

		<div class="wrap">
					<div id="icon-plugins" class="icon32"></div>
					<h2>PageTemplate on-the-fly</h2>


					<form method="post" action="tools.php?page=wp_page_template_on_the_fly">

						<table class="form-table">

							<tr valign="top">
								<th scope="row">File name</th>
								<td>
									<input name="file_name"/>.php
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Page template name</th>
								<td>
									<input name="page_template_name"/>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">Your custom PHP code</th>
								<td>
									<textarea name="custom_code" rows="10" cols="70"></textarea>
								</td>
							</tr>

						</table>


						<?php
						submit_button();
						?>
					</form>

		</div>
		<?php
	}


	private function create_test_page(){

		$new_test_page = array(
			'post_title'    => 'Test Page - page template: ' . $this->page_template_name,
			'post_content'  => 'content',
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_author'   => 1
		);

		// Insert the post into the database
		$page_id = wp_insert_post( $new_test_page );

		return $page_id;
	}


	private function add_page_template_to_test_page( $page_id, $page_file_name ){
		add_post_meta( $page_id, '_wp_page_template', $page_file_name );
	}

}

$flying_pagetemplate = new wp_pagetemplate_on_the_fly();