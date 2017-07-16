# WordPress FAU FabLab modifications

WordPress mods and extensions which are not worth for a own plugin.

## Contents

### `um_custom_validate_captcha`

Custom validation for the captcha field in register form for
[UltimateMember](https://github.com/ultimatemember/ultimatemember/).

#### Usage:

- Define `FABLAB_CAPTCHA_SOLUTION` in `wp-config.php`
- add a text field to your forms
- add custom validation `um_custom_validate_captcha` to this text field
- add a permanent redirect from `/wp-login.php` to `/register/` in your web server

## License

[CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)
