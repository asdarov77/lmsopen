<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Aukstructure;
use App\Models\Category;
use App\Models\Course;

class CourseFilter extends AbstractFilter
{
    public const TITLE = 'title';
    public const PATH = 'path';
    public const AIRCRAFT_ID = 'aircraft_id';
    public const CATEGORY_ID = 'category_id';
    public const PARENT_ID = 'parent_id';
    public const TYPE_ID = 'type_id';
    public const COURSE_ID = 'course_id';

    protected function getCallbacks(): array
    {
        return [
            self::TITLE => [$this, 'title'],
            self::PATH => [$this, 'path'],
            self::AIRCRAFT_ID => [$this, 'aircraftId'],
            self::CATEGORY_ID => [$this, 'categoryId'],
            self::PARENT_ID => [$this, 'parentId'],
            self::TYPE_ID => [$this, 'typeId'],
            self::COURSE_ID => [$this, 'courseId'],
        ];
    }

    public function title(Builder $builder, $value)
    {
        $builder->where('title', 'like', "%{$value}%");
    }

    public function path(Builder $builder, $value)
    {
        $builder->where('path', 'like', "%{$value}%");
    }

    public function aircraftId(Builder $builder, $value)
    {
        $builder->where('aircraft_id', $value);
    }

    public function categoryId(Builder $builder, $categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return;
        }

        $categoryCode = trim($category->code ?? '');

        $builder->whereHas('categories', function ($query) use ($categoryId) {
            $query->where('categories.id', '=', $categoryId);
        });

        if ($categoryCode) {
            $builder->withWhereHas('aukstructures', function ($query) use ($categoryCode) {
                $query->where('categories', 'like', "%{$categoryCode}%")
                    ->orWhere('categories', '=', '')
                    ->where('type', '=', 0);
            });
        }

        $builder->with(['aukstructures', 'categories']);
    }

    public function parentId(Builder $builder, $value)
    {
        $builder->withWhereHas('aukstructures', function ($q) use ($value) {
            $q->where('parent_id', $value);
        });
    }

    public function typeId(Builder $builder, $value)
    {
        $builder->withWhereHas('aukstructures', function ($q) use ($value) {
            $q->where('type', $value);
        });
    }

    public function courseId(Builder $builder, $value)
    {
        $course = Course::where('id', $value)->first();
        
        if ($course) {
            $builder->where('id', $course->id)->with('aukstructures');
        } else {
            $aukstructure = Aukstructure::find($value);
            if ($aukstructure) {
                $builder->where('id', $aukstructure->course_id)
                    ->with('aukstructures');
            }
        }
    }
}
