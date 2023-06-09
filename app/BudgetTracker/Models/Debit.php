<?php

namespace App\BudgetTracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\BudgetTracker\Enums\EntryType;
use App\BudgetTracker\Factories\DebitFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class Debit extends Entry
{
    use HasFactory;

    const DEFAULT_CATEGORY = 55;

    /**
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {   
        parent::__construct($attributes);
        
        $this->attributes['type'] = EntryType::Debit->value;
        $this->attributes['category_id'] = self::DEFAULT_CATEGORY;
        
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return DebitFactory::new();
    }

    /**
     * Get all of the models from the database.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function all($columns = ['*'])
    {
        $query = parent::all($columns);
        return $query->where('type',EntryType::Debit->value);
    }
}
