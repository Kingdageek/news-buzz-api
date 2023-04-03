<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DataSource extends Model
{
    use HasFactory;
    protected $table = "datasources";
    protected $fillable = [
        "name", "description", "base_url", "is_active", "str_id"
    ];

    /**
     * Get the news sources for this datasource.
     */
    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    /**
     * The categories that belong to this datasource.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            "category_data_source",
            "data_source_id",
            "category_id"
        )->withTimestamps();
    }
}
