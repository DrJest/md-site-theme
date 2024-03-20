<?php

class MissionDayCalendar_Widget extends WP_Widget
{
  function __construct()
  {
    parent::__construct(
      'md_calendar_widget',
      __('Mission Day Calendar Widget', 'mdsite'),
      array('description' => __('MD Calendar', 'mdsite'),)
    );
  }

  private function getColor($post)
  {
    $type = get_field('type', $post->ID);
    if ($type == 'Normal') {
      return '#00a651';
    } else if ($type == 'Lite') {
      return '#f7941d';
    } else if ($type == 'Anomaly') {
      return '#ed1c24';
    }
  }

  public function widget($args, $instance)
  {
    $posts = get_posts(array(
      'post_type' => 'mission-day',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'meta_query' => array(
        'relation'      => 'AND',
        array(
          'key' => 'date',
          'value' => date("Ymd"),
          'compare' => '>='
        ),
      )
    ));
    $json = array();
    foreach ($posts as $post) {
      $json[] = array(
        'title' => $post->post_title,
        'start' => get_field('date', $post->ID),
        'url' => get_permalink($post->ID),
        'color' => $this->getColor($post)
      );
    }

    $id = 'md-calendar-' . uniqid();
    $json = json_encode($json);
    echo $args['before_widget'];
?>
    <div id="<?php echo $id; ?>"></div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('<?php echo $id; ?>');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          contentHeight: "auto",
          handleWindowResize: true,
          events: <?php echo $json; ?>,

        });
        calendar.render();
      });
    </script>

    <style>

    </style>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

  <?php
    echo $args['after_widget'];
  }

  public function form($instance)
  {
    if (isset($instance['title'])) {
      $title = $instance['title'];
    } else {
      $title = __('New title', 'mdsite');
    }
  ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
<?php
  }

  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    return $instance;
  }
}
