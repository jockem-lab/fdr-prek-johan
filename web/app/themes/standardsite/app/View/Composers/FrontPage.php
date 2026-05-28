<?php

namespace App\View\Composers;

class FrontPage extends PrekComposer
{
    protected static $views = [
        'front-page',
    ];

    public function with()
    {
        $listings = [];

        $query = new \WP_Query([
            'post_type'      => 'fasad_listing',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'orderby'        => 'meta_value',
            'meta_key'       => '_fasad_firstPublished',
            'order'          => 'DESC',
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_fasad_sold',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_fasad_sold',
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ],
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Hämta location (serialiserat objekt)
                $location_raw = get_post_meta($post_id, '_fasad_location', true);
                $location = $location_raw ? @unserialize($location_raw) : null;
                $address = $location->address ?? get_the_title();

                // Hämta economy/pris
                $economy_raw = get_post_meta($post_id, '_fasad_economy', true);
                $economy = $economy_raw ? @unserialize($economy_raw) : null;
                $price = $economy->price->primary->amount ?? '';
                if ($price) {
                    $price = number_format($price, 0, ',', ' ') . ' kr';
                }
                $fee = $economy->fee->amount ?? '';
                if ($fee) {
                    $fee = number_format($fee, 0, ',', ' ') . ' kr/mån';
                }

                // Område: district (Vasastan/Östermalm) först, fallback city
                $district = $location->district ?? '';
                $city = $location->city ?? '';
                $omrade = $district ?: $city;

                // Hämta bilder — alla, inte bara första
                $images_raw = get_post_meta($post_id, '_fasad_images', true);
                $images_s1 = $images_raw ? @unserialize($images_raw) : [];
                $images = is_string($images_s1) ? @unserialize($images_s1) : $images_s1;
                $image_list = [];
                if (is_array($images)) {
                    foreach ($images as $img) {
                        if (!empty($img->variants)) {
                            foreach ($img->variants as $v) {
                                if (($v->type ?? '') === 'large') {
                                    $image_list[] = $v->path;
                                    break;
                                }
                            }
                        }
                    }
                }
                $image = $image_list[0] ?? '';

                // Hämta fakta
                $size_raw = get_post_meta($post_id, '_fasad_size', true);
                $size_s1 = $size_raw ? @unserialize($size_raw) : [];
                $size = is_string($size_s1) ? @unserialize($size_s1) : $size_s1;
                $rooms = ($size && !empty($size->rooms)) ? $size->rooms . ' ' . ($size->roomsInformation ?? 'rum') : '';
                $area = '';
                if (!empty($size->area->areas) && is_array($size->area->areas)) {
                    foreach ($size->area->areas as $a) {
                        if (!empty($a->type) && $a->type === 'Boarea' && !empty($a->size)) {
                            $area = $a->size . ' ' . strtolower($a->unit ?? 'kvm');
                            break;
                        }
                    }
                }

                $type_raw = get_post_meta($post_id, '_fasad_descriptionType', true);
                $type_obj = $type_raw ? @unserialize($type_raw) : null;
                $type = $type_obj->alias ?? '';

                // Status-logik enligt kundens prio: budgivning > visning > förhandsvisning > activityCategory
                $status_alias = '';

                // PRIO 1: Pågående budgivning
                $bids_raw = get_post_meta($post_id, '_fasad_bids', true);
                $bids = $bids_raw ? @unserialize($bids_raw) : [];
                if (is_array($bids) && !empty($bids)) {
                    $status_alias = 'BUDGIVNING PÅGÅR';
                }

                // PRIO 2: Kommande visning
                if (!$status_alias) {
                    $showings_raw = get_post_meta($post_id, '_fasad_showings', true);
                    $showings = $showings_raw ? @unserialize($showings_raw) : [];
                    if (is_array($showings) && !empty($showings)) {
                        $now = time();
                        $upcoming = null;
                        foreach ($showings as $show) {
                            if (!empty($show->startDate)) {
                                $ts = strtotime($show->startDate);
                                if ($ts && $ts > $now && ($upcoming === null || $ts < $upcoming)) {
                                    $upcoming = $ts;
                                }
                            }
                        }
                        if ($upcoming) {
                            $status_alias = 'VISNING ' . date('j/n', $upcoming);
                        }
                    }
                }

                // PRIO 3: Förhandsvisning (fallback)
                if (!$status_alias) {
                    $preview_raw = get_post_meta($post_id, '_fasad_preview', true);
                    $preview = $preview_raw ? @unserialize($preview_raw) : null;
                    if (is_object($preview) && !empty($preview->activated)) {
                        $status_alias = 'FÖRHANDSVISNING';
                    }
                }

                // 4. Fallback: använd activityCategory.alias (TILL SALU, SÅLD osv)
                if (!$status_alias) {
                    $cat_raw = get_post_meta($post_id, '_fasad_activityCategory', true);
                    $cat = $cat_raw ? @unserialize($cat_raw) : null;
                    if (is_object($cat) && !empty($cat->alias)) {
                        $alias = $cat->alias;
                        // Mappa till kortare svenska statusar
                        if (stripos($alias, 'försäljning') !== false) {
                            $status_alias = 'TILL SALU';
                        } elseif (stripos($alias, 'såld') !== false) {
                            $status_alias = 'SÅLD';
                        } else {
                            $status_alias = mb_strtoupper($alias);
                        }
                    }
                }

                $listings[] = (object)[
                    'id'      => $post_id,
                    'slug'    => get_post_field('post_name', $post_id),
                    'address' => $address,
                    'price'   => $price,
                    'fee'     => $fee,
                    'type'    => $type,
                    'rooms'   => $rooms,
                    'area'    => $area,
                    'omrade'  => $omrade,
                    'image'   => $image,
                    'images'  => $image_list,
                    'status'  => $status_alias,
                ];
            }
            wp_reset_postdata();
        }

        return [
            'listings'         => $listings,
            'fp_intro_rubrik'  => \get_field('fp_intro_rubrik') ?: '',
            'fp_intro_text'    => \get_field('fp_intro_text') ?: '',
            'fp_intro_knapp'   => [
                'text' => \get_field('fp_intro_knapp_text') ?: 'Se alla objekt',
                'url'  => \get_field('fp_intro_knapp_url') ?: get_permalink(get_page_by_path('objekt')),
            ],
            'fp_listings_rubrik' => \get_field('fp_listings_rubrik') ?: 'Aktuella objekt',
            'fp_valuation'     => [
                'visa'   => \get_field('fp_valuation_visa') !== false ? \get_field('fp_valuation_visa') : true,
                'rubrik' => \get_field('fp_valuation_rubrik') ?: 'Gratis värdebedömning',
                'text'   => \get_field('fp_valuation_text') ?: '',
                'knapp'  => \get_field('fp_valuation_knapp') ?: 'Boka värdering',
            ],
        ];
    }
}
