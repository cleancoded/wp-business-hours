<?php

/**
 * Include a widget to display business hours.
 *
 * @package    WP Business Hours
 * @since      1.0.0
 * @author     CLEANCODED
 * @copyright  Copyright (c) 2017, CLEANCODED
 */
class WP_BUSINESS_HOURS_Widget extends WP_Widget {
	/**
	 * Set up the widget.
	 */
	public function __construct() {

		// Control options
		$control_options = array(
			'width'   => 444,
			'id_base' => 'WP_BUSINESS_HOURS',
		);

		// Widget info
		$widget_options = array(
			'classname'   => 'widget_wpbh',
			'description' => __( 'Displays a business hours widget', 'wp-business-hours' ),
		);

		// Create the widget
		parent::__construct(
			'WP_BUSINESS_HOURS', __( 'WP Business Hours', 'wp-business-hours' ), $widget_options, $control_options );

		// User customizations in the header
		add_action( 'wp_head', array( $this, 'wpbh_custom_styles_method_header' ) );
	}

	/**
	 * Output user customization the header for comparability
	 *
	 * @since 1.6.0
	 */
	function wpbh_custom_styles_method_header() {
		$settings = $this->get_settings();
		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			foreach ( $settings as $key => $setting ) {
				if ( isset( $setting['load_css'] ) ) {
					if ( $setting['load_css'] ) {
						echo '<style type="text/css">';
						// set custom header background and text colors
						echo '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-header{background-color:' . $setting['hcolor'] . ' !important;color:' . $setting['htxtcolor'] . ' !important;}';

						// set body background and text colors
						echo '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-body{background-color:' . $setting['bcolor'] . ' !important;color:' . $setting['btxtcolor'] . ' !important;}';

						// set footer background and text color
						echo '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-footer{background-color:' . $setting['fcolor'] . ' !important; color:' . $setting['f_text'] . ' !important;}';

						// set open/close badge color
						echo '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-body .open-status{background-color:' . $setting['op_color'] . ' !important;}';
						echo '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-body .closed-status{background-color:' . $setting['cls_color'] . ' !important;}';
						echo '</style>';

					}
				}

			}
		}

	}

	/**
	 * Save user customization in a css file
	 *
	 * @since 1.6.0
	 */
	function save_custom_style() {

		// custom style file
		$wpbh_custom_file = WP_BUSINESS_HOURS_DIR . '/css/wpbh-custom-style.css';
		global $wp_filesystem;
		// initialize the WP filesystem
		if ( empty( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		$wpbh_custom_css = $this->wpbh_custom_styles_method();
		if ( $wp_filesystem ) {
			$old = $wp_filesystem->get_contents( $wpbh_custom_file ); // read the current style file
			// only write the file if style is different
			if ( $old !== $wpbh_custom_css ) {
				$wp_filesystem->put_contents( $wpbh_custom_file, $wpbh_custom_css, FS_CHMOD_FILE );
			}
		}


	}

	/**
	 * Load the customizations in the header
	 * @since 1.6.0
	 */
	public function wpbh_custom_styles_method() {
		$settings = $this->get_settings();
		$output   = '';
		foreach ( $settings as $key => $setting ) {
			// set custom header background and text colors
			$output .= '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-header{background-color:' . $setting['hcolor'] . ' !important;color:' . $setting['htxtcolor'] . ' !important;}';

			// set body background and text colors
			$output .= '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-body{background-color:' . $setting['bcolor'] . ' !important;color:' . $setting['btxtcolor'] . ' !important;}';

			// set footer background and text color
			$output .= '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-footer{background-color:' . $setting['fcolor'] . ' !important; color:' . $setting['f_text'] . ' !important;}';

			// set open/close badge color
			$output .= '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-body .open-status{background-color:' . $setting['op_color'] . ' !important;}';
			$output .= '#adv_business_hrs-' . $setting['cssselector'] . ' .wpbh-wrapper .wpbh-body .closed-status{background-color:' . $setting['cls_color'] . ' !important;}';
		}

		return $output;
	}

	/**
	 * Widget function.
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		// save the custom style
		$this->save_custom_style();

		// Set up the arguments
		$instance = wp_parse_args( (array) $instance, wpbh_default_args() );

		// before the widget
		echo $before_widget;

		if ( $instance['title'] ) {
			echo $before_title . $instance['title'] . $after_title;
		}

		// output the widget
		echo wpbh_ajax_output( $instance );

		// after the widget
		echo $after_widget;
	}

	/**
	 * Update function.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		// open and close messages
		$instance['open_msg']  = $new_instance['open_msg'];
		$instance['close_msg'] = $new_instance['close_msg'];
		// current hours status
		$instance['cur_open']  = $new_instance['cur_open'];
		$instance['cur_close'] = $new_instance['cur_close'];
		// open and close all day message
		$instance['open_all']   = $new_instance['open_all'];
		$instance['closed_all'] = $new_instance['closed_all'];

		// holiday message
		$instance['holiday_msg'] = $new_instance['holiday_msg'];

		// seasonal message
		$instance['seasonal_msg'] = $new_instance['seasonal_msg'];
		$instance['season_start'] = $new_instance['season_start'];
		$instance['season_end']   = $new_instance['season_end'];

		// widget color customization settings
		$instance['hcolor']    = strip_tags( $new_instance['hcolor'] );
		$instance['htxtcolor'] = strip_tags( $new_instance['htxtcolor'] );
		$instance['bcolor']    = strip_tags( $new_instance['bcolor'] );
		$instance['btxtcolor'] = strip_tags( $new_instance['btxtcolor'] );
		$instance['cal_color'] = strip_tags( $new_instance['cal_color'] );
		$instance['fcolor']    = strip_tags( $new_instance['fcolor'] );
		$instance['f_text']    = strip_tags( $new_instance['f_text'] );

		// open and closer hours header image
		$instance['open_header']   = ( ! empty( $new_instance['open_header'] ) ) ? strip_tags( $new_instance['open_header'] ) : '';
		$instance['closed_header'] = ( ! empty( $new_instance['closed_header'] ) ) ? strip_tags( $new_instance['closed_header'] ) : '';

		// open and close badge colors
		$instance['op_color']  = strip_tags( $new_instance['op_color'] );
		$instance['cls_color'] = strip_tags( $new_instance['cls_color'] );

		// font awesome usage
		$instance['use-font']     = ( isset( $new_instance['use-font'] ) ? 1 : 0 );
		$instance['font-icon']    = strip_tags( $new_instance['font-icon'] );
		$instance['include-font'] = ( isset( $new_instance['include-font'] ) ? 1 : 0 );

		// extra info settings
		$instance['bfwidget'] = $new_instance['bfwidget'];
		$instance['afwidget'] = $new_instance['afwidget'];
		// record for customization
		$instance['cssselector'] = strip_tags( $new_instance['cssselector'] );
		// tabs
		$instance['tab'] = $new_instance['tab'];
		// view customizations
		$instance['all_day']    = ( isset( $new_instance['all_day'] ) ? 1 : 0 );
		$instance['all_week']   = ( isset( $new_instance['all_week'] ) ? 1 : 0 );
		$instance['short']      = ( isset( $new_instance['short'] ) ? 1 : 0 );
		$instance['group']      = ( isset( $new_instance['group'] ) ? 1 : 0 );
		$instance['count-down'] = ( isset( $new_instance['count-down'] ) ? 1 : 0 );

		// hide today's hours if closed
		$instance['hide_today']    = ( isset( $new_instance['hide_today'] ) ? 1 : 0 );
		$instance['hide_open']     = ( isset( $new_instance['hide_open'] ) ? 1 : 0 );
		$instance['show_tomorrow'] = ( isset( $new_instance['show_tomorrow'] ) ? 1 : 0 );

		// show hours as 24 hours
		$instance['show_24hr'] = ( isset( $new_instance['show_24hr'] ) ? 1 : 0 );

		// load using ajax
		$instance['load_ajax'] = ( isset( $new_instance['load_ajax'] ) ? 1 : 0 );
		// load css in the header
		$instance['load_css'] = ( isset( $new_instance['load_css'] ) ? 1 : 0 );

		// business hour data
		$instance['repeat'] = array();

		if ( isset ( $new_instance['repeat'] ) ) {
			foreach ( $new_instance['repeat'] as $k => $value ) {
				$instance['repeat'][ $k ] = $value;
			}
		}

		// holidays data
		$instance['holiday']       = array();
		$instance['holiday_date']  = array();
		$instance['holiday_start'] = array();
		$instance['holiday_end']   = array();

		if ( isset( $new_instance['holiday'] ) ) {
			foreach ( $new_instance['holiday'] as $key => $value ) {
				if ( ! empty( trim( $value ) ) ) {
					$instance['holiday'][ $key ]       = $value;
					$instance['holiday_date'][ $key ]  = $new_instance['holiday_date'][ $key ];
					$instance['holiday_start'][ $key ] = $new_instance['holiday_start'][ $key ];
					$instance['holiday_end'][ $key ]   = $new_instance['holiday_end'][ $key ];
				}
			}
		}


		return $instance;
	}


	/**
	 * Form function.
	 */
	public function form( $instance ) {
		// look for the default values
		$instance = wp_parse_args( (array) $instance, wpbh_default_args() );

		$holiday       = isset( $instance['holiday'] ) ? $instance['holiday'] : array();
		$holiday_date  = isset( $instance['holiday_date'] ) ? $instance['holiday_date'] : array();
		$holiday_start = isset( $instance['holiday_start'] ) ? $instance['holiday_start'] : array();
		$holiday_end   = isset( $instance['holiday_end'] ) ? $instance['holiday_end'] : array();
		$holiday[]     = '';
		$form          = '';

		// widget admin tabs icons using font awesome
		$tabs = array(
			'fa-cog',
			'fa-database',
			'fa-calendar-plus-o',
			'fa-paint-brush',
			'fa-eye',
			'fa-file-text-o',
			'fa-code',
		);
		// output the admin area fields
		?>
        <div class="plugin-name">
			<?php _e( 'WP Business Hours', 'wp-business-hours' ); ?>
            <span class="plugin-version">
        <?php echo WP_BUSINESS_HOURS_VERSION; ?>
      </span>
        </div>
        <script type="text/javascript">
            // Tabs function
            jQuery(document).ready(function ($) {
                // Tabs function
                $('ul.navtabs li').each(function (i) {
                    $(this).bind("click", function () {
                        var liIndex = $(this).index();
                        var content = $(this).parent("ul").next().children("li").eq(liIndex);
                        $(this).addClass('active').siblings("li").removeClass('active');
                        $(content).show().addClass('active').siblings().hide().removeClass('active');
                        $(this).parent("ul").find("input").val(0);
                        $('input', this).val(1);
                    });
                });
                // Widget background
                $("#wpbh-<?php echo $this->id; ?>").closest(".widget-inside").addClass("widgetdialogbg");
            });
        </script>
        <script type="text/javascript">
            // change the input color if empty
            jQuery(document).ready(function ($) {
                $(':input.time-picker').on('input', function () {
                    $(this).toggleClass('empty', this.value.length === 0);
                }).trigger('input');
            });
        </script>

        <!-- #wpbh begins -->
        <div id="wpbh-adm-wrapper">
            <div id="wpbh-<?php echo $this->id; ?>" class="widgetdialog tabbable tabs-left">
                <!-- display the tab options -->
                <ul class="nav navtabs">
					<?php foreach ( $tabs as $key => $tab ) : ?>
                        <li class="fes-<?php echo $key; ?>
                            <?php echo $instance['tab'][ $key ] ? 'active' : ''; ?> fa fa-lg <?php echo $tab; ?>">
                            <input type="hidden" name="<?php echo $this->get_field_name( 'tab' ); ?>[]"
                                   value="<?php echo $instance['tab'][ $key ]; ?>"/>
                        </li>
					<?php endforeach; ?>
                </ul>
                <!-- display the content -->
                <ul class="tabcontent">
                    <!-- first tab begins -->
                    <li class="tab-pane <?php if ( $instance['tab'][0] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- title of the widget -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'title' ); ?>">
									<?php _e( 'Title', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                                       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['title'] ); ?>"/>
                            </li>
                            <!-- open message -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'open_msg' ); ?>">
									<?php _e( 'Open Hours Message', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'open_msg' ); ?>"
                                       name="<?php echo $this->get_field_name( 'open_msg' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['open_msg'] ); ?>"/>
                            </li>
                            <!-- closed message -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'close_msg' ); ?>">
									<?php _e( 'Closed Hours Message', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'close_msg' ); ?>"
                                       name="<?php echo $this->get_field_name( 'close_msg' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['close_msg'] ); ?>"/>
                            </li>
                            <!-- current open message -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'cur_open' ); ?>">
									<?php _e( 'Currently Open/Closed Badges', 'wp-business-hours' ); ?>
                                </label>
                                <input class="column-last" id="<?php echo $this->get_field_id( 'cur_open' ); ?>"
                                       name="<?php echo $this->get_field_name( 'cur_open' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['cur_open'] ); ?>" size="20"/>

                                <input class="column-last" id="<?php echo $this->get_field_id( 'cur_close' ); ?>"
                                       name="<?php echo $this->get_field_name( 'cur_close' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['cur_close'] ); ?>" size="20"/>
                            </li>
                            <!-- open 24 hours message -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'open_all' ); ?>">
									<?php _e( 'Open 24 hours Text', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'open_all' ); ?>"
                                       name="<?php echo $this->get_field_name( 'open_all' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['open_all'] ); ?>"/>
                            </li>
                            <!-- closed all day -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'closed_all' ); ?>">
									<?php _e( 'Closed All Day Text', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'closed_all' ); ?>"
                                       name="<?php echo $this->get_field_name( 'closed_all' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['closed_all'] ); ?>"/>
                            </li>
                            <!-- holiday message -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'holiday_msg' ); ?>">
									<?php _e( 'Holiday Message for Header', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'holiday_msg' ); ?>"
                                       name="<?php echo $this->get_field_name( 'holiday_msg' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['holiday_msg'] ); ?>"/>
                            </li>
                            <!-- seasonal message -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'seasonal_msg' ); ?>">
									<?php _e( 'Seasonal Message', 'wp-business-hours' ); ?>
                                </label>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'seasonal_msg' ); ?>"
                                       name="<?php echo $this->get_field_name( 'seasonal_msg' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['seasonal_msg'] ); ?>"/>
                            </li>
                        </ul>
                    </li>
                    <!-- business hours data -->
                    <li class="tab-pane <?php if ( $instance['tab'][1] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- data -->
                            <li>
                                <label>
									<?php _e( 'Business Hours Data', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Opening and closing hours using 12 hr clock. Ex: start at 8:00 AM and end at 4:00 PM.', 'wp-business-hours' ); ?>
                                </span>
                            </li>
							<?php
							$week_hours_data   = week_hours_data();
							$repeatable_fields = isset ( $instance['repeat'] ) ? $instance['repeat'] : array();
							?>
							<?php foreach ( $week_hours_data as $day_key => $day_info ) { ?>
                                <li class="weekly-data">

                                    <label class="week-days"><?php echo $day_info['short']; ?></label>
                                    <table id="repeatable-fieldset-one">

                                        <tbody>
										<?php
										$start_time = isset( $repeatable_fields[ $day_key ]["start_time"] ) ? $repeatable_fields[ $day_key ]["start_time"] : '';
										$end_time   = isset( $repeatable_fields[ $day_key ]["end_time"] ) ? $repeatable_fields[ $day_key ]["end_time"] : '';

										if ( $start_time || $end_time ) :
											foreach ( $repeatable_fields[ $day_key ]["start_time"] as $k => $field ) {
												?>
                                                <tr>
                                                    <td><input type="text" class="widefat"
                                                               name="<?php echo $this->get_field_name( 'repeat' ) ?>[<?php echo $day_key; ?>][start_time][]"
                                                               value="<?php echo $field; ?>"
                                                        />

                                                    </td>

                                                    <td><input type="text" class="widefat"
                                                               name="<?php echo $this->get_field_name( 'repeat' ) ?>[<?php echo $day_key; ?>][end_time][]"
                                                               value="<?php echo $repeatable_fields[ $day_key ]["end_time"][ $k ]; ?>"
                                                        />
                                                    </td>

                                                    <td><a class="button remove-row" href="#">-</a></td>
                                                    <td><a class="button add-row" href="#">+</a></td>

                                                </tr>
												<?php
											}
										else :
											?>
                                            <tr>
                                                <td><input type="text" class="widefat"
                                                           name="<?php echo $this->get_field_name( 'repeat' ) ?>[<?php echo $day_key; ?>][start_time][]"/>
                                                </td>


                                                <td><input type="text" class="widefat"
                                                           name="<?php echo $this->get_field_name( 'repeat' ) ?>[<?php echo $day_key; ?>][end_time][]"
                                                           value=""/></td>

                                                <td><a class="button remove-row" href="#">-</a></td>
                                                <td><a class="button add-row" href="#">+</a></td>

                                            </tr>
										<?php endif; ?>
                                        </tbody>
                                    </table>
                                </li>
							<?php } ?>

                            <li>
								<span class="controlDesc">
									<?php _e( '<strong>NOTE:</strong> If both fields are left empty, the front end will display hours as closed all day.', 'wp-business-hours' ); ?>
								</span>
                            </li>
                        </ul>
                    </li>
                    <!-- holidays -->
                    <li class="tab-pane <?php if ( $instance['tab'][2] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- data -->
                            <li>
                                <label>
									<?php _e( 'Holidays & Special Days', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Holidays data only works for today\'s hours. Please visit <a href="https://support.bestwebsite.com/" target="_blank">Help Center</a> for details.', 'wp-business-hours' ); ?>
                                </span>
                            </li>

							<?php
							foreach ( $holiday as $index => $value ) {
								$h_name  = isset( $holiday[ $index ] ) ? $holiday[ $index ] : '';
								$h_date  = isset( $holiday_date[ $index ] ) ? $holiday_date[ $index ] : '';
								$h_start = isset( $holiday_start[ $index ] ) ? $holiday_start[ $index ] : '';
								$h_end   = isset( $holiday_end[ $index ] ) ? $holiday_end[ $index ] : '';
								?>
                                <li>
                                    <table id="holidays-fieldset-one" width="100%">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <input type="text"
                                                       name="<?php echo $this->get_field_name( 'holiday' ); ?>[<?php echo $index; ?>]"
                                                       value="<?php echo $h_name; ?>"
                                                       class="widefat"
                                                       placeholder="Christmas"
                                                >

                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="<?php echo $this->get_field_name( 'holiday_date' ); ?>[<?php echo $index; ?>]"
                                                       value="<?php echo $h_date; ?>"
                                                       class="widefat"
                                                       placeholder="12/25">

                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="text"
                                                       name="<?php echo $this->get_field_name( 'holiday_start' ); ?>[<?php echo $index; ?>]"
                                                       value="<?php echo $h_start; ?>"
                                                       class="widefat"
                                                       placeholder="10:00 AM">

                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="<?php echo $this->get_field_name( 'holiday_end' ); ?>[<?php echo $index; ?>]"
                                                       value="<?php echo $h_end; ?>"
                                                       class="widefat"
                                                       placeholder="3:00 PM">

                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </li>
							<?php } ?>

                            <!-- data -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'season_start' ); ?>">
									<?php _e( 'Seasonal Time Period', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Select seasonal start and end date. Leave empty for regular yearly hours.', 'wp-business-hours' ); ?>
                                </span>
                            </li>
                            <li>
                                <table id="seasons-fieldset-one" width="100%">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <input class="widefat"
                                                   id="<?php echo $this->get_field_id( 'season_start' ); ?>"
                                                   name="<?php echo $this->get_field_name( 'season_start' ); ?>"
                                                   type="text"
                                                   value="<?php echo esc_attr( $instance['season_start'] ); ?>"
                                                   size="20"
                                                   placeholder="6/21"/>

                                        </td>
                                        <td>
                                            <input class="widefat"
                                                   id="<?php echo $this->get_field_id( 'season_end' ); ?>"
                                                   name="<?php echo $this->get_field_name( 'season_end' ); ?>"
                                                   type="text"
                                                   value="<?php echo esc_attr( $instance['season_end'] ); ?>" size="20"
                                                   placeholder="9/22"/>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </li>

                        </ul>
                    </li>
                    <!-- customization tab -->
                    <li class="tab-pane <?php if ( $instance['tab'][3] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- header background and text colors -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'hcolor' ); ?>">
									<?php _e( 'Header Background and Text Colors', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
									<?php _e( 'Select a background color and and a text color for the widget\'s header respectively.', 'wp-business-hours' ); ?>
								</span>
                                <input type="text" name="<?php echo $this->get_field_name( 'hcolor' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'hcolor' ); ?>"
                                       value="<?php echo esc_attr( $instance['hcolor'] ); ?>"
                                       data-default-color="#FE6E4E"/>
                                <input type="text" name="<?php echo $this->get_field_name( 'htxtcolor' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'htxtcolor' ); ?>"
                                       value="<?php echo esc_attr( $instance['htxtcolor'] ); ?>"
                                       data-default-color="#FFFFFF"/>
                            </li>
                            <!-- body background and text colors -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'bcolor' ); ?>">
									<?php _e( 'Body Background and Text Colors', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Select a color for the body background and text color for the content.', 'wp-business-hours' ); ?>
                                </span>
                                <input type="text" name="<?php echo $this->get_field_name( 'bcolor' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'bcolor' ); ?>"
                                       value="<?php echo esc_attr( $instance['bcolor'] ); ?>"
                                       data-default-color="#FFFFFF"/>
                                <input type="text" name="<?php echo $this->get_field_name( 'btxtcolor' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'btxtcolor' ); ?>"
                                       value="<?php echo esc_attr( $instance['btxtcolor'] ); ?>"
                                       data-default-color="#486060"/>
                            </li>
                            <!-- calendar image color -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'cal_color' ); ?>">
									<?php _e( 'Calendar Image/Icon Color', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Color for the calendar image/icon in the header.', 'wp-business-hours' ); ?>
                                </span>
                                <input type="text" name="<?php echo $this->get_field_name( 'cal_color' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'cal_color' ); ?>"
                                       value="<?php echo esc_attr( $instance['cal_color'] ); ?>"
                                       data-default-color="#FFFFFF"/>
                            </li>
                            <!-- footer background color -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'fcolor' ); ?>">
									<?php _e( 'Footer Background and Text Color', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Select footer background color and text color respectively.', 'wp-business-hours' ); ?>
                                </span>
                                <input type="text" name="<?php echo $this->get_field_name( 'fcolor' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'fcolor' ); ?>"
                                       value="<?php echo esc_attr( $instance['fcolor'] ); ?>"
                                       data-default-color="#f8f4f1"/>
                                <input type="text" name="<?php echo $this->get_field_name( 'f_text' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'f_text' ); ?>"
                                       value="<?php echo esc_attr( $instance['f_text'] ); ?>"
                                       data-default-color="#486060"/>
                            </li>
                            <!-- open and closed message colors -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'op_color' ); ?>">
									<?php _e( 'Open/Closed Badge Color', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Select a color for the open and closed badges respectively.', 'wp-business-hours' ); ?>
                                </span>
                                <input type="text" name="<?php echo $this->get_field_name( 'op_color' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'op_color' ); ?>"
                                       value="<?php echo esc_attr( $instance['op_color'] ); ?>"
                                       data-default-color="#C5D92B"/>
                                <input type="text" name="<?php echo $this->get_field_name( 'cls_color' ); ?>"
                                       class="color-picker" id="<?php echo $this->get_field_id( 'cls_color' ); ?>"
                                       value="<?php echo esc_attr( $instance['cls_color'] ); ?>"
                                       data-default-color="#FE6E4E"/>
                            </li>


                        </ul>
                    </li>
                    <!-- Display customization tab -->
                    <li class="tab-pane <?php if ( $instance['tab'][4] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- display all week days -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'all_week' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['all_week'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'all_week' ); ?>"
                                           name="<?php echo $this->get_field_name( 'all_week' ); ?>"/>
									<?php _e( 'Display hours for the entire week', 'wp-business-hours' ); ?></label>
                            </li>
                            <!-- display week days as short name -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'short' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['short'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'short' ); ?>"
                                           name="<?php echo $this->get_field_name( 'short' ); ?>"/>
									<?php _e( 'Abbreviations of days of the week', 'wp-business-hours' ); ?></label>
                            </li>
                            <!-- hide today's hours if closed -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'hide_today' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['hide_today'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'hide_today' ); ?>"
                                           name="<?php echo $this->get_field_name( 'hide_today' ); ?>"/>
									<?php _e( 'Hide today\'s hours when closed', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- hide today's hours if open -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'hide_open' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['hide_open'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'hide_open' ); ?>"
                                           name="<?php echo $this->get_field_name( 'hide_open' ); ?>"/>
									<?php _e( 'Hide today\'s hours when open', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- show tomorrow's hours -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'show_tomorrow' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['show_tomorrow'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'show_tomorrow' ); ?>"
                                           name="<?php echo $this->get_field_name( 'show_tomorrow' ); ?>"/>
									<?php _e( 'Show tomorrow\'s hours', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- display all day as 24 hours -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'all_day' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['all_day'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'all_day' ); ?>"
                                           name="<?php echo $this->get_field_name( 'all_day' ); ?>"/>
									<?php _e( 'Display open 24 hours for certain days', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- display hours as 24 hours -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'show_24hr' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['show_24hr'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'show_24hr' ); ?>"
                                           name="<?php echo $this->get_field_name( 'show_24hr' ); ?>"/>
									<?php _e( 'Display hours using 24 hour format', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- group days -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'group' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['group'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'group' ); ?>"
                                           name="<?php echo $this->get_field_name( 'group' ); ?>"/>
									<?php _e( 'Group consecutive days', 'wp-business-hours' ); ?></label>
                            </li>
                            <!-- using font awesome as icon -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'use-font' ); ?>">
                                    <input class="checkbox use-font"
                                           type="checkbox" <?php checked( $instance['use-font'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'use-font' ); ?>"
                                           name="<?php echo $this->get_field_name( 'use-font' ); ?>"/>
									<?php _e( 'Use Font Awesome Icon', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc left-padding">
                                    <?php _e( 'Visit <a href="http://fontawesome.io/icons/" target="_blank">here</a> to get list of icons.', 'wp-business-hours' ); ?>
								</span>
                                <input class="left-padding" id="<?php echo $this->get_field_id( 'font-icon' ); ?>"
                                       name="<?php echo $this->get_field_name( 'font-icon' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['font-icon'] ); ?>" size="20"/>
                            </li>
                            <!-- include font awesome files -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'include-font' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['include-font'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'include-font' ); ?>"
                                           name="<?php echo $this->get_field_name( 'include-font' ); ?>"/>
									<?php _e( 'Include Font Awesome CSS File in Front End', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- countdown in the footer -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'count-down' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['count-down'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'count-down' ); ?>"
                                           name="<?php echo $this->get_field_name( 'count-down' ); ?>"/>
									<?php _e( 'Countdown to Closing and Opening Hours', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- use ajax for loading -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'load_ajax' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['load_ajax'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'load_ajax' ); ?>"
                                           name="<?php echo $this->get_field_name( 'load_ajax' ); ?>"/>
									<?php _e( 'Load widget using ajax', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                            <!-- load css in the header -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'load_css' ); ?>">
                                    <input class="checkbox"
                                           type="checkbox" <?php checked( $instance['load_css'], true ); ?>
                                           id="<?php echo $this->get_field_id( 'load_css' ); ?>"
                                           name="<?php echo $this->get_field_name( 'load_css' ); ?>"/>
									<?php _e( 'Load customizations as inline style in the header', 'wp-business-hours' ); ?>
                                </label>
                            </li>
                        </ul>
                    </li>
                    <!-- Extra tab -->
                    <li class="tab-pane <?php if ( $instance['tab'][5] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- before the widget info -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'bfwidget' ); ?>">
									<?php _e( 'Before Widget', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Info to display before the widget. HTML tags are supported.', 'wp-business-hours' ); ?>
								</span>
                                <textarea name="<?php echo $this->get_field_name( 'bfwidget' ); ?>"
                                          id="<?php echo $this->get_field_id( 'bfwidget' ); ?>" rows="3"
                                          class="widefat"><?php echo esc_textarea( $instance['bfwidget'] ); ?></textarea>
                            </li>
                            <!-- after the widget info -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'afwidget' ); ?>">
									<?php _e( 'After Widget', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Info to display after the widget. HTML tags are supported.', 'wp-business-hours' ); ?>
                                </span>
                                <textarea name="<?php echo $this->get_field_name( 'afwidget' ); ?>"
                                          id="<?php echo $this->get_field_id( 'afwidget' ); ?>" rows="3"
                                          class="widefat"><?php echo esc_textarea( $instance['afwidget'] ); ?></textarea>
                            </li>
                            <!-- image upload -->
                            <li>
                                <label for="<?php echo $this->get_field_id( 'open_header' ); ?>">
									<?php _e( 'Header Background Image', 'wp-business-hours' ); ?>
                                </label>

                                <span class="controlDesc">
                                    <?php _e( 'Header image during opening hours.', 'wp-business-hours' ); ?>
                                </span>
                                <input class="column-last" id="<?php echo $this->get_field_id( 'open_header' ); ?>"
                                       name="<?php echo $this->get_field_name( 'open_header' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['open_header'] ); ?>" size="30"/>

                                <button class="wpbh-add-media-button button button-primary" data-type="image">Upload
                                </button>
                                <span class="controlDesc top-padding">
                                    <?php _e( 'Header image during closing hours.', 'wp-business-hours' ); ?>
                                </span>
                                <input class="column-last" id="<?php echo $this->get_field_id( 'closed_header' ); ?>"
                                       name="<?php echo $this->get_field_name( 'closed_header' ); ?>" type="text"
                                       value="<?php echo esc_attr( $instance['closed_header'] ); ?>" size="30"/>

                                <button class="wpbh-add-media-button button button-primary" data-type="image">Upload
                                </button>
                                <span class="controlDesc" style="margin-top: 10px;">
                                    <?php _e( '<strong>NOTE:</strong> Must set both images, this will replace the default header. Which means calendar image and today\'s date will be hidden.', 'wp-business-hours' ); ?>
                                </span>

                            </li>
                        </ul>
                    </li>
                    <!-- Shortcode tab -->
                    <li class="tab-pane <?php if ( $instance['tab'][6] ) : ?>active<?php endif; ?>">
                        <ul>
                            <!-- shortcode -->
                            <li>
                                <label>
									<?php _e( 'Shortcode', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'Use the following shortcode to display this widget anywhere in your posts and pages.', 'wp-business-hours' ); ?>
                                </span>
                                <input class="widefat" id="<?php echo $this->get_field_id( 'cssselector' ); ?>"
                                       name="<?php echo $this->get_field_name( 'cssselector' ); ?>" type="text"
                                       value="<?php echo esc_attr( $this->number ); ?>" style="display:none;"/>
                                <span class="wpbhshortcode">
                                    <?php echo '[wp-business-hours id="' . $this->number . '"]'; ?>
                                </span>
                                <span class="controlDesc">
                                    <?php _e( '<strong>Note:</strong> You must save the widget first and drag it to inactive widgets area at the bottom of this page and configure it.', 'wp-business-hours' ); ?>
								</span>
                            </li>
                            <li>
                                <label>
									<?php _e( 'Shortcode for Themes', 'wp-business-hours' ); ?>
                                </label>
                                <span class="controlDesc">
                                    <?php _e( 'If you want to insert the shortcode directly in your theme, use the following code:', 'wp-business-hours' ); ?>
                                </span>
                                <span class="wpbhshortcode">
                                    <?php echo '&lt;?php echo do_shortcode(\'[wp-business-hours id="' . $this->number . '"]\'); ?&gt;'; ?>
                                </span>
                            </li>
                        </ul>
                    </li>
                </ul><!-- .tabcontent -->
            </div><!-- #wpbh ends -->
        </div><!-- #wpbh-adm-wrapper -->
		<?php
	}
}
