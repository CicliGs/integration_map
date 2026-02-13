<?php

declare(strict_types=1);

namespace App\Services\Yandex;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * Parses Yandex Maps reviews page HTML into company info and review list.
 */
final class YandexPageParser
{
    private const SELECTORS_COMPANY_NAME = [
        "//*[contains(@class, 'orgpage-header-view__header-title')]",
        "//*[contains(@class, 'orgpage-header-view__title')]",
        "//*[contains(@class, 'card-title-view__title')]",
        "//h1",
        "//meta[@property='og:title']/@content",
        "//title",
    ];

    private const SELECTORS_RATING_BLOCK = [
        "//*[contains(@class, 'business-summary-rating-badge-view')]",
        "//*[contains(@class, 'business-rating-badge-view__rating')]/ancestor::*[contains(@class, 'business-rating-badge-view')][1]",
        "//*[contains(@class, 'business-rating-badge-view')]",
    ];

    private const SELECTORS_RATING_TEXT = [
        "//*[contains(@class, 'business-rating-badge-view__rating-text')]",
        "//*[contains(@class, 'business-rating-badge-view__rating')]",
    ];

    private const SELECTOR_REVIEW_CARD = "//*[contains(@class, 'business-reviews-card-view__review')]";

    /**
     * Parse HTML into company block and reviews array.
     *
     * @param  string  $html  Full page HTML
     * @param  int  $limit  Max number of reviews to return
     * @return array{company: array{name: mixed, rating: mixed, reviews_count: mixed, ratings_count: mixed}, reviews: array}
     */
    public function parse(string $html, int $limit): array
    {
        if ($html === '') {
            return $this->emptyResult();
        }

        $xpath = $this->createXpath($html);
        if ($xpath === null) {
            return $this->emptyResult();
        }

        return [
            'company' => $this->parseCompanyInfo($xpath),
            'reviews' => $this->parseReviews($xpath, $limit),
        ];
    }

    /**
     * @return array{name: mixed, rating: mixed, reviews_count: mixed, ratings_count: mixed}
     */
    private function parseCompanyInfo(DOMXPath $xpath): array
    {
        $name = $this->resolveCompanyName($xpath);
        $ratingBlockText = $this->firstNonEmptyText($xpath, self::SELECTORS_RATING_BLOCK);
        $ratingText = $this->firstNonEmptyText($xpath, self::SELECTORS_RATING_TEXT);

        $rating = $this->extractDecimal($ratingText) ?? $this->extractDecimal($ratingBlockText);

        $reviewsCount = $this->extractInteger($this->firstNonEmptyText($xpath, [
            "//*[contains(@class, 'business-rating-badge-view__review-count')]",
        ]));
        if ($reviewsCount === null && $ratingBlockText !== null) {
            $reviewsCount = $this->extractIntegerFromPattern($ratingBlockText, '/([\d\s]+)\s+отзыв/iu');
        }
        if ($reviewsCount === null) {
            $reviewsCount = $this->findIntegerInNodes(
                $xpath->query("//*[contains(text(), 'отзыв') or contains(text(), 'Отзыв')]"),
                '/([\d\s]+)\s+отзыв/iu'
            );
        }

        $ratingsCount = $this->extractInteger($this->firstNonEmptyText($xpath, [
            "//*[contains(@class, 'business-rating-badge-view__votes')]",
            "//*[contains(@class, 'business-rating-badge-view__count')]",
        ]));
        if ($ratingsCount === null && $ratingBlockText !== null) {
            $ratingsCount = $this->extractIntegerFromPattern($ratingBlockText, '/([\d\s]+)\s+оцен/iu');
        }
        if ($ratingsCount === null) {
            $ratingsCount = $this->findIntegerInNodes(
                $xpath->query("//*[contains(text(), 'оцен') or contains(text(), 'Оцен')]"),
                '/([\d\s]+)\s+оцен/iu'
            );
        }

        return [
            'name' => $name,
            'rating' => $rating,
            'reviews_count' => $reviewsCount,
            'ratings_count' => $ratingsCount,
        ];
    }

    /**
     * Resolve company name from page (title/header selectors), normalized.
     */
    private function resolveCompanyName(DOMXPath $xpath): ?string
    {
        $raw = $this->firstNonEmptyText($xpath, self::SELECTORS_COMPANY_NAME);
        return $this->normalizeCompanyName($raw);
    }

    /**
     * @return array<int, array{id: int, author: mixed, rating: mixed, date: mixed, text: mixed, branch: mixed}>
     */
    private function parseReviews(DOMXPath $xpath, int $limit): array
    {
        $nodes = $xpath->query(self::SELECTOR_REVIEW_CARD);
        if ($nodes === false || $nodes->length === 0) {
            return [];
        }

        $items = [];
        $index = 0;
        foreach ($nodes as $node) {
            if ($index >= $limit) {
                break;
            }
            $items[] = $this->parseReviewNode($xpath, $node, $index);
            $index++;
        }
        return $items;
    }

    /**
     * @return array{id: int, author: mixed, rating: mixed, date: mixed, text: mixed, branch: mixed}
     */
    private function parseReviewNode(DOMXPath $xpath, DOMNode $node, int $index): array
    {
        $ratingText = $this->firstNonEmptyText($xpath, [
            ".//*[contains(@class, 'business-rating-badge-view__rating-text')]",
            ".//*[contains(@class, 'business-rating-badge-view__rating')]",
        ], $node);

        $rating = $this->extractDecimal($ratingText);
        if ($rating === null) {
            $rating = $this->extractRatingFromAriaOrTitle($xpath, $node);
        }

        $body = $this->firstNonEmptyText($xpath, [
            ".//*[contains(@class, 'business-review-view__body-text')]",
            ".//*[contains(@class, 'business-review-view__body')]",
        ], $node);
        if ($body === null) {
            $body = $this->normalizeWhitespace($node->textContent ?? '');
        }

        return [
            'id' => $index,
            'author' => $this->firstNonEmptyText($xpath, [
                ".//*[contains(@class, 'business-review-view__author')]",
                ".//*[contains(@class, 'business-review-view__name')]",
            ], $node),
            'rating' => $rating,
            'date' => $this->firstNonEmptyText($xpath, [
                ".//*[contains(@class, 'business-review-view__date')]",
            ], $node),
            'text' => $body,
            'branch' => $this->firstNonEmptyText($xpath, [
                ".//*[contains(@class, 'business-review-view__branch')]",
                ".//*[contains(@class, 'business-review-view__location')]",
                ".//*[contains(@class, 'business-review-view__address')]",
            ], $node),
        ];
    }

    /**
     * Try to extract a rating value from aria-label or title attributes inside the node.
     */
    private function extractRatingFromAriaOrTitle(DOMXPath $xpath, DOMNode $node): ?float
    {
        $attrNodes = $xpath->query(".//*[@aria-label or @title]", $node);
        if ($attrNodes === false) {
            return null;
        }
        foreach ($attrNodes as $attrNode) {
            if (! $attrNode instanceof DOMElement) {
                continue;
            }
            foreach (['aria-label', 'title'] as $attrName) {
                if (! $attrNode->hasAttribute($attrName)) {
                    continue;
                }
                $label = $this->normalizeWhitespace($attrNode->getAttribute($attrName));
                if ($label !== '') {
                    $decimal = $this->extractDecimal($label);
                    if ($decimal !== null) {
                        return $decimal;
                    }
                }
            }
        }
        return null;
    }

    /**
     * First non-empty text content matching one of the XPath expressions (optionally under context).
     *
     * @param  array<int, string>  $expressions  XPath expressions to try in order
     * @return string|null  First non-empty trimmed text, or null
     */
    private function firstNonEmptyText(DOMXPath $xpath, array $expressions, ?DOMNode $context = null): ?string
    {
        foreach ($expressions as $expr) {
            $nodes = $context === null ? $xpath->query($expr) : $xpath->query($expr, $context);
            if ($nodes === false || $nodes->length === 0) {
                continue;
            }
            foreach ($nodes as $node) {
                $text = $node instanceof DOMAttr ? $node->value : $node->textContent;
                $text = $this->normalizeWhitespace($text ?? '');
                if ($text !== '') {
                    return $text;
                }
            }
        }
        return null;
    }

    /**
     * Collapse whitespace to single spaces and trim.
     */
    private function normalizeWhitespace(string $text): string
    {
        $replaced = preg_replace('/\s+/u', ' ', $text);
        return trim($replaced ?? '');
    }

    /**
     * Strip hash prefix and "— Yandex Maps" suffix, normalize spaces.
     */
    private function normalizeCompanyName(?string $name): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }
        $name = $this->normalizeWhitespace($name);
        $name = preg_replace('/^#\s*/u', '', $name) ?? $name;
        $name = preg_replace('/\s*[—\-]\s*(Яндекс\s+Карты?|Yandex\s+Maps).*$/iu', '', $name) ?? $name;
        $trimmed = trim($name);
        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Extract first decimal number from text (e.g. "4,5" or "4.5").
     */
    private function extractDecimal(?string $text): ?float
    {
        if ($text === null || $text === '') {
            return null;
        }
        if (preg_match('/(\d+[,.]?\d*)/u', $text, $m) !== 1) {
            return null;
        }
        $value = str_replace(',', '.', $m[1]);
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Extract first integer from text (digits and spaces).
     */
    private function extractInteger(?string $text): ?int
    {
        if ($text === null || $text === '') {
            return null;
        }
        if (preg_match('/([\d\s]+)/u', $text, $m) !== 1) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $m[1]);
        return $digits !== '' ? (int) $digits : null;
    }

    /**
     * Extract integer from first capturing group of regex pattern (e.g. "123 отзыв" -> 123).
     */
    private function extractIntegerFromPattern(string $text, string $pattern): ?int
    {
        if (preg_match($pattern, $text, $m) !== 1) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $m[1] ?? '');
        return $digits !== '' ? (int) $digits : null;
    }

    /**
     * Search node list for first text matching pattern and return extracted integer.
     *
     * @param  \DOMNodeList|false  $nodes  Query result (or false)
     * @param  string  $pattern  Regex with one capturing group for digits
     * @return int|null
     */
    private function findIntegerInNodes($nodes, string $pattern): ?int
    {
        if ($nodes === false || $nodes->length === 0) {
            return null;
        }
        foreach ($nodes as $node) {
            $text = $this->normalizeWhitespace($node->textContent ?? '');
            $int = $this->extractIntegerFromPattern($text, $pattern);
            if ($int !== null) {
                return $int;
            }
        }
        return null;
    }

    /**
     * Build DOMXPath from HTML string. Returns null if HTML cannot be loaded.
     */
    private function createXpath(string $html): ?DOMXPath
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        if (! @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html)) {
            return null;
        }
        return new DOMXPath($dom);
    }

    /**
     * Empty result structure (no company, no reviews).
     *
     * @return array{company: array{name: null, rating: null, reviews_count: null, ratings_count: null}, reviews: array}
     */
    private function emptyResult(): array
    {
        return [
            'company' => [
                'name' => null,
                'rating' => null,
                'reviews_count' => null,
                'ratings_count' => null,
            ],
            'reviews' => [],
        ];
    }
}
