<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::THEME, 'frontend/ForeverCompanies/tf', __DIR__);
//making the theme into a module so that the csp_whitelist.xml gets read
ComponentRegistrar::register(ComponentRegistrar::MODULE, 'ForeverCompanies_tf', __DIR__);
