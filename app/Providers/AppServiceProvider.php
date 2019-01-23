<?php

namespace App\Providers;

use App\Markdown\ExternalLinkProcessor;
use Illuminate\Support\ServiceProvider;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Spatie\Sheets\ContentParsers\MarkdownParser;
use Spatie\Sheets\ContentParsers\MarkdownWithFrontMatterParser;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->when([MarkdownParser::class, MarkdownWithFrontMatterParser::class])
            ->needs(CommonMarkConverter::class)
            ->give(function () {
                $environment = Environment::createCommonMarkEnvironment();
                $environment->addDocumentProcessor(new ExternalLinkProcessor());
                $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'js']));
                $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(['html', 'php', 'js']));
                return new CommonMarkConverter(['safe' => true], $environment);
            });
    }
}
