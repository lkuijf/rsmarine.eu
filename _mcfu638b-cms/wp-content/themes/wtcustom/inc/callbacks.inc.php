<?php
function getTaxonomiesSimplified(WP_REST_Request $request) {
    $taxonomies = get_taxonomies([
        'public'   => true,
        '_builtin' => false
    ], 'obejcts');
    $taxonomies = (object)$taxonomies;
    $taxAndTerms = new \stdClass();
    foreach($taxonomies as $k => $taxonomy) {
        $taxAndTerms->{$k}['label'] = $taxonomy->label;
        $terms = get_terms(array(
                'taxonomy' => $k,
                'hide_empty' => false,
            ));
        $termList = new \stdClass();
        foreach($terms as $term) $termList->{$term->term_id} = $term->name;
        $taxAndTerms->{$k}['terms'] = $termList;
    }
    $response = new WP_REST_Response($taxAndTerms);
    $response->set_status(200);
    return $response;
}
function getMediaSimplified(WP_REST_Request $request) {
    $media = get_posts([
        'numberposts' => -1,
        'post_type' => 'attachment',
    ]);
    $aRes = [];
    foreach ($media as $item) {
        $oP = new stdClass();
        $oP->id = $item->ID;
        $oP->url = $item->guid;
        $topic = '';
        $alt = '';
        if(isset(get_post_meta($item->ID, 'attach_to_topic')[0])) $topic = get_post_meta($item->ID, 'attach_to_topic')[0];
        if(isset(get_post_meta($item->ID, '_wp_attachment_image_alt')[0])) $alt = get_post_meta($item->ID, '_wp_attachment_image_alt')[0];
        $oP->topic = $topic;
        $oP->alt = $alt;
        $aRes[] = $oP;
    }
    $response = new WP_REST_Response($aRes);
    $response->set_status(200);
    return $response;
}
function getPagesSimplified(WP_REST_Request $request) {
    $pages = get_pages();
    $aRes = getPagesCollectionAttrs($pages);
    $response = new WP_REST_Response($aRes);
    $response->set_status(200);
    return $response;
}
function getPostsSimplified(WP_REST_Request $request) {
    $parameters = $request->get_params();
    $orderby = 'date';
    $order = 'DESC';
    if (isset($parameters['orderby'])) {
        $orderby = $parameters['orderby'];
    }
    if (isset($parameters['order'])) {
        $order = $parameters['order'];
    }
    $posts = get_posts([
        'numberposts' => -1,
        'orderby' => $orderby,
        'order' => $order,
        'post_type' => 'job_offer',
    ]);
    $aRes = getPostsCollectionAttrs($posts);
    $response = new WP_REST_Response($aRes);
    $response->set_status(200);
    return $response;
}

function getWebsiteOptions() {
    global $carbonFieldsArgs; // using global. Importing does not work: https://stackoverflow.com/questions/11086773/php-function-use-variable-from-outside
    $aOptions = array();
    foreach($carbonFieldsArgs['websiteOptions'] as $opt) {
        $aOptions[$opt[1]] = carbon_get_theme_option($opt[1]);
    }
    $response = new WP_REST_Response($aOptions);
    $response->set_status(200);
    return $response;
}
function getHeadContent() {
  $res = do_action( 'wp_head' );
  $response = new WP_REST_Response($res);
  $response->set_status(200);
  return $response;
}
function getPagesCollectionAttrs($coll) {
    $aRes = [];
    foreach ($coll as $item) {
        $oP = new stdClass();
        $oP->id = $item->ID;
        $oP->title = $item->post_title;
        $oP->slug = $item->post_name;
        $oP->parent = $item->post_parent;
        $oP->order = $item->menu_order;
        $oP->status = $item->post_status;
        $oP->date = $item->post_date;
        $aRes[] = $oP;
    }
    return $aRes;
}
function getPostsCollectionAttrs($coll) {
    $aRes = [];
    foreach ($coll as $item) {
        $oP = new stdClass();

        $tags = get_the_tags($item->ID);
        $aTags = array();
        if($tags) {
            foreach ($tags as $oTag) {
                $aTags[$oTag->slug] = $oTag->name;
            }
        }

        $groups = get_post_meta($item->ID, 'esplendor_group');
        $group = false;
        if($groups) {
            $group = $groups[0];
        }

        $metaTopics = get_post_meta($item->ID, 'topics');
        $topics = array();
        if($metaTopics && count(array_filter($metaTopics))) {
            $topics = $metaTopics[0];
        }

        $oP->id = $item->ID;
        $oP->title = $item->post_title;
        $oP->slug = $item->post_name;
        $oP->parent = $item->post_parent;
        $oP->order = $item->menu_order;
        $oP->status = $item->post_status;
        $oP->date = $item->post_date;
        $oP->category = get_the_category($item->ID)[0]->name;
        $oP->tags = $aTags;
        $oP->esplendor_group = $group;
        $oP->topics = $topics;
        $aRes[] = $oP;
    }
    return $aRes;
}
