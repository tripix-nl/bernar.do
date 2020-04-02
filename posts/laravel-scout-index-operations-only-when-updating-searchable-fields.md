---
title: Laravel Scout index operations only when updating searchable fields.
date: 2019-04-02
summary: When updating a model, which is made searchable using Laravel Scout, it will dispatch a MakeSearchable job. The problem here is that it also does that when a field that isn't even searchable was updated. Let's fix that!
---

One of the great solutions that comes with Laravel is [Laravel Scout](https://laravel.com/docs/7.x/scout). Coupled with the free Community plan that [Algolia](https://www.algolia.com/) offers, you'll have a pretty sweet deal right out of the box.

But imagine you have a `Topic` model which is searchable by title, just title. It would be easy to assume that that when other fields on the model are updated, you won't have an index operation sent to Algolia, since those fields are not searchable. But that is not the case, every time the model is updated an index operation is sent. So having a `post_count` or `view_count` field that is updated very frequently on the `Topic` model might cause you to use more than the maximum of 50.000 index operations per month on Algolia's free plan.

## Fixing the searchable model

To change this behaviour we have to override the `bootSearchable` method in the `Searchable` trait. We then change the default `ModelObserver` into a custom one; `OnlySearchableModelObserver`.

```
<?php

namespace App;

use App\OnlySearchableModelObserver;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Laravel\Scout\SearchableScope;

class Topic extends Model
{
    use Searchable;
    
    public static function bootSearchable(): void
    {
        static::addGlobalScope(new SearchableScope);

        static::observe(new OnlySearchableModelObserver());

        (new static)->registerSearchableMacros();
    }
    
    public function toSearchableArray(): array
    {
        return [
            'title' => (string) $this->title,
        ];
    }
}
```

Our custom `OnlySearchableModelObserver` will simply extend the default `ModelObserver` and override the `saved` method. In there we add a check to see if any of the updated - dirty - fields intersect with the searchable fields we defined on our model. 

```
<?php

namespace App;

use Laravel\Scout\ModelObserver;

class OnlySearchableModelObserver extends ModelObserver
{
    public function saved($model): void
    {
        if (static::syncingDisabledFor($model)) {
            return;
        }

        if (! $model->shouldBeSearchable()) {
            $model->unsearchable();

            return;
        }

        // Check if any searchable fields have changed
        if (empty(array_intersect_key($model->toSearchableArray(), $model->getDirty()))) {
            return;
        }

        $model->searchable();
    }
}
```

That's it! Since I couldn't find anything about this myself, I deciced it was a great excuse to write a blog post about it. Hopefully it comes in handy, or maybe there is a better solution, let me know on Twitter! 

## Another solution

Instead of fixed this issue like we did just now, there is an alternative, at least if you are using Algolia. The open source package [Scout Extended](https://github.com/algolia/scout-extended) by Algolia fixes this issue and has many more options and improvements. It features searching in multiple models, index configuration in version control, zero downtime re-imports, and many other features.
