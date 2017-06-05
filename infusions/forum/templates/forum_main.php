<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates/forum_main.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
/**
 * Forum Page Control Layout
 */
if (!function_exists('render_forum')) {
    function render_forum($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        if (isset($_GET['viewforum'])) {
            forum_viewforum($info);
        } else {
            if (isset($_GET['section']) && $_GET['section'] == 'participated') {
                render_participated($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'latest') {
                render_laft($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'tracked') {
                render_tracked($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'unanswered') {
                render_unanswered($info);
            } elseif (isset($_GET['section']) && $_GET['section'] == 'unsolved') {
                render_unsolved($info);
            } elseif (!isset($_GET['section']) or isset($_GET['section']) && $_GET['section'] == 'thread') {
                render_forum_main($info);
            }
        }
    }
}

/**
 * Forum Page
 */
if (!function_exists('render_forum_main')) {
    /**
     * Main Forum Page - Recursive
     *
     * @param array $info
     * @param int   $id - counter nth
     */
    function render_forum_main(array $info, $id = 0) {
        require_once FORUM_CLASS."autoloader.php";
        $locale = fusion_get_locale();
        ?>
        <div class='spacer-sm'>
            <?php echo render_breadcrumbs() ?>
        </div>
        <div class='forum-header' style="background: url(<?php echo FORUM.'images/default_forum_bg.jpg' ?>) no-repeat; background-size:cover;">
            <div class='banner' style='display:block; height:180px; overflow:hidden;'>
                <h2 class='p-20 center-y text-white' style='z-index: 2'><?php echo $locale['forum_0013'] ?></h2>
            </div>
        </div>
        <div class='spacer-sm'>
            <div class='row'>
                <div class='col-xs-12 col-sm-9 col-lg-9'>

                    <div class='row'>
                        <div class='col-xs-6 col-sm-4 col-md-3 col-lg-2'></div>
                    </div>

                    <table class='table table-responsive clear'>
                        <?php
                        if (!empty($info['forums'][$id])) {
                            $forums = $info['forums'][$id];
                            $x = 1;
                            foreach ($forums as $forum_id => $data) {
                                if ($data['forum_type'] == '1') {
                                    ?>
                                    <tr>
                                        <td style='padding-top:20px;'>
                                            <a title='<?php echo $data['forum_link']['title'] ?>' class='forum-subject' href='<?php echo $data['forum_link']['link'] ?>'>
                                                <small class='text-uppercase'>
                                                    <strong>
                                                        <?php echo $data['forum_link']['title'] ?>
                                                    </strong>
                                                </small>
                                            </a>
                                        </td>
                                        <td style='padding-top:20px;'>
                                            <small class='text-uppercase strong text-lighter'>Threads</small>
                                        </td>
                                        <td style='padding-top:20px;'>
                                            <small class='text-uppercase strong text-lighter'>Posts</small>
                                        </td>
                                        <td class='col-xs-4' style='padding-top:20px;'>
                                            <small class='text-uppercase strong text-lighter'>Latest Threads</small>
                                        </td>
                                    </tr>
                                    <?php
                                    if (isset($info['forums'][0][$forum_id]['child'])) {
                                        echo "<!---subforums-->";
                                        $i = 1;
                                        $sub_forums = $info['forums'][0][$forum_id]['child'];
                                        foreach ($sub_forums as $sub_forum_id => $cdata) {
                                            render_forum_item($cdata, $i);
                                            $i++;
                                        }
                                    } else {
                                        echo "<div class='well'>\n";
                                        echo $locale['forum_0327'];
                                        echo "</div>\n";
                                    }
                                    ?>
                                    <?php
                                } else {
                                    echo "<div class='well'>";
                                    render_forum_item($data, $x);
                                    echo "</div>\n";
                                    $x++;
                                }
                            }
                        } else {
                            echo "<div class='well text-center'>".$locale['forum_0328']."</div>\n";
                        }
                        ?>
                    </table>
                </div>
                <div class='col-xs-12 col-sm-3 col-lg-3'>
                    <?php //print_p($info, 1) ?>
                    <div class='spacer-sm m-b-50'>
                        <a class='btn btn-primary btn-block' href='<?php echo $info['new_topic_link']['link'] ?>'><i class='fa fa-comment m-r-10'></i><?php echo $info['new_topic_link']['title'] ?></a>
                    </div>
                    <?php
                    $threadTags = \PHPFusion\Forums\ForumServer::tag(TRUE, FALSE)->get_TagInfo();
                    if (!empty($threadTags['tags'])) : ?>
                        <!--Forum Tags-->
                        <h4 class='spacer-sm'><strong>Filter by Tags</strong></h4>
                        <ul class="list-group spacer-md">
                            <?php foreach ($threadTags['tags'] as $tag_id => $tag_data) : ?>
                                <li class='list-group-item<?php echo($tag_data['tag_active'] == TRUE ? ' active' : '') ?>'>
                                    <a href="<?php echo $tag_data['tag_link'] ?>">
                                        <div class="pull-left m-r-10"><i class="fa fa-square fa-lg" style="color:<?php echo $tag_data['tag_color'] ?>"></i></div>
                                        <div class="pull-left">
                                            <?php echo $tag_data['tag_title'] ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <!--//Forum Tags-->

                        <?php
                    endif;
                    // Run custom query
                    $custom_result = dbquery("SELECT thread_id, thread_subject, thread_author, thread_postcount FROM ".DB_FORUM_THREADS."
                        INNER JOIN ".DB_FORUMS.(multilang_column('FO') ? " WHERE forum_language='".LANGUAGE."' AND " : " WHERE ").groupaccess('forum_access')." and (thread_lastpost >=:one_week and thread_lastpost < :current) and thread_locked=:not_locked and thread_hidden=:not_hidden
                        GROUP BY thread_id ORDER BY thread_postcount DESC LIMIT 10",
                        [
                            ':one_week'   => TIME - (7 * 24 * 3600),
                            ':current'    => TIME,
                            ':not_locked' => 0,
                            ':not_hidden' => 0,
                        ]);
                    if (dbrows($custom_result)) : ?>
                        <h4 class='spacer-sm'><strong>Popular Threads This Week</strong></h4>
                        <div class='spacer-md'>
                            <?php while ($popular = dbarray($custom_result)) :
                                $user = fusion_get_user($popular['thread_author']);
                                ?>
                                <div>
                                    <a href='<?php echo FORUM."viewthread.php?thread_id=".$popular['thread_id'] ?>'><strong><?php echo $popular['thread_subject'] ?></strong></a><br/>
                                    <?php echo $locale['by'] ?> <?php echo profile_link($user['user_id'], $user['user_name'], $user['user_status']) ?>
                                    <span class='text-lighter'><i class='fa fa-comment'></i> <?php echo format_word($popular['thread_postcount'], $locale['fmt_post']) ?></span>
                                </div>
                                <hr/>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        //print_p($info, true);
    }
}

/**
 * Forum Item
 */
if (!function_exists('render_forum_item')) {
    /**
     * Switch between different types of forum list containers
     *
     * @param $data
     * @param $i
     */
    function render_forum_item($data, $i) {
        $locale = fusion_get_locale();
        /*if ($i > 0) {
            echo "<div id='forum_".$data['forum_id']."' class='forum-container'>\n";
        } else {
            echo "<div id='forum_".$data['forum_id']."' class='panel panel-default'>\n";
            echo "<div class='panel-body'>\n";
        }*/

        ?>
        <tr>
            <td style='border-radius: 4px 0 0 4px; background: #f7f7f7; border-top:4px solid #fff; border-bottom:4px solid #fff;'>
                <a title='<?php echo $data['forum_link']['title'] ?>' class='forum-subject' href='<?php echo $data['forum_link']['link'] ?>'>
                    <strong>
                        <?php echo $data['forum_link']['title'] ?>
                    </strong>
                </a>
                <?php if ($data['forum_description']) : echo "<div class='forum-description'>".$data['forum_description']."</div>\n"; endif; ?>
                <?php if ($data['forum_moderators']) : echo "<span class='forum-moderators'><small><strong>".$locale['forum_0007']."</strong>".$data['forum_moderators']."</small></span>\n"; endif; ?>
            </td>
            <td style='background: #f7f7f7;  border-top:4px solid transparent; border-bottom:4px solid #fff;'>
                <?php echo $data['forum_threadcount_word'] ?>
            </td>
            <td style='background: #f7f7f7; border-radius: 0px 4px 4px 0; border-top:4px solid #fff; border-bottom:4px solid #fff; border-right: 4px solid #fff;'>
                <?php echo $data['forum_postcount_word'] ?>
            </td>
            <td style='background: #f7f7f7; border-radius: 4px; border-top:4px solid #fff; border-left:8px solid #fff; border-bottom:4px solid #fff;'>
                <?php
                if ($data['forum_lastpostid'] == 0) {
                    echo $locale['forum_0005'];
                } else {
                    echo "<div class='clearfix'>\n";
                    if (!empty($data['last_post']['avatar'])) {
                        echo "<div class='pull-left lastpost-avatar m-r-10'>".$data['last_post']['avatar']."</div>";
                    }
                    echo "<div class='overflow-hide'>\n";
                    echo "<span class='forum_thread_link'><a style='font-weight: 400; color: #333; text-decoration:underline; font-size:85%;' href='".$data['last_post']['post_link']."'>".trim_text($data['thread_subject'], 35)."</a></span><br/>";
                    echo "<span class='forum_profile_link'>".$data['last_post']['profile_link']." - ".$data['last_post']['time']."</span>\n";
                    echo "</div>\n</div>\n";
                }
                ?>
            </td>
        </tr>

        <?php
        /*
        if ($data['forum_image'] && file_exists(FORUM."images/".$data['forum_image'])) {
            echo thumbnail(FORUM."images/".$data['forum_image'], '50px');
        } else {
            echo "<div class='forum-icon'>".$data['forum_icon_lg']."</div>\n";
        }*/
        /*
        switch ($data['forum_type']) {
            case '3':
                echo "<div class='col-xs-12 col-sm-12'>\n";
                echo "<a class='display-inline-block forum-link' href='".$data['forum_link']['link']."'>".$data['forum_link']['title']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
                if (isset($data['child'])) {
                    echo "<div class='clearfix sub-forum'>\n";
                    foreach ($data['child'] as $cdata) {
                        echo "<i class='entypo level-down'></i>\n";
                        echo "<span class='nowrap'>\n";
                        if (isset($cdata['forum_type'])) {
                            echo $data['forum_icon'];
                        }
                        echo "<a href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$cdata['forum_id']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a></span>";
                        echo "<br/>\n";
                    }
                    echo "</div>\n";
                }
                echo "</div>\n";
                break;
            default:
                echo "<div class='col-xs-12 col-sm-6'>\n";
                echo "
				<a class='display-inline-block forum-link' href='".$data['forum_link']['link']."'>".$data['forum_link']['title']."</a>\n<span class='m-l-5'>".$data['forum_new_status']."</span><br/>";
                if (isset($data['child'])) {
                    echo "<div class='clearfix sub-forum'>\n";
                    echo "<div class='pull-left'>\n";
                    echo "<i class='entypo level-down'></i>\n";
                    echo "</div>\n";
                    echo "<div class='overflow-hide'>\n";
                    foreach ($data['child'] as $cdata) {
                        if (isset($cdata['forum_type'])) {
                            echo $data['forum_icon'];
                        }
                        echo "<a href='".INFUSIONS."forum/index.php?viewforum&amp;forum_id=".$cdata['forum_id']."' class='forum-subforum display-inline-block m-r-10'>".$cdata['forum_name']."</a><br/>";
                    }
                    echo "</div>\n";
                    echo "</div>\n";
                }
                echo "</div>\n";
        }
        */
    }
}

/**
 * For $_GET['viewforum'] view present.
 */
if (!function_exists('forum_viewforum')) {
    function forum_viewforum($info) {
        $locale = fusion_get_locale();

        $data = $info['item'][$_GET['forum_id']];

        ?>
        <div class='spacer-sm'>
            <?php echo render_breadcrumbs() ?>
        </div>
        <div class='forum-header' style="background: url(<?php echo FORUM.'images/default_forum_bg.jpg' ?>) no-repeat; background-size:cover;">
            <div class='banner' style='display:block; height:180px; overflow:hidden;'>
                <div class='center-y p-20'>
                    <!--- add forum image here --->
                    <h2 class='text-white'><?php echo $info['forum_name'] ?></h2>
                    <div class='forum-description text-white'><?php echo $info['forum_description'] ?></div>
                </div>
            </div>
        </div>
        <?php if ($info['forum_rules']) : alert("<span class='strong'><i class='fa fa-exclamation fa-fw'></i>".$locale['forum_0350']."</span> ".$info['forum_rules']); endif; ?>
        <div class='spacer-md'>
            <div class='row'>
                <div class='col-xs-12 col-sm-6 col-md-5 col-lg-2'>
                    <?php if (iMEMBER && $info['permissions']['can_post'] && !empty($info['new_thread_link'])) : ?>
                        <a class='btn btn-primary' href='<?php echo $info['new_thread_link'] ?>'><i class='m-r-10 fa fa-comment'></i><?php echo $locale['forum_0264'] ?></a>
                    <?php endif; ?>
                </div>
                <div class='col-xs-12 col-sm-6 col-md-7 col-lg-10'>
                    <?php
                    if (isset($_GET['view'])) {
                        switch ($_GET['view']) {
                            default:
                            case 'threads':
                                if ($info['forum_type'] > 1) {
                                    echo "<!--pre_forum-->\n";
                                    // Threads Render
                                    render_forum_threads($info);
                                    ?>
                                    <div class='list-group-item m-t-20'>
                                        <?php echo "
                                    <span>".sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    "; ?>
                                    </div>
                                    <?php if ($info['forum_moderators']) : echo "<div class='list-group-item'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>\n"; endif;
                                }
                                break;
                            case 'subforums':
                                if (!empty($info['item'][$_GET['forum_id']]['child'])) {
                                    echo "<div class='forum-title m-t-20'>".$locale['forum_0351']."</div>\n";
                                    $i = 1;
                                    echo "<div class='list-group-item'>\n";
                                    foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
                                        render_forum_item($subforum_data, $i);
                                        $i++;
                                    }
                                    echo "</div>\n";
                                }
                                break;
                        }
                    } else {
                        if ($info['forum_type'] > 1) {
                            echo "<!--pre_forum-->\n";
                            // Threads Render
                            render_forum_threads($info);
                            ?>
                            <div class='list-group-item m-t-20'>
                                <?php echo "
                                    <span>".sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] == TRUE ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    <span>".sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] == TRUE ? "<strong  class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."</span><br/>
                                    "; ?>
                            </div>
                            <?php if ($info['forum_moderators']) : echo "<div class='list-group-item'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>\n"; endif;
                        } else {

                            //print_p($info);
                            if (!empty($info['item'][$_GET['forum_id']]['child'])) {
                                echo "<div class='forum-title m-t-20'>".$locale['forum_0351']."</div>\n";
                                $i = 1;
                                echo "<div class='list-group-item'>\n";
                                foreach ($info['item'][$_GET['forum_id']]['child'] as $subforum_id => $subforum_data) {
                                    render_forum_item($subforum_data, $i);
                                    $i++;
                                }
                                echo "</div>\n";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php

    }
}

/* Forum Filter */
if (!function_exists('forum_filter')) {
    function forum_filter($info) {
        // Put into core views

        $locale = fusion_get_locale();

        // This one need to push to core.
        $selector = array(
            'today'  => $locale['forum_0212'],
            '2days'  => $locale['forum_p002'],
            '1week'  => $locale['forum_p007'],
            '2week'  => $locale['forum_p014'],
            '1month' => $locale['forum_p030'],
            '2month' => $locale['forum_p060'],
            '3month' => $locale['forum_p090'],
            '6month' => $locale['forum_p180'],
            '1year'  => $locale['forum_3015']
        );

        // This one take out from default filtrations
        // Type $_GET['type']
        $selector2 = array(
            'all'         => $locale['forum_0374'],
            'discussions' => $locale['forum_0222'],
            'attachments' => $locale['forum_0223'],
            'poll'        => $locale['forum_0314'],
            'solved'      => $locale['forum_0378'],
            'unsolved'    => $locale['forum_0379'],
        );

        $selector3 = array(
            'author'  => $locale['forum_0052'],
            'time'    => $locale['forum_0381'],
            'subject' => $locale['forum_0051'],
            'reply'   => $locale['forum_0054'],
            'view'    => $locale['forum_0053'],
        );

        // how to stack it.
        $selector4 = array(
            'descending' => $locale['forum_0230'],
            'ascending'  => $locale['forum_0231']
        );

        if (isset($_GET['tag_id']) && isnum($_GET['tag_id']) || isset($_GET['forum_id']) && isnum($_GET['forum_id'])) {
            ?>
            <div class='clearfix'>
                <div class='pull-left'>
                    <?php echo $locale['forum_0388']; ?>
                    <div class='forum-filter'>
                        <button class='btn btn-xs <?php echo(isset($_GET['time']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                            <?php echo(isset($_GET['time']) && in_array($_GET['time'], array_flip($selector)) ? $selector[$_GET['time']] : $locale['forum_0211']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu'>
                            <?php
                            foreach ($info['filter']['time'] as $filter_locale => $filter_link) {
                                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class='pull-left'>
                    <div class='forum-filter'>
                        <?php echo $locale['forum_0225'] ?>
                        <button class='btn btn-xs <?php echo(isset($_GET['sort']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                            <?php echo(isset($_GET['sort']) && in_array($_GET['sort'], array_flip($selector3)) ? $selector3[$_GET['sort']] : $locale['forum_0381']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right'>
                            <?php
                            foreach ($info['filter']['sort'] as $filter_locale => $filter_link) {
                                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                    <div class='forum-filter'>
                        <button class='btn btn-xs <?php echo(isset($_GET['order']) ? "btn-info" : "btn-default") ?> dropdown-toggle' data-toggle='dropdown'>
                            <?php echo(isset($_GET['order']) && in_array($_GET['order'], array_flip($selector4)) ? $selector4[$_GET['order']] : $locale['forum_0230']) ?>
                            <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-right'>
                            <?php
                            foreach ($info['filter']['order'] as $filter_locale => $filter_link) {
                                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>


            <?php
            /*echo "<div class='forum-filter'>\n";
            echo "<button class='btn btn-xs btn-default dropdown-toggle' data-toggle='dropdown'>".(isset($_GET['type']) && in_array($_GET['type'],
                    array_flip($selector2)) ? $selector2[$_GET['type']] : $locale['forum_0390'])." <span class='caret'></span></button>\n";
            echo "<ul class='dropdown-menu'>\n";
            foreach ($info['filter']['type'] as $filter_locale => $filter_link) {
                echo "<li><a class='text-smaller' href='".$filter_link."'>".$filter_locale."</a></li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
            */
            ?>


            <?php
        }
    }

}

if (!function_exists('render_forum_threads')) {
    function render_forum_threads($info) {

        $locale = fusion_get_locale();
        ?>
        <div class='list-group-item p-t-15 p-b-15'>
            <?php
            if ($info['forum_type'] == 1 && $info['subforum_count']) {
                ?>
                <a href='<?php echo $info['subforum_link']['link'] ?>'><?php echo $info['subforum_link']['title'] ?></a>
                <?php
            }
            if (!empty($info['filters']['type'])) {
                foreach ($info['filters']['type'] as $key => $tabs) {
                    ?>
                    <a href='<?php echo $tabs['link'] ?>' class='m-r-10<?php echo $tabs['active'] ? " text-active" : "" ?> strong'><?php echo $tabs['icon'].$tabs['title'] ?> (<?php echo $tabs['count'] ?>)</a>
                    <?php
                }
            }
            ?>
            <hr/>
            <div class='clearfix'>
                <div class='pull-left'>
                    <?php forum_filter($info); ?>
                </div>
                <div class='pull-right'>
                    <?php
                    if (!empty($info['threads']['pagenav'])) {
                        echo "<div class='text-right'>\n";
                        echo $info['threads']['pagenav'];
                        echo "</div>\n";
                    }
                    ?>
                </div>
            </div>
            <hr/>
            <!---forumthreads-->
            <table class='table table-striped table-responsive clear'>
                <thead>
                <tr>
                    <th>
                        <small><strong>Topic Title</strong></small>
                    </th>
                    <th>
                        <small><strong>Author</strong></small>
                    </th>
                    <th class='no-break'>
                        <small><strong>Latest Activity</strong></small>
                    </th>
                    <th>
                        <small><strong>Views</strong></small>
                    </th>
                    <th>
                        <small><i class='fa fa-comment'></i></small>
                    </th>
                    <th>
                        <small><i class='fa fa-thumbs-o-up'></i></small>
                    </th>
                    <th></th>
                </tr>
                <tbody class='text-smaller'>
                <?php
                if (!empty($info['threads'])) {
                    if (!empty($info['threads']['sticky'])) {
                        foreach ($info['threads']['sticky'] as $cdata) {
                            render_thread_item($cdata);
                        }
                    }
                    if (!empty($info['threads']['item'])) {
                        foreach ($info['threads']['item'] as $cdata) {
                            render_thread_item($cdata);
                        }
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>".$locale['forum_0269']."</td></tr>\n";
                }
                ?>
                </tbody>
                </thead>
            </table>
        </div>
        <?php
        if (!empty($info['threads']['pagenav2'])) {
            echo "<div class='hidden-sm hidden-md hidden-lg m-t-15'>\n";
            echo $info['threads']['pagenav2'];
            echo "</div>\n";
        }

    }
}

/* display threads -- need to simplify */
if (!function_exists('render_thread_item')) {
    function render_thread_item($info) {

        $locale = fusion_get_locale();
        ?>
        <tr id='thread_<?php echo $info['thread_id'] ?>'>
            <td>
                <div class='clearfix'>
                    <div class='pull-left m-r-10'><?php echo $info['thread_last']['avatar'] ?></div>
                    <div class='overflow-hide'>
                        <a class='forum-link' href='<?php echo $info['thread_link']['link'] ?>'><?php echo $info['thread_link']['title'] ?></a>
                        <span class='m-l-10 m-r-10 text-lighter'><?php echo implode('', $info['thread_icons']) ?></span>
                        <?php echo $info['thread_pages']; ?>
                    </div>
                </div>
            </td>
            <td><?php echo $info['thread_starter']['profile_link'] ?></td>
            <td>
                <small><?php echo timer($info['thread_last']['time']) ?></small>
            </td>
            <td><strong><?php echo number_format($info['thread_views']) ?></strong></td>
            <td><strong><?php echo number_format($info['thread_postcount']) ?></strong></td>
            <td><strong><?php echo number_format($info['vote_count']) ?></strong></td>
            <td>
                <?php
                echo isset($info['track_button']) ? "<div class='forum_track'><a onclick=\"return confirm('".$locale['global_060']."');\" href='".$info['track_button']['link']."'>".$info['track_button']['name']."</a>\n</div>\n" : '';
                ?>
            </td>
        </tr>
        <?php

    }
}

if (!function_exists("render_participated")) {
    function render_participated($info) {
        $locale = fusion_get_locale('', FORUM_LOCALE);
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            // sort by date.
            $last_date = '';
            foreach ($info['item'] as $data) {
                $cur_date = date('M d, Y', $data['post_datestamp']);
                if ($cur_date != $last_date) {
                    $last_date = $cur_date;
                    $title = "<div class='post_title m-b-10'>".$locale['forum_0525']." ".$last_date."</div>\n";
                    echo $title;
                }
                render_thread_item($data);
            }
            if ($info['post_rows'] > 20) {
                echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $info['post_rows'], 3, FUSION_REQUEST, "rowstart")."\n</div>\n";
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_054']."</div>\n";
        }
    }
}

if (!function_exists("render_laft")) {
    function render_laft($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
        }
        echo "<hr/>\n";
        echo $info['threads_time_filter'];
    }
}

if (!function_exists("render_tracked")) {
    /* Tracked Section */
    function render_tracked($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                // do a thread.
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_059']."</div>\n";
        }
    }
}

if (!function_exists("render_unanswered")) {
    /* Unanswered Section */
    function render_unanswered($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                // do a thread.
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
        }
    }
}

if (!function_exists("render_unsolved")) {
    /* Unsolved Section */
    function render_unsolved($info) {
        $locale = fusion_get_locale();
        echo render_breadcrumbs();
        if (!empty($info['item'])) {
            $i = 0;
            foreach ($info['item'] as $data) {
                // do a thread.
                render_thread_item($data);
                $i++;
            }
        } else {
            echo "<div class='well text-center'>".$locale['global_023']."</div>\n";
        }
    }
}

/* Custom Modal New Topic */
if (!function_exists('forum_newtopic')) {
    function forum_newtopic() {
        $locale = fusion_get_locale();

        if (isset($_POST['select_forum'])) {
            $_POST['forum_sel'] = isset($_POST['forum_sel']) && isnum($_POST['forum_sel']) ? $_POST['forum_sel'] : 0;
            redirect(FORUM.'post.php?action=newthread&forum_id='.$_POST['forum_sel']);
        }
        echo openmodal('newtopic', $locale['forum_0057'], array('button_id' => 'newtopic', 'class' => 'modal-md'));
        $index = dbquery_tree(DB_FORUMS, 'forum_id', 'forum_cat');
        $result = dbquery("SELECT a.forum_id, a.forum_name, b.forum_name as forum_cat_name, a.forum_post
		 FROM ".DB_FORUMS." a
		 LEFT JOIN ".DB_FORUMS." b ON a.forum_cat=b.forum_id
		 WHERE ".groupaccess('a.forum_access')." ".(multilang_table("FO") ? "AND a.forum_language='".LANGUAGE."' AND" : "AND")."
		 (a.forum_type ='2' or a.forum_type='4') AND a.forum_post < ".USER_LEVEL_PUBLIC." AND a.forum_lock !='1' ORDER BY a.forum_cat ASC, a.forum_branch ASC, a.forum_name ASC");
        $options = array();
        if (dbrows($result) > 0) {
            while ($data = dbarray($result)) {
                $depth = get_depth($index, $data['forum_id']);
                if (checkgroup($data['forum_post'])) {
                    $options[$data['forum_id']] = str_repeat("&#8212;",
                            $depth).$data['forum_name']." ".($data['forum_cat_name'] ? "(".$data['forum_cat_name'].")" : '');
                }
            }

            echo "<div class='well clearfix m-t-10'>\n";
            echo form_select('forum_sel', $locale['forum_0395'], '', array(
                'options' => $options,
                'inline'  => 1,
                'width'   => '100%'
            ));
            echo "<div class='display-inline-block col-xs-12 col-sm-offset-3'>\n";
            echo form_button('select_forum', $locale['forum_0396'], 'select_forum', array('class' => 'btn-primary btn-sm'));
            echo "</div>\n";
            echo "</div>\n";
            echo closeform();
        } else {
            echo "<div class='well text-center'>\n";
            echo $locale['forum_0328'];
            echo "</div>\n";
        }
        echo closemodal();
    }
}

if (!function_exists('render_postify')) {
    function render_postify($info) {
        opentable($info['title']);
        echo "<div class='".($info['error'] ? "alert alert-danger" : "well")." text-center'>\n";
        echo(!empty($info['description']) ? $info['description']."<br/>\n" : "");
        foreach ($info['link'] as $link) {
            echo "<p><a href='".$link['url']."'>".$link['title']."</a></p>\n";
        }
        echo "</div>\n";
        closetable();
    }
}
