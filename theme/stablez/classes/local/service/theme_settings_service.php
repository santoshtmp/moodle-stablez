<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme settings service for the stablez theme.
 *
 * Provides a singleton service layer for retrieving and processing
 * all theme_stablez admin settings. Methods return structured arrays
 * ready for use as Mustache template contexts.
 *
 * @package   theme_stablez
 * @copyright 2025 stablez
 * @author    santoshtmp7 <https://santoshmagar.com.np/>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_stablez\local\service;

defined('MOODLE_INTERNAL') || die();

use core\output\theme_config;
use local_stablezhelpers\local\traits\StablezSingleton;
use moodle_url;

/**
 * Singleton service for reading and formatting theme_stablez settings.
 *
 * Wraps {@see theme_config} to provide structured template contexts
 * for footer, contact, front-page, and start-guideline sections.
 * Use {@see self::get_instance()} rather than constructing directly.
 */
class theme_settings_service {

    use StablezSingleton;

    /**
     * Loaded theme configuration object for theme_stablez.
     *
     * @var \core\output\theme_config
     */
    public $theme;

    /**
     * Returns the singleton instance, creating it on first call.
     *
     * Declared explicitly so that IDE "Go to Definition" navigates
     * here rather than to the StablezSingleton trait.
     *
     * @return static The singleton instance.
     */
    public static function get_instance(): static {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Initialises the service by loading the stablez theme configuration.
     *
     * Called automatically by the StablezSingleton trait after construction.
     *
     * @return void
     */
    public function init(): void {
        $this->theme = theme_config::load('stablez');
    }

    /**
     * Returns a single theme setting value or its file URL.
     *
     * @param  string $setting  The setting name as defined in settings.php.
     * @param  string $filearea Optional file area name. When provided the
     *                          method returns the URL of the stored file
     *                          rather than the raw setting value.
     * @return string           Setting value, file URL, or empty string when absent.
     */
    public function setting(string $setting, string $filearea = ''): string {
        if ($filearea) {
            return $this->theme->setting_file_url($setting, $filearea) ?? '';
        }
        return (string)($this->theme->settings->$setting ?? '');
    }

    /**
     * Builds the template context for the site footer.
     *
     * Processes the copyright string, footer description, contact toggle,
     * and any number of configurable footer menu columns. Each menu column
     * is defined in the admin settings as a label and a newline-separated
     * list of "Title | URL" pairs.
     *
     * @return array{
     *     copyright: string,
     *     footer_description: string,
     *     footer_contact_section: string,
     *     footer_contact_section_label: string,
     *     footer_menu: array<int, array{label: string, label_class: string, items: array, items_present: bool}>
     * }
     */
    public function footer_settings(): array {
        $copyright          = $this->theme->settings->copyright ?? '';
        $footer_menu_number = (int)($this->theme->settings->footer_menu_number ?? 0);

        $templatecontext = [
            'copyright'                  => $copyright
                ? str_replace('{year}', date('Y'), format_string($copyright))
                : '',
            'footer_description'         => format_string($this->theme->settings->footer_description ?? ''),
            'footer_contact_section'     => $this->theme->settings->footer_contact_section ?? '',
            'footer_contact_section_label' => format_string($this->theme->settings->footer_contact_section_label ?? ''),
            'footer_menu'                => [],
        ];

        for ($i = 0, $j = 1; $i < $footer_menu_number; $i++, $j++) {
            $label_key = 'footer_menu_label_' . $j;
            $items_key = 'footer_menu_items_' . $j;
            $raw_items = $this->theme->settings->$items_key ?? '';
            $menu_items = [];

            if ($raw_items) {
                $index = 0;
                foreach (explode("\n", $raw_items) as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }

                    [$title, $link] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
                    $menu_items[$index]['title'] = format_string($title);
                    $menu_items[$index]['link']  = ($link !== '') ? $link : '#';
                    $index++;
                }
            }

            $label = $this->theme->settings->$label_key ?? '';
            $templatecontext['footer_menu'][$i] = [
                'label'         => format_string($label),
                'label_class'   => str_replace([' ', '_'], '-', strtolower($label)),
                'items'         => $menu_items,
                'items_present' => !empty($menu_items),
            ];
        }

        return $templatecontext;
    }

    /**
     * Builds the template context for the contact details section.
     *
     * Extracts the iframe src from a raw map embed code (if present),
     * splits comma-separated phone numbers into an array, and
     * collects social media links. Sets `isEmpty` to true when no
     * contact data has been configured at all.
     *
     * @return array{
     *     contact_form_recipient_email: string,
     *     contact_form_recipient_name: string,
     *     contact_name: string,
     *     location_address: string,
     *     other_contact_info: string,
     *     map_location_src: string,
     *     phone_number_exist: bool,
     *     phone_number: array|false,
     *     mail: string,
     *     website: string,
     *     social_link: array{facebook: string, twitter: string, linkedin: string, instagram: string},
     *     isEmpty: bool
     * }
     */
    public function contact_details_settings(): array {
        $phone_number = $this->theme->settings->phone_number ?? '';
        $map_location = $this->theme->settings->map_location ?? '';

        // Extract the src attribute from an iframe embed snippet, falling
        // back to the raw value if no iframe is found.
        preg_match('/<iframe[^>]+src="([^"]+)"/', $map_location, $matches);
        $map_src = !empty($matches[1]) ? $matches[1] : $map_location;

        $templatecontext = [
            'contact_form_recipient_email' => $this->theme->settings->contact_form_recipient_email ?? '',
            'contact_form_recipient_name'  => $this->theme->settings->contact_form_recipient_name ?? '',
            'contact_name'                 => format_string($this->theme->settings->contact_name ?? ''),
            'location_address'             => format_string($this->theme->settings->location_address ?? ''),
            'other_contact_info'           => format_text($this->theme->settings->other_contact_info ?? ''),
            'map_location_src'             => $map_src,
            'phone_number_exist'           => !empty($phone_number),
            'phone_number'                 => $phone_number ? array_map('trim', explode(',', $phone_number)) : false,
            'mail'                         => $this->theme->settings->mail ?? '',
            'website'                      => $this->theme->settings->website ?? '',
            'social_link'                  => [
                'facebook'  => $this->theme->settings->facebook  ?? '',
                'twitter'   => $this->theme->settings->twitter   ?? '',
                'linkedin'  => $this->theme->settings->linkedin  ?? '',
                'instagram' => $this->theme->settings->instagram ?? '',
            ],
        ];

        $templatecontext['isEmpty'] = self::isArrayValuesEmpty($templatecontext);

        return $templatecontext;
    }

    /**
     * Builds the template context for the front-page hero and popup sections.
     *
     * Handles three mutually exclusive hero banner variants:
     *  - Slider: iterates over N configured banner slides.
     *  - Single banner: uses the first banner image/title/description.
     *  - Disabled: hero_banner key is absent from the context.
     *
     * When the banner popup is enabled, the required JS and CSS assets
     * are registered on the current page via {@see \moodle_page::requires}.
     *
     * @return array{
     *     hero_banner?: bool,
     *     hero_banner_slider?: bool,
     *     banner_slider?: array,
     *     banner_title_1?: string,
     *     banner_description_1?: string,
     *     banner_image_1?: string,
     *     hero_banner_login_card?: string,
     *     banner_cta_count?: int,
     *     banner_cta?: array,
     *     banner_popup_enable?: bool,
     *     banner_popup_link?: string,
     *     banner_popup_image?: string
     * }
     */
    public function front_page_settings(): array {
        global $PAGE;

        $templatecontext = [];

        // --- Hero banner ---------------------------------------------------
        if (!empty($this->theme->settings->hero_banner)) {
            $templatecontext['hero_banner'] = true;

            if (!empty($this->theme->settings->hero_banner_slider)) {
                // Multi-slide carousel.
                $templatecontext['hero_banner_slider'] = true;
                $slide_count = (int)($this->theme->settings->hero_banner_slider_number ?? 0);

                for ($i = 0; $i < $slide_count; $i++) {
                    $n = $i + 1;
                    $templatecontext['banner_slider'][$i] = [
                        'banner_title'       => format_string($this->theme->settings->{"banner_title_{$n}"} ?? ''),
                        'banner_description' => format_text($this->theme->settings->{"banner_description_{$n}"} ?? ''),
                        'banner_image'       => $this->theme->setting_file_url("banner_image_{$n}", "banner_image_{$n}"),
                    ];
                }
            } else {
                // Single static banner.
                $templatecontext['banner_title_1']       = format_string($this->theme->settings->banner_title_1 ?? '');
                $templatecontext['banner_description_1'] = format_text($this->theme->settings->banner_description_1 ?? '');
                $templatecontext['banner_image_1']       = $this->theme->setting_file_url('banner_image_1', 'banner_image_1');
            }

            // Optional login card overlay inside the hero.
            if (!empty($this->theme->settings->hero_banner_login_card)) {
                $templatecontext['hero_banner_login_card'] = $this->theme->settings->hero_banner_login_card;
            }

            // Call-to-action buttons.
            $cta_count = (int)($this->theme->settings->banner_cta_count ?? 0);
            $templatecontext['banner_cta_count'] = $cta_count;

            for ($i = 0; $i < $cta_count; $i++) {
                $n = $i + 1;
                $templatecontext['banner_cta'][$i] = [
                    'label' => format_string($this->theme->settings->{"banner_cta_label_{$n}"} ?? ''),
                    'link'  => $this->theme->settings->{"banner_cta_link_{$n}"} ?? '',
                ];
            }
        }

        // --- Banner popup --------------------------------------------------
        if (!empty($this->theme->settings->banner_popup_enable)) {
            $templatecontext['banner_popup_enable'] = true;
            $templatecontext['banner_popup_link']   = $this->theme->settings->banner_popup_link ?? '';
            $templatecontext['banner_popup_image']  = $this->theme->setting_file_url('banner_popup_image', 'banner_popup_image');

            $PAGE->requires->js(new moodle_url('/theme/stablez/javascript/home-banner-popup.js'));
            $PAGE->requires->css(new moodle_url('/theme/stablez/style/home-banner-popup.css'));
        }

        return $templatecontext;
    }

    /**
     * Builds the template context for the "Getting started" guideline section.
     *
     * Iterates over each configured guideline item. When no custom image
     * is uploaded for an item, a default SVG icon is used based on the
     * item's position (1 = user, 2 = check, 3+ = web/globe).
     *
     * @return array{
     *     start_guideline?: array<int, array{image: string, title: string, desc: string}>
     * }
     */
    public function start_guideline_settings(): array {
        $templatecontext = [];
        $item_count = (int)($this->theme->settings->start_guideline_item_count ?? 0);

        if ($item_count <= 0) {
            return $templatecontext;
        }

        /** Default icon paths indexed by item position (1-based). */
        $default_icons = [
            1 => '/theme/stablez/pix/icons/start-guideline-user.svg',
            2 => '/theme/stablez/pix/icons/start-guideline-circle-check.svg',
        ];
        $fallback_icon = '/theme/stablez/pix/icons/start-guideline-web.svg';

        for ($i = 1; $i <= $item_count; $i++) {
            $image_key = "start_guideline_image_{$i}";
            $image     = $this->theme->setting_file_url($image_key, $image_key);

            if (empty($image)) {
                $image = $default_icons[$i] ?? $fallback_icon;
            }

            $templatecontext['start_guideline'][$i - 1] = [
                'image' => $image,
                'title' => format_string($this->theme->settings->{"start_guideline_title_{$i}"} ?? ''),
                'desc'  => format_string($this->theme->settings->{"start_guideline_desc_{$i}"} ?? ''),
            ];
        }

        return $templatecontext;
    }

    /**
     * Recursively checks whether every value in an array is empty.
     *
     * Returns true only when all scalar values are empty and all nested
     * arrays also contain exclusively empty values. Useful for deciding
     * whether a contact details block has any content worth rendering.
     *
     * @param  array $array The array to inspect (may be nested).
     * @return bool         True if every value is empty, false otherwise.
     */
    public static function isArrayValuesEmpty(array $array): bool {
        foreach ($array as $value) {
            if (is_array($value)) {
                if (!self::isArrayValuesEmpty($value)) {
                    return false;
                }
            } elseif (!empty($value)) {
                return false;
            }
        }
        return true;
    }
}
