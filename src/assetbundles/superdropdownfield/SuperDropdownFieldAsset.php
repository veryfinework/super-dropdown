<?php
/**
 * Super dropdown plugin for Craft CMS 3.x
 *
 * Adds a field type that generates side-by-side and cascading dropdowns from data.
 *
 * @link      https://github.com/veryfinework
 * @copyright Copyright (c) 2020 veryfine
 */

namespace veryfine\superdropdown\assetbundles\superdropdownfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * SuperdropdownFieldAsset AssetBundle
 **
 * @author    veryfine
 * @package   Superdropdown
 * @since     1.0.0
 */
class SuperDropdownFieldAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        // path to publishable resources
        $this->sourcePath = "@veryfine/superdropdown/assetbundles/superdropdownfield/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Superdropdown.js',
        ];

        $this->css = [
            'css/Superdropdown.css',
        ];

        parent::init();
    }
}
