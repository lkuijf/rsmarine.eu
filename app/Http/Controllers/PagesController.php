<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Helpers\ApiCall;
use App\Http\Helpers\SimplePagesApi;
use App\Http\Helpers\SimplePostsApi;
use App\Http\Helpers\SimpleMediaApi;
use App\Http\Helpers\SimpleTaxonomiesApi;
use App\Http\Helpers\Menu;
use App\Http\Helpers\PageApi;
use App\Http\Helpers\PostApi;
use App\Http\Helpers\CustomPostApi;
use App\Http\Helpers\WebsiteOptionsApi;
use App\Http\Helpers\WooApiCall;
use App\Http\Helpers\WooCategoriesApi;
// use App\Http\Helpers\WooCategoryApi;
use App\Http\Helpers\WooFilterProductsApi;
use App\Http\Helpers\FilterJobOffersApi;

class PagesController extends Controller
{
    public $allMediaById = array();
    public $allTaxonomiesById;
    public $allPagesPerParent = array();

    public function home() {
        return view('page');
    }

    public function showPage(Request $request, $section, $page, $subpage) {
// echo "<br />\n" . (isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'') . "<br />\n";
        /*** WP Post to trash (also custom posts) referer hack (https://bestflex.wtgroup.nl/?trashed=1&ids=241) ***/
        if($request->query('trashed')) return redirect('_mcfu638b-cms/wp-admin/index.php');
        
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $this->allPagesPerParent = $simplePages->pagesPerParent;
        $allSlugsNested = $simplePages->getAllSlugs();
// dd($allSlugsNested);
        if(!isset($allSlugsNested[$section]) || ($page && !isset($allSlugsNested[$section]['children'][$page])) || ($subpage && !isset($allSlugsNested[$section]['children'][$page]['children'][$subpage]))) {
            return abort(404);
        } else {
            $pageId = $allSlugsNested[$section];
            if($page) $pageId = $allSlugsNested[$section]['children'][$page];
            if($subpage) $pageId = $allSlugsNested[$section]['children'][$page]['children'][$subpage];
            if(is_array($pageId)) $pageId = $pageId['id'];
        }

        $simpleTaxonomies = new SimpleTaxonomiesApi();
        $simpleTaxonomies->get();
        $this->allTaxonomiesById = $simpleTaxonomies->makeListById();

        $content = $this->getContent($pageId);
        $options = $this->getWebsiteOptions();

        if(isset($options['header_image'])) $options['header_image'] = $this->generateImageUrl($options['header_image']);
        
// dd($options);
        // $cartTotalItems = ShopController::getTotalCartItems();
        // $loggedInUserId = ShopController::getLoggedinUser();
// dd($content->contentSections);
        $data= [
            'head_title' => $content->pageTitle,
            'meta_description' => $content->pageMetaDescription,
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            // 'cart_total' => $cartTotalItems,
            // 'user_logged_in' => $loggedInUserId,
            'content_sections' => $content->contentSections,
        ];
        if($section == 'contact')
            return view('contact-page')->with('data', $data);
        if($section == 'vacatures')
            return view('vacatures-page')->with('data', $data);
        if($section == 'interviews') {
            $interviews = new CustomPostApi('interview');
            $allIns = $interviews->get();
            $allInterviews = $allIns;
            foreach($allInterviews as $i => $inter) {
                $url = $this->generateImageUrl($inter->image);
                $alt = $this->generateImageAlt($inter->image);
                $allInterviews[$i]->image = array('url' => $url, 'alt' => $alt);
            }
            $data['interviews'] = $allInterviews;
            // dd($allInterviews);
            return view('interviews-page')->with('data', $data);
        } else if($section == 'producten') {
            $mainCats = $this->getMainMenuItems();
            $data['shop_main_cats'] = $mainCats;
            return view('shop-root-category')->with('data', $data);
        } else if($section == 'afspraak-maken') {
            return view('bookly-page')->with('data', $data);
        } else
        return view('standard-page')->with('data', $data);
    }
    // public function getInterviews() {
    //     $interviews = new CustomPostApi('interview');
    // }
    public function showVacature($slug, $apply) {
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = $this->getWebsiteOptions();
        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $this->allMediaById = $simpleMedia->makeListById();
        $simpleTaxonomies = new SimpleTaxonomiesApi();
        $simpleTaxonomies->get();
        $this->allTaxonomiesById = $simpleTaxonomies->makeListById();
// dd($this->allTaxonomiesById);
        $jobOffer = new CustomPostApi('job_offer', false, $slug);
        $jo = $jobOffer->get();
        if(!count($jo)) return abort(404);
// dd($jo);

        $otherJobs = new FilterJobOffersApi();
        $otherJobs->parameters = array();
        // $otherJobs->parameters['location'] = 'Dordrecht';

        $sidebarJobs = $otherJobs->get();

        foreach($sidebarJobs as $k => $job) {
            if($job->image) {
                $url = $this->generateImageUrl($job->image);
                $alt = $this->generateImageAlt($job->image);
                $sidebarJobs[$k]->image = [];
                $sidebarJobs[$k]->image['url'] = $url;
                $sidebarJobs[$k]->image['alt'] = $alt;
            }
        }
// dd($sidebarJobs);
        $randomKeys = array_rand($sidebarJobs, 4);
        $sideJobsToShow = array();
        for($x=0;$x<count($randomKeys);$x++) $sideJobsToShow[] = $sidebarJobs[$randomKeys[$x]];
// dd($sideJobsToShow);
        $data= [
            'head_title' => $jo[0]->title->rendered . ' - Solliciteer voor deze job - Best Flex',
            'meta_description' => $jo[0]->intro,
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'apply_for_job' => $apply,
            'job_offer_title' => $jo[0]->title->rendered,
            'job_offer_content' => $jo[0]->text,
            'job_offer_img_url' => ($jo[0]->image?$this->generateImageUrl((int)$jo[0]->image):''),
            'job_offer_img_alt' => ($jo[0]->image?$this->generateImageAlt((int)$jo[0]->image):''),
            'job_offer_job_cat' => $this->getTerms($jo[0]->job_cat),
            'job_offer_uren_per_week' => $this->getTerms($jo[0]->uren_per_week),
            'job_offer_type_job' => $this->getTerms($jo[0]->type_job),
            'job_offer_locatie' => $this->getTerms($jo[0]->locatie),
            'other_jobs' => $sideJobsToShow,
        ];
// dd($data);
        return view('vacature-detail-page')->with('data', $data);
    }
    public function showInterview($slug) {
        $simplePages = new SimplePagesApi();
        $htmlMenu = new Menu($simplePages->get());
        $htmlMenu->generateUlMenu();
        $options = $this->getWebsiteOptions();
        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $this->allMediaById = $simpleMedia->makeListById();
        // $simpleTaxonomies = new SimpleTaxonomiesApi();
        // $simpleTaxonomies->get();
        // $this->allTaxonomiesById = $simpleTaxonomies->makeListById();

        // $jobOffer = new CustomPostApi('job_offer', false, $slug);
        $interview = new CustomPostApi('interview', false, $slug);


        $in = $interview->get();
        if(!count($in)) return abort(404);
// dd($in);

        $data= [
            'head_title' => 'Interview: ' . html_entity_decode($in[0]->title->rendered) . ' - Best Flex',
            'meta_description' => $in[0]->intro,
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'interview_title' => $in[0]->title->rendered,
            'interview_text' => $in[0]->text,
            // 'job_offer_content' => $jo[0]->text,
            // 'job_offer_img_url' => ($jo[0]->image?$this->generateImageUrl((int)$jo[0]->image):''),
            // 'job_offer_img_alt' => ($jo[0]->image?$this->generateImageAlt((int)$jo[0]->image):''),
            // 'job_offer_job_cat' => $this->getTerms($jo[0]->job_cat),
            // 'job_offer_uren_per_week' => $this->getTerms($jo[0]->uren_per_week),
            // 'job_offer_type_job' => $this->getTerms($jo[0]->type_job),
            // 'job_offer_locatie' => $this->getTerms($jo[0]->locatie),
        ];
// dd($data);
        return view('interview-detail-page')->with('data', $data);
    }
    public function getTerms($termIds) {
        $aRes = array();
        foreach($termIds as $id) {
            $aRes[] = $this->allTaxonomiesById->$id;
        }
        return $aRes;
    }
    public function getContent($id) {
        $res = new \stdClass();
        $metaDesc = '';
        $hTitle = '';
        $sections = [];
        $reqPage = new PageApi($id);
        $pageData = $reqPage->get();
// dd($pageData);
        foreach($pageData->head_tags as $htag) {
            if(isset($htag->attributes->name) && $htag->attributes->name == 'description') $metaDesc = $htag->attributes->content;
        }
        if($pageData->title->rendered == '[HOMEPAGE]') $hTitle = 'RS Marine Shipmanagement Ltd - Connecting seafarers, vessel and Shipowner';
        else $hTitle = $pageData->title->rendered . ' - RS Marine';

        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $this->allMediaById = $simpleMedia->makeListById();
        if($pageData->content->rendered) {
            $s = [];
            $s['type'] = 'text';
            $s['text'] = $pageData->content->rendered;
            $s['color'] = '';
            $s['orientation'] = 'text_left';
            $s['gallery'] = [];
            $sections[] = $s;
        }
// dd($pageData,$this->allMediaById);
// dd($pageData->crb_sections);
        if(isset($pageData->crb_sections) && count($pageData->crb_sections)) {
            $loadWoo_once = true;
            foreach($pageData->crb_sections as $sec) {
                $s = [];
                $s['type'] = $sec->_type;
                // if($sec->_type == 'hero') {
                //     $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $sec->image);
                //     $s['img']['url'] = $img;
                //     $s['img']['alt'] = str_replace(['-', '_'], ' ', pathinfo($img, PATHINFO_FILENAME));
                // }


                if($sec->_type == '1column') {
                    if(isset($sec->fullwidth) && count($sec->fullwidth)) {
                        $s['1column'] = array();
                        foreach($sec->fullwidth as $fullWidthItem) {
                            if($fullWidthItem->_type == 'afbeelding') {
                                $fullWidthItem->img = $this->generateImageUrl($fullWidthItem->image);
                                $fullWidthItem->alt = $this->generateImageAlt($fullWidthItem->image);
                                unset($fullWidthItem->image);
                            }
                            if($fullWidthItem->_type == 'bestand') {
                                $fullWidthItem->file = $this->generateImageUrl($fullWidthItem->file);
                            }

                            if($fullWidthItem->_type == 'nieuws-items') {
                                $aValuesToRetreive = array('title', 'site_title', 'news_url', 'text', 'image');
                                if(isset($fullWidthItem->news_associations) && count($fullWidthItem->news_associations)) {
                                    foreach($fullWidthItem->news_associations as $k => $newsItem) {
                                        $oCustPostType = $this->getCustomPostTypeViaRestApi($newsItem->subtype, $newsItem->id, $aValuesToRetreive);
                                        if($oCustPostType->image) $oCustPostType->image = $this->getMediaGallery(array($oCustPostType->image));
                                        $fullWidthItem->news_associations[$k] = $oCustPostType;
                                    }
                                }
                            }

                            $s['1column'][] =  $fullWidthItem;
                        }
                    }
                }
                if($sec->_type == '2column') {
                    $s['2column']['left'] = array();
                    $s['2column']['right'] = array();
                    if(isset($sec->left) && count($sec->left)) {
                        foreach($sec->left as $leftItem) {
                            if($leftItem->_type == 'afbeelding') {
                                $leftItem->img = $this->generateImageUrl($leftItem->image);
                                $leftItem->alt = $this->generateImageAlt($leftItem->image);
                                unset($leftItem->image);
                            }
                            if($leftItem->_type == 'bestand') {
                                $leftItem->file = $this->generateImageUrl($leftItem->file);
                            }
                            if($leftItem->_type == 'nieuws-items') {
                                $aValuesToRetreive = array('title', 'site_title', 'news_url', 'text', 'image');
                                if(isset($leftItem->news_associations) && count($leftItem->news_associations)) {
                                    foreach($leftItem->news_associations as $k => $newsItem) {
                                        $oCustPostType = $this->getCustomPostTypeViaRestApi($newsItem->subtype, $newsItem->id, $aValuesToRetreive);
                                        if($oCustPostType->image) $oCustPostType->image = $this->getMediaGallery(array($oCustPostType->image));
                                        $leftItem->news_associations[$k] = $oCustPostType;
                                    }
                                }
                            }
                            $s['2column']['left'][] = $leftItem;
                        }
                    }
                    if(isset($sec->right) && count($sec->right)) {
                        foreach($sec->right as $rightItem) {
                            if($rightItem->_type == 'afbeelding') {
                                $rightItem->img = $this->generateImageUrl($rightItem->image);
                                $rightItem->alt = $this->generateImageAlt($rightItem->image);
                                unset($rightItem->image);
                            }
                            if($rightItem->_type == 'bestand') {
                                $rightItem->file = $this->generateImageUrl($rightItem->file);
                            }
                            if($rightItem->_type == 'nieuws-items') {
                                $aValuesToRetreive = array('title', 'site_title', 'news_url', 'text', 'image');
                                if(isset($rightItem->news_associations) && count($rightItem->news_associations)) {
                                    foreach($rightItem->news_associations as $k => $newsItem) {
                                        $oCustPostType = $this->getCustomPostTypeViaRestApi($newsItem->subtype, $newsItem->id, $aValuesToRetreive);
                                        if($oCustPostType->image) $oCustPostType->image = $this->getMediaGallery(array($oCustPostType->image));
                                        $rightItem->news_associations[$k] = $oCustPostType;
                                    }
                                }
                            }
                            $s['2column']['right'][] = $rightItem;
                        }
                    }
                }
                if($sec->_type == 'banner') {
                    // $s['wl_header'] = $sec->writing_letters_header;
                    // $s['bl_header'] = $sec->block_letters_header;
                    $s['text_align'] = $sec->text_align;
                    $s['text_color'] = $sec->text_color;
                    $s['image_opacity'] = $sec->image_opacity;
                    // $img = str_replace(array('http://', '_mcfu638b-cms/wp-content/uploads'), array('https://', 'media'), $sec->image);
                    $s['img']['url'] = $this->generateImageUrl($sec->image);
                    $s['img']['alt'] = $this->generateImageAlt($sec->image);
                    $s['checked'] = $sec->extra_padding;
                    $s['text'] = $sec->text;
                    $s['links'] = [];
                    if(isset($sec->links) && count($sec->links)) {
                        foreach($sec->links as $bnrLink) {
                            $lnk = [];
                            $lnk['text'] = $bnrLink->button_text;
                            $lnk['color'] = $bnrLink->button_color;
                            $lnk['cust_link'] = $bnrLink->custom_link;
                            $lnk['url'] = '';
                            if(isset($bnrLink->links) && count($bnrLink->links)) {
                                foreach($this->allPagesPerParent[0] as $rootPage) {
                                    if($rootPage->id == $bnrLink->links[0]->id) {
                                        $lnk['slug'] = $rootPage->slug;
                                    }
                                }
                            }
                            $s['links'][] = $lnk;
                        }
                    }
                }
                if($sec->_type == 'text_flex') {
                    $s['hdr'] = $sec->header;
                    $s['text_l'] = $sec->text_left;
                    $s['text_r'] = $sec->text_right;
                    $s['background_color'] = $sec->background_color;
                    $s['stretch'] = $sec->stretch;
                    $s['links_l'] = [];
                    $s['links_r'] = [];
                    if(isset($sec->links_left) && count($sec->links_left)) {
                        foreach($sec->links_left as $bnrLink) {
                            $lnk = [];
                            $lnk['text'] = $bnrLink->button_text;
                            $lnk['color'] = $bnrLink->button_color;
                            $lnk['cust_link'] = $bnrLink->custom_link;
                            $lnk['url'] = '';
                            if(isset($bnrLink->links) && count($bnrLink->links)) {
                                foreach($this->allPagesPerParent[0] as $rootPage) {
                                    if($rootPage->id == $bnrLink->links[0]->id) {
                                        $lnk['slug'] = $rootPage->slug;
                                    }
                                }
                            }
                            $s['links_l'][] = $lnk;
                        }
                    }
                    if(isset($sec->links_right) && count($sec->links_right)) {
                        foreach($sec->links_right as $bnrLink) {
                            $lnk = [];
                            $lnk['text'] = $bnrLink->button_text;
                            $lnk['color'] = $bnrLink->button_color;
                            $lnk['cust_link'] = $bnrLink->custom_link;
                            $lnk['url'] = '';
                            if(isset($bnrLink->links) && count($bnrLink->links)) {
                                foreach($this->allPagesPerParent[0] as $rootPage) {
                                    if($rootPage->id == $bnrLink->links[0]->id) {
                                        $lnk['slug'] = $rootPage->slug;
                                    }
                                }
                            }
                            $s['links_r'][] = $lnk;
                        }
                    }
                }
                if($sec->_type == 'text_grid') {
                    $s['grid_items'] = [];
                    foreach($sec->crb_media_item as $crbItem) {
                        if(isset($crbItem->image) && $crbItem->image) {
                            $crbItem->image_url = $this->generateImageUrl($crbItem->image);
                            $crbItem->image_alt = $this->generateImageAlt($crbItem->image);
                            unset($crbItem->image);
                        }
                        $s['grid_items'][] = $crbItem;
                    }
                }
                if($sec->_type == 'info_icons') {
                    $s['info_icons'] = [];
                    foreach($sec->info_icons as $iItem) {
                        if(isset($iItem->image) && $iItem->image) {
                            $iItem->image_url = $this->generateImageUrl($iItem->image);
                            $iItem->image_alt = $this->generateImageAlt($iItem->image);
                            unset($iItem->image);
                        }
                        $s['info_icons'][] = $iItem;
                    }
                }
                if($sec->_type == 'testimonials') {
                    $s['testimonials'] = $sec->testimonials;
                }
                if($sec->_type == 'colleagues') {
                    $s['people'] = array();
                    $aValuesToRetreive = array('title', 'function', 'text', 'image');
                    foreach($sec->colleague_associations as $personAssoc) {
                        $oCustPostType = $this->getCustomPostTypeViaRestApi($personAssoc->subtype, $personAssoc->id, $aValuesToRetreive);
                        if($oCustPostType->image) $oCustPostType->image = $this->getMediaGallery(array($oCustPostType->image));
                        $s['people'][] = $oCustPostType;
                    }
                }
                if($sec->_type == 'joboffers') {
                    $s['jobOffers'] = array();
                    $aValuesToRetreive = array('title', 'slug', 'intro', 'job_cat', 'uren_per_week', 'type_job', 'locatie', 'image');
                    foreach($sec->job_offer_associations3 as $jobOfferAssoc) {
                        $oCustPostType = $this->getCustomPostTypeViaRestApi($jobOfferAssoc->subtype, $jobOfferAssoc->id, $aValuesToRetreive);
                        if($oCustPostType->image) {
                            $aI = array();
                            $aI['url'] = $this->generateImageUrl($oCustPostType->image);
                            $aI['alt'] = $this->generateImageAlt($oCustPostType->image);
                            $oCustPostType->image = $aI;
                        }

                        if($oCustPostType->job_cat) $oCustPostType->job_cat = $this->getTerms($oCustPostType->job_cat);
                        if($oCustPostType->uren_per_week) $oCustPostType->uren_per_week = $this->getTerms($oCustPostType->uren_per_week);
                        if($oCustPostType->type_job) $oCustPostType->type_job = $this->getTerms($oCustPostType->type_job);
                        if($oCustPostType->locatie) $oCustPostType->locatie = $this->getTerms($oCustPostType->locatie);
                        
                        $oCustPostType->taxonomyTerms = new \stdClass();
                        $oCustPostType->taxonomyTerms->job_cat = $oCustPostType->job_cat;
                        $oCustPostType->taxonomyTerms->uren_per_week = $oCustPostType->uren_per_week;
                        $oCustPostType->taxonomyTerms->type_job = $oCustPostType->type_job;
                        $oCustPostType->taxonomyTerms->locatie = $oCustPostType->locatie;
                        unset($oCustPostType->job_cat);
                        unset($oCustPostType->uren_per_week);
                        unset($oCustPostType->type_job);
                        unset($oCustPostType->locatie);

                        // if($oCustPostType->slug) {
                        //     $oCustPostType->url = '/vacatures/' . $oCustPostType->slug;
                        //     unset($oCustPostType->slug);
                        // }
                        
                        $s['jobOffers'][] = $oCustPostType;
                    }
                }
                if($sec->_type == 'text') {
                    // $s['color'] = $sec->color;
                    // $s['orientation'] = 'text_left';
                    // if(isset($sec->orientation)) $s['orientation'] = $sec->orientation;
                    // $s['wl_header'] = $sec->writing_letters_header;
                    // $s['bl_header'] = $sec->block_letters_header;
                    // $s['margin'] = $sec->margin;
                    // $s['valign_center'] = $sec->vertical_align_center;
                    // $s['orientation'] = $sec->orientation;
                    // $s['background_color'] = $sec->background_color;
                    $s['text'] = $sec->text;
                    // $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $sec->image);
                    // $s['img']['url'] = $img;
                    // $s['img']['alt'] = str_replace(['-', '_'], ' ', pathinfo($img, PATHINFO_FILENAME));
                    // $s['gallery'] = [];
                    // if(isset($sec->crb_media_gallery) && count($sec->crb_media_gallery)) {
                    //     $s['gallery'] = $this->getMediaGallery($sec->crb_media_gallery);
                    //     // foreach($sec->crb_media_gallery as $mediaId) {
                    //     //     $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $this->allMediaById[$mediaId]->url);
                    //     //     $alt = str_replace(['-', '_'], ' ', pathinfo($img, PATHINFO_FILENAME));
                    //     //     if($this->allMediaById[$mediaId]->alt) $alt = $this->allMediaById[$mediaId]->alt;
                    //     //     $i['img'] = $img;
                    //     //     $i['alt'] = $alt;
                    //     //     $s['gallery'][] = $i;
                    //     // }
                    // }
                }
                if($sec->_type == 'information_blocks_holder') {
                    $s['blocks'] = $sec->information_blocks;
                    foreach($s['blocks'] as $i => $block) {
                        if($block->image) {
                            $s['blocks'][$i]->image = $this->getMediaGallery(array($block->image));
// dd($block->image);
                            // $im = parse_url($block->image);
                            // $arr = array();
                            // $arr['img'] = $im['path'];
                            // $arr['alt'] = 'altje';
                            // $s['blocks'][$i]->image = $arr;

                            // $s['blocks'][$i]->image['img'] = $block->image;
                            // $s['blocks'][$i]->image['alt'] = 'altje';
                        }
                        if($block->crb_association) {
                            // dd($block->crb_association);
                            // dd($this->allPagesPerParent);
                            // $assocPage = new PageApi($block->crb_association);
                            foreach($this->allPagesPerParent[0] as $rootPage) {
                                // dd($rootPage->id);
                                // dd($block->crb_association->id);
                                if($rootPage->id == $block->crb_association[0]->id) {
                                    $block->crb_association[0]->slug = $rootPage->slug;
                                }
                            }
                        }
                    }
                }
                // if($sec->_type == 'people_holder') {
                //     $s['blocks'] = $sec->people_blocks;
                //     foreach($s['blocks'] as $i => $block) {
                //         if($block->image) {
                //             $s['blocks'][$i]->image = $this->getMediaGallery(array($block->image));
                //         }
                //     }
                // }
                // if($sec->_type == 'person_wraps') {
                //     $s['people'] = array();
                //     $aValuesToRetreive = array('title', 'board_role', 'board_email', 'board_phone', 'image');
                //     foreach($sec->people_associations as $personAssoc) {
                //         $oCustPostType = $this->getCustomPostTypeViaRestApi($personAssoc->subtype, $personAssoc->id, $aValuesToRetreive);
                //         if($oCustPostType->image) $oCustPostType->image = $this->getMediaGallery(array($oCustPostType->image));
                //         $s['people'][] = $oCustPostType;
                //     }
                // }
                
                // if($sec->_type == 'solutions') {
                //     $s['icon_boxes'] = [];
                //     if(isset($sec->icon_boxes) && count($sec->icon_boxes)) {
                //         foreach($sec->icon_boxes as $box) {
                //             $b['icon'] = $box->icon;
                //             $b['text'] = $box->text;
                //             $s['icon_boxes'][] = $b;
                //         }
                //     }
                // }
                // if($sec->_type == 'activities') {
                //     $s['fields'] = [];
                //     if(isset($sec->activity_fields) && count($sec->activity_fields)) {
                //         foreach($sec->activity_fields as $field) {
                //             $s['fields'][] = $field->text;
                //         }
                //     }
                // }
                // if($sec->_type == 'services') {
                //     $s['background'] = $sec->background;
                //     $s['icon_boxes'] = [];
                //     if(isset($sec->icon_boxes) && count($sec->icon_boxes)) {
                //         foreach($sec->icon_boxes as $box) {
                //             $b['icon'] = $box->icon;
                //             $b['image']['url'] = '';
                //             $b['image']['alt'] = '';
                //             if($box->image) $b['image']['url'] = $this->generateImageUrl($box->image);
                //             if($box->image) $b['image']['alt'] = $this->generateImageAlt($box->image);
                //             $b['text'] = $box->text;
                //             $s['icon_boxes'][] = $b;
                //         }
                //     }
                // }
                // if($sec->_type == 'featured_products') {
                //     $s['fProducts'] = [];
                //     $s['fCatTitle'] = '';
                //     $s['fCatSlug'] = '';
                //     if(isset($sec->crb_association) && count($sec->crb_association)) {
                //         if($loadWoo_once) {
                //             $wooProducts = new WooFilterProductsApi();
                //             $wooProducts->setHttpBasicAuth();
                //             $wooProducts->parameters['crb[is_featured]'] = 'yes';
                //             $wooProducts->parameters['per_page'] = 99;
                //             $allFeaturedProducts = $wooProducts->get();

                //             $wooCategories = new WooCategoriesApi();
                //             $wooCategories->setHttpBasicAuth();
                //             $wooCategories->get();
                //             $wooCategories->setCategoriesPerParent();
                //             $wooCategories->getAllCatsById();
                            
                //             $loadWoo_once = false;
                //         }

                //         foreach($sec->crb_association as $ass) {
                //             $s['fCatTitle'] = $wooCategories->categoriesById[$ass->id]['name'];
                //             $s['fCatUrl'] = array_key_last($wooCategories->getBreadCrumbUrls($ass->id));
                //             foreach($allFeaturedProducts as $fp) {
                //                 if(in_array($ass->id, $fp->categories) || in_array($ass->id, $fp->ancestors)) {
                //                     $prod = [];
                //                     $prod['id'] = $fp->id;
                //                     $prod['title'] = $fp->name;
                //                     $prod['slug'] = $fp->slug;
                //                     $prod['image'] = ($fp->images && $fp->images[0]?$fp->images[0]:'');
                //                     $prod['price'] = $fp->price;
                //                     $s['fProducts'][] = $prod;
                //                 }
                //             }
                //         }
                //     }
                // }
                // if($sec->_type == 'contact_form') {
                //     $s['checked'] = $sec->show_contact_form;
                //     session(['wt_previous_url' => url()->current()]);  // Using custom session variable, because: URL::previous / url()->previous() uses the header information stored within referrer, the referrer header is not always filled, so can be empty
                // }
                // if($sec->_type == 'cta_afspraak_maken') {
                //     $s['checked'] = $sec->show_afspraak_maken;
                // }
                // if($sec->_type == 'media_picture_gallery') {
                //     $s['gallery'] = [];
                //     if(isset($sec->crb_media_gallery) && count($sec->crb_media_gallery)) {
                //         $s['gallery'] = $this->getMediaGallery($sec->crb_media_gallery);
                //     }
                // }
                // if($sec->_type == 'team_members') {
                //     $s['members'] = $sec->t_members;
                //     foreach($s['members'] as $i => $member) {
                //         if($member->image) {
                //             $s['members'][$i]->image = $this->getMediaGallery(array($member->image));
                //         }
                //     }
                // }
                // if($sec->_type == 'advantages_and_testimonials') {
                //     $s['advantages'] = $sec->advantages;
                    
                //     foreach($sec->testimonials as $i => $tes) {
                //         $imgUrl = '';
                //         $imgAlt = '';
                //         if($tes->image) $imgUrl = $this->generateImageUrl($tes->image);
                //         if($tes->image) $imgAlt = $this->generateImageAlt($tes->image);
                //         $sec->testimonials[$i]->image = new \stdClass();
                //         $sec->testimonials[$i]->image->url = $imgUrl;
                //         $sec->testimonials[$i]->image->alt = $imgAlt;
                //     }
                //     $s['testimonials'] = $sec->testimonials;
                // }
                $sections[] = $s;
            }
        }
// dd($sections);
        $res->pageMetaDescription = $metaDesc;
        $res->pageTitle = $hTitle;
        $res->contentSections = $sections;
        return $res;
    }
    public function getCustomPostTypeViaRestApi($customPostType, $id, $valsToReturn) {
        $res = new \stdClass();
        $call = new ApiCall();
        $call->endpoint = '/index.php/wp-json/wp/v2/' . $customPostType . '/' . $id;
        $oReturned = $call->get();
        foreach($valsToReturn as $val) {
            if($val == 'title') $res->{$val} = $oReturned->{$val}->rendered;
            else $res->{$val} = $oReturned->{$val};
        }
        return $res;
    }
    public function getMediaGallery($gall) {
        $res = [];
        foreach($gall as $mediaId) {
            $url = $this->generateImageUrl($mediaId);
            $alt = $this->generateImageAlt($mediaId);
            if(isset($this->allMediaById[$mediaId]) && isset($this->allMediaById[$mediaId]->alt)) $alt = $this->allMediaById[$mediaId]->alt;
            $i['img'] = $url;
            $i['alt'] = $alt;
            $res[] = $i;
        }
        return $res;
    }
    public function generateImageUrl($mediaId) {
        if(isset($this->allMediaById[$mediaId]))
            return str_replace(array('http://', '_mcfu638b-cms/wp-content/uploads'), array('https://', 'media'), $this->allMediaById[$mediaId]->url);
        else
            return 'https://via.placeholder.com/800x600?text=Geen+afbeelding+gevonden';
    }
    public function generateImageAlt($mediaId) {
        if(isset($this->allMediaById[$mediaId]))
            return str_replace(['-', '_'], ' ', pathinfo($this->allMediaById[$mediaId]->url, PATHINFO_FILENAME));
        else
            return 'Placeholder image';
    }
    public static function getWebsiteOptions() {
        $allWebsiteOptions = new WebsiteOptionsApi();
        $websiteOptions = $allWebsiteOptions->get();
        return (array)$websiteOptions;
    }
    public function getMainMenuItems() {
        $cats = array();
        $wooCats = new WooCategoriesApi();
        $wooCats->setHttpBasicAuth();
        $wooCats->get();
        $wooCats->setCategoriesPerParent();
        foreach($wooCats->res[0] as $rootCats) {
            if($rootCats->slug == 'uncategorized') continue;
            $cats[$rootCats->slug] = $rootCats->name;
        }
        return $cats;
    }
    public function showOnePager($orderId = false) {
        $simplePages = new SimplePagesApi();
        $spages = $simplePages->get();
        $htmlMenu = new Menu($spages);
        $htmlMenu->generateUlMenu();
        $options = $this->getWebsiteOptions();

        $simpleMedia = new SimpleMediaApi();
        $simpleMedia->get();
        $this->allMediaById = $simpleMedia->makeListById();

        $allCrbSections = array();
        foreach($spages[0] as $sPage) {
            $pageA = [];
            $pageA['type'] = '_anchor';
            $pageA['value'] = $sPage->slug;
            $allCrbSections[] = $pageA;

            /* check rotterdamsehorecawandeling.nl for details */
            
            $crbSecs = $this->getPageCrbSections($sPage->id);
            $allCrbSections = array_merge($allCrbSections, $crbSecs);
        }
        // dd($allCrbSections);
        $data= [
            'html_menu' => $htmlMenu->html,
            'website_options' => $options,
            'content_sections' => $allCrbSections,
        ];
        return view('page')->with('data', $data);
    }
    public function getPageCrbSections($id) {
        $reqPage = new PageApi($id);
        $pageData = $reqPage->get();
        $allSections = [];
        if(isset($pageData->crb_sections) && count($pageData->crb_sections)) {
            $sections = $this->handleCrbSections($pageData->crb_sections);
            $allSections = array_merge($allSections, $sections); 
        }
        return $allSections;
    }
    public function handleCrbSections($pCrbSecs) {
        // dd($pCrbSecs);
        $secs = [];
        foreach($pCrbSecs as $sec) {
            $s = [];
            $s['type'] = $sec->_type;
            if($sec->_type == 'text') {
                // $s['color'] = $sec->color;
                $s['text'] = $sec->text;
                // $s['gallery'] = [];
                // if(isset($sec->crb_media_gallery) && count($sec->crb_media_gallery)) {
                //     foreach($sec->crb_media_gallery as $mediaId) {
                //         $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $allMediaById[$mediaId]->url);
                //         $alt = str_replace(['-', '_'], ' ', pathinfo($img, PATHINFO_FILENAME));
                //         if($allMediaById[$mediaId]->alt) $alt = $allMediaById[$mediaId]->alt;
                //         $i['img'] = $img;
                //         $i['alt'] = $alt;
                //         $s['gallery'][] = $i;
                //     }
                // }
            }
            if($sec->_type == 'reserve_form') {
                $s['checked'] = $sec->show_reserve_form;
            }
            if($sec->_type == 'order_form') {
                $s['checked'] = $sec->crb_show_order_form;
            }
            if($sec->_type == 'banner') {
                // dd($sec);
                $img = $this->getMediaGallery(array($sec->image));
                
                // $img = str_replace('_mcfu638b-cms/wp-content/uploads', 'media', $sec->image);
                $s['image'] = $img;
                // dd($s);
            }
            $secs[] = $s;
        }
        // dd($secs);
        return $secs;
    }
}
