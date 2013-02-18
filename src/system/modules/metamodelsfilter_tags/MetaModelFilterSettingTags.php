<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Filter "tags" for FE-filtering, based on filters by the meta models team.
 *
 * @package	   MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class MetaModelFilterSettingTags extends MetaModelFilterSettingSimpleLookup
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
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objMetaModel = $this->getMetaModel();
		$objAttribute = $objMetaModel->getAttributeById($this->get('attr_id'));
		$strParamName = $this->getParamName();
		$arrParamValue = $arrFilterUrl[$strParamName];

		$arrOptions = $this->getParameterFilterOptions($objAttribute, NULL);

		if ($objAttribute && $strParamName && is_array($arrParamValue) && $arrOptions)
		{

			$arrIds = array();

			foreach($arrParamValue as $strParamValue)
			{
				if($arrOptions[$strParamValue])
				{
					$arrIds[] = $objAttribute->searchFor($strParamValue);
				}
			}

			// AND / OR tags
			for($i = 0; $i<count($arrIds); $i++)
			{
				$arrIds[0] = ($objAttribute->get('type')=='select' || $this->get('useor')) ? array_unique(array_merge($arrIds[0],$arrIds[$i])) : array_unique(array_intersect($arrIds[0],$arrIds[$i]));
			}

			$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($arrIds[0]));
			return;

		}

		$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList(NULL));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));

		$arrOptions = $this->getParameterFilterOptions($objAttribute, $arrIds);

		asort($arrOptions);

		return array(
			$this->getParamName() => $this->prepareFrontendFilterWidget(array
			(
				'label'     => array(
					// TODO: make this multilingual.
					($this->get('label') ? $this->get('label') : $objAttribute->getName()),
					'GET: ' . $this->getParamName()
				),
				'inputType' => 'tags',
				'options'   => $arrOptions,
				'eval'      => array(
					'includeBlankOption' => ($this->get('blankoption') ? true : false),
					'blankOptionLabel'   => &$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
					'multiple'     => true,
					'colname'      => $objAttribute->getColname(),
					'urlparam'     => $this->getParamName(),
					'onlyused'     => $this->get('onlyused'),
					'onlypossible' => $this->get('onlypossible'),
					'template'     => $this->get('template')
					)
			),
			$arrFilterUrl,
			$arrJumpTo,
			$blnAutoSubmit)
		);
	}

	/**
	 * Overrides the parent implementation to always return true, as this setting is always available for FE filtering.
	 *
	 * @return bool true as this setting is always available.
	 */
	public function enableFEFilterWidget()
	{
		return true;
	}
}

