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
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\models\Section;

/**
 * EntriesSource class
 *
 *
 * @author    veryfine
 * @package   Superdropdown
 * @since     1.0.0
 *
 */

class EntriesSource extends DropdownSource
{

    /**
     * Key used for key and name of the topLevel dropdown array
     *
     * @var string
     */
    public string $elementType = Entry::class;

    public function getElements($field) : void
    {
        $entryQuery = Entry::find();

        $maxLevels = $field->maxNestingLevel ? '<= ' . $field->maxNestingLevel : null;

        switch ($field->entrySection) {
            case '*':
                $entryQuery->withStructure()->level($maxLevels);
                $this->topLevelName = 'entries';
                break;

            case 'singles':
                $sectionsArray = Craft::$app->sections->getSectionsByType(Section::TYPE_SINGLE);
                $sectionIds = ArrayHelper::getColumn($sectionsArray, 'id');
                $entryQuery->sectionId($sectionIds);
                $this->topLevelName = 'singles';
                break;

            default:
                $sectionUId = StringHelper::afterLast($field->entrySection, ':');
                $section = Craft::$app->entries->getSectionByUid($sectionUId);
                $entryQuery->sectionId($section->id);
                if ($section->type === Section::TYPE_STRUCTURE) {
                    $entryQuery->withStructure()->level($maxLevels);
                }
                $this->topLevelName = $section->handle;
        }

        $this->elements = $entryQuery->all();
    }

}