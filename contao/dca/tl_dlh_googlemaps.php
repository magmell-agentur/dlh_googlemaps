<?php
use Contao\Backend;
use Contao\System;
use Contao\DataContainer;
use Contao\BackendUser;
use delahaye\googlemaps\MapModel;
use delahaye\googlemaps\GoogleMaps;
use Contao\Input;
use Contao\Image;
use Contao\FilesModel; 
use Contao\StringUtil;
use Contao\Files;
use Contao\Date;
use Contao\DC_Table;
use Contao\DateTime;
use Contao\Database;
/**
 * dlh_googlemaps
 * Extension for Contao Open Source CMS (contao.org)
 *
 * Copyright (c) 2014 de la Haye
 *
 * @package dlh_googlemaps
 * @author  Christian de la Haye
 * @link    http://delahaye.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Table tl_dlh_googlemaps
 */
$GLOBALS['TL_DCA']['tl_dlh_googlemaps'] = [

    // Config
    'config'      => [
		'dataContainer'               => DC_Table::class,
        'ctable'           => ['tl_dlh_googlemaps_elements'],
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'filter;search,limit',
        ],
        'label'             => [
            'fields'         => ['title'],
            'format'         => '%s',
            'label_callback' => ['tl_dlh_googlemaps', 'listRecords'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'  ,    
            'children',
            'copy'      , 
            'delete'     ,
            'show'       
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__' => [
            'useMapTypeControl',
            'useZoomControl',
            'usePanControl',
            'useRotateControl',
            'useScaleControl',
            'useStreetViewControl',
            'useOverviewMapControl',
            'useClusterer',
        ],
        'default'      => '{title_legend},title,geocoderAddress,geocoderCountry,center,mapSize,zoom;{maptype_legend:hide},mapTypeId,mapTypesAvailable,disableDoubleClickZoom,draggable,scrollwheel,staticMapNoscript,useClusterer;{maptypecontrols_legend:hide},useMapTypeControl;{zoomcontrol_legend:hide},useZoomControl;{rotatecontrol_legend:hide},useRotateControl;{pancontrol_legend:hide},usePanControl;{scalecontrol_legend:hide},useScaleControl;{streetviewcontrol_legend:hide},useStreetViewControl;{overviewmapcontrol_legend:hide},useOverviewMapControl;{parameter_legend:hide},parameter,moreParameter',
    ],

    // Subpalettes
    'subpalettes' => [
        'useMapTypeControl'     => 'mapTypeControlStyle,mapTypeControlPos',
        'useZoomControl'        => 'zoomControlStyle,zoomControlPos',
        'useRotateControl'      => 'rotateControlStyle,rotateControlPos',
        'usePanControl'         => 'panControlStyle,panControlPos',
        'useScaleControl'       => 'scaleControlPos',
        'useStreetViewControl'  => 'streetViewControlPos',
        'useOverviewMapControl' => 'overviewMapControlOpened',
        'useClusterer'          => 'clustererImg',
    ],

    // Fields
    'fields'      => [
        'id'                       => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'                   => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'center'                   => [
            'label'         => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['center'],
            'exclude'       => true,
            'search'        => true,
            'inputType'     => 'text',
            'eval'          => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql'           => "varchar(64) NOT NULL default ''",
            'save_callback' => [
                ['tl_dlh_googlemaps', 'generateCoords'],
            ],
        ],
        'geocoderAddress'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['geocoderAddress'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'geocoderCountry'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['geocoderCountry'],
            'exclude'   => true,
            'filter'    => true,
            'sorting'   => true,
            'inputType' => 'select',
            'options'   => System::getContainer()->get('contao.intl.countries'),
            'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(2) NOT NULL default 'de'",
        ],
        'mapSize'                  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['mapSize'],
            'exclude'   => true,
            'inputType' => 'imageSize',
            'options_callback' => function ()
            {
                    return array_merge(['' => '-'], System::getContainer()->get('contao.image.sizes')->getOptionsForUser(BackendUser::getInstance()));
            },
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => ['rgxp' => 'digit', 'nospace' => true, 'helpwizard' => false, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'zoom'                     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['zoom'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20'],
            'default'   => '10',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "int(10) unsigned NOT NULL default '10'",
        ],
        'mapTypeId'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['mapTypeId'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN'],
            'default'   => 'ROADMAP',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['mandatory' => true],
            'sql'       => "varchar(16) NOT NULL default 'ROADMAP'",
        ],
        'mapTypesAvailable'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['mapTypesAvailable'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'options'   => ['HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN'],
            'default'   => serialize(['HYBRID', 'ROADMAP', 'SATELLITE', 'TERRAIN']),
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['mandatory' => true, 'multiple' => true],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'staticMapNoscript'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['staticMapNoscript'],
            'exclude'   => true,
            'default'   => false,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'useClusterer'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useClusterer'],
            'exclude'   => true,
            'filter'    => true,
            'default'   => false,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr m12'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'clustererImg'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['clustererImg'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'maxlength' => 255],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'useMapTypeControl'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useMapTypeControls'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'mapTypeControlStyle'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['mapTypeControlStyle'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['DEFAULT', 'DROPDOWN_MENU', 'HORIZONTAL_BAR'],
            'default'   => 'DEFAULT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['mandatory' => true],
            'sql'       => "varchar(16) NOT NULL default 'DEFAULT'",
        ],
        'mapTypeControlPos'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['controlPos'],
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => [
                'TOP_LEFT',
                'TOP_CENTER',
                'TOP_RIGHT',
                'LEFT_TOP',
                'C1',
                'RIGHT_TOP',
                'LEFT_CENTER',
                'C2',
                'RIGHT_CENTER',
                'LEFT_BOTTOM',
                'C3',
                'RIGHT_BOTTOM',
                'BOTTOM_LEFT',
                'BOTTOM_CENTER',
                'BOTTOM_RIGHT',
            ],
            'default'   => 'TOP_RIGHT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['cols' => 3, 'tl_class' => 'dlh_googlemaps_position'],
            'sql'       => "varchar(16) NOT NULL default 'TOP_RIGHT'",
        ],
        'useZoomControl'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useZoomControl'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'zoomControlStyle'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['zoomControlStyle'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['ANDROID', 'DEFAULT', 'SMALL', 'ZOOM_PAN'],
            'default'   => 'DEFAULT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['mandatory' => true],
            'sql'       => "varchar(16) NOT NULL default 'DEFAULT'",
        ],
        'zoomControlPos'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['controlPos'],
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => [
                'TOP_LEFT',
                'TOP_CENTER',
                'TOP_RIGHT',
                'LEFT_TOP',
                'C1',
                'RIGHT_TOP',
                'LEFT_CENTER',
                'C2',
                'RIGHT_CENTER',
                'LEFT_BOTTOM',
                'C3',
                'RIGHT_BOTTOM',
                'BOTTOM_LEFT',
                'BOTTOM_CENTER',
                'BOTTOM_RIGHT',
            ],
            'default'   => 'TOP_LEFT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['cols' => 3, 'tl_class' => 'dlh_googlemaps_position'],
            'sql'       => "varchar(16) NOT NULL default 'TOP_LEFT'",
        ],
        'useRotateControl'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useRotateControl'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'rotateControlPos'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['controlPos'],
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => [
                'TOP_LEFT',
                'TOP_CENTER',
                'TOP_RIGHT',
                'LEFT_TOP',
                'C1',
                'RIGHT_TOP',
                'LEFT_CENTER',
                'C2',
                'RIGHT_CENTER',
                'LEFT_BOTTOM',
                'C3',
                'RIGHT_BOTTOM',
                'BOTTOM_LEFT',
                'BOTTOM_CENTER',
                'BOTTOM_RIGHT',
            ],
            'default'   => 'TOP_LEFT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['cols' => 3, 'tl_class' => 'dlh_googlemaps_position'],
            'sql'       => "varchar(16) NOT NULL default 'TOP_LEFT'",
        ],
        'usePanControl'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['usePanControl'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'panControlPos'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['controlPos'],
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => [
                'TOP_LEFT',
                'TOP_CENTER',
                'TOP_RIGHT',
                'LEFT_TOP',
                'C1',
                'RIGHT_TOP',
                'LEFT_CENTER',
                'C2',
                'RIGHT_CENTER',
                'LEFT_BOTTOM',
                'C3',
                'RIGHT_BOTTOM',
                'BOTTOM_LEFT',
                'BOTTOM_CENTER',
                'BOTTOM_RIGHT',
            ],
            'default'   => 'TOP_LEFT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['cols' => 3, 'tl_class' => 'dlh_googlemaps_position'],
            'sql'       => "varchar(16) NOT NULL default 'TOP_LEFT'",
        ],
        'useStreetViewControl'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useStreetViewControl'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'streetViewControlPos'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['controlPos'],
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => [
                'TOP_LEFT',
                'TOP_CENTER',
                'TOP_RIGHT',
                'LEFT_TOP',
                'C1',
                'RIGHT_TOP',
                'LEFT_CENTER',
                'C2',
                'RIGHT_CENTER',
                'LEFT_BOTTOM',
                'C3',
                'RIGHT_BOTTOM',
                'BOTTOM_LEFT',
                'BOTTOM_CENTER',
                'BOTTOM_RIGHT',
            ],
            'default'   => 'TOP_LEFT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['cols' => 3, 'tl_class' => 'dlh_googlemaps_position'],
            'sql'       => "varchar(16) NOT NULL default 'TOP_LEFT'",
        ],
        'useOverviewMapControl'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useOverviewMapControl'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'overviewMapControlOpened' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['overviewMapControlOpened'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'disableDoubleClickZoom'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['disableDoubleClickZoom'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'scrollwheel'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['scrollwheel'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'draggable'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['draggable'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'useScaleControl'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['useScaleControl'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => '1',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'scaleControlPos'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['controlPos'],
            'exclude'   => true,
            'inputType' => 'radioTable',
            'options'   => [
                'TOP_LEFT',
                'TOP_CENTER',
                'TOP_RIGHT',
                'LEFT_TOP',
                'C1',
                'RIGHT_TOP',
                'LEFT_CENTER',
                'C2',
                'RIGHT_CENTER',
                'LEFT_BOTTOM',
                'C3',
                'RIGHT_BOTTOM',
                'BOTTOM_LEFT',
                'BOTTOM_CENTER',
                'BOTTOM_RIGHT',
            ],
            'default'   => 'BOTTOM_LEFT',
            'reference' => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['references'],
            'eval'      => ['cols' => 3, 'tl_class' => 'dlh_googlemaps_position'],
            'sql'       => "varchar(16) NOT NULL default 'BOTTOM_LEFT'",
        ],
        'parameter'                => [
            'label'       => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['parameter'],
            'exclude'     => true,
            'inputType'   => 'textarea',
            'eval'        => ['preserveTags' => true, 'decodeEntities' => true, 'class' => 'monospace', 'rte' => 'ace', 'helpwizard' => true],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'moreParameter'            => [
            'label'       => &$GLOBALS['TL_LANG']['tl_dlh_googlemaps']['moreParameter'],
            'exclude'     => true,
            'inputType'   => 'textarea',
            'eval'        => ['preserveTags' => true, 'decodeEntities' => true, 'class' => 'monospace', 'rte' => 'ace', 'helpwizard' => true],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
    ],
];


/**
 * Class tl_dlh_googlemaps
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  2014 de la Haye
 * @author     Christian de la Haye <http://delahaye.de>
 * @package    dlh_googlemaps
 */
class tl_dlh_googlemaps extends Backend
{



    /**
     * Get geo coodinates drom address
     *
     * @param string
     * @param object
     *
     * @return string
     */
    function generateCoords($varValue, DataContainer $dc)
    {
        return $varValue ? $varValue : \delahaye\GeoCode::getCoordinates($dc->activeRecord->geocoderAddress, $dc->activeRecord->geocoderCountry, 'de');
    }


    /**
     * List records
     *
     * @param array
     *
     * @return string
     */
    public function listRecords($arrRow)
    {
        $return = '<strong>' . $arrRow['title'] . '</strong>';
        if ($arrRow['center'] && $arrRow['zoom'] && $arrRow['mapTypeId'])
        {
            $apikey   = '';
            $pageList = Database::getInstance()->prepare("select dlh_googlemaps_apikey from tl_page")->execute();
            while ($pageList->next())
            {
                if ($pageList->dlh_googlemaps_apikey)
                {
                    $apikey = '&key=' . $pageList->dlh_googlemaps_apikey;
                    continue;
                }
            }

            $src    = 'https://maps.google.com/maps/api/staticmap?center=' . $arrRow['center'] . $apikey . '&zoom=' . ($arrRow['zoom'] - 2) . '&maptype=' . strtolower(
                    $arrRow['mapTypeId']
                ) . '&language=' . $GLOBALS['TL_LANGUAGE'] . '&size=300x150';
            $return .= '<div style="margin-top:5px;margin-bottom:20px;"><img src="' . $src . '" alt="" /></div>';
        }

        return $return;
    }


}

?>