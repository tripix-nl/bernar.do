<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\Sheets\Facades\Sheets;
use Spatie\Sheets\Sheet;

class Post extends Sheet implements Feedable
{
    protected $slug;
    protected $title;
    protected $summary;
    protected $date;

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create([
            'id' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'updated' => $this->date,
            'link' => url($this->slug),
            'author' => 'Bernardo Hulsman',
        ]);
    }

    public static function getFeedItems(): Collection
    {
        return Sheets::collection('posts')->all();
    }

    public function getDateAttribute(): Carbon
    {
        return Carbon::createFromTimestamp($this->attributes['date']);
    }
}
