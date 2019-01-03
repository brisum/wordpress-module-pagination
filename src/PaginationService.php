<?php

namespace Brisum\Wordpress\Pagination;

use Brisum\Lib\ObjectManager;

class PaginationService
{
    public function render()
    {
        $base = null;
        $format =  null;

        if (is_search()) {
            $base = HOME_URL . '/%_%/?s=' . get_search_query();
            $format = 'page/%#%';
        }

        /** @var Pagination $pagination */
        $pagination = ObjectManager::getInstance()->create(
            'Brisum\Wordpress\Pagination\Pagination',
            [
                'args' => array_filter([
                    'base' => $base,
                    'format' => $format,
                    'prev_text' => '&lsaquo;',
                    'next_text' => '&rsaquo;',
                    'mid_size' => 3
                ])
            ]
        );

        echo $pagination->content();
    }
}
