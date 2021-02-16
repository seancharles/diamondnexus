<?php

namespace ForeverCompanies\CustomAttributes\Model\Config\Source\Product;

/**
 * Customization types mode source
 */
class CustomizationType extends AbstractCustomizationType
{

    const OPTIONS = [
        ' ' => '',
        'Metal Type' => 'metal_type',
        'Chain Length' => 'chain_length',
        'Chain Width' => 'chain_size',
        'Ring Size' => 'ring_size',
        'Certified Stone' => 'certified_stone',
        'Total Carat Weight' => 'tcw',
        'Stone Color' => 'color',
        'Stone Shape' => 'cut',
        'Shape' => 'shape',
        'Band Width' => 'band_width'
    ];

    const TITLE_MAPPING = [
        '' => '',
        'Metal Type' => 'Metal Type',
        'Chain Length' => 'Chain Length',
        'Chain Width' => 'Chain Width',
        'Ring Size' => 'Ring Size',
        'Certified Stone' => 'Certified Stone',
        'Total Carat Weight' => 'Total Carat Weight',
        'Stone Color' => 'Stone Color',
        'Color' => 'Stone Color',
        'Pearl Color' => 'Stone Color',
        'Stone Shape' => 'Stone Shape',
        'Shape' => 'Shape',
        'Center Stone Cut' => 'Stone Shape',
        'Band Width' => 'Band Width',
    ];
}
