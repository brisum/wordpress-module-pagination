<?php

namespace Brisum\Wordpress\Pagination;

class Pagination {
    /**
     * @var array
     */
    protected $args;

    /**
     * Pagination constructor.
     * @param array $args
     */
    public function __construct($args = array())
    {
        $this->args = $args;
    }

    /**
     * @return array|string
     */
    public function content() {
        global $wp_query;
        $max = $wp_query->max_num_pages;
        $current = get_query_var('paged');

        if ( !$current ) {
            $current = 1;
        }

        $a['base'] = str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999)));
        $a['total'] = $max;
        $a['current'] = $current;
        $a['type'] = 'list';

        $total = 1; //1 - выводить текст "Страница N из N", 0 - не выводить
        $a['mid_size'] = 3; //сколько ссылок показывать слева и справа от текущей
        $a['end_size'] = 1; //сколько ссылок показывать в начале и в конце
        $a['prev_text'] = '&larr;'; //текст ссылки "Предыдущая страница"
        $a['next_text'] = '&rarr;'; //текст ссылки "Следующая страница"
        $a = array_merge($a, $this->args);

        return $this->links($a);
    }

    public function links( $args = '' ) {
        $defaults = array(
            'base' => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
            'format' => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
            'total' => 1,
            'current' => 0,
            'show_all' => false,
            'prev_next' => true,
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
            'end_size' => 1,
            'mid_size' => 2,
            'type' => 'plain',
            'add_args' => false, // array of query args to add
            'add_fragment' => ''
        );

        $args = wp_parse_args( $args, $defaults );
        extract($args, EXTR_SKIP);

        // Who knows what else people pass in $args
        $total = (int) $total;
        if ( $total < 2 )
            return;

        $current  = (int) $current;
        $end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
        $mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
        $add_args = is_array($add_args) ? $add_args : false;
        $r = '';
        $page_links = array();
        $n = 0;
        $dots = false;

        if ( $prev_next && $current && 1 < $current ) :
            $pN = $current - 1;
            // $link = str_replace('%_%', 2 == $current ? '' : $format, $base);
            $link = str_replace('%_%', $format, $base);
            // $link = str_replace('%#%', $current - 1, $link);
            $link = str_replace(1 == $pN ? 'page/%#%/' : '%#%', 1 == $pN ? '' : $pN, $link);

            if ( $add_args )
                $link = add_query_arg( $add_args, $link );
            $link .= $add_fragment;

            $page_links[] = '<a class="prev page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $prev_text . '</a>';
        endif;

        for ( $n = 1; $n <= $total; $n++ ) :
            $n_display = number_format_i18n($n);

            if ( $n == $current ) :
                $page_links[] = "<span class='page-numbers current'>$n_display</span>";
                $dots = true;
            else :
                if ( $show_all || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :

                    // $link = str_replace('%_%', 1 == $n ? '' : $format, $base);
                    $link = str_replace('%_%', $format, $base);

                    // fix /page/1/
                    $link = str_replace(1 == $n ? 'page/%#%/' : '%#%', 1 == $n ? '' : $n, $link);
                    // end fix

                    if ( $add_args )
                        $link = add_query_arg( $add_args, $link );
                    $link .= $add_fragment;
                    $page_links[] = "<a class='page-numbers' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>$n_display</a>";
                    $dots = true;
                elseif ( $dots && !$show_all ) :
                    $page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
                    $dots = false;
                endif;
            endif;
        endfor;

        if ( $prev_next && $current && ( $current < $total || -1 == $total ) ) :
            $link = str_replace('%_%', $format, $base);
            $link = str_replace('%#%', $current + 1, $link);
            if ( $add_args )
                $link = add_query_arg( $add_args, $link );
            $link .= $add_fragment;
            $page_links[] = '<a class="next page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $next_text . '</a>';
        endif;
        switch ( $type ) :
            case 'array' :
                return $page_links;
                break;
            case 'list' :
                $r .= "<ul class='page-numbers'>\n\t<li>";
                $r .= join("</li>\n\t<li>", $page_links);
                $r .= "</li>\n</ul>\n";
                break;
            default :
                $r = join("\n", $page_links);
                break;
        endswitch;
        return $r;
    }
}
