<?php

namespace App\Interfaces;

use GuzzleHttp\Cookie\CookieJarInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Psr\Http\Message\UriInterface;

/**
 * Interface BrowserInterface
 */
interface BrowserInterface
{
    /**
     * @param UriInterface
     *
     * @return PageInterface
     */
    public function accessUrl(UriInterface $url): PageInterface;

    /**
     * @param UriInterface
     * @param array
     *
     * @return PageInterface
     */
    public function submitForm(UriInterface $url, array $params): PageInterface;

    /**
     * @return PageInterface
     */
    public function getCurrentPage(): PageInterface;
    /**
     * @return bool
     */
    public function navigateBack(): Boolean;

    /**
     * @return bool
     */
    public function navigateForward(): Boolean;
}
