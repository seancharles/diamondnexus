<?php

namespace ForeverCompanies\CustomAttributes\Model\Config\Source\Product;

/**
 * Customization types mode source
 */
class BundleCustomizationType extends AbstractCustomizationType
{
    const OPTIONS = [
        ' ' => '',
        'Matching Band' => 'matching_band',
        'Center Stone Size' => 'center_stone_size'
    ];

    const TITLE_MAPPING = [
        '' => '',
        'Matching Band' => 'Matching Band',
        'Matching Bands' => 'Matching Band',
        'Center Stone Size' => 'Center Stone Size',
    ];

}
