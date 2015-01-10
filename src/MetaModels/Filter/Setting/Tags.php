<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage FilterTags
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\Filter;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\FrontendIntegration\FrontendFilterOptions;

/**
 * Filter "tags" for FE-filtering, based on filters by the MetaModels team.
 *
 * @package    MetaModels
 * @subpackage FilterTags
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Tags extends SimpleLookup
{
    /**
     * Overrides the parent implementation to always return true, as this setting is always available for FE filtering.
     *
     * @return bool true as this setting is always available.
     */
    public function enableFEFilterWidget()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
    {
        return in_array($strKeyOption, (array) $arrWidget['value']) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
    {
        $arrCurrent = (array) $arrWidget['value'];

        if ($this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)) {
            $arrCurrent = array_diff($arrCurrent, array($strKeyOption));
        } else {
            $arrCurrent[] = $strKeyOption;
        }

        return implode(',', $arrCurrent);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $objMetaModel = $this->getMetaModel();
        $objAttribute = $objMetaModel->getAttributeById($this->get('attr_id'));
        $strParamName = $this->getParamName();

        $arrParamValue = $this->buildParamValue($arrFilterUrl, $strParamName);
        $arrOptions    = $this->getParameterFilterOptions($objAttribute, null);
        $arrParamValue = $this->filterParamValue($arrParamValue, $arrOptions);

        if ($objAttribute && $strParamName && is_array($arrParamValue) && $arrOptions) {
            // Determine which parenting rule to use, AND or OR.
            if ($this->get('useor')) {
                $objParentRule = new ConditionOr();
            } else {
                $objParentRule = new ConditionAnd();
            }

            // We allow the current and the fallback language to be searched by default.
            $arrValidLanguages = array(
                $this->getMetaModel()->getActiveLanguage(),
                $this->getMetaModel()->getFallbackLanguage()
            );

            foreach ($arrParamValue as $strParamValue) {
                // Restrict to valid options for obvious reasons.
                if (array_key_exists($strParamValue, $arrOptions)) {
                    $objSubFilter = new Filter($objMetaModel);
                    $objSubFilter->addFilterRule(
                        new SearchAttribute($objAttribute, $strParamValue, $arrValidLanguages)
                    );

                    $objParentRule->addChild($objSubFilter);
                }
            }

            $objFilter->addFilterRule($objParentRule);

            return;
        }

        // If no setting has been defined, we appear transparently as "not defined" and return all items.
        $objFilter->addFilterRule(new StaticIdList(null));
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        $objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));

        $arrCount   = array();
        $arrOptions = $this->getParameterFilterOptions($objAttribute, $arrIds, $arrCount);

        $arrParamValue  = null;
        $strParamName   = $this->getParamName();
        $arrMyFilterUrl = $arrFilterUrl;
        // If we have a value, we have to explode it by comma to have a valid value which the active checks may cope
        // with.
        if (array_key_exists($strParamName, $arrFilterUrl) && !empty($arrFilterUrl[$strParamName])) {
            if (is_array($arrFilterUrl[$strParamName])) {
                $arrParamValue = $arrFilterUrl[$strParamName];
            } else {
                $arrParamValue = explode(',', $arrFilterUrl[$strParamName]);
            }

            // Ok, this is rather hacky here. The magic value of '--none--' means clear in the widget.
            if (in_array('--none--', $arrParamValue)) {
                $arrParamValue = null;
            }

            // Also hacky, the magic value of '--all--' means check all items in the widget.
            if (is_array($arrParamValue) && in_array('--all--', $arrParamValue)) {
                $arrParamValue = array_keys($arrOptions);
            }

            if ($arrParamValue) {
                $arrMyFilterUrl[$strParamName] = $arrParamValue;
            }
        }

        $GLOBALS['MM_FILTER_PARAMS'][] = $strParamName;

        return $this->buildParameterFilterWidgets(
            $arrJumpTo,
            $objFrontendFilterOptions,
            $objAttribute,
            $strParamName,
            $arrOptions,
            $arrCount,
            $arrParamValue,
            $arrMyFilterUrl
        );
    }

    /**
     * Build filter param values.
     *
     * @param array  $arrFilterUrl Given filter url.
     * @param string $strParamName Name of filter param.
     *
     * @return array|null
     */
    private function buildParamValue($arrFilterUrl, $strParamName)
    {
        $arrParamValue = null;
        if (array_key_exists($strParamName, $arrFilterUrl) && !empty($arrFilterUrl[$strParamName])) {
            if (is_array($arrFilterUrl[$strParamName])) {
                $arrParamValue = $arrFilterUrl[$strParamName];
            } else {
                $arrParamValue = explode(',', $arrFilterUrl[$strParamName]);
            }
        }

        return $arrParamValue;
    }

    /**
     * Filter --all-- and --none-- values.
     *
     * @param array $arrParamValue Param value which shall be filtered.
     * @param array $arrOptions    Filter options.
     *
     * @return array
     */
    private function filterParamValue($arrParamValue, $arrOptions)
    {
        // Filter out the magic keyword for none selected.
        if ($arrParamValue && in_array('--none--', $arrParamValue)) {
            $arrParamValue = array();
        }

        // Filter out the magic keyword for all selected.
        if ($arrParamValue && in_array('--all--', $arrParamValue)) {
            $arrParamValue = array_keys($arrOptions);

            return $arrParamValue;
        }

        return $arrParamValue;
    }

    /**
     * Build parameter filter widgets.
     *
     * @param array                 $arrJumpTo                The jumpTo page.
     * @param FrontendFilterOptions $objFrontendFilterOptions Frontendfilter options.
     * @param IAttribute            $objAttribute             MetaModel Attribute.
     * @param string                $strParamName             Param name.
     * @param array                 $arrOptions               Options.
     * @param array                 $arrCount                 Count configuration.
     * @param array|null            $arrParamValue            Parameter value.
     * @param array                 $arrMyFilterUrl           Filter url of the current filter.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function buildParameterFilterWidgets(
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions,
        $objAttribute,
        $strParamName,
        $arrOptions,
        $arrCount,
        $arrParamValue,
        $arrMyFilterUrl
    ) {
        return  array(
            $this->getParamName() => $this->prepareFrontendFilterWidget(
                array(
                    'label' => array(
                        ($this->get('label') ? $this->get('label') : $objAttribute->getName()),
                        'GET: ' . $strParamName
                    ),
                    'inputType' => 'tags',
                    'options' => $arrOptions,
                    'count' => $arrCount,
                    'showCount' => $objFrontendFilterOptions->isShowCountValues(),
                    'eval' => array(
                        'includeBlankOption' => (
                            $this->get('blankoption')
                            && !$objFrontendFilterOptions->isHideClearFilter()
                        ),
                        'blankOptionLabel'   => &$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
                        'multiple'           => true,
                        'colname'            => $objAttribute->getColname(),
                        'urlparam'           => $strParamName,
                        'onlyused'           => $this->get('onlyused'),
                        'onlypossible'       => $this->get('onlypossible'),
                        'template'           => $this->get('template')
                    ),
                    // We need to implode again to have it transported correctly in the frontend filter.
                    'urlvalue' => !empty($arrParamValue) ? implode(',', $arrParamValue) : ''
                ),
                $arrMyFilterUrl,
                $arrJumpTo,
                $objFrontendFilterOptions
            )
        );
    }
}
