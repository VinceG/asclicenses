<?php

namespace ASCLicenses;

use GuzzleHttp\Client;
use League\Csv\Reader;
use League\Csv\Statement;
use Psr\SimpleCache\CacheInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Cache\Simple\FilesystemCache;

class Licenses
{
    const CACHE_KEY_FILE = 'asc_licenses_file';

    protected $client;

    protected $location = 'https://www.asc.gov/Content/category1/st_data/v_Export_ALL_With_Discip.txt';

    protected $cache;

    protected $reader;

    protected $statement;

    protected $ttl = 86400; // 24 hours
    
    public function __construct()
    {
        $this->setClient(new Client())
            ->setCache(new FilesystemCache());
    }

    public function all($state = null)
    {
        // Check if licenses file was downloaded and is valid
        if(!$this->fileExists()) {
            $this->download();
        }

        // Parse the records so we can have easier access to the list of appraisers 
        // instead of parsing the CSV file every single time we call the methods
        $this->parseFile();

        return $this->licenses($state);
    }

    protected function parseFile()
    {
        $this->reader = Reader::createFromString($this->licensesContents());
        $this->reader->setDelimiter("\t");
        $this->reader->setHeaderOffset(0);
        $this->statement = (new Statement());
    }

    protected function licenses($state = null)
    {
        if($state) {
            return $this->statement->where(function($record) use($state) {
                return strtolower($record['st_abbr']) === strtolower($state);
            })->process($this->reader);
        }

        return $this->statement->process($this->reader);
    }

    protected function licensesContents()
    {
        return $this->cache->get(static::CACHE_KEY_FILE);
    }

    protected function download()
    {
        $response = $this->client->get($this->getLocation());

        $this->cache->set(static::CACHE_KEY_FILE, (string) $response->getBody(), $this->ttl);
    }

    public function fileExists()
    {
        return $this->cache->has(static::CACHE_KEY_FILE);
    }

    /**
     * Get the value of location
     */ 
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */ 
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of client
     */ 
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return  self
     */ 
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the value of ttl
     */ 
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set the value of ttl
     *
     * @return  self
     */ 
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get the value of cache
     */ 
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * Set the value of cache
     *
     * @return  self
     */ 
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get the value of reader
     */ 
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * Set the value of reader
     *
     * @return  self
     */ 
    public function setReader($reader)
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * Get the value of statement
     */ 
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Set the value of statement
     *
     * @return  self
     */ 
    public function setStatement($statement)
    {
        $this->statement = $statement;

        return $this;
    }
}