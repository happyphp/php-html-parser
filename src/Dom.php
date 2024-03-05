<?php

declare(strict_types=1);

namespace Haphp\HtmlParser;

use GuzzleHttp\Client;
use Haphp\HtmlParser\Dom\Node\Collection;
use Haphp\HtmlParser\Dom\RootAccessTrait;
use Haphp\HtmlParser\Contracts\DomInterface;
use GuzzleHttp\Psr7\Request;
use Haphp\HtmlParser\Discovery\CleanerDiscovery;
use Haphp\HtmlParser\Exceptions\StrictException;
use Haphp\HtmlParser\Exceptions\LogicalException;
use Haphp\HtmlParser\Discovery\DomParserDiscovery;
use Haphp\HtmlParser\Exceptions\CircularException;
use Haphp\HtmlParser\Contracts\Dom\ParserInterface;
use Haphp\HtmlParser\Exceptions\NotLoadedException;
use Haphp\HtmlParser\Contracts\Dom\CleanerInterface;
use Psr\Http\Client\ClientInterface;
use Haphp\HtmlParser\Exceptions\ChildNotFoundException;
use Psr\Http\Message\RequestInterface;
use Haphp\HtmlParser\Exceptions\ContentLengthException;
use Psr\Http\Client\ClientExceptionInterface;
use function strlen;
use function file_get_contents;

/**
 * Class Dom.
 */
class Dom implements DomInterface
{
    use RootAccessTrait;

    /**
     * The charset we would like the output to be in.
     *
     * @var string
     */
    private string $defaultCharset = 'UTF-8';

    /**
     * The document string.
     *
     * @var Content
     */
    private Content $content;

    /**
     * A global options array to be used by all load calls.
     *
     * @var ?Options
     */
    private ?Options $globalOptions = null;

    /**
     * @var ParserInterface
     */
    private ParserInterface $domParser;

    /**
     * @var CleanerInterface
     */
    private CleanerInterface $domCleaner;

    public function __construct(?ParserInterface $domParser = null, ?CleanerInterface $domCleaner = null)
    {
        if ($domParser === null) {
            $domParser = DomParserDiscovery::find();
        }
        if ($domCleaner === null) {
            $domCleaner = CleanerDiscovery::find();
        }

        $this->domParser = $domParser;
        $this->domCleaner = $domCleaner;
    }

    /**
     * Returns the inner html of the root node.
     *
     * @throws NotLoadedException
     */
    public function __toString(): string
    {
        $this->isLoaded();

        return $this->root->innerHtml();
    }

    /**
     * Loads the dom from a document file/url.
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function loadFromFile(string $file, ?Options $options = null): Dom
    {
        $content = @file_get_contents($file);
        if ($content === false) {
            throw new LogicalException('file_get_contents failed and returned false when trying to read "' . $file . '".');
        }

        return $this->loadStr($content, $options);
    }

    /**
     * Use a curl interface implementation to attempt to load
     * the content from an url.
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     * @throws ClientExceptionInterface
     */
    public function loadFromUrl(string $url, ?Options $options = null, ?ClientInterface $client = null, ?RequestInterface $request = null): Dom
    {
        if ($client === null) {
            $client = new Client();
        }
        if ($request === null) {
            $request = new Request('GET', $url);
        }

        $response = $client->sendRequest($request);
        $content = $response->getBody()->getContents();

        return $this->loadStr($content, $options);
    }

    /**
     * Parsers the html of the given string. Used for load(), loadFromFile(),
     * and loadFromUrl().
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function loadStr(string $str, ?Options $options = null): Dom
    {
        $localOptions = new Options();
        if ($this->globalOptions !== null) {
            $localOptions = $localOptions->setFromOptions($this->globalOptions);
        }
        if ($options !== null) {
            $localOptions = $localOptions->setFromOptions($options);
        }

        $html = $this->domCleaner->clean($str, $localOptions, $this->defaultCharset);

        $this->content = new Content($html);

        $this->root = $this->domParser->parse($localOptions, $this->content, strlen($str));
        $this->domParser->detectCharset($localOptions, $this->defaultCharset, $this->root);

        return $this;
    }

    /**
     * Sets a global options array to be used by all load calls.
     */
    public function setOptions(Options $options): Dom
    {
        $this->globalOptions = $options;

        return $this;
    }

    /**
     * Find elements by css selector on the root node.
     *
     * @throws NotLoadedException
     * @throws ChildNotFoundException
     *
     * @return mixed|Collection|null
     */
    public function find(string $selector, int $nth = null): mixed
    {
        $this->isLoaded();

        return $this->root->find($selector, $nth);
    }

    /**
     * Simple wrapper function that returns an element by the
     * id.
     *
     * @param $id
     *
     * @return mixed|Collection|null
     *@throws ChildNotFoundException
     *
     * @throws NotLoadedException
     */
    public function getElementById($id): mixed
    {
        $this->isLoaded();

        return $this->find('#' . $id, 0);
    }

    /**
     * Simple wrapper function that returns all elements by
     * tag name.
     *
     * @return mixed|Collection|null
     *@throws ChildNotFoundException
     *
     * @throws NotLoadedException
     */
    public function getElementsByTag(string $name): mixed
    {
        $this->isLoaded();

        return $this->find($name);
    }

    /**
     * Simple wrapper function that returns all elements by
     * class name.
     *
     * @return mixed|Collection|null
     *@throws ChildNotFoundException
     *
     * @throws NotLoadedException
     */
    public function getElementsByClass(string $class): mixed
    {
        $this->isLoaded();

        return $this->find('.' . $class);
    }

    /**
     * Checks if the load methods have been called.
     *
     * @throws NotLoadedException
     */
    private function isLoaded(): void
    {
        if (is_null($this->content)) {
            throw new NotLoadedException('Content is not loaded!');
        }
    }
}
