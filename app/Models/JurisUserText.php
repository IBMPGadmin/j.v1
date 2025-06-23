<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class JurisUserText extends Model
{
    use HasFactory;
    
    protected $table = 'juris_user_texts';
    
    protected $fillable = [
        'user_id',
        'document_table',
        'document_section_id',
        'text_content',
        'text_type'
    ];
    
    /**
     * Get the user that owns the text annotation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
