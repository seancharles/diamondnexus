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
        'Chain Width' => 'chain_width',
        'Ring Size' => 'ring_size',
        'Certified Stone' => 'certified_stone',
        'Total Carat Weight' => 'tcw',
        'Stone Color' => 'color',
        'Stone Shape' => 'cut',
        'Shape' => 'shape'
    ];
}
