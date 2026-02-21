<?php
class MissionDayUMM_Widget extends WP_Widget
{
  function __construct()
  {
    parent::__construct(
      'md_umm_widget',
      __('Mission UMM Widget', 'mdsite'),
      array('description' => __('MD UMM', 'mdsite'),)
    );
  }

  public function widget($args, $instance)
  {
    $uniqid = uniqid();
    $id = 'md-umm-' . $uniqid;
    $fn = 'initUMMMap' . $uniqid;

    echo $args['before_widget'];
    $ummdata = get_field('umm_export', get_the_ID());
    $imatdata = get_field('imat_export', get_the_ID());
    if ($ummdata || $imatdata):
?>
      <div id="<?php echo $id; ?>-wrapper">
        <div id="<?php echo $id; ?>"></div>
        <div id="<?php echo $id; ?>-fullscreen" title="fullscreen"></div>
      </div>

      <style>
        #<?php echo $id; ?>-wrapper {
          position: relative;
        }

        #<?php echo $id; ?> {
          min-height: 500px;
          width: 100%;
        }

        #<?php echo $id; ?>-fullscreen {
          position: absolute;
          top: 10px;
          right: 10px;
          z-index: 1000;
          background-color: white;
          padding: 10px;
          border-radius: 5px;
          cursor: pointer;
          height: 30px;
          width: 30px;
          background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0z" fill="none"/><path d="M5 3h14c1.1 0 1.99.9 1.99 2L21 19c0 1.1-.89 2-1.99 2H5c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2zm14 16V5H5v14h14z"/></svg>');
          background-repeat: no-repeat;
          background-position: center;
        }

        @media(orientation: portrait) {
          #<?php echo $id; ?> {
            min-height: 300px;
          }
        }

        .md-site-popup {
          display: flex;
          flex-direction: row;
          min-width: 320px;
        }

        .md-site-popup-img {
          flex: 0 0 80px;
          padding-right: 6px;
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
        }
      </style>

      <script>
        (() => {
          const MDUMMJSON = <?php echo $ummdata ? $ummdata : 'null'; ?>;
          const MDIMATJSON = <?php echo $imatdata ? $imatdata : 'null'; ?>;
          const MDUMMMap = function() {
            this.map = null;
            this.markers = [];
            this.routes = [];

            this.init = () => {
              this.map = L.map('<?php echo $id; ?>').setView([0, 0], 1);
              L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="http://cartodb.com/attributions">CartoDB</a>'
              }).addTo(this.map);
              if (MDUMMJSON && MDUMMJSON.missions) {
                for (let m of MDUMMJSON.missions) {
                  const first = m.portals[0];
                  const markerOptions = {};
                  if (m.missionBadgeUrl) {
                    markerOptions.icon = L.icon({
                      iconUrl: m.missionBadgeUrl,
                      iconSize: [40, 40],
                      iconAnchor: [20, 20],
                      popupAnchor: [0, -20]
                    });
                  }
                  const marker = L.marker([
                    first.location.latitude,
                    first.location.longitude
                  ], markerOptions).addTo(this.map);
                  const url = `https://intel.ingress.com/intel?pll=${first.location.latitude},${first.location.longitude}`;
                  const maps = `https://maps.google.com/maps?ll=${first.location.latitude},${first.location.longitude}&q=${first.location.latitude},${first.location.longitude}%20(${m.missionTitle})`;
                  if(m.missionBadgeUrl) {
                    marker.bindPopup(`<div class="md-site-popup">
                      <div class="md-site-popup-img">
                        <img src="${m.missionBadgeUrl}" alt="${m.missionTitle}" />
                        <a href="${url}" target="_blank">Go to portal</a>
                        <a href="${maps}" target="_blank">Maps</a>
                      </div>
                      <div class="md-site-popup-text">
                        <h5>${m.missionTitle}</h5>
                        <p>${m.missionDescription}</p>
                      </div>
                    </div>`);
                  }
                  else {
                    marker.bindPopup(`<div class="md-site-popup">
                      <div class="md-site-popup-text">
                        <h5>${m.missionTitle}</h5>
                        <p>${m.missionDescription}</p>
                        <a href="${url}" target="_blank">Go to portal</a>
                        <a href="${maps}" target="_blank">Maps</a>
                      </div>
                    </div>`);
                  }
                  let route = L.polyline(m.portals.map(p => {
                    return [
                      p.location.latitude,
                      p.location.longitude
                    ];
                  }), {
                    color: 'red'
                  }).addTo(this.map);
                  this.routes.push(route);
                  this.markers.push(marker);
                }
              } else if (MDIMATJSON) {
                for(let m of MDIMATJSON) {
                  const first = m.missionDetails.pois[0];
                  const imatMarkerOptions = {};
                  if (m.missionDetails.mission.definition.logo_url) {
                    imatMarkerOptions.icon = L.icon({
                      iconUrl: m.missionDetails.mission.definition.logo_url,
                      iconSize: [40, 40],
                      iconAnchor: [20, 20],
                      popupAnchor: [0, -20]
                    });
                  }
                  const marker = L.marker([
                    first.location.latitude,
                    first.location.longitude
                  ], imatMarkerOptions).addTo(this.map);
                  const url = `https://intel.ingress.com/intel?pll=${first.location.latitude},${first.location.longitude}`;
                  const maps = `https://maps.google.com/maps?ll=${first.location.latitude},${first.location.longitude}&q=${first.location.latitude},${first.location.longitude}%20(${m.missionDetails.mission.definition.name})`;
                  marker.bindPopup(`<div class="md-site-popup">
                    <div class="md-site-popup-img">
                      <img src="${m.missionDetails.mission.definition.logo_url}" alt="${m.missionDetails.mission.definition.name}" />
                      <a href="${url}" target="_blank">Go to portal</a>
                      <a href="${maps}" target="_blank">Maps</a>
                    </div>
                    <div class="md-site-popup-text">
                      <h5>${m.missionDetails.mission.definition.name}</h5>
                      <p>${m.missionDetails.mission.definition.description}</p>
                    </div>
                  </div>`);
                  let route = L.polyline(m.missionDetails.pois.map(p => {
                    return [
                      p.location.latitude,
                      p.location.longitude
                    ];
                  }), {
                    color: 'red'
                  }).addTo(this.map);
                  this.routes.push(route);
                  this.markers.push(marker);
                }
              }
              this.map.fitBounds(this.markers.map(marker => marker.getLatLng()));
            };

            console.log(MDUMMJSON);
          }
          window.<?php echo $fn; ?> = new MDUMMMap();
          window.<?php echo $fn; ?>.init();
          document.getElementById('<?php echo $id; ?>-fullscreen').addEventListener('click', () => {
            document.getElementById('<?php echo $id; ?>').requestFullscreen();
          });
        })();
      </script>
    <?php
    endif;
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
