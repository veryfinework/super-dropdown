<?php
/**
 * Super dropdown plugin for Craft CMS 3.x
 *
 * Adds a field type that generates side-by-side and cascading dropdowns from data.
 *
 * @link      https://github.com/veryfinework
 * @copyright Copyright (c) 2020 veryfine
 */

namespace veryfine\superdropdown\variables;

use craft\elements\Category;
use craft\helpers\Json;
use craft\helpers\StringHelper;

/**
 *
 * @author    veryfine
 * @package   Superdropdown
 * @since     1.0.0
 *
 */
class SuperdropdownVariable
{
    // Public Methods
    // =========================================================================

    public function categoryCascade($categoryGroupHandle, $maxLevels)
    {
        $categories = Category::find()
            ->group($categoryGroupHandle)
            ->level('<= '. $maxLevels)
            ->all();

        $dropdowns = [];

        $dropdowns['topLevel'] = [
            'name' => $categoryGroupHandle,
            'type' => 'primary',
            'options' => []
        ];

        foreach ($categories as $category) {

            $option = [
                'label' => $category->title,
                'value' => $category->id
            ];

            if ($category->hasDescendants && $category->level < $maxLevels) {

                $subselectName =  StringHelper::toKebabCase($category->title);

                $option['subselect'] = $subselectName;

                // create dropdown for subcategory with empty options
                if (!array_key_exists($category->title, $dropdowns)) {
                    $dropdowns[$subselectName] = [
                        'name' => $subselectName,
                        'type' => 'conditional',
                        'options' => []
                    ];
                }
            }

            $dropdownName = (bool)$category->parent
                ? StringHelper::toKebabCase($category->parent->title)
                : 'topLevel' ;

            $dropdowns[$dropdownName]['options'][] = $option;

        }

        return Json::encode($dropdowns);
    }

}
