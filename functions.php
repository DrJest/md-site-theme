<?php

require_once('widgets/mission-day-map.widget.php');
require_once('widgets/mission-day-calendar.widget.php');

/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */

add_action('wp_enqueue_scripts', 'hello_elementor_child_style');
function hello_elementor_child_style()
{
	wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}

add_filter('acf/fields/google_map/api', function ($api) {
	$api['key'] = get_field('google_api_key', 'option');
	return $api;
});

// Register widget for MissionDay post type, showing the map
function register_missionday_widget()
{
	register_widget('MissionDayMap_Widget');
	register_widget('MissionDayCalendar_Widget');
}
add_action('widgets_init', 'register_missionday_widget');

add_filter('rest_mission-day_query', 'filter_md', 999, 2);
function filter_md($args, $request)
{
	if (!isset($request['mdq'])) {
		return $args;
	}

	$mdq_value = json_decode($request['mdq']);

	foreach ($mdq_value as $key => $value) {
		if (is_array($value)) {
			$args['meta_query'][] = array(
				'key' => $key,
				'value' => $value,
				'compare' => 'IN',
			);
			continue;
		}
		if (is_object($value)) {
			foreach ($value as $k => $v) {
				$args['meta_query'][] = array(
					'key' => $key,
					'value' => $v,
					'compare' => $k,
				);
			}
			continue;
		}
		$args['meta_query'][] = array(
			'key' => $key,
			'value' => $value,
			'compare' => 'LIKE',
		);
	}

	return $args;
}

add_action('wp_enqueue_scripts', 'md_enqueue_scripts');
function md_enqueue_scripts()
{
	wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
	wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
}
