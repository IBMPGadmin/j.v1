namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_name',
        'client_email',
        'client_status',
        'user_id'
    ];
    
    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}