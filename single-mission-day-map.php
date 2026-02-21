<html>

<head>
  <style>
    html,
    body {
      margin: 0 !important;
      padding: 0 !important;
      font-family: 'Courier New', Courier, monospace;
    }

    .widget,
    .widget>div,
    .widget>div>div {
      height: 100vh;
      width: 100vw;
    }

    .custom-logo-link {
      position: absolute;
      left: 10px;
      bottom: 10px;
      z-index: 9999;
      height: 60px;
    }

    .custom-logo-link>img {
      height: 100%;
      width: auto;
    }

    .md-site-popup-img img {
      border: none;
      border-radius: 0;
      box-shadow: none;
      height: auto;
      max-width: 100%;
    }

    h5 {
      font-size: 1.25rem;
      margin-block-start: .5rem;
      margin-block-end: 1rem;
      font-family: inherit;
      font-weight: 500;
      line-height: 1.2;
      color: inherit;
    }
  </style>
  <title>
    <?php echo get_the_title(); ?> Map by MissionDay.site
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body>
  <?php
  the_widget('MissionDayUMM_Widget', array('title' => get_the_title(), 'post_id' => $post_id));
  if (has_custom_logo()) {
    $custom_logo_id = get_theme_mod('custom_logo');
    $custom_logo_url = wp_get_attachment_image_src($custom_logo_id, 'full')[0];
    echo '<a class="custom-logo-link" href="' . get_home_url() . '" target="_blank"><img src="' . esc_url($custom_logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '"></a>';
  }
  ?>
  <script>
    if (!document.querySelector('.widget').innerHTML) {
      document.querySelector('.widget').innerHTML = `
        <div style="padding: 24px; text-align: center; box-sizing: border-box;">
        <h1>Map not available</h1>
        Please contact the administrator of this site or the organizers of this Mission Day to enable the MissionDay Map widget.
        </div>
      `;
    }
  </script>
</body>

</html>
