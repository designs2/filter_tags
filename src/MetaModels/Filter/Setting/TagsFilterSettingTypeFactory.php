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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

/**
 * Attribute type factory for tags filter settings.
 */
class TagsFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->setTypeName('tags')
            ->setTypeIcon('system/modules/metamodelsfilter_tags/html/filter_tags.png')
            ->setTypeClass('MetaModels\Filter\Setting\Tags');

        foreach (array(
            'select',
            'translatedselect',
            'text',
            'translatedtext',
            'tags',
            'translatedtags',
         ) as $attribute) {
            $this->addKnownAttributeType($attribute);
        }
    }
}
