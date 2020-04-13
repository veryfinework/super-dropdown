<?php
/**
 * Super dropdown plugin for Craft CMS 3.x
 *
 * Adds a field type that generates side-by-side and cascading dropdowns from data.
 *
 * @link      https://github.com/veryfinework
 * @copyright Copyright (c) 2020 veryfine
 */

namespace veryfine\superdropdown\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Json;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\web\View;

use veryfine\superdropdown\assetbundles\superdropdownfield\SuperDropdownFieldAsset;
use veryfine\superdropdown\sources\CategoriesSource;
use veryfine\superdropdown\sources\EntriesSource;
use yii\db\Schema;


/**
 * Superdropdown Field
 *
 *
 * @author    veryfine
 * @package   Superdropdown
 * @since     1.0.0
 *
 * @property array $entries
 * @property mixed $settingsHtml
 * @property array $sourceOptions
 * @property array $categories
 */
class Superdropdown extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * category group id string
     *
     * @var string
     */
    public $categoryGroup;

    /**
     * entry section id string
     *
     * @var string
     */
    public $entrySection;

    /**
     * Source: 'jsonData' or 'template' or 'element'
     *
     * @var string
     */
    public $sourceType = '';

    /**
     * How the fields are arranged
     *
     * @var string
     */
    public $layout = 'inline';

    /**
     * How the labels are arranged
     *
     * @var string
     */
    public $labelLayout = 'inline'; // or 'stacked'

    /**
     * Element class
     *
     * @var string
     */
    public $elementType = 'categories';

    /**
     * Element class
     *
     * @var string
     */
    public $elementTypeMap = [
        'entries' => Entry::class,
        'categories' => Category::class
    ];

    /**
     * Character limit on labels for elements
     *
     * @var string
     */
    public $labelLength = 30;

    /**
     * Level limit on structures for Elements
     *
     * @var string
     */
    public $maxNestingLevel = 3;

    /**
     *
     *
     * @var string
     */
    public $queryParams = '';

    /**
     * Include a blank option in category dropdowns
     *
     * @var string
     */
    public $blankOption = false;

    /**
     * JSON data
     *
     * @var string
     */
    public $jsonData = '';

    /**
     * Path to frontend template that returns JSON
     *
     * @var string
     */
    public $template = '';

    /**
     * Whether to return an array or an Element
     *
     * @var string
     */
    public $returnType = 'array';

    // Static Methods
    // =========================================================================

    /**
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('super-dropdown', 'Super Dropdown');
    }

    /**
     * @inheritdoc
     */
    public static function valueType(): string
    {
        return 'mixed';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        } else if ($value === null && $this->isFresh($element)) {
            $value = [];
        }

        if (!is_array($value)) {
            return null;
        }

        // remove any empty values caused by blank options
        foreach ($value as $key => $val) {
            if ($val === '') {
                unset($value[$key]);
            }
        }

        // return an element when rendering page templates if requested
        if ($this->returnType === 'element'
            && $this->sourceType === 'element'
            && !Craft::$app->view->isRenderingPageTemplate
            && $element
        ) {

            $elementId = StringHelper::beforeFirst(array_pop($value), ':');

            $class = $this->elementTypeMap[$this->elementType];

            $value = Craft::$app->elements->getElementById($elementId, $class);

        }

        return $value;
    }

    /**
     * Normalizes the available sources into select input options.
     *
     * @return array
     */
    public function getSourceOptions(): array
    {
        // context of 'modal' retrieves all groups, 'index' retrieves only groups editable by the user
        $availableCategoryGroups = Craft::$app->getElementIndexes()->getSources(Category::class, 'modal');
        $availableSections = Craft::$app->getElementIndexes()->getSources(Entry::class, 'modal');

        return [
            'categories' => $this->makeOptionsFromSources($availableCategoryGroups),
            'sections' => $this->makeOptionsFromSources($availableSections)
        ];
    }

//    public function getElementOptions(): array
//    {
//        return $this->makeOptionsFromSources([
//            [
//                'label' => 'Entries',
//                'key' => Entry::class
//            ],
//            [
//                'label' => 'Categories',
//                'key' => Category::class
//            ]
//
//        ]);
//
//    }

    public function makeOptionsFromSources($sources): array
    {

        $options = [];
        $optionNames = [];

        foreach ($sources as $source) {
            // skip headings
            if (!isset($source['heading'])) {
                $options[] = [
                    'label' => Html::encode($source['label']),
                    'value' => $source['key']
                ];
                $optionNames[] = $source['label'];
            }
        }

        // Sort alphabetically
        array_multisort($optionNames, SORT_NATURAL | SORT_FLAG_CASE, $options);

        return $options;

    }

    /**
     * @inheritDoc
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'super-dropdown/_components/fields/Superdropdown_settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();

        // Register our asset bundle
        $view->registerAssetBundle(SuperDropdownFieldAsset::class);

        // Get our id and namespace
        $id = $view->formatInputId($this->handle);
        $namespacedId =$view->namespaceInputId($id);

        $fieldSettings = $this->getSettings();

        $sourceType = $fieldSettings['sourceType'];

        switch ($sourceType) {
            case 'element':
                $elementSource = ($this->elementType === 'entries') ? new EntriesSource() : new CategoriesSource();
                $dropdownsArray = $elementSource->getElementsAsDropdownArray($this);
                break;

            case 'template':
                $oldMode = $view->getTemplateMode();
                $view->setTemplateMode(View::TEMPLATE_MODE_SITE);

                $dropdownsArray = Json::decode( $view->renderTemplate(
                    $fieldSettings['template'], ['field' => $this]
                ));

                $view->setTemplateMode($oldMode);
                break;

            case 'jsonData':
            default:
                $dropdownsArray = Json::decode($fieldSettings['jsonData']);

        }

        // prep array for creating select inputs
        $allDropdowns = $this->prepDropdownsForInputHtmlTemplate($dropdownsArray, $value);

        // Variables to pass to JavaScript
        $jsonVars = [
            'id' => "{$namespacedId}-field",
            'editable' => []
            ];
        $jsonVars = Json::encode($jsonVars);
        $view->registerJs('window.CE_Superdropdown(' . $jsonVars . ');');

        // Render the input template
        return $view->renderTemplate(
            'super-dropdown/_components/fields/Superdropdown_input',
            [
                'name' => $this->handle,
                'id' => $id,
                'class' => 'layout-'.$this->layout,
                'value' => $value,
                'field' => $this,
                'namespacedId' => $namespacedId,
                'labelLayout' => $this->labelLayout,
                'dropdowns' => $allDropdowns,
            ]
        );
    }

    /**
     *
     * Prepare JSON data for use by the template
     *
     * @param $dropdowns
     * @param $value
     * @return array
     */
    public function prepDropdownsForInputHtmlTemplate($dropdowns, $value): array
    {

        $allDropdowns = [];
        $conditionalSubselectKeys = [];

        foreach ($dropdowns as &$dropdown) {

            $key = $dropdown['name'];
            $savedValue =  (!empty($value) && array_key_exists($key, $value)) ? $value[$key] : null;

            if ($savedValue === null)
            {
                if (array_key_exists('type', $dropdown) && $dropdown['type'] === 'primary')
                {
                    $dropdown['initialvalue'] = '0';
                } else {
                    $dropdown['initialvalue'] = '-1';
                }
            }

            foreach ($dropdown['options'] as $index => &$option) {

                if(array_key_exists('subselect', $option)) {
                    $allkeys = explode(',', $option['subselect']);
                    foreach ($allkeys as $key) {
                        $conditionalSubselectKeys[] = $key;
                    }
                }

                // set selected -- fuzzy compare for integer strings
                if ((array_key_exists('value', $option) && $option['value'] !== null && $option['value'] == $savedValue)
                    || ($savedValue === null && isset($option['default']) )
                ) {
                    $option['selected'] = true;
                    $dropdown['initialvalue'] = $index;
                }

            }

            $allDropdowns[$dropdown['name']] = $dropdown;

        }

        foreach ($conditionalSubselectKeys as $conditionalSubselectKey) {
            $allDropdowns[$conditionalSubselectKey]['isConditional'] = true;
        }

        return $allDropdowns;

    }
}
