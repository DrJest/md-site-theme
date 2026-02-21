# md-site-theme
Custom Wordpress theme for MissionDay.site

## Map
- `/mission-day/{slug}/map` - Show or embed map (if available)

## API Endpoints
API endpoints are available at `/wp-json/wp/v2/mission-day` and include the following:

- `/wp-json/wp/v2/mission-day` - Search Mission Days
- `/wp-json/wp/v2/mission-day/{id}` - Get a single Mission Day

### Search Mission Days By Date, Area or Type
You can use `mdq` parameter to search for Mission Days by date, area or type. The parameters is a JSON object with the following properties:

- `date` - Date of the Mission Day (YYYYMMDD)
- `area` - Area of the Mission Day (emea, apac, amer)
- `type` - Type of the Mission Day (normal, lite, anomaly)
- `city` - City of the Mission Day
- `enl_poc` - Enlightened POC of the Mission Day
- `res_poc` - Resistance POC of the Mission Day

#### Examples
- `/wp-json/wp/v2/mission-day?mdq={"date":{"<":"20240131"}}` - Get all Mission Days before 2024-01-31
- `/wp-json/wp/v2/mission-day?mdq={"area":["emea"],"type":"lite"}` - Get all Mission Days in EMEA that are Lite
- `/wp-json/wp/v2/mission-day?mdq={"city":"London"}` - Get all Mission Days in London
- `/wp-json/wp/v2/mission-day?mdq={"area":["emea","amer"],"type":"lite"}` - Get all Mission Days in EMEA that are Lite

If the value is an array, it will be treated as an IN query. If the value is an object, it will be treated as a comparison query. If the value is a string, it will be treated as a full text search query.
