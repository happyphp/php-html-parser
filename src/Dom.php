<?php

declare(strict_types=1);


use GuzzleHttp\Client;
use Dom\Node\Collection;
use Dom\RootAccessTrait;
use Contracts\DomInterface;
use GuzzleHttp\Psr7\Request;
use Discovery\CleanerDiscovery;
use Exceptions\StrictException;
use Exceptions\LogicalException;
use Discovery\DomParserDiscovery;
use Exceptions\CircularException;
use Contracts\Dom\ParserInterface;
use Exceptions\NotLoadedException;
use Contracts\Dom\CleanerInterface;
use Psr\Http\Client\ClientInterface;
use Exceptions\ChildNotFoundException;
use Psr\Http\Message\RequestInterface;
use Exceptions\UnknownChildTypeException;
use Psr\Http\Client\ClientExceptionInterface;

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
    private $defaultCharset = 'UTF-8';

    /**
     * The document string.
     *
     * @var Content
     */
    private $content;

    /**
     * A global options array to be used by all load calls.
     *
     * @var ?Options
     */
    private $globalOptions;

    /**
     * @var ParserInterface
     */
    private $domParser;
    /**
     * @var \Contracts\Dom\CleanerInterface
     */
    private $domCleaner;

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
     * @throws ChildNotFoundException
     * @throws UnknownChildTypeException
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
     * @throws \Exceptions\ChildNotFoundException
     * @throws \Exceptions\CircularException
     * @throws \Exceptions\ContentLengthException
     * @throws \Exceptions\LogicalException
     * @throws StrictException
     */
    public function loadFromFile(string $file, ?Options $options = null): Dom
    {
        $content = @\file_get_contents($file);
        if ($content === false) {
            throw new LogicalException('file_get_contents failed and returned false when trying to read "' . $file . '".');
        }

        return $this->loadStr($content, $options);
    }

    /**
     * Use a curl interface implementation to attempt to load
     * the content from a url.
     *
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws \Exceptions\ContentLengthException
     * @throws \Exceptions\LogicalException
     * @throws \Exceptions\StrictException
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
     * @throws \Exceptions\CircularException
     * @throws \Exceptions\ContentLengthException
     * @throws LogicalException
     * @throws \Exceptions\StrictException
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

        $this->root = $this->domParser->parse($localOptions, $this->content, \strlen($str));
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
    public function find(string $selector, int $nth = null)
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
     * @return mixed|\Dom\Node\Collection|null
     *@throws \Exceptions\ChildNotFoundException
     *
     * @throws \Exceptions\NotLoadedException
     */
    public function getElementById($id)
    {
        $this->isLoaded();

        return $this->find('#' . $id, 0);
    }

    /**
     * Simple wrapper function that returns all elements by
     * tag name.
     *
     * @return mixed|\Dom\Node\Collection|null
     *@throws ChildNotFoundException
     *
     * @throws \Exceptions\NotLoadedException
     */
    public function getElementsByTag(string $name)
    {
        $this->isLoaded();

        return $this->find($name);
    }

    /**
     * Simple wrapper function that returns all elements by
     * class name.
     *
     * @return mixed|\Dom\Node\Collection|null
     *@throws ChildNotFoundException
     *
     * @throws \Exceptions\NotLoadedException
     */
    public function getElementsByClass(string $class)
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
        if (\is_null($this->content)) {
            throw new NotLoadedException('Content is not loaded!');
        }
    }
}
