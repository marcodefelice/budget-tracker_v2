<?php

namespace App\BudgetTracker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionJobConfiguration extends Model
{
    use HasFactory;

    public $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
      ];
}
