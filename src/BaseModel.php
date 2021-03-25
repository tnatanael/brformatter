<?php

namespace TNatanael\BrFormatter;

use Illuminate\Database\Eloquent\Model as EloquentModel;

use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class BaseModel extends EloquentModel
{
    //Model Events
    public static function boot()
    {
        parent::boot();

        // I/O Automatic Formating
        static::retrieved([FormatManipulator::class, 'format_output']);
        static::saving([FormatManipulator::class, 'format_input']);
    }

    //Exibição de datas gerenciadas pelo Laravel em formato BR
    public function getCreatedAtAttribute($value)
    {
        return $this::formatDateString($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return $this::formatDateString($value);
    }

    public function getDeletedAtAttribute($value)
    {
        return $this::formatDateString($value);
    }

    private static function formatDateString($string_date)
    {
        if (is_null($string_date)) return null;

        Log::info($string_date);

        return Carbon::createFromFormat('Y-m-d H:i:s', $string_date)->format('d/m/Y H:i');
    }
}
