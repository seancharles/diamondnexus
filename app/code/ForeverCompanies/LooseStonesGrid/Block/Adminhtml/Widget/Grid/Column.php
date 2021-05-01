<?php

namespace ForeverCompanies\LooseStonesGrid\Block\Adminhtml\Widget\Grid;

use Magento\Backend\Block\Widget\Grid\Column as OrigColumn;

class Column extends OrigColumn
{   
    public function afterGetRowField(\Magento\Backend\Block\Widget\Grid\Column $subject, $str)
    {
        $subjectIndex = $subject->getIndex(); 
        
        if ($subjectIndex == "cert_url_key") {
            return '<a href="' . $str . '" target="_blank">Cert</a>';
        } elseif($subjectIndex == "diamond_img_url") {
            return '<a href="' . $str . '" target="_blank">Image</a>';
        } elseif($subjectIndex == "video_url") {
            return '<a href="' . $str . '" target="_blank">Video</a>';
        }   
        return $str;
    }
}