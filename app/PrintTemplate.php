<?php

namespace App;

class PrintTemplate
{
    protected $title;

    protected $logoUrl = '/icon512_maskable.png';

    protected $infos = [];

    protected $footerInfos = [];

    protected $footerContent = '';

    protected $total = null;

    protected $itemHeaders = null;

    protected $items = null;

    protected $itemsWithHeaders = [];

    protected $layout = 'print_template';

    public function layout58mm()
    {
        $this->layout = 'print_58mm_template';
        return $this;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    public function getInfos(): array
    {
        return $this->infos;
    }

    public function getFooterInfos(): array
    {
        return $this->footerInfos;
    }

    public function getFooter(): string
    {
        return $this->footerContent;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function getItemHeaders(): ?array
    {
        return $this->itemHeaders;
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function getItemsWithHeaders(): array
    {
        return $this->itemsWithHeaders;
    }


    public function title(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function logoUrl(string $url): static
    {
        $this->logoUrl = $url;
        return $this;
    }

    public function info(string $key, string $value)
    {
        $this->infos[$key] = $value;
        return $this;
    }

    public function footerInfo(string $key, string $value)
    {
        $this->footerInfos[$key] = $value;
        return $this;
    }

    public function footer(string $footer)
    {
        $this->footerContent = $footer;
        return $this;
    }

    public function total(float $total)
    {
        $this->total = $total;
        return $this;
    }

    public function itemHeaders(array $headers)
    {
        $this->itemHeaders = $headers;
        return $this;
    }

    public function items(array $items)
    {
        $this->items = $items;
        return $this;
    }

    public function itemWithHeaders(array $item, array $headers)
    {
        $this->itemsWithHeaders[] = [
            'item' => $item,
            'headers' => $headers
        ];
        return $this;
    }

    public function itemsWithHeaders(array $itemsWithHeaders)
    {
        $this->itemsWithHeaders = $itemsWithHeaders;
        return $this;
    }

    public function validate()
    {
        if (!$this->title) {
            throw new \Exception('Title is required');
        }

        if (!$this->logoUrl) {
            throw new \Exception('Logo URL is required');
        }

        // Validate standard items and headers
        if ($this->itemHeaders && !$this->items) {
            throw new \Exception('Items are required when headers are provided');
        }

        if ($this->items && !$this->itemHeaders) {
            throw new \Exception('Headers are required when items are provided');
        }

        // Only validate if both items and headers are present using the old method
        if ($this->itemHeaders && $this->items && count($this->items) > 0) {
            if (count($this->itemHeaders) !== count($this->items[0])) {
                throw new \Exception('Headers and items must have the same length');
            }
        }

        // Validate each item with its own headers
        foreach ($this->itemsWithHeaders as $itemWithHeader) {
            if (!isset($itemWithHeader['headers']) || !isset($itemWithHeader['item'])) {
                throw new \Exception('Each item with headers must have both item and headers keys');
            }

            if (count($itemWithHeader['headers']) !== count($itemWithHeader['item'])) {
                throw new \Exception('Item headers and item data must have the same length');
            }
        }
    }


}
