# WordPress FAU FabLab modifications

WordPress mods and extensions which are not worth for a own plugin.

## Contents

### `um_custom_validate_captcha`

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

- Ensure that jQuery is available as `jQuery` variable
- Add the widget called "Türstatus" in the WordPress "Customizer"

## License

[CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)
