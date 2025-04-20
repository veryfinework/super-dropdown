<?php
/**
 * Super dropdown plugin for Craft CMS 3.x
 *
 * Adds a field type that generates side-by-side and cascading dropdowns from data.
 *
 * @link      https://github.com/veryfinework
 * @copyright Copyright (c) 2020 veryfine
 */

namespace veryfine\superdropdown\sources;

use Craft;
use craft\elements\Category;
use craft\helpers\StringHelper;

/**
 * CategoriesSource class
 *
 *
 * @author    veryfine
 * @package   Superdropdown
 * @since     1.0.0
 *
 */

class CategoriesSource extends DropdownSource
{

    /**
     * Key used for key and name of the topLevel dropdown array
     *
     * @var string
     */
    public string $elementType = Category::class;

    public function getElements($field) : void
    {
        $groupUId = StringHelper::afterLast($field->categoryGroup, ':');
        $group = Craft::$app->categories->getGroupByUid($groupUId);
        $maxLevels = $field->maxNestingLevel ? '<= '. $field->maxNestingLevel : null;

        $categories = Category::find()
            ->group($group)
            ->level($maxLevels)
            ->all();

        // Fill in any gaps
        $categoriesService = Craft::$app->getCategories();
        $categoriesService->fillGapsInCategories($categories);

        $this->topLevelName = $group->handle;

        $this->elements = $categories;
    }

}