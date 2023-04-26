<?php

namespace App\BudgetTracker\Models;

use App\BudgetTracker\factories\PayeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class Payee extends Model
{
    use HasFactory;

    public $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(array $attributes = []): Factory
    {
        return PayeeFactory::new($attributes);
    }

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->attributes['date_time'] = time();
        $this->attributes['uuid'] = uniqid();

        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
        'updated_at'  => 'date:Y-m-d',
        'deletad_at'  => 'date:Y-m-d',
        'date_time' =>  'date:Y-m-d h:i:s'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function entry()
    {
        return $this->hasMany(Entry::class);
    }
}
