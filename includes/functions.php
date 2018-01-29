<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_wpbh_ajax_widget', 'wpbh_ajax_text' );
add_action( 'wp_ajax_nopriv_wpbh_ajax_widget', 'wpbh_ajax_text' );

/**
 * Handle ajax output
 *
 * @since 1.6.0
 */
function wpbh_ajax_text() {
	$nonce = $_POST['ajax_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'wpbh-ajax-nonce' ) ) {
		die ();
	}

	$data   = $_POST['data'];
	$return = array();
	if ( is_array( $data ) ) {
		foreach ( $data as $key => $text ) {
			$return[ $key ] = do_shortcode( base64_decode( $text ) );
		}
	}
	echo json_encode( $return );
	exit;
}

/**
 * Output the widget
 *
 * @param $args
 *
 * @return string
 * @since 1.6.0
 */
function wpbh_ajax_output( $args ) {

	$wpbh_main_output = wpbh_output( $args );
	if ( $args['load_ajax'] ) {
		$wpbh_main_output = $args['cssselector'];
		$main_shortcode  = base64_encode( '[wp-business-hours-ajax id="' . $wpbh_main_output . '"]' );
		$output          = '';

		$output .= "<div class='wpbh_ajax_widgets' data-text='" . $main_shortcode . "'>";
		$output .= "<div class='wpbh-spinner'><div class='wpbh-double-bounce1'></div><div class='wpbh-double-bounce2'></div></div>";
		$output .= "</div>";

		return $output;
	}

	return $wpbh_main_output;
}

/**
 * Set of default arguments.
 *
 * @return array
 * @since 1.0.0
 */
function wpbh_default_args() {
	return array(
		'title'         => esc_attr__( 'Business Hours', 'wp-business-hours' ),
		'open_msg'      => esc_attr__( 'We are currently open! Yes, come in', 'wp-business-hours' ),
		'close_msg'     => esc_attr__( 'Sorry, We are currently closed!', 'wp-business-hours' ),
		'cur_open'      => esc_attr__( 'Open', 'wp-business-hours' ),
		'cur_close'     => esc_attr__( 'Closed', 'wp-business-hours' ),
		'open_all'      => esc_attr__( 'Open 24 hours', 'wp-business-hours' ),
		'closed_all'    => esc_attr__( 'Closed All Day', 'wp-business-hours' ),
		'holiday_msg'   => esc_attr__( 'Special holiday hours in effect.', 'wp-business-hours' ),
		'seasonal_msg'  => esc_attr__( 'We are closed for the season. We open on', 'wp-business-hours' ),
		'season_start'  => '',
		'season_end'    => '',
		'open_header'   => '',
		'closed_header' => '',
		'hcolor'        => '#FE6E4E',
		'htxtcolor'     => '#FFFFFF',
		'bcolor'        => '#FFFFFF',
		'btxtcolor'     => '#486060',
		'cal_color'     => '#FFFFFF',
		'fcolor'        => '#f8f4f1',
		'f_text'        => '#486060',
		'op_color'      => '#C5D92B',
		'cls_color'     => '#FE6E4E',
		'use-font'      => false,
		'font-icon'     => 'fa-calendar-o',
		'include-font'  => false,
		'bfwidget'      => '',
		'afwidget'      => '',
		'cssselector'   => '',
		'all_day'       => false,
		'all_week'      => false,
		'short'         => false,
		'group'         => false,
		'count-down'    => false,
		'hide_today'    => false,
		'hide_open'     => false,
		'show_tomorrow' => false,
		'show_24hr'     => false,
		'load_ajax'     => false,
		'load_css'      => false,
		'tab'           => array(
			0 => true,
			1 => false,
			2 => false,
			3 => false,
			4 => false,
			5 => false,
			6 => false,
		),
		'holiday'       => array(),
		'holiday_date'  => array(),
		'holiday_start' => array(),
		'holiday_end'   => array(),
	);
}

/**
 * @param $args
 *
 * @return string
 */
function wpbh_output( $args ) {
	// create the week days data array
	$week_hours_data  = week_hours_data();
	$open_status_flag = false; // set a status flag
	$is_today_flag    = false; // is today flag
	$is_holiday_flag  = false; // is today a holiday flag
	$open_header      = $args['open_header'];
	$closed_header    = $args['closed_header'];

	$holiday_dates  = $args['holiday_date']; // get all holidays
	$holiday_names  = $args['holiday']; // get all holidays names
	$holiday_starts = $args['holiday_start']; // get all holidays start hours
	$holiday_ends   = $args['holiday_end']; // get all holidays end hours
	$holiday_index  = ''; // create a temporary holiday index

	$season_start_date = date_i18n( "F j", strtotime( $args['season_start'] ) ); // season start day

	// check to se if today is a holiday
	foreach ( $holiday_dates as $holiday_day => $holiday_info ) {
		if ( ( $holiday_info == date_i18n( 'n/j' ) ) || ( $holiday_info == current_time( 'n/j' ) ) ) {
			$is_holiday_flag = true;
			$holiday_index   = $holiday_day; // set the index
		}
	}


	if ( ! empty( $args['open_header'] ) || ! empty( $args['closed_header'] ) ) {
		$open_header_image_path   = wp_get_attachment_url( $open_header );
		$closed_header_image_path = wp_get_attachment_url( $closed_header );
	}


	if ( $args['include-font'] ) {
		font_awesome_style();
	}

	$output = '';
	// text direction
	if ( function_exists( 'is_rtl' ) ) {
		$dir = is_rtl() ? 'rtl' : 'ltr';
	}
	// print the before widget info
	if ( ! empty( $args['bfwidget'] ) ) {
		$output .= '<p>' . do_shortcode( $args['bfwidget'] ) . '</p>';
	}

	// display the widget
	$output .= "<div id='adv_business_hrs-{$args['cssselector']}'><div class='wpbh-wrapper wpbh-wrap-$dir'>";

	// check season
	if ( ! check_season_period( $args['season_start'], $args['season_end'] ) && ! empty($args['season_start']) ) {
		$output .= "<div class='wpbh-body'><span class='seasonal-msg'>{$args['seasonal_msg']}</span><span class='seasonal-start'> " . $season_start_date . "</span></div>";
		$output .= "</div></div>";

		// return the output and exit
		return $output;
	}

	// set the header for holiday
	if ( $is_holiday_flag && ! empty ( $args['holiday_msg'] ) ) {

		// default header
		$output .= "<div class='wpbh-header'><div class='today-holiday'>";
		// today's date
		$output .= "<span class='holiday-name'>" . $holiday_names[ $holiday_index ] . "</span><span class='holiday-day'>" . date_i18n( 'l ' ) . date_i18n( "F j", strtotime( $holiday_dates[ $holiday_index ] ) ) . "</span>";
		if ( ! empty( $args['holiday_msg'] ) ) {
			$output .= "<span class='holiday-msg'>{$args['holiday_msg']}</span>";
		}
		$output .= "</div></div>"; // end of header

	} elseif ( ! empty( $args['open_header'] ) && ! empty( $args['closed_header'] ) ) {
		foreach ( $week_hours_data as $day_key => $day_info ) {
			if ( ( $day_info['day'] == date_i18n( 'l' ) ) || ( $day_info['day'] == current_time( 'l' ) ) ) {
				$is_today_flag = true; // set the today flag
				// check the time set
				foreach ( $args['repeat'][ $day_key ]["start_time"] as $k => $field ) {
					// if it is within the time range
					if ( check_time_range( $field, $args['repeat'][ $day_key ]["end_time"][ $k ] ) ) {
						$open_status_flag = true; // set the flag
					}
				}
				// if open flag is true
				if ( $open_status_flag ) {
					$output .= "<div class='wpbh-header' style=\"background: url('$open_header_image_path') no-repeat center center;-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;\">";
				} else {
					$output .= "<div class='wpbh-header' style=\"background: url('$closed_header_image_path') no-repeat center center;-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;\">";
				}
			}
		}
		$output .= "</div>"; // end of header
	} else {
		// default header
		$output .= "<div class='wpbh-header'>";
		// today's date
		$output .= "<div class='today-data'><span class='day-number'>" . current_time( 'j' ) . "</span><span class='day-name'>" . date_i18n( 'l' ) . "</span></div>";
		// if user selected font awesome icon
		if ( $args['use-font'] ) {
			$output .= "<div class='custom-cal'><i class='fa fa-fw {$args['font-icon']}' style='color:{$args['cal_color']};'></i></div>";
		} else {
			// display the default calendar image
			$output .= "<div class='calendar-img'>" . get_calendar_image( $args['cal_color'] ) . "</div>";
		}
		$output .= "</div>"; // end of header
	}


	// output the body
	$output .= "<div class='wpbh-body'><div class='hours-data'>";

	// only show regular hours if it is not a holiday
	if ( ! $is_holiday_flag ) {
		// loop through the entire week and output today's data
		foreach ( $week_hours_data as $day_key => $day_info ) {
			if ( ( $day_info['day'] == date_i18n( 'l' ) ) || ( $day_info['day'] == current_time( 'l' ) ) ) {
				$output .= "<div class='count-col'><div class='numbers pull-left'>" . __( 'Today', 'wp-business-hours' ) . "";
				foreach ( $args['repeat'][ $day_key ]["start_time"] as $k => $field ) {
					if ( check_time_range( $field, $args['repeat'][ $day_key ]["end_time"][ $k ] ) ) {
						$open_status_flag = true;
					}
				}
				// check to see if open and hide open is not set
				if ( $open_status_flag && ! $args['hide_open'] ) {
					$output .= "<span class='open-status'>{$args['cur_open']}</span>";
				} elseif ( $args['hide_today'] || $args['hide_open'] ) {
					$output .= "";
				} else {
					$output .= "<span class='closed-status'>{$args['cur_close']}</span>";
				}
				$output .= "</div>"; // closing div tag

				foreach ( $args['repeat'][ $day_key ]["start_time"] as $k => $field ) {
					if ( $args['all_day'] && $field == '12:00 AM' && $args['repeat'][ $day_key ]["end_time"][ $k ] == '11:59 PM' ) {
						$output .= "<div class='numbers pull-right'><span class='count'>{$args['open_all']}</span></div>";
					} else if ( ( $field == '' ) && ( $args['repeat'][ $day_key ]["end_time"][ $k ] == '' ) ) {
						$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['closed_all']}</span></div>";
					} elseif ( ! $open_status_flag && $args['hide_today'] ) {
						$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['cur_close']}</span></div>";
					} elseif ( $open_status_flag && $args['hide_open'] ) {
						$output .= "<div class='numbers pull-right'><span class='open-status'>{$args['cur_open']}</span></div>";
					} elseif ( $args['show_24hr'] ) {
						$output .= "<div class='numbers pull-right'><span class='count'>" . convert_time( $field ) . " - " . convert_time( $args['repeat'][ $day_key ]["end_time"][ $k ] ) . "</span></div>";
					} else {
						$output .= "<div class='numbers pull-right'><span class='count'>{$field} - {$args['repeat'][ $day_key ]["end_time"][ $k ]}</span></div><br>";
					}
				}
				$output .= "</div>";

				// Set opening and closing time for the JS counters
				$closing_time = date( "H:i", strtotime( $args['repeat'][ $day_key ]["end_time"][ $k ] ) );
				$opening_time = date( "H:i", strtotime( $field ) );
			}

		}
	} else {

		$output .= "<div class='count-col'><div class='numbers pull-left'>" . __( 'Today', 'wp-business-hours' ) . "";

		$holiday_starts[ $holiday_index ];
		$holiday_ends[ $holiday_index ];
		// check to status
		if ( check_time_range( $holiday_starts[ $holiday_index ], $holiday_ends[ $holiday_index ] ) ) {
			$open_status_flag = true; // set it as open
		}

		if ( $open_status_flag && ! $args['hide_open'] ) {
			$output .= "<span class='open-status'>{$args['cur_open']}</span>";
		} elseif ( $args['hide_today'] || $args['hide_open'] ) {
			$output .= "";
		} else {
			$output .= "<span class='closed-status'>{$args['cur_close']}</span>";
		}
		$output .= "</div>"; // closing div tag

		if ( $args['all_day'] && $holiday_starts[ $holiday_index ] == '12:00 AM' && $holiday_ends[ $holiday_index ] == '11:59 PM' ) {
			$output .= "<div class='numbers pull-right'><span class='count'>{$args['open_all']}</span></div>";
		} else if ( ( $holiday_starts[ $holiday_index ] == '' ) && ( $holiday_ends[ $holiday_index ] == '' ) ) {
			$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['closed_all']}</span></div>";
		} elseif ( ! $open_status_flag && $args['hide_today'] ) {
			$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['cur_close']}</span></div>";
		} elseif ( $open_status_flag && $args['hide_open'] ) {
			$output .= "<div class='numbers pull-right'><span class='open-status'>{$args['cur_open']}</span></div>";
		} elseif ( $args['show_24hr'] ) {
			$output .= "<div class='numbers pull-right'><span class='count'>" . convert_time( $holiday_starts[ $holiday_index ] ) . " - " . convert_time( $holiday_ends[ $holiday_index ] ) . "</span></div>";
		} else {
			$output .= "<div class='numbers pull-right'><span class='count'>{$holiday_starts[$holiday_index]} - {$holiday_ends[$holiday_index]}</span></div><br>";
		}
		$output .= "</div>";

		// Set opening and closing time for the JS counters
		$closing_time = date( "H:i", strtotime( $holiday_ends[ $holiday_index ] ) );
		$opening_time = date( "H:i", strtotime( $holiday_starts[ $holiday_index ] ) );


	}

	// JS counters data
	$right_now     = current_time( 'Y-m-j' );
	$time_now      = current_time( 'timestamp' );
	$tomorrow_time = date( 'Y-m-j', strtotime( '+1 day', $time_now ) );


	if ( $opening_time < $closing_time ) {
		$to_time   = $right_now . ' ' . $closing_time;
		$from_time = $right_now . ' ' . $opening_time;
	} else {
		$to_time   = $tomorrow_time . ' ' . $closing_time;
		$from_time = $right_now . ' ' . $opening_time;
	}


	// If user selected to display tomorrow's hours
	if ( $args['show_tomorrow'] ) {
		// loop through the entire week and output today's data
		foreach ( $week_hours_data as $tomorrow_key => $tomorrow_info ) {
			$time_now      = current_time( 'timestamp' ); // get the current time
			$tomorrow_time = date_i18n( 'l', strtotime( '+1 day', $time_now ) ); // set tomorrow time

			if ( ( $tomorrow_info['day'] == $tomorrow_time ) ) {
				$output .= "<div class='count-col'>";
				$output .= "<div class='numbers pull-left'>" . __( 'Tomorrow', 'wp-business-hours' ) . "</div>";

				foreach ( $args['repeat'][ $tomorrow_key ]["start_time"] as $k => $field ) {
					if ( $args['all_day'] && $field == '12:00 AM' && $args['repeat'][ $tomorrow_key ]["end_time"][ $k ] == '11:59 PM' ) {
						$output .= "<div class='numbers pull-right'><span class='count'>{$args['open_all']}</span></div>";
					} else if ( ( $field == '' ) && ( $args['repeat'][ $tomorrow_key ]["end_time"][ $k ] == '' ) ) {
						$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['closed_all']}</span></div>";
					} elseif ( $args['show_24hr'] ) {
						$output .= "<div class='numbers pull-right'><span class='count'>" . convert_time( $field ) . " - " . convert_time( $args['repeat'][ $tomorrow_key ]["end_time"][ $k ] ) . "</span></div>";
					} else {
						$output .= "<div class='numbers pull-right'><span class='count'>{$field} - {$args['repeat'][ $tomorrow_key ]["end_time"][ $k ]}</span></div><br>";
					}
				}

				$output .= "</div>";
			}
		}
	}


	// if user selected to display entire week
	if ( $args['all_week'] ) {
		// if grouping is enabled
		if ( $args['group'] ) {
			$groupArray         = array(); // create a new array for grouping
			$prev_group_key     = '';
			$group_key_appendix = 0; // appendix
			// loop through the entire week data

			foreach ( $week_hours_data as $data_key => $data_day ) {

				$group_key = $args['repeat'][ $data_key ]["start_time"][0] . ' - ' . $args['repeat'][ $data_key ]["end_time"][0];

				if ( $group_key !== $prev_group_key ) {
					$group_key_appendix ++;
				}
				$prev_group_key = $group_key;
				$real_group_key = $group_key . '_' . $group_key_appendix;

				if ( ! isset( $groupArray[ $real_group_key ] ) ) {
					$groupArray[ $real_group_key ] = [];
				}
				// create the final array
				$groupArray[ $real_group_key ][ $data_key ] = $data_day;


			}

			foreach ( $groupArray as $data_key => $data ) {
				$firstDay = key( $data );
				// remove the appendix
				$data_key = substr( $data_key, 0, strpos( $data_key, '_' ) );
				// check and see if the group contains more than one day
				if ( count( $data ) > 1 ) {
					$lastDay = end( $data );
					// if user selected to display short week names
					if ( $args['short'] ) {
						$output .= "<div class='count-col'><div class='numbers pull-left'>"
						           . $data[ $firstDay ]['short'] . ' - ' . $lastDay['short'] . "</div>";
					} else {
						$output .= "<div class='count-col'><div class='numbers pull-left'>"
						           . $data[ $firstDay ]['day'] . ' - ' . $lastDay['day'] . "</div>";
					}
				} else {
					if ( $args['short'] ) {
						$output .= "<div class='count-col'><div class='numbers pull-left'>" . $data[ $firstDay ]['short'] . "</div>";
					} else {
						$output .= "<div class='count-col'><div class='numbers pull-left'>" . $data[ $firstDay ]['day'] . "</div>";
					}

				}
				// display the time
				if ( $args['all_day'] && $data_key === '12:00 AM - 11:59 PM' ) {
					$output .= "<div class='numbers pull-right'><span class='count'>{$args['open_all']}</span></div>";
				} else if ( $data_key == ' - ' ) {
					$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['closed_all']}</span></div>";
				} elseif ( $args['show_24hr'] ) {
					$output .= "<div class='numbers pull-right'><span class='count'>" . convert_time_set( $data_key ) . "</span></div>";
				} else {
					$output .= "<div class='numbers pull-right'><span class='count'>{$data_key}</span></div>";
				}

				$output .= "</div>";
			}


		} else {
			foreach ( $week_hours_data as $day_key => $day_info ) {
				if ( $args['short'] ) {
					$output .= "<div class='count-col'><div class='numbers pull-left'>{$day_info['short']}</div>";
				} else {
					$output .= "<div class='count-col'><div class='numbers pull-left'>{$day_info['day']}</div>";
				}
				foreach ( $args['repeat'][ $day_key ]["start_time"] as $k => $field ) {
					if ( $args['all_day'] && $field == '12:00 AM' && $args['repeat'][ $day_key ]["end_time"][ $k ] == '11:59 PM' ) {
						$output .= "<div class='numbers pull-right'><span class='count'>{$args['open_all']}</span></div>";
					} else if ( ( $field == '' ) && ( $args['repeat'][ $day_key ]["end_time"][ $k ] == '' ) ) {
						$output .= "<div class='numbers pull-right'><span class='closed-status'>{$args['closed_all']}</span></div>";
					} elseif ( $args['show_24hr'] ) {
						$output .= "<div class='numbers pull-right'><span class='count'>" . convert_time( $field ) . " - " . convert_time( $args['repeat'][ $day_key ]["end_time"][ $k ] ) . "</span></div>";
					} else {
						$output .= "<div class='numbers pull-right'><span class='count'>{$field} - {$args['repeat'][ $day_key ]["end_time"][ $k ]}</span></div><br>";
					}

				}
				$output .= "</div>";
			}
		}
	}

	// closing divs for the body
	$output .= '</div></div>';

	// output the footer
	if ( ! empty( $args['open_msg'] ) || ! empty( $args['close_msg'] ) ) {
		$output .= "<div class='wpbh-footer'>";
		if ( $open_status_flag ) {
			$output .= "{$args['open_msg']}";
			if ( $args['count-down'] ) {
				$output .= "<div id='closing-target' style='display: none;'>" . htmlspecialchars( $to_time ) . "</div>";
				closing_counter(); // load the js
				$output .= "<div id='closing-countdown'></div>";
			}
		} else {
			$output .= "{$args['close_msg']}";
			if ( $args['count-down'] ) {
				$output .= "<div id='opening-target' style='display: none;'>" . htmlspecialchars( $from_time ) . "</div>";
				opening_counter(); // load the js
				$output .= "<div id='opening-countdown'></div>";
			}
		}
		$output .= "</div>";
	}

	// closing divs for the wrapper
	$output .= '</div></div>';

	// print the after widget info
	if ( ! empty( $args['afwidget'] ) ) {
		$output .= '<p>' . do_shortcode( $args['afwidget'] ) . '</p>';
	}

	// return the final output
	return $output;
}

/**
 * Create the calendar image
 *
 * @param $color
 *
 * @return string
 */
function get_calendar_image( $color ) {
	$svg_image      = '
	<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 489.9 489.9" enable-background="new 0 0 489.9 489.9" width="512px" height="512px">
	  <g>
	    <g>
	      <path d="m412.8,259.45v-213.6h-88.6v-40.7h-20.8v40.7h-86.5v-40.7h-20.9v40.7h-86.5v-40.7h-20.9v40.7h-88.6v355.5h267.2c13.8,48 57.6,83.4 109.1,83.4 62.5,0 113.6-52.1 113.6-115.7 0.1-50.7-32.3-94-77.1-109.6zm-391.9-192.8h67.7v44.8h20.8v-44.8h86.6v44.8h20.8v-44.8h86.5v44.8h20.8v-44.8h67.9v95.9h-371.1v-95.9zm242.5,313.9h-242.5v-0.1-197h371.1v71c-5.1-0.7-10.3-1.1-15.6-1.1-62.5,0-113.6,52.1-113.6,115.7 5.68434e-14,3.9 0.2,7.7 0.6,11.5zm113,83.3c-51.1,0-92.8-42.7-92.8-94.9 0-52.1 41.7-94.9 92.8-94.9s92.8,42.7 92.8,94.9-41.8,94.9-92.8,94.9z" fill="' . $color . '"/>
	      <polygon points="365.9,354.45 332.5,354.45 332.5,375.25 386.8,375.25 386.8,293.25 365.9,293.25" fill="' . $color . '"/>
	    </g>
	  </g>
	</svg>';
	$calendar_image = base64_encode( $svg_image );
	$calendar_image = '<img src="data:image/svg+xml;utf8;base64,' . $calendar_image . '" />';

	return $calendar_image;
}

/**
 * Add font awesome style in the bottom of the page
 *
 * @since 1.1.0
 */
function font_awesome_style() {
	wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
}


/**
 * Check the time
 *
 * @param $start_date
 * @param $end_date
 *
 * @return bool
 */
function check_time_range( $start_date, $end_date ) {

	$start_time   = date( 'H:i', strtotime( $start_date ) );
	$end_time     = date( 'H:i', strtotime( $end_date ) );
	$current_time = current_time( 'H:i' );
	if ( $start_time < $end_time ) {
		return $current_time >= $start_time && $current_time <= $end_time;
	} elseif ( $start_time == '00:00' && $end_time == '00:00' ) {
		return false;
	} else {
		return $current_time >= $start_time || $current_time <= $end_time;
	}

}

/**
 * Check season period
 *
 * @param $start_date
 * @param $end_date
 *
 * @return bool
 * @since 1.6.1
 */
function check_season_period( $start_date, $end_date ) {
	$current_time = current_time( 'n/j' );

	// timestamps
	$start_season = strtotime( $start_date );
	$end_season   = strtotime( $end_date );
	$time         = strtotime( $current_time );

	return ( ( $time >= $start_season ) && ( $time <= $end_season ) );

}

/**
 * Load java script for the counter
 *
 * @since 1.1.0
 */
function closing_counter() {
	wp_enqueue_script( 'closing_counter', WP_BUSINESS_HOURS_URL . '/js/wpbh-closing-counter.js', __FILE__ );
}

/**
 * Load java script for the counter
 *
 * @since 1.1.0
 */
function opening_counter() {
	wp_register_script( 'opening_counter', WP_BUSINESS_HOURS_URL . '/js/wpbh-opening-counter.js', __FILE__ );

	$translation_array_msg = array(
		'future_msg' => __( 'another day', 'wp-business-hours' )
	);
	wp_localize_script( 'opening_counter', 'object_msg', $translation_array_msg );

	// enqueued script
	wp_enqueue_script( 'opening_counter' );
}


/**
 * Convert time from 12 hr to 24 hr
 *
 * @param $time
 *
 * @return string
 * @since 1.6.1
 */
function convert_time( $time ) {
	return $time_format = date( "H:i", strtotime( $time ) );
}

/**
 * Convert time set from 12 hr to 24 hr
 *
 * @param $time_set
 *
 * @return string
 * @since 1.6.1
 */
function convert_time_set( $time_set ) {
	$time  = explode( " - ", $time_set );
	$start = date( "H:i", strtotime( $time[0] ) );
	$end   = date( "H:i", strtotime( $time[1] ) );

	return $start . " - " . $end;
}


/**
 * Week data
 *
 * @return array
 * @since 1.1.0
 */
function week_hours_data() {
	$week_data = array(
		'Monday'    => array(
			'day'   => __( 'Monday', 'wp-business-hours' ),
			'short' => __( 'Mon', 'wp-business-hours' ),
		),
		'Tuesday'   => array(
			'day'   => __( 'Tuesday', 'wp-business-hours' ),
			'short' => __( 'Tue', 'wp-business-hours' ),
		),
		'Wednesday' => array(
			'day'   => __( 'Wednesday', 'wp-business-hours' ),
			'short' => __( 'Wed', 'wp-business-hours' ),
		),
		'Thursday'  => array(
			'day'   => __( 'Thursday', 'wp-business-hours' ),
			'short' => __( 'Thu', 'wp-business-hours' ),
		),
		'Friday'    => array(
			'day'   => __( 'Friday', 'wp-business-hours' ),
			'short' => __( 'Fri', 'wp-business-hours' ),
		),
		'Saturday'  => array(
			'day'   => __( 'Saturday', 'wp-business-hours' ),
			'short' => __( 'Sat', 'wp-business-hours' ),
		),
		'Sunday'    => array(
			'day'   => __( 'Sunday', 'wp-business-hours' ),
			'short' => __( 'Sun', 'wp-business-hours' ),
		)
	);

	return $week_data;
}