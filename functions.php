<?php

defined('WPINC') || die();

require_once('widgets/mission-day-map.widget.php');
require_once('widgets/mission-day-marker.widget.php');
require_once('widgets/mission-day-calendar.widget.php');
require_once('widgets/mission-day-umm.widget.php');

const MDSITE_SECONDS_BETWEEN_GUEST_API_CALLS = 1;

const MDSITE_RATE_LIMITED_IPS = array();

const MDSITE_NEVER_RATE_LIMITED_IPS = array(
  '127.0.0.1',
  '::1'
);

add_action('wp_enqueue_scripts', 'hello_elementor_child_style');
function hello_elementor_child_style()
{
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
  wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));

  wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
  wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array('jquery'));
}

add_filter('acf/fields/google_map/api', function ($api) {
  $api['key'] = get_field('google_api_key', 'option');
  return $api;
});

function register_missionday_widget()
{
  register_widget('MissionDayMap_Widget');
  register_widget('MissionDayMarker_Widget');
  register_widget('MissionDayCalendar_Widget');
  register_widget('MissionDayUMM_Widget');
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

function mdsite_client_ip()
{
  global $mdsite_client_ip;

  if (is_null($mdsite_client_ip)) {
    $server_vars = array(
      'HTTP_CLIENT_IP',
      'HTTP_X_FORWARDED_FOR',
      'REMOTE_ADDR',
    );

    foreach ($server_vars as $server_var) {
      if (!array_key_exists($server_var, $_SERVER)) {
        // The server variable isn't set - do nothing.
      } elseif (empty($mdsite_client_ip = filter_var($_SERVER[$server_var], FILTER_VALIDATE_IP))) {
        // The IP address is not valid - do nothing.
      } else {
        // We've got a valid IP address in the global variable $mdsite_client_ip,
        // so we can "break" out of the foreach(...) loop here.
        break;
      }
    }

    // Make sure we don't leave something like an empty string or "false"
    // in $mdsite_client_ip
    if (empty($mdsite_client_ip)) {
      $mdsite_client_ip = null;
    }
  }

  return $mdsite_client_ip;
}

function mdsite_rest_api_init(WP_REST_Server $wp_rest_server)
{
  $is_client_rate_limited = false;
  $transient_key = null;

  if (empty($client_ip = mdsite_client_ip())) {
    // We don't know the client's IP address so we probably don't want to do
    // anything here.
  } elseif (!empty(MDSITE_NEVER_RATE_LIMITED_IPS) && in_array($client_ip, MDSITE_NEVER_RATE_LIMITED_IPS)) {
    // Never rate-limit IP addresses in the MDSITE_NEVER_RATE_LIMITED_IPS array.
  } else {
    $transient_key = 'mdsite_' . $client_ip;
    $rate_limited_ips = apply_filters('mdsite_rate_limited_ips', MDSITE_RATE_LIMITED_IPS);

    if (!empty($rate_limited_ips)) {
      $is_client_rate_limited = in_array($client_ip, $rate_limited_ips);
    } else {
      $is_client_rate_limited = !is_user_logged_in();
    }

    $is_client_rate_limited = (bool)apply_filters('mdsite_is_client_rate_limited', $is_client_rate_limited);
  }

  if (!$is_client_rate_limited) {
    // The client is not rate-limited - do nothing
  } elseif (empty($transient_key)) {
    // If we couldn't figure out the transient key - do nothing
  } elseif (empty(get_transient($transient_key))) {
    $seconds_between_api_calls = intval(apply_filters('mdsite_seconds_between_api_calls', MDSITE_SECONDS_BETWEEN_GUEST_API_CALLS, $client_ip));
    if ($seconds_between_api_calls > 0) {
      set_transient(
        $transient_key,
        '1',
        $seconds_between_api_calls
      );
    }
  } else {
    wp_send_json(
      array(
        'clientIp' => $client_ip,
        'message' => 'Slow down your API calls',
      ),
      429
    );
  }
}
add_action('rest_api_init', 'mdsite_rest_api_init', 10, 1);

function md_site_add_rewrite_rule()
{
  add_rewrite_rule(
    '^mission-day/([^/]+)/map/?$',
    'index.php?mission-day=$matches[1]&map_page=1',
    'top'
  );
}
add_action('init', 'md_site_add_rewrite_rule');

function mdsite_add_query_vars($vars)
{
  $vars[] = 'map_page';
  return $vars;
}
add_filter('query_vars', 'mdsite_add_query_vars');

function mdsite_load_map_template($template)
{
  if (get_query_var('map_page')) {
    return locate_template('single-mission-day-map.php');
  }
  return $template;
}
add_filter('template_include', 'mdsite_load_map_template');

function mdsite_rewrite_flush()
{
  md_site_add_rewrite_rule();
  flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'mdsite_rewrite_flush');
