<?php

namespace TNatanael\BrFormatter;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class BaseModel extends Model
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

        if (substr_count($string_date, 'T') == 1) {
            return Carbon::parse($string_date)->format('d/m/Y H:i');
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $string_date)->format('d/m/Y H:i');
    }
}
