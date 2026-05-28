<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Course;
use App\Models\Category;
use App\Models\Aukstructure;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AircraftController extends Controller
{
    public function index()
    {
        $aircrafts = Aircraft::with(['courses.categories', 'categories'])->orderBy('id')->get();
        return response()->json($aircrafts);
    }

    public function show($id)
    {
        $aircraft = Aircraft::with(['courses.aukstructures.links', 'courses.categories', 'categories'])->findOrFail($id);
        return response()->json($aircraft);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'path' => 'required|string|max:255|unique:aircrafts,path',
        ]);

        $aircraft = Aircraft::create($validated);

        // Load courses for this aircraft
        $this->loadCoursesFromStorage($aircraft);

        return response()->json($aircraft, 201);
    }

    /**
     * Scan storage/app/private for aircraft folders and load their courses
     */
    public function scanStorage()
    {
        $coursesPath = Config::get('app.courses_path', storage_path('app/private/'));
        $results = [
            'aircrafts_found' => 0,
            'aircrafts_created' => 0,
            'courses_loaded' => 0,
            'errors' => [],
            'categories_created' => 0,
            'aukstructures_created' => 0,
            'questions_imported' => 0,
        ];

        if (!is_dir($coursesPath)) {
            return response()->json(['message' => 'Courses directory not found: ' . $coursesPath], 404);
        }

        $items = array_diff(scandir($coursesPath), ['..', '.']);

        foreach ($items as $item) {
            if (!is_dir($coursesPath . '/' . $item)) {
                continue;
            }

            $results['aircrafts_found']++;

            // Create or find aircraft
            $aircraft = Aircraft::firstOrCreate(
                ['path' => $item],
                ['title' => $item]
            );

            if ($aircraft->wasRecentlyCreated) {
                $results['aircrafts_created']++;
            }

            // Load courses for this aircraft
            $courseDirs = array_diff(scandir($coursesPath . '/' . $item), ['..', '.']);

            foreach ($courseDirs as $courseDir) {
                if (!is_dir($coursesPath . '/' . $item . '/' . $courseDir)) {
                    continue;
                }

                $manifestPath = "{$item}/{$courseDir}/imsmanifest.xml";

                if (!Storage::exists($manifestPath)) {
                    continue;
                }

                try {
                    $result = $this->loadCourseFromManifest($manifestPath, $aircraft);
                    $results['courses_loaded']++;
                    $results['categories_created'] += $result['categories_created'] ?? 0;
                    $results['aukstructures_created'] += $result['aukstructures_created'] ?? 0;
                    $results['questions_imported'] += $result['questions_imported'] ?? 0;
                } catch (\Exception $e) {
                    $results['errors'][] = "{$item}/{$courseDir}: " . $e->getMessage();
                }
            }
        }

        return response()->json($results);
    }

    /**
     * Load/refresh courses from storage for a specific aircraft
     */
    public function loadCourses($aircraftId)
    {
        $aircraft = Aircraft::findOrFail($aircraftId);
        $results = [
            'courses_loaded' => 0, 
            'errors' => [],
            'categories_created' => 0,
            'aukstructures_created' => 0,
            'questions_imported' => 0,
        ];

        $coursesPath = Config::get('app.courses_path', storage_path('app/private/'));
        $aircraftPath = $coursesPath . '/' . $aircraft->path;

        if (!is_dir($aircraftPath)) {
            return response()->json(['message' => 'Aircraft directory not found'], 404);
        }

        $courseDirs = array_diff(scandir($aircraftPath), ['..', '.']);

        foreach ($courseDirs as $courseDir) {
            if (!is_dir($aircraftPath . '/' . $courseDir)) {
                continue;
            }

            $manifestPath = "{$aircraft->path}/{$courseDir}/imsmanifest.xml";

            if (!Storage::exists($manifestPath)) {
                continue;
            }

            try {
                $result = $this->loadCourseFromManifest($manifestPath, $aircraft);
                $results['courses_loaded']++;
                $results['categories_created'] += $result['categories_created'] ?? 0;
                $results['aukstructures_created'] += $result['aukstructures_created'] ?? 0;
                $results['questions_imported'] += $result['questions_imported'] ?? 0;
            } catch (\Exception $e) {
                $results['errors'][] = "{$courseDir}: " . $e->getMessage();
            }
        }

        return response()->json($results);
    }

    /**
     * Load a single course from its manifest file
     */
    public function loadCourseFromManifest(string $manifestPath, Aircraft $aircraft)
    {
        $contents = Storage::get($manifestPath);
        $xml = simplexml_load_string($contents);
        if (!$xml) {
            throw new \Exception('Invalid XML in manifest');
        }

        // Parse resources
        $resources = [];
        if (isset($xml->resources->resource)) {
            foreach ($xml->resources->resource as $resource) {
                $temp = [];
                if (isset($resource->file)) {
                    foreach ($resource->file as $file) {
                        $filename = (string)$file->attributes()['href'];
                        $ext = strtolower(substr($filename, -4));
                        if (substr($ext, 0, 3) === 'htm' || $ext === 'html') {
                            $temp[] = $filename;
                        }
                    }
                }
                $identifier = (string)$resource->attributes()['identifier'];
                if ($identifier && !empty($temp)) {
                    $resources[$identifier] = $temp;
                }
            }
        }

        // Create or update course
        $courseTitle = (string)$xml->organizations->organization->title ?: 'Untitled Course';
        $course = Course::updateOrCreate(
            ['path' => $aircraft->path . '/' . basename(dirname($manifestPath))],
            [
                'title' => $courseTitle,
                'aircraft_id' => $aircraft->id,
                'short_description' => 'Course description',
                'long_description' => 'Long course description',
            ]
        );

        // Extract and create categories
        $categoryCodes = $this->extractCategoryCodes($xml->organizations->organization);
        $categoryIds = [];
        
        foreach (array_unique($categoryCodes) as $code) {
            $cat = Category::updateOrCreate(
                [
                    'code' => $code,
                    'aircraft_id' => $aircraft->id
                ],
                [
                    'title' => $this->getCategoryTitle($code),
                    'description' => 'test',
                ]
            );
            $categoryIds[] = $cat->id;
        }

        // Sync course categories
        if (!empty($categoryIds)) {
            $course->categories()->syncWithoutDetaching($categoryIds);
        }

        // Parse organization structure
        $startnode = $xml->organizations->organization;
        $this->recurseXML($startnode, 0, null, $course->id, $resources, $aircraft->id);

        // Load GIFT questions if present
        $giftDir = dirname($manifestPath) . '/GIFT';
        $giftDirStorage = 'private/' . $aircraft->path . '/' . basename(dirname($manifestPath)) . '/GIFT';
        $questionsImported = 0;
        
        if (Storage::exists($giftDirStorage)) {
            $giftFiles = Storage::files($giftDirStorage);
            foreach ($giftFiles as $giftFile) {
                try {
                    // Read gift file content
                    $giftContent = Storage::get($giftFile);
                    $parser = new \App\GiftParser\GiftParser();
                    $parser->parse($giftContent);
                    $questionsImported++;
                } catch (\Exception $e) {
                    // Silently skip invalid gift files
                }
            }
        }

        // Count created items
        $aukstructuresCreated = Aukstructure::where('course_id', $course->id)->count();

        return [
            'categories_created' => count($categoryIds),
            'aukstructures_created' => $aukstructuresCreated,
            'questions_imported' => $questionsImported,
        ];
    }

    /**
     * Extract unique category codes from XML organization items
     */
    private function extractCategoryCodes($node, &$codes = [])
    {
        foreach ($node->item as $item) {
            $catAttr = (string)$item->attributes()['categories'];
            if ($catAttr) {
                $parts = explode(',', $catAttr);
                foreach ($parts as $code) {
                    $code = trim($code);
                    if ($code && !in_array($code, $codes)) {
                        $codes[] = $code;
                    }
                }
            }
            $this->extractCategoryCodes($item, $codes);
        }
        return $codes;
    }

    /**
     * Get human-readable title for category code
     */
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

    private function recurseXML($xml, $auktype, $parent_id, $course_id, $resources, $aircraftId, $attrs_ident = '', $attrs_cat = '')
    {
        $child_count = 0;

        foreach ($xml as $key => $value) {
            // Capture attributes from current element
            if ($value && $value->attributes()['categories'] && $value->attributes()['identifierref']) {
                $attrs_ident = (string)$value->attributes()['identifierref'];
                $attrs_cat = (string)$value->attributes()['categories'];
            }
            $child_count++;

            if ($this->recurseXML($value, $auktype, $parent_id, $course_id, $resources, $aircraftId, $attrs_ident, $attrs_cat) == 0) {
                $description = ['0' => 'название', '1' => 'тема', '2' => 'раздел', '3' => 'модуль'];

                 $auk = Aukstructure::updateOrCreate(
                     [
                         'title' => (string)$value,
                         'parent_id' => $parent_id === null ? null : (int)$parent_id,
                         'course_id' => (int)$course_id,
                     ],
                     [
                         'type' => $auktype,
                         'description' => $description[$auktype] ?? '',
                         'categories' => $attrs_cat,
                         'identifier' => $attrs_ident,
                     ]
                 );
                $parent_id = $auk->id;

                // Create links for type 3 (modules)
                if ($auktype == 3 && isset($resources[$attrs_ident])) {
                    foreach ($resources[$attrs_ident] as $_link) {
                        Link::updateOrCreate(
                            [
                                'link' => (string)$_link,
                                'aukstructure_id' => (int)$parent_id
                            ],
                        );
                    }
                }

                // Parse categories from attr and sync to pivot tables
                if ($attrs_cat && $aircraftId) {
                    $curCatCodes = explode(",", $attrs_cat);

                    foreach ($curCatCodes as $code) {
                        $code = trim($code);

                        $curCatId = Category::where([
                            ['code', '=', $code],
                            ['aircraft_id', '=', $aircraftId]
                        ])->pluck('id')->first();

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

    /**
     * Load courses when aircraft is created
     */
    private function loadCoursesFromStorage(Aircraft $aircraft)
    {
        $coursesPath = Config::get('app.courses_path', storage_path('app/private/'));
        $aircraftPath = $coursesPath . '/' . $aircraft->path;

        if (!is_dir($aircraftPath)) {
            return;
        }

        $courseDirs = array_diff(scandir($aircraftPath), ['..', '.']);

        foreach ($courseDirs as $courseDir) {
            if (!is_dir($aircraftPath . '/' . $courseDir)) {
                continue;
            }

            $manifestPath = "{$aircraft->path}/{$courseDir}/imsmanifest.xml";

            if (Storage::exists($manifestPath)) {
                try {
                    $this->loadCourseFromManifest($manifestPath, $aircraft);
                } catch (\Exception $e) {
                    // Log error but continue
                }
            }
        }
    }

    public function showClassesFs()
    {
        $coursesPath = Config::get('app.courses_path', storage_path('app/private/'));
        $classes = [];

        if (is_dir($coursesPath)) {
            $items = array_diff(scandir($coursesPath), ['..', '.']);
            foreach ($items as $item) {
                if (is_dir($coursesPath . '/' . $item)) {
                    $classes[] = $item;
                }
            }
        }

        return response()->json(array_values($classes));
    }

    public function showAuks(string $air)
    {
        $coursesPath = Config::get('app.courses_path');
        $fullPath = $coursesPath . '/' . $air;
        $auks = [];

        if (is_dir($fullPath)) {
            $auks = array_diff(scandir($fullPath), ['..', '.']);
        }

        return response()->json($auks);
    }

    /**
     * Clear all courses and related data
     */
    public function clearDatabase()
    {
        try {
            // Clear all related tables in correct order
            DB::table('aukstructure_category')->truncate();
            DB::table('category_course')->truncate();
            DB::table('links')->truncate();
            DB::table('aukstructures')->delete();
            DB::table('courses')->delete();
            DB::table('categories')->delete();
            DB::table('questions')->delete();
            DB::table('answers')->delete();
            DB::table('group2learnings')->delete();

            return response()->json([
                'message' => 'База данных успешно очищена',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка очистки базы данных: ' . $e->getMessage(),
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}