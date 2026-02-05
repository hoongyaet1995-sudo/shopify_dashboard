<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table      = 'customers';    // Point to your specific table
    protected $primaryKey = 'customers_id'; // Set your custom PK

    protected $fillable = [
        'customers_id',
        'customers_name',
        'username',
        'password',
        'created_date',
        // Add any other columns you intend to save here
    ];
}
