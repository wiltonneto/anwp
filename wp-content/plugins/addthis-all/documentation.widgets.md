# Documentation for Widgets in the AddThis WordPress Plugin

## About
There are two ways to use widgets. First, users can drag and stop widgets into different widgets areas inside WordPress. Second, they provide developers with an easy way to add a widgets functionality into any part of their theme.

This documentions goes over the information a developer would need to add an AddThis widget directly into the code of their theme.

If you are not already familar with how to use a widget directly in PHP, see WordPress' documentation on [the_widget](https://codex.wordpress.org/Function_Reference/the_widget).

## List / Class Names

1. AddThisSharingButtonsSquareWidgets
1. AddThisSharingButtonsOriginalWidget
1. AddThisSharingButtonsCustomWidget - Pro. This will only render when used on a page with a Pro AddThis profile.
1. AddThisSharingButtonsJumboWidget - Pro. This will only render when used on a page with a Pro AddThis profile.
1. AddThisSharingButtonsResponsiveWidget - Pro. This will only render when used on a page with a Pro AddThis profile.
1. AddThisFollowButtonsHorizontalWidget
1. AddThisFollowButtonsVerticalWidget
1. AddThisFollowButtonsCustomWidget - Pro. This will only render when used on a page with a Pro AddThis profile.
1. AddThisRecommendedContentHorizontalWidget
1. AddThisRecommendedContentVerticalWidget
1. AddThisGlobalOptionsWidget

## Supported Arguments
WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) function takes three arguements.
1. `$widget` - A string. The name of the widget (one of the widgets in the above list)
1. `$instance` - An associative array.
1. `$args` - An associative array.

The AddThisGlobalOptionsWidget widget does not support any arguments. The rest support the following.

Below are exhaustive lists of supported keys for `$instance` and `$args` that will remain the same between bugfix and minor version releases.

Supported keys for `$instance`:
* title - a display title

Supported keys for `$args`:
* before_title - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation
* after_title - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation
* before_widget - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation
* after_widget - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation

## Code Examples

### echoing out the HTML for original sharing buttons with the title 'please share' and the text ':)' after
```php
$widgetClassName = 'AddThisSharingButtonsOriginalWidget';
$instance = array(
  'title' => 'please share',
);
$args = array:)  'after_widget' => 'thanks',
);
the_widget($widgetClassName, $instance, $args);
```

### echoing out the HTML for horizontal follow buttons with the title 'follow me'
```php
$widgetClassName = 'AddThisFollowButtonsHorizontalWidget';
$instance = array(
  'title' => 'follow me',
);
the_widget($widgetClassName, $instance);
```

### echoing out the HTML for Vertical Recommended Content
```php
$widgetClassName = 'AddThisRecommendedContentVerticalWidget';
the_widget($widgetClassName);
```