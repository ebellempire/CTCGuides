<?php

class CTCGuidesPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'initialize',
    );

    public function hookInitialize()
    {
        add_shortcode('guides', array($this, 'ctc_postlist'));
        add_shortcode('toc', array($this, 'ctc_table_of_contents'));
    }

    public function ctc_postlist($args)
    {
        return '<div id="ctc-widget-container">'.ctc_display_postlist($args).'</div>';
    }
    public function ctc_table_of_contents($args)
    {
        return '<div id="ctc_toc_container"></div><script type="text/javascript" src="/plugins/CTCGuides/ctc_toc.js"></script>';
    }
}

function ctc_sortByDateDesc($a, $b)
{
    return strtotime($a["inserted"]) < strtotime($b["inserted"]);
}

function ctc_display_postlist($args)
{
    $current=get_current_record('SimplePagesPage', false);
    $guideParentId=isset($args['parent']) && ($bp=get_record('SimplePagesPage', array('slug'=>$args['parent']))) ?
        $bp->id : $current->id;
    if ($guideParentId) {
        $pages = get_db()->getTable('SimplePagesPage')->findAll();
        $total=isset($args['number']) ? filter_var($args['number'], FILTER_VALIDATE_INT) : 10;
        $excerpt_length=isset($args['length']) ? filter_var($args['length'], FILTER_VALIDATE_INT) : 500;
        $show_author=isset($args['author']) ? filter_var($args['author'], FILTER_VALIDATE_BOOLEAN) : true;
        $show_date=isset($args['date']) ? filter_var($args['date'], FILTER_VALIDATE_BOOLEAN) : true;
        $show_readmore=isset($args['more']) ? filter_var($args['more'], FILTER_VALIDATE_BOOLEAN) : true;
        $truncate = '&nbsp;&hellip;';
        $posts=array();
        $i=0;
        usort($pages, "ctc_sortByDateDesc");
        foreach ($pages as $page) {
            if ($i==$total) {
                break;
            }
            $class=(($i+1) % 2 == 0) ? 'even' : 'odd';
            $html=null;
            if ($page['parent_id']==$guideParentId && $page['is_published']) {
                $date=date('d M Y', strtotime($page['inserted']));
                $url=WEB_ROOT.'/'.$page['slug'];
                if ($show_readmore) {
                    $truncate .= '&nbsp;<a href="'.$url.'">'.__('Read More').'</a>';
                }
                $userid=$page['created_by_user_id'];
                $username=get_record_by_id('user', $userid)->name ?
                    get_record_by_id('user', $userid)->name :
                    get_record_by_id('user', $userid)->username;
                $html.='<article class="post post-position-'.($i+1).' '.$class.'">';
                $html.='<h3><a href="'.$url.'">'.$page['title'].'</a></h3>';
                $html.='<div class="byline">'.($show_date ? __('Posted on %s', $date) : null).' '.($show_author ? __('by %s', $username) : null).'</div>';

                $html.='<p>'.snippet(
                    $page['text'],
                    0,
                    $excerpt_length,
                    $truncate
                ).'</p>';

                $html.='</article>';

                $posts[]=$html;
                $i++;
            }
        }
        return count($posts)>0 ? '<div class="posts">'.implode('', $posts) .'</div>' : '<p><em>No posts found!</em></p>';
    } else {
        return '<p><em>No posts found!</em></p>';
    }
}
