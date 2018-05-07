<?php

namespace AppGear\CoreBundle\Model;

use AppGear\CoreBundle\DependencyInjection\TaggedManager;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property;
use AppGear\CoreBundle\Helper\ModelHelper;
use Cosmologist\Gears\StringType;
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
     * Registered bundles
     *
     * @var array
     */
    private $bundles;

    /**
     * ModelManager constructor.
     *
     * @param array              $definitions   Models definitions
     * @param TaggedManager      $taggedManager Tagged service manager
     * @param ContainerInterface $container     Service container
     * @param array              $bundles       Registered bundles
     */
    public function __construct(
        array $definitions,
        TaggedManager $taggedManager,
        ContainerInterface $container,
        array $bundles
    )
    {
        $this->definitions   = $definitions;
        $this->taggedManager = $taggedManager;
        $this->container     = $container;
        $this->bundles       = $bundles;
    }

    /**
     * Return model by name
     *
     * @param string $name The model name
     *
     * @return Model
     */
    public function get($name)
    {
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
            $model->setParent($this->get($definition['parent']));
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
                        /** @var Property\Field $property */
                        $property = $this->instance($propertyDefinition['type']);
                        if (array_key_exists('defaultValue', $propertyDefinition)) {
                            $property->setDefaultValue($propertyDefinition['defaultValue']);
                        }
                        break;
                    case 'relationship':
                        $property = $this->instance($propertyDefinition['type']);
                        /* @var $property Property\Relationship */
                        if ($propertyDefinition['target'] !== null) {
                            $property->setTarget($this->get($propertyDefinition['target']));
                        }
                        $property->setComposition($propertyDefinition['composition']);
                        break;
                    case 'classType':
                        /** @var Property\ClassType $property */
                        $property = $this->instance('core.property.classType');
                        if (array_key_exists('className', $propertyDefinition)) {
                            $property->setClassName($propertyDefinition['className']);
                        }
                        break;
                    case 'collection':
                        /** @var Property\Collection $property */
                        $property = $this->instance('core.property.collection');
                        if (array_key_exists('className', $propertyDefinition)) {
                            $property->setClassName($propertyDefinition['className']);
                        }
                        break;
                    default:
                        throw new RuntimeException('Unexpected property type in the definition',
                            var_export($propertyDefinition));
                }

                $extensions = [];
                if (array_key_exists('extensions', $propertyDefinition) && is_array($propertyDefinition['extensions'])) {
                    foreach ($propertyDefinition['extensions'] as $extensionDefinition) {
                        $extensions[] = $this->load($extensionDefinition);
                    }
                }

                $property->setName($propertyName);
                $property->setCalculated($definition['properties'][$propertyName]['calculated'] ?? null);
                $property->setExtensions($extensions);

                $properties   = $model->getProperties();
                $properties[] = $property;
                $model->setProperties($properties);
            }
        }

        $extensions = [];
        if (array_key_exists('extensions', $definition) && is_array($definition['extensions'])) {
            foreach ($definition['extensions'] as $extensionDefinition) {
                $extensions[] = $this->load($extensionDefinition);
            }
            $model->setExtensions($extensions);
        }

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

        return new $className;
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
        return ModelHelper::getFqcn($name, $this->bundles);
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
     * Is instance of FQCN model
     *
     * @param string|object $instance FQCN or object
     *
     * @return string
     */
    public function isModel($instance)
    {
        if (!is_object($instance) && !is_string($instance)) {
            throw new RuntimeException('Invalid instance type: ' . gettype($instance));
        }

        $fqcn   = is_object($instance) ? get_class($instance) : $instance;
        $bundle = $this->findClassBundleAlias($fqcn);

        if ($bundle === null) {
            return false;
        }

        $bundleClass = (new \ReflectionClass($this->bundles[$bundle]));

        if (StringType::strAfter($fqcn, $bundleClass->getNamespaceName()) === false) {
            return false;
        }

        return true;
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
        if (!is_object($instance) && !is_string($instance)) {
            throw new RuntimeException('Invalid instance type: ' . gettype($instance));
        }

        $fqcn   = is_object($instance) ? get_class($instance) : $instance;
        $bundle = $this->findClassBundleAlias($fqcn);

        if ($bundle === null) {
            throw new RuntimeException('Bundle not found for object: ' . $fqcn);
        }

        $bundleClass = (new \ReflectionClass($this->bundles[$bundle]));

        // Ищем оставшуются часть после bundle namespace в fqcn
        // Ищем не с начала строки, так как $instance может быть проксёй вокрук модели
        // По факту это может быть причиной коллизий, если fqcn содержит namespace бандла, но не является моделью этого бандла
        // это крайне маловероятно, поэтому принимаем это допущение в пользу простоты резолвинга
        $parts = explode('\\', trim(StringType::strAfter($fqcn, $bundleClass->getNamespaceName()), '\\'));

        // remove "Entity" namespace part
        array_shift($parts);

        // Add bundle alias
        array_unshift($parts, $bundle);

        $parts = array_map(
            function ($value) {
                return CamelSnakeCase::camelToSnake($value);
            },
            $parts);
        $name  = implode('.', $parts);

        return $name;
    }

    /**
     * Find bundle alias for class
     *
     * @param string $fqcn FQCN
     *
     * @return null|string
     */
    protected function findClassBundleAlias($fqcn)
    {
        foreach ($this->bundles as $alias => $bundleClass) {
            $bundleClassRefl = new \ReflectionClass($bundleClass);
            // Проверяем что fqcn содержит bundle namespace
            // Проверяем не с начала строки, так как $instance может быть проксёй вокрук модели
            // По факту это может быть причиной коллизий, если fqcn содержит namespace бандла, но не является моделью этого бандла
            // это крайне маловероятно, поэтому принимаем это допущение в пользу простоты резолвинга
            if (strpos($fqcn, $bundleClassRefl->getNamespaceName()) !== false) {
                return $alias;
            }
        }

        return null;
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
                if ($definition['parent'] === $name) {
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
