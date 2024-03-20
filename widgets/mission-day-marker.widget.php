<?php
class MissionDayMarker_Widget extends WP_Widget
{
  function __construct()
  {
    parent::__construct(
      'md_mmarker_widget',
      __('Mission Day Marker Widget', 'mdsite'),
      array('description' => __('MD Marker', 'mdsite'),)
    );
  }

  public function widget($args, $instance)
  {
    $uniqid = uniqid();
    $id = 'md-marker-' . $uniqid;
    $fn = 'initMap' . $uniqid;

    echo $args['before_widget'];
?>

    <div id="<?php echo $id; ?>"></div>

    <style>
      #<?php echo $id; ?> {
        min-height: 400px;
        width: 100%;
      }

      @media(orientation: portrait) {
        #<?php echo $id; ?> {
          min-height: 240px;
        }
      }

      .md-map-filters {
        display: flex;
        flex-direction: row;
        justify-content: end;
        align-items: center;
        margin-bottom: 6px;
        column-gap: 12px;
      }
    </style>

    <script>
      (() => {
        const MDMarkerMap = function() {
          const pinSVGHole = "M7 9.5A2.5 2.5 0 014.5 7 2.5 2.5 0 017 4.5 2.5 2.5 0 019.5 7 2.5 2.5 0 017 9.5M7 0A7 7 0 000 7C0 12.25 7 20 7 20 7 20 14 12.25 14 7A7 7 0 007 0Z";
          this.map = null;
          this.markerImages = {
            'normal': L.divIcon({
              html: `<svg width="21" height="30" viewBox="0 0 14 20" stroke="#FFF" fill="#00a651" xmlns="http://www.w3.org/2000/svg"><path d="${pinSVGHole}"/></svg>`,
              className: "svg-icon",
              iconSize: [21, 30],
              iconAnchor: [10.5, 30],
            }),
            'lite': L.divIcon({
              html: `<svg width="21" height="30" viewBox="0 0 14 20" stroke="#FFF" fill="#f7941d" xmlns="http://www.w3.org/2000/svg"><path d="${pinSVGHole}"/></svg>`,
              className: "svg-icon",
              iconSize: [21, 30],
              iconAnchor: [10.5, 30],
            }),
            'anomaly': L.divIcon({
              html: `<svg width="21" height="30" viewBox="0 0 14 20" stroke="#FFF" fill="#ed1c24" xmlns="http://www.w3.org/2000/svg"><path d="${pinSVGHole}"/></svg>`,
              className: "svg-icon",
              iconSize: [21, 30],
              iconAnchor: [10.5, 30],
            }),
          };
          this.markers = [];

          this.init = () => {
            this.map = L.map('<?php echo $id; ?>').setView([0, 0], 1);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 12,
              attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(this.map);
            this.loadMarker(<?php the_ID(); ?>);
          };

          this.loadMarker = async (id) => {
            if (!id) return;
            const markerInfo = await fetch(`/wp-json/wp/v2/mission-day/${id}`).then(res => res.json());
            if (this.markers.length) this.markers.forEach(marker => marker.remove());
            this.markers = [];
            const date = new Date(
              markerInfo.acf.date.substring(0, 4),
              markerInfo.acf.date.substring(4, 6) - 1,
              markerInfo.acf.date.substring(6, 8)
            ).toLocaleDateString('en-US', {
              day: 'numeric',
              month: 'long',
              year: 'numeric'
            });
            const position = {
              lat: parseFloat(markerInfo.acf.location.lat),
              lng: parseFloat(markerInfo.acf.location.lng)
            };
            const marker = L.marker(position, {
              icon: this.markerImages[markerInfo.acf.type]
            }).addTo(this.map);
            marker.bindPopup(`<a href="${markerInfo.link}">${markerInfo.title.rendered}</a><br />${date}`, {
              offset: L.point(0, -21)
            });
            this.markers.push(marker);
            this.map.fitBounds(this.markers.map(marker => marker.getLatLng()));
          };
        }
        window.<?php echo $fn; ?> = new MDMarkerMap();
        window.<?php echo $fn; ?>.init();
      })();
    </script>
  <?php
    echo $args['after_widget'];
  }

  // Creating widget Backend
  public function form($instance)
  {
    if (isset($instance['title'])) {
      $title = $instance['title'];
    } else {
      $title = __('New title', 'mdsite');
    }
    // Widget admin form
  ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
<?php
  }

  // Updating widget replacing old instances with new
  public function update($new_instance, $old_instance)
  {
    $instance = array();
    $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
    return $instance;
  }

  // Class wpb_widget ends here
}
