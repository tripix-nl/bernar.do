<?php

namespace App\Markdown;

use League\CommonMark\Block\Element\Document;
use League\CommonMark\DocumentProcessorInterface;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Util\Configuration;
use League\CommonMark\Util\ConfigurationAwareInterface;

class ExternalLinkProcessor implements DocumentProcessorInterface, ConfigurationAwareInterface
{
    private $config;

    public function setConfiguration(Configuration $configuration)
    {
        $this->config = $configuration;
    }

    public function processDocument(Document $document): void
    {
        $walker = $document->walker();

        while (($event = $walker->next())) {
            $node = $event->getNode();

            if (!($node instanceof Link) || !$event->isEntering()) {
                continue;
            }

            $url = $node->getUrl();

            if ($this->isUrlExternal($url)) {
                $node->data['attributes']['target'] = '_blank';
            }
        }
    }

    private function isUrlExternal(string $url): bool
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host !== $this->config->getConfig('host');
    }
}
