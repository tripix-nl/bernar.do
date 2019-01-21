<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\Sheets\Facades\Sheets;
use Spatie\Sheets\Sheet;

class Post extends Sheet implements Feedable
{
    public function toFeedItem()
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

    public static function getFeedItems()
    {
        return Sheets::collection('posts')->all();
    }

    public function getDateAttribute()
    {
        return Carbon::createFromTimestamp($this->attributes['date']);
    }
}
