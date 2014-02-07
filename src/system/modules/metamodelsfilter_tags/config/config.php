<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FilterTags
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Frontend filter
 */
$GLOBALS['METAMODELS']['filters']['tags']['class']         = 'MetaModels\Filter\Setting\Tags';
$GLOBALS['METAMODELS']['filters']['tags']['image']         = 'system/modules/metamodelsfilter_tags/html/filter_tags.png';
$GLOBALS['METAMODELS']['filters']['tags']['info_callback'] = array('MetaModels\DcGeneral\Events\Table\FilterSetting\DrawSetting', 'modelToLabelWithAttributeAndUrlParam');
$GLOBALS['METAMODELS']['filters']['tags']['attr_filter'][] = 'select';
$GLOBALS['METAMODELS']['filters']['tags']['attr_filter'][] = 'text';
$GLOBALS['METAMODELS']['filters']['tags']['attr_filter'][] = 'translatedselect';
$GLOBALS['METAMODELS']['filters']['tags']['attr_filter'][] = 'translatedtags';
