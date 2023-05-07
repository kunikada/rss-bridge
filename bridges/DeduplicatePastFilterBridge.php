<?php

class DeduplicatePastFilterBridge extends FeedExpander
{
    const MAINTAINER = 'kunikada';
    const NAME = 'DeduplicatePastFilter';
    const URI = 'https://github.com/RSS-Bridge/rss-bridge';
    const DESCRIPTION = 'Filters duplicated feeds in the past';
    const PARAMETERS = [
        [
            'target' => [
                'name' => 'Target feed URL',
                'type' => 'text',
                'required' => true,
                'exampleValue' => 'http://example.com/rss',
                ],
            'filter_url' => [
                'name' => 'Deduplicate on URL',
                'type' => 'checkbox',
                'required' => false,
                'defaultValue' => 'checked',
                ],
            'filter_title' => [
                'name' => 'Deduplicate on title',
                'type' => 'checkbox',
                'required' => false,
                ],
            'duration' => [
                'name' => 'Past days',
                'type' => 'number',
                'required' => true,
                'defaultValue' => 180,
                ],
        ]
    ];
    const CACHE_TIMEOUT = 3600;

    private function getPastItems()
    {
        static $cache;
        if (isset($cache)) {
            return $cache;
        }

        $items = $this->loadCacheValue($this->getInput('target'));
        if ($items === null) {
            return [];
        }

        $threshold = time() - $this->getInput('duration') * 24 * 60 * 60;
        $filtered = array_filter($items, function($item) use ($threshold) {
            return $item['timestamp'] > $threshold;
        });

        $cache = $filtered;
        return $cache;
    }

    private function setPastItems()
    {
        $this->saveCacheValue($this->getInput('target_url'), array_merge($this->getPastItems(), $this->items));
    }

    protected function parseItem($feedItem)
    {
        $item = parent::parseItem($feedItem);
        foreach ($this->getPastItems() as $pastItem) {
            if ($item['timestamp'] !== $pastItem['timestamp']) {
                if ($this->getInput('filter_url') && $item['uri'] === $pastItem['uri']) {
                    return;
                }
                if ($this->getInput('filter_title') && $item['title'] === $pastItem['title']) {
                    return;
                }
            }
        }
        return $item;
    }

    public function collectData()
    {
        $this->collectExpandableDatas($this->getInput('target'));
        $this->setPastItems();
    }

}
