<?php

namespace AppGear\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TaggedManager implements manager for the tagged service
 */
class TaggedManager
{
    /**
     * Services
     *
     * @var array
     */
    protected $services = [];

    /**
     * Service container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * TaggedManager constructor.
     *
     * @param ContainerInterface $container Service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Add service to manager
     *
     * @param string $id         Service ID
     * @param string $tag        Service tag
     * @param array  $attributes Service tag attributes
     */
    public function addService($id, $tag, array $attributes = [])
    {
        $this->services[] = compact('id', 'tag', 'attributes');
    }

    /**
     * Find service by tag and attributes
     *
     * @param string $tag        Tag name
     * @param array  $attributes Search for attributes (by key-value)
     *
     * @todo Метод должен возвращать инстанц сервиса, а не его описание
     *       Сейчас описание сервиса используется только в GenerateSource::buildLogic, поэтому когда избавимся от
     *       PlatformBundle, то нужно будет провести данный рефакторинг
     *
     * @return array
     */
    public function findServices($tag, array $attributes = [])
    {
        $result = [];

        foreach ($this->services as $service) {
            if ($service['tag'] === $tag) {

                if ($attributes === [] || $this->checkAttributes($service['attributes'], $attributes)) {
                    $result[] = $service;
                }
            }
        }

        return $result;
    }

    /**
     * Return service by tag and attributes
     *
     * @param string $tag        Tag name
     * @param array  $attributes Search for attributes (by key-value)
     *
     * @return object
     */
    public function get($tag, array $attributes = [])
    {
        foreach ($this->services as $service) {
            if ($service['tag'] === $tag) {

                if ($attributes === [] || $this->checkAttributes($service['attributes'], $attributes)) {
                    return $this->container->get($service['id']);
                }
            }
        }

        return null;
    }

    /**
     * Check if service attributes contains needed attributes
     *
     * @param array $serviceAttributes Service attributes
     * @param array $needAttributes    Need attributes (by key-value)
     *
     * @return bool
     */
    protected function checkAttributes($serviceAttributes, $needAttributes)
    {
        foreach ($needAttributes as $name => $value) {
            if (!array_key_exists($name, $serviceAttributes) || $serviceAttributes[$name] !== $value) {
                return false;
            }
        }

        return true;
    }
}