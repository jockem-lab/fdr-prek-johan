<?php

namespace App\View\Composers;

class SingleListing extends PrekComposer
{
    protected static $views = [
        'single-fasad_listing',
        'fasad.content-single-fasad_listing',
    ];

    public function with()
    {
        // Hämta post_id
        $post_id = get_the_ID();
        if (!$post_id) {
            global $wp_query;
            $post_id = $wp_query->get_queried_object_id();
        }
        if (!$post_id) {
            $fasad_slug = get_query_var('fasad_listing');
            if ($fasad_slug) {
                $post = get_page_by_path($fasad_slug, OBJECT, 'fasad_listing');
                if ($post) $post_id = $post->ID;
            }
        }
        if (!$post_id) return ['listing' => null];

        // ─────────────────────────────────────────────
        // Helper: säker unserialize
        // ─────────────────────────────────────────────
        $get = function($key) use ($post_id) {
            $raw = get_post_meta($post_id, $key, true);
            if (!$raw) return null;
            return @unserialize($raw);
        };

        // ─────────────────────────────────────────────
        // Location (adress, område, koordinater)
        // ─────────────────────────────────────────────
        $location = $get('_fasad_location');
        $address  = $location->address ?? get_the_title();
        $city     = $location->city ?? '';
        $zipCode  = $location->zipCode ?? '';
        $lat      = $location->coordinate->latitude ?? null;
        $lng      = $location->coordinate->longitude ?? null;
        $area     = $location->area ?? '';

        // Område: först location->area, annars extrahera från titel
        if (!$area) {
            $title_parts = array_map('trim', explode(',', get_the_title()));
            $area = end($title_parts);
        }

        // ─────────────────────────────────────────────
        // Pris och avgift
        // ─────────────────────────────────────────────
        $economy = $get('_fasad_economy');
        $price_amount = $economy->price->primary->amount ?? 0;
        $price_suffix = $economy->price->primary->suffix ?? '';
        $price_formatted = $price_amount
            ? number_format($price_amount, 0, ',', ' ') . ' kr' . ($price_suffix ? '/' . $price_suffix : '')
            : '';
        $monthly_fee = $economy->monthlyFee ?? '';

        // ─────────────────────────────────────────────
        // Bilder
        // ─────────────────────────────────────────────
        $images_data = $get('_fasad_images') ?: [];
        $images = [];
        if (is_array($images_data)) {
            foreach ($images_data as $img) {
                if (!empty($img->path)) {
                    $images[] = $img->path;
                }
            }
        }

        // ─────────────────────────────────────────────
        // Storlek och rum
        // ─────────────────────────────────────────────
        $size = $get('_fasad_size');
        $living_area = $size->livingArea ?? '';
        $supplemental_area = $size->supplementalArea ?? '';
        $size_formatted = $living_area ? $living_area . ' kvm' : '';
        if ($supplemental_area) $size_formatted .= ' + ' . $supplemental_area . ' kvm';

        // ─────────────────────────────────────────────
        // Fakta (rum, etc)
        // ─────────────────────────────────────────────
        $facts = $get('_fasad_facts');
        $rooms = $facts->rooms ?? '';
        $floor = $facts->floor ?? '';
        $hasElevator = $facts->hasElevator ?? false;
        $balconyType = $facts->balconyType ?? '';
        $fireplace = $facts->fireplace ?? '';

        // Våning
        $floor_text = '';
        if ($floor) {
            $floor_text = $floor;
            if ($hasElevator) $floor_text .= ', hiss finns';
        }

        // ─────────────────────────────────────────────
        // Beskrivnings-typ (Bostadsrätt, Villa, etc)
        // ─────────────────────────────────────────────
        $type_obj = $get('_fasad_descriptionType');
        $type = $type_obj->alias ?? '';

        // ─────────────────────────────────────────────
        // Status-logik (samma som listning)
        // ─────────────────────────────────────────────
        $activity = $get('_fasad_activityCategory');
        $is_sold = get_post_meta($post_id, '_fasad_sold', true) === '1';
        $preview = $get('_fasad_preview');
        $is_preview = ($preview && !empty($preview->activated));
        $bids = $get('_fasad_bids');
        $has_bids = is_array($bids) && count($bids) > 0;
        $showings = $get('_fasad_showings') ?: [];

        // Hitta nästa visning
        $next_showing = null;
        if (is_array($showings)) {
            $now = time();
            foreach ($showings as $sh) {
                $start = strtotime($sh->startDate ?? '');
                if ($start && $start >= $now) {
                    if (!$next_showing || $start < strtotime($next_showing->startDate)) {
                        $next_showing = $sh;
                    }
                }
            }
        }

        // Status-badge text
        $status_label = '';
        if ($next_showing) {
            $start = strtotime($next_showing->startDate);
            $status_label = 'VISNING ' . date('j/n', $start);
        } elseif ($is_preview) {
            $status_label = 'FÖRHANDSVISNING';
        } elseif ($has_bids) {
            $status_label = 'BUDGIVNING PÅGÅR';
        } elseif ($is_sold) {
            $status_label = 'SÅLD';
        } else {
            $alias = $activity->alias ?? '';
            if ($alias === 'Försäljning') $status_label = 'TILL SALU';
            elseif ($alias === 'Såld ej tillträdd') $status_label = 'SÅLD';
            else $status_label = 'TILL SALU';
        }

        // ─────────────────────────────────────────────
        // Säljtext
        // ─────────────────────────────────────────────
        $sales_title = get_post_meta($post_id, '_fasad_salesTitle', true) ?: $address;
        $sales_text  = get_post_meta($post_id, '_fasad_salesText', true) ?: '';

        // ─────────────────────────────────────────────
        // Byggnad och förening
        // ─────────────────────────────────────────────
        $building = $get('_fasad_building');
        $built_year = $building->constructionYear ?? '';

        $association = $get('_fasad_association');
        $assoc_name = $association->name ?? '';
        $assoc_genuine = ($association->genuine ?? false) ? 'Ja' : 'Nej';
        $assoc_form = $association->formOfOwnership->alias ?? 'Bostadsrätt - Lägenhet';

        // ─────────────────────────────────────────────
        // Energideklaration
        // ─────────────────────────────────────────────
        $energy = $get('_fasad_energy');
        $energy_value = $energy->energyDeclaration->value ?? '';
        $energy_unit = 'kWh per kvm/år';
        $energy_status = $energy->energyDeclaration->status ?? '';
        $energy_date = $energy->energyDeclaration->date ?? '';

        // ─────────────────────────────────────────────
        // Mäklare
        // ─────────────────────────────────────────────
        $realtors_data = $get('_fasad_realtors') ?: [];
        $realtors = [];
        if (is_array($realtors_data)) {
            foreach ($realtors_data as $r) {
                $first = $r->firstname ?? '';
                $last  = trim($r->lastname ?? '');
                $email = $r->email ?? '';
                $email_lc = strtolower($email);

                // Försök matcha till lokal team-bild via email-prefix
                // /app/uploads/team/{prefix}.jpg
                $email_prefix = strtolower(strstr($email, '@', true));
                $local_image = '';
                $upload_dir = wp_upload_dir();
                $team_path = $upload_dir['basedir'] . '/team/' . $email_prefix . '.jpg';
                if (file_exists($team_path)) {
                    $local_image = $upload_dir['baseurl'] . '/team/' . $email_prefix . '.jpg';
                }

                $realtors[] = (object)[
                    'firstname'    => $first,
                    'lastname'     => $last,
                    'fullname'     => trim($first . ' ' . $last),
                    'email'        => $email,
                    'phone'        => $r->phoneString ?? $r->cellphoneString ?? '',
                    'image'        => $local_image ?: ($r->image ?? ''),
                    'title'        => $r->title ?? 'Fastighetsmäklare',
                    'type'         => $r->type ?? 'Ansvarig Mäklare',
                ];
            }
        }

        // ─────────────────────────────────────────────
        // Visningar (alla framtida)
        // ─────────────────────────────────────────────
        $upcoming_showings = [];
        if (is_array($showings)) {
            $now = time();
            foreach ($showings as $sh) {
                $start = strtotime($sh->startDate ?? '');
                if (!$start) continue;
                $end_str = $sh->endDate ?? '';
                $end = $end_str ? strtotime($end_str) : null;

                $weekdays = ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'];
                $months_short = ['','jan','feb','mar','apr','maj','jun','jul','aug','sep','okt','nov','dec'];

                $weekday = $weekdays[(int)date('w', $start)];
                $day = (int)date('j', $start);
                $mon = (int)date('n', $start);

                $time_str = date('H.i', $start);
                if ($end) $time_str .= '-' . date('H.i', $end);

                $upcoming_showings[] = (object)[
                    'weekday' => $weekday,
                    'day'     => $day,
                    'month'   => $mon,
                    'time'    => $time_str,
                    'note'    => $sh->note ?? '',
                    'is_past' => $start < $now,
                ];
            }
            // Sortera på datum
            usort($upcoming_showings, function($a, $b) use ($showings) {
                return 0; // behåll API-ordning
            });
        }

        // ─────────────────────────────────────────────
        // Dokument
        // ─────────────────────────────────────────────
        $documents_data = $get('_fasad_documents') ?: [];
        $documents = [];
        if (is_array($documents_data)) {
            foreach ($documents_data as $doc) {
                $title = $doc->title ?? $doc->type->alias ?? 'Dokument';
                $url   = $doc->url ?? $doc->path ?? '';
                if ($url) {
                    $documents[] = (object)[
                        'title' => mb_strtoupper($title),
                        'url'   => $url,
                    ];
                }
            }
        }

        // ─────────────────────────────────────────────
        // Beskrivningar (renoveringar mm)
        // ─────────────────────────────────────────────
        $descriptions = $get('_fasad_descriptions') ?: [];
        $renovations = '';
        if (is_array($descriptions)) {
            foreach ($descriptions as $d) {
                $alias = $d->type->alias ?? '';
                if (stripos($alias, 'renover') !== false || stripos($alias, 'genomfö') !== false) {
                    $renovations = $d->content ?? '';
                    break;
                }
            }
        }

        // ─────────────────────────────────────────────
        // Liknande objekt (andra aktiva)
        // ─────────────────────────────────────────────
        $similar = get_posts([
            'post_type'      => 'fasad_listing',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'post__not_in'   => [$post_id],
            'orderby'        => 'rand',
            'meta_query'     => [
                [
                    'key'     => '_fasad_sold',
                    'value'   => '1',
                    'compare' => '!=',
                ],
            ],
        ]);
        $similar_listings = [];
        foreach ($similar as $s) {
            $loc = @unserialize(get_post_meta($s->ID, '_fasad_location', true));
            $imgs = @unserialize(get_post_meta($s->ID, '_fasad_images', true));
            $first_img = '';
            if (is_array($imgs) && !empty($imgs[0]->path)) $first_img = $imgs[0]->path;

            $sim_addr = $loc->address ?? $s->post_title;
            $sim_area = $loc->area ?? '';
            if (!$sim_area) {
                $parts = array_map('trim', explode(',', $s->post_title));
                $sim_area = end($parts);
            }
            $similar_listings[] = (object)[
                'id'      => $s->ID,
                'url'     => get_permalink($s->ID),
                'address' => mb_strtoupper($sim_addr),
                'area'    => $sim_area,
                'image'   => $first_img,
            ];
        }

        return [
            'listing' => (object)[
                'id'          => $post_id,
                'address'     => $address,
                'area'        => $area,
                'city'        => $city,
                'zipCode'     => $zipCode,
                'lat'         => $lat,
                'lng'         => $lng,

                'price'       => $price_formatted,
                'monthlyFee'  => $monthly_fee ? number_format($monthly_fee, 0, ',', ' ') . ' kr/mån' : '',

                'images'      => $images,
                'imagesJson'  => wp_json_encode($images),

                'livingArea'      => $living_area,
                'supplementalArea'=> $supplemental_area,
                'sizeFormatted'   => $size_formatted,
                'rooms'           => $rooms,
                'floor'           => $floor_text,
                'balconyType'     => $balconyType,
                'fireplace'       => $fireplace,

                'type'        => $type,
                'statusLabel' => $status_label,

                'salesTitle'  => $sales_title,
                'salesText'   => $sales_text,

                'builtYear'   => $built_year,

                'assocName'   => $assoc_name,
                'assocGenuine'=> $assoc_genuine,
                'assocForm'   => $assoc_form,

                'energyValue' => $energy_value,
                'energyUnit'  => $energy_unit,
                'energyStatus'=> $energy_status,
                'energyDate'  => $energy_date,

                'realtors'    => $realtors,
                'showings'    => $upcoming_showings,
                'documents'   => $documents,
                'renovations' => $renovations,
                'similar'     => $similar_listings,
            ],
        ];
    }
}
