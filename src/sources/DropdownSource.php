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

use craft\helpers\StringHelper;
use yii\base\Component;

/**
 * DropdownSource base class
 *
 *
 * @author    veryfine
 * @package   Superdropdown
 * @since     1.0.0
 *
 *
 * @property array $normalizedValue
 */

class DropdownSource extends Component
{

    /**
     * Key used for key and name of the topLevel dropdown array
     *
     * @var string
     */
    public $elementType = '';

    /**
     * Key used for topLevel dropname array
     *
     * @var string
     */
    public $topLevelName = '';

    /**
     *
     * @var array
     */
    public $elements = [];


    public function getElements($field)
    {
        $this->elements = [];
    }

    /**
     *
     * Prepare entry data for use by the template
     *
     * @param $field
     * @return array
     */
    public function getElementsAsDropdownArray($field): array
    {

        $this->getElements($field);

        $dropdowns = [];

        $dropdowns[$this->topLevelName] = [
            'name' => $this->topLevelName,
            'type' => 'primary',
            'options' => []
        ];

        foreach ($this->elements as $element) {

            $label = $field->labelLength ? StringHelper::truncate($element->title, $field->labelLength) : $element->title;

            $option = [
                'label' => $label,
                'value' => $element->id . ':' . $element->title
            ];

            $selectName = (bool)$element->parent ? $element->parent->id : $this->topLevelName;
            $subselectName = $element->id;

            // create subselect array
            if ($element->hasDescendants && $element->level < $field->maxNestingLevel) {

                $option['subselect'] = $subselectName;

                // create dropdown for subcategory with empty options
                if (!array_key_exists($element->title, $dropdowns)) {
                    $dropdowns[$subselectName] = [
                        'name' => $subselectName,
                        'type' => 'conditional',
                        'options' => []
                    ];
                }
            }

            $dropdowns[$selectName]['options'][] = $option;

        }

        if ($field->blankOption) {
            $this->addBlankOptions($dropdowns, $field->blankOptionLabel);
        }

        return $dropdowns;
    }

    public function addBlankOptions(&$dropdowns, $blankOptionLabel) {

        // add blank options, skip the first
        $first = true;
        foreach ($dropdowns as &$dropdown) {
            if ($first) {
                $first = false;
                continue;
            }

            array_unshift($dropdown['options'], [
                'label' => $blankOptionLabel,
                'value' => ''
            ]);
        }

        return $dropdowns;
    }

    /**
     * Transforms a nested array of dropdowns into a flattened array
     *
     *
     * @param $dataArray
     * @param $value
     * @return array
     */
    public function cascadingDropdowns($dataArray, $value): array
    {

        $allDropdowns = [];

        $makeDropdown = static function( $dropdown, $level ) use ( &$makeDropdown, &$allDropdowns ) {

            $dropdown['level'] = $level; // unused
            $allDropdowns[$dropdown['name']] = $dropdown;

            foreach ($dropdown['options'] as &$option) {
                if(array_key_exists('subselect', $option)) {

                    $subDropdown = $option['subselect'];
                    $subDropdown['isConditional'] = true;

                    $makeDropdown($subDropdown, $level+1);
                }
            }
        };

        foreach ($dataArray as $topLevelDropdown) {
            $makeDropdown($topLevelDropdown, 0);
        }

        // use $value to set selected options
        foreach ($allDropdowns as &$dropdown) {
            $key = $dropdown['name'];

            $savedValue =  (!empty($value) && array_key_exists($key, $value)) ? $value[$key] : null;

            foreach ($dropdown['options'] as &$option) {

                // set selected
                if ($option['value'] === $savedValue
                    || ($savedValue === null && isset($option['default']) )
                ) {
                    $option['selected'] = true;
                }

                // make relevant children active
                if(array_key_exists('subselect', $option)) {

                    // show sub-dropdown if parent is selected
                    if (array_key_exists('selected', $option)) {
                        $allDropdowns[$option['subselect']['name']]['active'] = true;
                    }

                    $option['subselect'] = $option['subselect']['name'];
                }


            }
        }

        return $allDropdowns;
    }
}