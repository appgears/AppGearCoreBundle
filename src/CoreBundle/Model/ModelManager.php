<?php

namespace AppGear\CoreBundle\Model;

use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Extension\Property\Computed;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use Cosmologist\Gears\StringType\CamelSnakeCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModelManager
{
    /**
     * Models definitions
     *
     * @var array
     */
    private $definitions = [];

    /**
     * Model to module map
     *
     * Key is full model name, value is module name
     *
     * @var array
     */
    private $modelModuleMap = [];

    /**
     * Models
     *
     * @var array
     */
    private $models = [];

    /**
     * Tagged service manager
     *
     * @var TaggedManager
     */
    private $taggedManager;

    /**
     * Service container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * ModelManager constructor.
     *
     * @param array              $definitions   Models definitions
     * @param TaggedManager      $taggedManager Tagged service manager
     * @param ContainerInterface $container     Service container
     */
    public function __construct(array $definitions, TaggedManager $taggedManager, ContainerInterface $container)
    {
        foreach ($definitions as $moduleName => $moduleDefinitions) {
            foreach ($moduleDefinitions as $name => $definition) {
                $fullModelName                        = $moduleName . '.entity.' . $name;
                $this->definitions[$fullModelName]    = $definition;
                $this->modelModuleMap[$fullModelName] = $moduleName;
            }
        }
        $this->taggedManager = $taggedManager;
        $this->container     = $container;
    }

    /**
     * Return model by name
     *
     * @param string $name   The model name
     * @param string $prefix Prefix
     *
     * @return Model
     */
    public function get($name, $prefix = '')
    {
        $name = $this->fullName($name, $prefix);

        if (!array_key_exists($name, $this->models)) {
            $this->initialize($name);
        }

        return $this->models[$name];
    }

    /**
     * Return model by it instance
     *
     * @param string|object $instance FQCN or object
     *
     * @return Model
     */
    public function getByInstance($instance)
    {
        return $this->get($this->name($instance));
    }

    /**
     * Return all existing models
     *
     * @return Model[]
     */
    public function all()
    {
        // Check if all models are initialized
        if (count($this->definitions) !== count($this->models)) {

            // Get not-initialized models names
            $toInitialize = array_diff(array_keys($this->definitions), array_keys($this->models));
            foreach ($toInitialize as $name) {
                $this->initialize($name);
            }
        }

        return $this->models;
    }

    /**
     * Initialize model
     *
     * @param string $name The model name
     */
    protected function initialize($name)
    {
        if (!array_key_exists($name, $this->definitions)) {
            throw new RuntimeException(sprintf('Definition for the "%s" model is not exists', $name));
        }
        $definition = $this->definitions[$name];

        $model = new Model();
        $model->setName($name);
        $this->models[$name] = $model;

        if (array_key_exists('parent', $definition)) {
            $model->setParent($this->get($definition['parent'], $this->modelModuleMap[$name] . '.entity.'));
        }
        if (array_key_exists('abstract', $definition)) {
            $model->setAbstract($definition['abstract']);
        }
        if (array_key_exists('toString', $definition)) {
            $model->setToString($definition['toString']);
        }

        if (array_key_exists('properties', $definition)) {
            foreach ($definition['properties'] as $propertyName => $propertyDefinition) {
                /* @var $property Property */
                $type               = key($propertyDefinition);
                $propertyDefinition = $propertyDefinition[$type];
                switch ($type) {
                    case 'field':
                        $propertyType      = $propertyDefinition['type'];
                        $propertyModelName = $this->fullName($propertyType, $this->modelModuleMap[$name] . '.entity.property.field.');
                        $property          = $this->instance($propertyModelName);
                        break;
                    case 'relationship':
                        $propertyType      = $propertyDefinition['type'];
                        $propertyModelName = $this->fullName($propertyType, $this->modelModuleMap[$name] . '.entity.property.relationship.');
                        $property          = $this->instance($propertyModelName);
                        /* @var $property Property\Relationship */
                        $property->setTarget($this->get($propertyDefinition['target'], $this->modelModuleMap[$name] . '.entity.'));
                        break;
                    case 'classType':
                        /** @var Property\ClassType $property */
                        $property = $this->instance('app_gear.core_bundle.entity.property.classType');
                        if (array_key_exists('className', $propertyDefinition)) {
                            $property->setClassName($propertyDefinition['className']);
                        }
                        break;
                    case 'collection':
                        /** @var Property\Collection $property */
                        $property = $this->instance('app_gear.core_bundle.entity.property.collection');
                        if (array_key_exists('className', $propertyDefinition)) {
                            $property->setClassName($propertyDefinition['className']);
                        }
                        break;
                    default:
                        throw new RuntimeException('Unexpected property type in the definition',
                            var_export($propertyDefinition));
                }

                $extensions = [];
                if (array_key_exists('extensions', $propertyDefinition) &&
                    is_array($propertyDefinition['extensions']) &&
                    count($propertyDefinition['extensions']) > 0
                ) {
                    foreach ($propertyDefinition['extensions'] as $extensionDefinition) {
                        $extensions[] = $this->load($extensionDefinition);
                    }
                }

                $property->setName($propertyName);
                $property->setExtensions($extensions);

                $properties   = $model->getProperties();
                $properties[] = $property;
                $model->setProperties($properties);
            }
        }
    }

    /**
     * Build model full name
     *
     * @param string $name   Short model name
     * @param string $prefix Prefix
     *
     * @return string
     */
    protected function fullName($name, $prefix = '')
    {
        if (array_key_exists($name, $this->definitions)) {
            return $name;
        }

        return $prefix . $name;
    }

    /**
     * Get model instance
     *
     * @param string $name The model name
     *
     * @return mixed
     */
    public function instance($name)
    {
        $className = $this->fullClassName($name);
        $instance  = new $className;
        $this->injectServices($name, $instance);

        return new $className;
    }

    /**
     * Inject services for computed fields
     *
     * @param string $name     The model name
     * @param object $instance Instance
     *
     * @return object
     */
    public function injectServices($name, $instance)
    {
        $model = $this->get($name);
        foreach ($model->getProperties() as $property) {
            /** @var Property $property $extension */
            foreach ($property->getExtensions() as $extension) {
                if ($extension instanceof Computed) {
                    $extensionModel = $this->getByInstance($extension);
                    $services       = $this->taggedManager->findServices(
                        'extension.property.computed',
                        ['model' => $extensionModel->getName()]
                    );
                    if (count($services) !== 1) {
                        throw new \RuntimeException(
                            sprintf('Found more than 1 or not found services for computed extension with name: %s',
                                $extensionModel->getName())
                        );
                    }

                    $service       = array_pop($services);
                    $serviceSetter = 'set' . ucfirst($property->getName()) . 'Service';
                    $instance->$serviceSetter($this->container->get($service['id']));

                    break;
                }
            }
        }

        return $instance;
    }

    /**
     * Build class name for the model name
     *
     * @param string $name The model name
     *
     * @return string The class name
     */
    public function className($name)
    {
        $items     = explode('.', $name);
        $className = array_pop($items);
        $className = CamelSnakeCase::snakeToCamel($className);

        return $className;
    }

    /**
     * Build full class name for the model name
     *
     * @param string $name The model name
     *
     * @return string The class name
     */
    public function fullClassName($name)
    {
        $className = CamelSnakeCase::snakeToCamel($name);
        $className = str_replace('.', ' ', $className);
        $className = ucwords($className);
        $className = str_replace(' ', '\\', $className);

        return $className;
    }

    /**
     * Return namespace for the model class name
     *
     * @param string $name The model name
     *
     * @return string Namespace
     */
    public function scope($name)
    {
        $items = explode('\\', $this->fullClassName($name));
        array_pop($items);

        return implode('\\', $items);
    }

    /**
     * Return model name by instance
     *
     * @param string|object $instance FQCN or object
     *
     * @return string
     */
    public function name($instance)
    {
        $fqcn  = is_object($instance) ? get_class($instance) : $instance;
        $parts = explode('\\', $fqcn);
        $parts = array_map(
            function ($value) {
                return CamelSnakeCase::camelToSnake($value);
            },
            $parts);
        $name  = implode('.', $parts);

        return $name;
    }

    /**
     * Return children for model
     *
     * @param string $name Name
     *
     * @return Model[]
     */
    public function children($name)
    {
        $children = [];
        foreach ($this->definitions as $definitionName => $definition) {
            if (array_key_exists('parent', $definition)) {
                $parentName     = $definition['parent'];
                $parentFullName = $this->fullName($parentName, $this->modelModuleMap[$name] . '.entity.');
                if ($parentFullName === $name) {
                    $children[] = $this->get($definitionName);
                }
            }
        }

        return $children;
    }

    /**
     * Load definition model, instance it and simple init with data
     *
     * @param array $definition Definition
     *
     * @return object
     */
    protected function load($definition)
    {
        $extension = $this->instance($definition['type']);
        foreach ($definition as $key => $value) {
            if ($key === 'type') {
                continue;
            }
            $setter = 'set' . ucfirst($key);
            if (method_exists($extension, $setter)) {
                $extension->$setter($value);
            }
        }

        return $extension;
    }
}
