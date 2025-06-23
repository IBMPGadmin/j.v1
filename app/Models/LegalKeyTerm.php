<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalKeyTerm extends Model
{
    protected $fillable = [
        'term',
        'definition',
        'language',
        'category',
        'source',
        'notes',
        'status'
    ];

    public static function getLanguages()
    {
        return [
            'en' => 'English',
            'fr' => 'French',
            'es' => 'Spanish',
            'zh' => 'Chinese',
            'hi' => 'Hindi',
            'ar' => 'Arabic',
        ];
    }

    public static function getCategories()
    {
        return [
            'Immigration Law',
            'Criminal Law',
            'Family Law',
            'Corporate Law',
            'International Law',
            'Administrative Law',
            'Human Rights',
            'Labor Law',
            'Tax Law',
            'General Legal Terms'
        ];
    }
}
