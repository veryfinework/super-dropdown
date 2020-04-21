# Super Dropdown plugin for Craft CMS 3.x
A custom field for the Craft CMS for building complex dropdown fields from native elements or data.

![Screenshot](http://veryfine.work/assets/img/video1.gif)

## Overview
This plugin transforms structured data into series of linked dropdowns. The data source can be Categories, Entries, or any JSON data that is properly formatted. The JSON data may be static or dynamic. Dynamic data can be supplied by Twig templates using the complete Craft API. Static JSON can simply be pasted into the field definition, or provided by a Twig template.

## Documentation
[Find complete documentation here.](https://veryfine.work/docs/superdropdown/)

## Some Things You Can Do With this Plugin
* Create a single dropdown from JSON data (static or dynamic)
* Create a set of adjacent dropdowns that are displayed and saved as a single field
* Create a set of drill-down style dropdowns based on hierarchical data (Categories, Entries, or custom)
* Much more

## The Advantages of a Super Dropdown Field
* Field options can be dynamic
* Simplify selecting Categories and Entries by replacing the the native modal selector with this field.
* Make field layouts in entry forms more compact by combining multiple fields into a single set of dropdowns.
* Skip the complications of coding linked dropdowns

## Requirements
This plugin requires Craft CMS 3.0.0 or later.

## Installation
You can install Super Dropdown via the Craft plugin store or Composer.

### Craft Plugin Store
Navigate to the _Plugin Store_ section of your Craft Control Panel, search for `Super Dropdown`, and click the `Add To Cart`, `Install` , or `Try` button.

### Composer

1. From your project directory:

        composer require veryfine/super-dropdown
     
2. Either install the plugin from the Craft Control Panel under Settings > Plugins and click the `Install` button for Super Dropdown, or, finish the installation from the command line:
 
        ./craft install/plugin super-dropdown

## Roadmap
* Support for more Craft Elements
* More fine-grained options

Brought to you by [Very Fine](http://veryfine.work)

