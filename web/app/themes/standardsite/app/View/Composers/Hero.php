<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composers\Concerns\AcfFields;
use PrekWebHelper\PrekWebHelper;

use function App\attributesToString;
use function App\getAcfGroup;

class Hero extends PrekComposer
{
    use AcfFields;

    private $heroGroup;
    protected static $views = [
        'partials.hero',
    ];

    public function with()
    {
        // Hoppa över hero på objektdetaljsidor och till salu-sidan
        $fasad_slug = get_query_var('fasad_listing');
        if (!empty($fasad_slug) && $fasad_slug !== '1' && strlen($fasad_slug) > 2) {
            return ['hero' => null];
        }
        // Hoppa över på undersidor
        if (is_page(['objekt', 'kontakt', 'om-oss', 'kommande', 'salda', 'journal']) || get_post_type() === 'journal') {
            return ['hero' => null];
        }

        $hero = [
            'slides' => [],
            'video' => [],
        ];
        $slides = [];
        $this->heroGroup = getAcfGroup($this->fields(), 'hero_group');
        if (getAcfGroup($this->heroGroup, 'hero_type', '') === 'slides') {
            $slides = $this->getSlides();
        } elseif (getAcfGroup($this->heroGroup, 'hero_type', '') === 'video') {
            $video = $this->getVideo();
        }
        if(!empty($slides)){
            $hero['slides'] = $slides;
        }
        if(!empty($video)){
            $hero['video'] = $video;
        }
        // Fallback: PREK-bilder
        if (empty($hero['slides']) && empty($hero['video'])) {
            $uploads = content_url('uploads');
            $hero['slides'] = [
                ['image' => ['src' => $uploads . '/oscars-hero1.jpg', 'srcset' => '', 'attributes' => 'class="slide-image"'], 'title' => '', 'subtitle' => ''],
                ['image' => ['src' => $uploads . '/oscars-hero2.jpg', 'srcset' => '', 'attributes' => 'class="slide-image"'], 'title' => '', 'subtitle' => ''],
                ['image' => ['src' => $uploads . '/oscars-hero3.jpg', 'srcset' => '', 'attributes' => 'class="slide-image"'], 'title' => '', 'subtitle' => ''],
            ];
        }
        return [
            'hero' => $hero,
        ];
    }

    private function getVideo()
    {
        $heroFilm = getAcfGroup($this->heroGroup, 'hero_video');
        $src = !empty($heroFilm['hero_video_file']) ? $heroFilm['hero_video_file']['url'] : '';
        $srcmobile = !empty($heroFilm['hero_video_file_mobile']) ? $heroFilm['hero_video_file_mobile']['url'] : '';
        return [
            'sources' => [
                'default' => $srcmobile,
                'md' => $src,
            ],
            'poster' => wp_get_attachment_image_url($heroFilm['hero_video_poster']['ID'], 'full'),
        ];
    }

    private function getSlides()
    {
        $slides = [];
        // Försök med nya ACF-fält först
        $heroSlides = \get_field('hero_slides');
        if (is_array($heroSlides) && !empty($heroSlides)) {
            foreach ($heroSlides as $slide) {
                if (empty($slide['image'])) continue;
                $imageUrl = is_array($slide['image']) ? $slide['image']['url'] : wp_get_attachment_image_url($slide['image'], 'full');
                if (empty($imageUrl)) continue;
                $slides[] = [
                    'image' => [
                        'src'        => $imageUrl,
                        'srcset'     => '',
                        'attributes' => 'class="slide-image"',
                    ],
                    'title'    => $slide['title'] ?? '',
                    'subtitle' => $slide['subtitle'] ?? '',
                ];
            }
        }
        // Fallback: gamla fältstrukturen
        if (empty($slides)) {
            $heroSlides = getAcfGroup($this->heroGroup, 'hero_slides');
            if (is_array($heroSlides)) {
                foreach ($heroSlides as $slide) {
                    $imageGroup = $slide['hero_slides_image_group'] ?? [];
                    if (empty($imageGroup) || empty($imageGroup['hero_slides_image'])) continue;
                    $imageUrl = wp_get_attachment_image_url($imageGroup['hero_slides_image']['ID'], 'full');
                    $slides[] = [
                        'image' => [
                            'src'        => $imageUrl,
                            'srcset'     => '',
                            'attributes' => 'class="slide-image"',
                        ],
                        'title'    => $slide['title'] ?? '',
                        'subtitle' => $slide['subtitle'] ?? '',
                    ];
                }
            }
        }
        return $slides;
    }

    public static function transformImage($src, $srcSmall, $imageSettings, $attributes = []) {
        $prekWebHelper = PrekWebHelper::getInstance();
        $image = [
            'attributes' => attributesToString(array_merge(['class' => '', 'loading' => 'lazy'], $attributes)),
            'src' => $prekWebHelper->image->processImage($src, $imageSettings['maxWidth'], $imageSettings['maxHeight'], $imageSettings['quality']),
            'srcset' => $prekWebHelper->image->processImageSrcset($src, $imageSettings['minWidth'], $imageSettings['maxWidth'], $imageSettings['minHeight'], $imageSettings['maxHeight'], $imageSettings['nrOfSizes'], $imageSettings['quality']),
            'srcSmall' => $prekWebHelper->image->processImage($srcSmall, $imageSettings['minWidth'], $imageSettings['minHeight'], $imageSettings['quality']),
//            'sizes' => '(min-width: 1100px) 992px, 100vw',
        ];
        return $image;
    }
}