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
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}


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
	 * {@inheritdoc}
	 */
	protected function getParamName()
	{
		if ($this->get('urlparam'))
		{
			return $this->get('urlparam');
		}

		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			return $objAttribute->getColName();
		}
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
		$arrOptions = $objAttribute->getFilterOptions(null, true);

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
	public function getParameters()
	{
		return ($strParamName = $this->getParamName()) ? array($strParamName) : array();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));

		$arrLabel = array(
			($this->get('label') ? $this->get('label') : $objAttribute->getName()),
			'GET: '.$this->get('urlparam')
			);

		$arrOptions = $objAttribute->getFilterOptions();

		// show only tags used somewhere
		if($this->get('onlyused'))
		{
			foreach($arrOptions as $key=>$val)
			{
				if(count($objAttribute->searchFor($key)) < 1)
				{
					unset($arrOptions[$key]);
				}
			}
		}

		return array(
			$this->getParamName() => array
			(
				'label'     => $arrLabel,
				'inputType' => 'tags',
				'options'   => $arrOptions,
				'eval'      => array(
					'multiple'     => true,
					'colname'      => $objAttribute->getColname(),
					'urlparam'     => $this->get('urlparam'),
					'onlyused'     => $this->get('onlyused'),
					'onlypossible' => $this->get('onlypossible'),
					'template'           => $this->get('template')
					)
			)
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

?>