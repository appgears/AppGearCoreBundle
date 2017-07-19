<?php

namespace AppGear\CoreBundle\Config;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Yaml\Parser;

/**
 * YAML file loader with import support.
 *
 * Example:
 * <code>
 * imports:
 *   - { resource: main.yml }
 * ...
 * </code>
 */
class YamlFileLoader extends FileLoader
{
    /**
     * YAML parser instance
     *
     * @var Parser
     */
    private $yamlParser;

    /**
     * Container builder
     *
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * YamlFileLoader constructor.
     *
     * @param FileLocatorInterface  $locator          File locator
     * @param ContainerBuilder|null $containerBuilder Container builder
     *                                                If passed then add all loaded files as container resource
     */
    public function __construct(FileLocatorInterface $locator, ContainerBuilder $containerBuilder = null)
    {
        parent::__construct($locator);

        $this->containerBuilder = $containerBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        $content = $this->loadFile($path);

        if ($this->containerBuilder !== null) {
            $this->containerBuilder->addResource(new FileResource($path));
        }

        // empty file
        if (null === $content) {
            return;
        }

        // imports
        if ($importedContent = $this->parseImports($content, $path)) {
            unset($content['imports']);
        } else {
            $importedContent = [];
        }

        return array_merge([$content], $importedContent);
    }

    /**
     * Loads a YAML file.
     *
     * @param string $file
     *
     * @return array The file content
     *
     * @throws InvalidArgumentException when the given file is not a local file or when it does not exist
     */
    protected function loadFile($file)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new RuntimeException(
                'Unable to load YAML config files as the Symfony Yaml Component is not installed.');
        }

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('The service file "%s" is not valid.', $file));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new Parser();
        }

        return $this->yamlParser->parse(file_get_contents($file));
    }

    /**
     * Parses all imports.
     *
     * @param array  $content File parsed content
     * @param string $file    File path
     *
     * @return array
     */
    private function parseImports($content, $file)
    {
        if (!isset($content['imports'])) {
            return;
        }

        if (!is_array($content['imports'])) {
            throw new InvalidArgumentException(
                sprintf('The "imports" key should contain an array in %s. Check your YAML syntax.', $file));
        }

        $imports = $content['imports'];
        unset($content['imports']);
        
        $summary = [];
        foreach ($imports as $import) {
            if (!is_array($import)) {
                throw new InvalidArgumentException(
                    sprintf('The values in the "imports" key should be arrays in %s. Check your YAML syntax.', $file));
            }

            $dir = dirname($file);
            $this->setCurrentDir($dir);
            if ($result = $this->import($import['resource'], null,
                isset($import['ignore_errors']) ? (bool) $import['ignore_errors'] : false, $file)
            ) {
                $summary = array_merge($summary, $result);
            }

            if ($this->containerBuilder !== null) {
                $resourcePath = realpath($dir.DIRECTORY_SEPARATOR.$import['resource']);
                if (!$resourcePath) {
                    throw new InvalidArgumentException(sprintf('Resource not found: %s', $resourcePath));
                }
                $this->containerBuilder->addResource(new FileResource($resourcePath));
            }
        }

        return $summary;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true);
    }
}