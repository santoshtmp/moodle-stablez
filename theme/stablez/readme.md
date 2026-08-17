## Development Reference:
1. https://moodledev.io/general/development/gettingstarted
2. https://docs.moodle.org/dev/Creating_a_theme_based_on_boost
3. https://docs.moodle.org/dev/Category:Themes
4. https://moodledev.io/docs/guides/templates   https://docs.moodle.org/dev/Templates
5. https://moodledev.io/docs/guides/javascript/modules 
6. https://moodledev.io/docs/apis/plugintypes/format#format-output-classes-and-templates
7. https://moodledev.io/docs/4.5/apis/subsystems/form
8. https://moodledev.io/docs/apis/core/dml  https://docs.moodle.org/dev/Data_manipulation_API
9. https://moodledev.io/docs/apis
10. https://moodledev.io/docs/apis/subsystems/external/writing-a-service
11. https://docs.moodle.org/dev/Web_service_API_functions
12. https://docs.moodle.org/dev/Events_API
13. https://moodledev.io/docs/5.0/apis/core/navigation 
14. https://moodledev.io/docs/5.0/apis/subsystems/admin
15. https://moodledev.io/docs/apis/subsystems/task/scheduled https://docs.moodle.org/dev/Task_API 
16. and others from moodle doc

## Development Environment
To setup development environment, you need to apply following setings in config.php or manage through admin settings.
##### Enable developer mode
@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = (E_ALL | E_STRICT); 
$CFG->debugdisplay = 1;
##### Disable cache for CSS and JavaScript
$CFG->cachejs = false;
$CFG->themedesignermode = true;
$CFG->cachetemplates = false;
$CFG->debugpurify = true;
$CFG->debugvalidators = true;