<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;
    protected $table = "categories";

    protected $fillable = ["name"];

    /**
     * The datasources that belong to the category.
     */
    public function datasources(): BelongsToMany
    {
        return $this->belongsToMany(
            DataSource::class, "category_data_source",
            "category_id", "data_source_id"
        )->withTimestamps();
    }
}
