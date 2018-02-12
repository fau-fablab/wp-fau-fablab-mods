# WordPress FAU FabLab modifications

WordPress mods and extensions which are not worth for a own plugin.

## Contents

### `fablab_um_custom_validate_captcha`

Custom validation for the captcha field in register form for
[UltimateMember](https://github.com/ultimatemember/ultimatemember/).

#### Usage:

- Define `FABLAB_CAPTCHA_SOLUTION` in `wp-config.php`
- Add a text field to your forms
- Add custom validation `um_custom_validate_captcha` to this text field
- Add a permanent redirect from `/wp-login.php` to `/register/` in your web server

### `DoorStateWidget`

Display the current door state. Information are fetched from our [custom SpaceAPI
implementation](https://github.com/fau-fablab/spaceapi/)

#### Usage:

- Add the widget called "Türstatus" in the WordPress "Customizer"
- The door state will additionally be displayed in the `.site-description` below the page title

### Filter events in "Nächste Termine" list on start page

Most people just want to see when the next (Open|Self|Rad|Zerspanungs|...)Lab takes place.
They are not interested in internal events like "OrgaTreffen".
So we have to filter out such events from the "Nächste Termine" list on the start page.

The easiest (but dirtiest) way is to do this with Javascript :tada:

#### Usage:

- Ensure that the `NEXT_EVENTS_CALENDAR_ID` in `script.js` is the ID of the events calendar list
  (check its class name)
- Ensure that all possible event names, that should be displayed in the list, are listed in
  `EVENT_NAMES_TO_DISPLAY` in `script.js` (lowercase, without `-`). An event will be show if one of
  the words in this list is part of the event title.

## License

[CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)
