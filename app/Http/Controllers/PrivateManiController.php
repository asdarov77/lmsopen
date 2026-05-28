<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Aukstructure;
use App\Models\Category;
use App\Models\Course;
use App\Models\Link;
use App\Models\Aircraft;

class PrivateManiController extends Controller
{
    public function RecurseXML($xml, $auktype, $parent_id, $course_id, $resources, $aircraftId, $attrs_ident = '', $attrs_cat = '', $allCategories = [])
    {
        $child_count = 0;

        foreach ($xml as $key => $value) {
            // Capture attributes from current element
            if ($value && $value->attributes()['categories'] && $value->attributes()['identifierref']) {
                $attrs_ident = (string)$value->attributes()['identifierref'];
                $attrs_cat = (string)$value->attributes()['categories'];
            }
            $child_count++;

            if ($this->RecurseXML($value, $auktype, $parent_id, $course_id, $resources, $aircraftId, $attrs_ident, $attrs_cat, $allCategories) == 0) {
                $description = ['0' => 'название', '1' => 'тема', '2' => 'раздел', '3' => 'модуль'];
                $el = Aukstructure::updateOrCreate(
                    [
                        'title' => (string)$value,
                        'parent_id' => (int)$parent_id,
                        'course_id' => (int)$course_id,
                    ],
                    [
                        'type' => $auktype,
                        'description' => $description[$auktype],
                        'categories' => $attrs_cat,
                        'identifier' => $attrs_ident,
                    ]
                );
                $parent_id = $el->id;

                // Create links for type 3 (modules)
                if ($auktype == 3 && isset($resources[$attrs_ident])) {
                    foreach ($resources[$attrs_ident] as $_links) {
                        Link::updateOrCreate(
                            [
                                'link' => (string)$_links,
                                'aukstructure_id' => (int)$parent_id
                            ],
                        );
                    }
                }

                // Parse categories from attr and sync to pivot tables
                if ($attrs_cat) {
                    $curCat = explode(",", $attrs_cat);

                    foreach ($curCat as $_curCat) {
                        $curCatId = (Category::where([
                            ['code', '=', trim($_curCat)],
                            ['aircraft_id', '=', $aircraftId]
                        ])
                            ->pluck('id')
                            ->first()
                        );

                        if ($curCatId) {
                            // Link aukstructure to category
                            DB::table('aukstructure_category')->updateOrInsert(
                                [
                                    'category_id' => (int)$curCatId,
                                    'aukstructure_id' => (int)$parent_id,
                                ],
                            );

                            // Link course to category (via pivot)
                            DB::table('category_course')->updateOrInsert(
                                [
                                    'category_id' => (int)$curCatId,
                                    'course_id' => (int)$course_id,
                                ],
                            );
                        }
                    }
                }

                $auktype++;
            }
        }
        return $child_count;
    }

    public function xmles00($aircraft, $auk)
    {
        $path = "private/{$aircraft}/{$auk}/imsmanifest.xml";
        $ext = pathinfo($path)['extension'];
        $header_type = $this->get_mime_type($ext);
        if (Storage::exists($path)) {
            $contents = Storage::get($path);
            $menuxmlcontent = $this->parsemanifest($contents, $aircraft);
            return response()->json($menuxmlcontent, 200, ['Content-Type' => $header_type], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        abort(404);
    }

    public function parsemanifest($contents, $aircraftPath = null)
    {
        $xml = simplexml_load_string($contents);
        $resources = [];
        $categories = [];

        // Resolve aircraft
        $aircraft = null;
        if ($aircraftPath) {
            $aircraft = Aircraft::where('path', $aircraftPath)->first();
        }
        $aircraftId = $aircraft ? $aircraft->id : null;

        // Parse resources - map identifier to HTML files
        if (isset($xml->resources->resource)) {
            foreach ($xml->resources->resource as $resource) {
                $identifier = (string)$resource->attributes()['identifier'];
                $temp = [];
                if (isset($resource->file)) {
                    foreach ($resource->file as $file) {
                        $filename = (string)$file->attributes()['href'];
                        $ext = substr($filename, -4);
                        if ($ext === 'html') {
                            $temp[] = $filename;
                        }
                    }
                }
                if ($identifier && $temp) {
                    $resources[$identifier] = $temp;
                }
            }
        }

        // Extract unique categories from organization items
        $allCategoryCodes = [];
        $this->extractCategoriesFromXml($xml->organizations->organization, $allCategoryCodes);

        // Create categories if aircraft context is known
        foreach ($allCategoryCodes as $code) {
            if ($aircraftId) {
                $cat = Category::updateOrCreate(
                    [
                        'code' => $code,
                        'aircraft_id' => $aircraftId
                    ],
                    [
                        'title' => $this->getCategoryTitle($code),
                        'description' => 'test',
                    ]
                );
                $categories[] = $cat->id;
            }
        }

        // Create or update course
        $courseTitle = (string)$xml->organizations->organization->title ?: $aircraftPath;
        
        if (!$aircraftId) {
            // Try to find or create aircraft
            if ($aircraftPath) {
                $aircraft = Aircraft::firstOrCreate(
                    ['path' => $aircraftPath],
                    ['title' => $aircraftPath]
                );
                $aircraftId = $aircraft->id;
                
                // Re-sync categories with correct aircraft_id
                foreach ($allCategoryCodes as $code) {
                    $cat = Category::updateOrCreate(
                        [
                            'code' => $code,
                            'aircraft_id' => $aircraftId
                        ],
                        [
                            'title' => $this->getCategoryTitle($code),
                            'description' => 'test',
                        ]
                    );
                    $categories[] = $cat->id;
                }
            }
        }

        $curAuk = Course::updateOrCreate(
            ['path' => $aircraftPath ? $aircraftPath . '/' . $courseTitle : $courseTitle],
            [
                'title' => $courseTitle,
                'aircraft_id' => $aircraftId,
                'short_description' => 'тестовый short_description',
                'long_description' => 'тестовый long_description',
            ]
        );

        $startnode = $xml->organizations->organization;
        $auktype = 0;
        $parent_id = 0;
        $course_id = $curAuk->id;

        $this->RecurseXML($startnode, $auktype, $parent_id, $course_id, $resources, $aircraftId, '', '', []);

        return ['success' => true, 'course_id' => $course_id];
    }

    private function extractCategoriesFromXml($xml, &$categories)
    {
        foreach ($xml->item as $item) {
            $catAttr = (string)$item->attributes()['categories'];
            if ($catAttr) {
                $codes = explode(",", $catAttr);
                foreach ($codes as $code) {
                    $code = trim($code);
                    if ($code && !in_array($code, $categories)) {
                        $categories[] = $code;
                    }
                }
            }
            $this->extractCategoriesFromXml($item, $categories);
        }
    }

    private function getCategoryTitle($code)
    {
        $titles = [
            'KE' => 'Командир экипажа',
            'PKE' => 'Помощник командира экипажа',
            'SH' => 'Штурман',
            'BI' => 'Бортинженер',
            'BR' => 'Бортрадист',
            'BT_ADO' => 'БТ АДО',
        ];
        return $titles[$code] ?? $code;
    }

    public function get_mime_type($ext)
    {
        $header_type = 'text/html';
        if ($ext == 'css') {
            $header_type = 'text/css';
        }
        if ($ext == 'js') {
            $header_type = 'text/javascript';
        }
        if ($ext == 'jpg') {
            $header_type = 'image/jpeg';
        }
        if ($ext == 'jpeg') {
            $header_type = 'image/jpeg';
        }
        if ($ext == 'png') {
            $header_type = 'image/png';
        }
        if ($ext == 'woff') {
            $header_type = 'font/woff';
        }
        if ($ext == 'ttf') {
            $header_type = 'font/ttf';
        }
        if ($ext == 'svg') {
            $header_type = 'image/svg+xml';
        }
        if ($ext == 'xml') {
            $header_type = 'application/xml';
        }
        return $header_type;
    }
}