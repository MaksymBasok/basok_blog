<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogPost extends Model
{
    use SoftDeletes;
    const UNKNOWN_USER = 1;
    protected $fillable
        = [
            'title',
            'slug',
            'category_id',
            'excerpt',
            'content_raw',
            'is_published',
            'published_at',
        ];

    /**
     * Категорія статті
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        //стаття належить категорії
        return $this->belongsTo(BlogCategory::class);
    }

    /**
     * Автор статті
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        //стаття належить користувачу
        return $this->belongsTo(User::class);
    }

    use HasFactory;
    //
    protected static function booted()
    {
        static::creating(function (BlogPost $post) {
            // якщо не прийшло з форми – генеруємо
            $post->content_raw  ??= strip_tags($post->content_html ?? '');
            $post->content_html ??= $post->content_raw;
        });

        static::updating(function (BlogPost $post) {
            $post->content_raw  ??= strip_tags($post->content_html ?? '');
            $post->content_html ??= $post->content_raw;
        });
    }
}
