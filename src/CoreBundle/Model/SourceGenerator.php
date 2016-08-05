<?php

namespace AppGear\CoreBundle\Model;

use AppGear\CoreBundle\Entity\Extension\Property\Computed;
use AppGear\CoreBundle\Entity\Model;
use AppGear\CoreBundle\Entity\Property\Property;
use AppGear\CoreBundle\Entity\Property\Relationship\ToMany;
use AppGear\CoreBundle\EntityService\ModelService;
use Cosmologist\Gears\FileSystem;
use PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Comment;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;

class SourceGenerator
{
    /**
     * Парсер PHP
     *
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * Фабрика для производства основных языковых конструкций
     *
     * @var \PhpParser\BuilderFactory
     */
    protected $factory;

    /**
     * Already generate models IDs
     *
     * @var array
     */
    protected $alreadyGeneratedModels = [];

    /**
     * Model manager
     *
     * @var ModelManager
     */
    private $manager;

    /**
     * Bundles classes
     *
     * @var array
     */
    private $bundlesClasses;

    /**
     * Constructor
     *
     * @param ModelManager $manager        Model manager
     * @param array        $bundlesClasses Bundles classes
     */
    public function __construct(ModelManager $manager, array $bundlesClasses)
    {
        $this->parser  = new Parser(new Lexer());
        $this->factory = new BuilderFactory();
        $this->manager = $manager;

        foreach ($bundlesClasses as $bundlesClass) {
            $this->bundlesClasses[(new ReflectionClass($bundlesClass))->getNamespaceName()] = $bundlesClass;
        }
    }

    /**
     * Генерирует исходный код
     *
     * @param Model $model Модель
     *
     * @return void
     */
    public function generate(Model $model)
    {
        // Проверяем что модель не генерировалась в текущей сессии (актуально при генерации родительских моделей)
        if (in_array($model->getName(), $this->alreadyGeneratedModels)) {
            return;
        }

        // Генерируем родительскую модель
        if ($parent = $model->getParent()) {
            $this->generate($parent);
        }

        // Принтер для генерации исходного кода на основе его представления
        $prettyPrinter = new Standard;

        $name   = $model->getName();
        $parent = $model->getParent();

        // Неймспейс
        $scope         = $this->manager->scope($name);
        $namespaceNode = new Node\Stmt\Namespace_(new Node\Name($scope));

        // Создаем класс для модели
        $classNode = $this->factory->class($this->manager->className($name));

        // Если модель наследуется
        $useNode = null;
        if ($parent !== null) {
            $parentName = $parent->getName();

            // Если имена моделей совпадают, то для избежаний конфликта добавляем extend с FQCN
            if ($this->manager->className($name) === $this->manager->className($parentName)) {
                $classNode->extend('\\' . $this->manager->fullClassName($parentName));
            } else {
                // Если неймспейс текущей модели не совпадает с неймспейсом родительской модели - добавляем его через use
                $parentScope = $this->manager->scope($parentName);
                if ($scope !== $parentScope) {
                    $useNode = new Node\Stmt\Use_(array(new UseUse(new Node\Name($this->manager->fullClassName($parentName)))));
                }

                $classNode->extend($this->manager->className($parentName));
            }
        }

        // Собираем свойства модели
        $this->buildProperties($model, $classNode);

        // Собираем __toString
        $this->buildToString($model, $classNode);

        // Генерируем исходный код
        $sourceElements = array($namespaceNode);
        if (isset($useNode)) {
            $sourceElements[] = $useNode;
        }
        $sourceElements[] = $classNode->getNode();
        $sourceCode       = $prettyPrinter->prettyPrintFile($sourceElements);

        // Сохраняем класс модели в файл
        $this->save($name, $sourceCode);

        $this->alreadyGeneratedModels[] = $name;
    }

    /**
     * Подключаем логику к классу через атомы
     *
     * @param Model $model     Модель
     * @param Node  $classNode Узел класса
     */
    private function buildProperties($model, $classNode)
    {
        foreach ($model->getProperties() as $property) {
            $this->buildProperty($property, $classNode);
        }
    }

    /**
     * Подключаем свойство к классу
     *
     * @param Property $property  Свойство
     * @param Node     $classNode Узел класса
     */
    private function buildProperty($property, $classNode)
    {
        $propertyName = $property->getName();

        // Создаем свойство
        $builder = $this->factory->property($propertyName)->makeProtected();

        // Значение по-умолчанию
        if ($property instanceof ToMany) {
            $builder->setDefault([]);
        }

        $node = $builder->getNode();

        // Комментарий к свойству
        $this->addDocComment($node, ucfirst($propertyName), 1);

        // Добавляем свойство к классу
        $classNode->addStmt($node);

        // Является ли свойство рассчитываемым
        $computedExtension = null;
        foreach ($property->getExtensions() as $extension) {
            if ($extension instanceof Computed) {
                $computedExtension = $extension;
                break;
            }
        }

        // Создаем свойства и сеттер для сервиса обеспечивающего расчет
        if ($computedExtension !== null) {
            $serviceName = $propertyName . 'Service';
            $builder     = $this->factory->property($serviceName)->makeProtected();
            $node        = $builder->getNode();
            $this->addDocComment($node, ucfirst($serviceName), 1);
            $classNode->addStmt($node);
            $this->addSetter($serviceName, $classNode);
        }

        // Сеттер нужен только для обычных полей
        if ($computedExtension === null) {
            $this->addSetter($propertyName, $classNode);
        }

        // Геттер
        $this->addGetter($propertyName, $classNode, $computedExtension);
    }

    /**
     * Генерирует док-комментарий из переданных строк комментария и добавляет к узлу
     *
     * @param Node         $node                    Узел к которому добавляется комментарий
     * @param array|string $comment                 Комментарий в виде строки или набора строк
     * @param int          $verticalOffsetLineCount Количество пустых строк перед комментарием
     */
    private function addDocComment($node, $comment, $verticalOffsetLineCount = 0)
    {
        // Если комментарий передан в виде одной строки - разбиваем на несколько строк
        if (is_string($comment)) {
            $lines = array_map('trim', explode("\n", $comment));
        } else {
            $lines = $comment;
        }

        $formattedComment = str_pad(PHP_EOL, $verticalOffsetLineCount);
        $formattedComment .= '/**' . PHP_EOL;
        foreach ($lines as $line) {
            $formattedComment .= ' * ' . $line . PHP_EOL;
        }
        $formattedComment .= ' */';

        $node->setAttribute('comments', array(new Comment\Doc($formattedComment)));
    }

    /**
     * Добавляет cеттер к классу для свойства
     *
     * @param string $propertyName Геттер для свойства
     * @param Node   $classNode    Узел класса
     */
    private function addSetter($propertyName, $classNode)
    {
        $setter = 'set' . ucfirst($propertyName);
        $code   = '<?php $this->' . $propertyName . ' = $' . $propertyName . '; return $this;';

        $this->addMethod($classNode, $setter, [$propertyName], $code, 'Set ' . $propertyName);
    }

    /**
     * Добавляет геттер к классу для свойства
     *
     * @param string $propertyName Геттер для свойства
     * @param Node   $classNode    Узел класса
     * @param null   $extension    Computed extension
     */
    private function addGetter($propertyName, $classNode, $extension = null)
    {
        $getter = 'get' . ucfirst($propertyName);

        if ($extension === null) {
            $code = '<?php return $this->' . $propertyName . ';';
        } else {
            /** @var ModelService $modelService */
            $modelService = $this->manager->getByInstance($extension);
            $options      = [];
            foreach ($modelService->getProperties() as $property) {
                $fieldGetter = 'get' . ucfirst($property->getName());
                $options[]   = '\'' . $property->getName() . '\' => ' . '\'' . $extension->$fieldGetter() . '\'';
            }
            $options = '[' . implode(', ', $options) . ']';

            $code = '<?php return $this->' . $propertyName . 'Service->compute($this, \'' . $propertyName . '\', ' . $options . ');';
        }

        $this->addMethod($classNode, $getter, [], $code, 'Get ' . $propertyName);
    }

    /**
     * Добавляет метод к классу для свойства
     *
     * @param Node   $classNode  Узел класса
     * @param string $name       Имя метод
     * @param array  $parameters Список параметров
     * @param string $code       Код метода
     * @param string $comment    Комментарий к методу
     */
    private function addMethod($classNode, $name, $parameters, $code, $comment)
    {
        $node = $this->factory->method($name)->makePublic();
        foreach ($parameters as $parameter) {
            if (is_scalar($parameter)) {
                $name = $parameter;
            } else {
                $name = $parameter['name'];
            }

            $node->addParam($this->factory->param($name));
        }
        $node->addStmts($this->parser->parse($code));
        $node = $node->getNode();
        $this->addDocComment($node, $comment, 1);
        $classNode->addStmt($node);
    }

    /**
     * Подключаем логику к классу через атомы
     *
     * @param Model $model     Модель
     * @param Node  $classNode Узел класса
     */
    private function buildToString($model, $classNode)
    {
        if ($toString = $model->getToString()) {
            $node = $this->factory->method('__toString')->makePublic();
            $code = '<?php return (string) $this->' . $toString . ';';
            $node->addStmts($this->parser->parse($code));
            $node = $node->getNode();
            $classNode->addStmt($node);
        }
    }

    /**
     * Сохраняет исходный код модели в соответсующий файл
     *
     * @param string $name       Имя модели
     * @param string $sourceCode Исходный код модели
     */
    private function save($name, $sourceCode)
    {
        $path    = $this->getModelPath($name);
        $dirPath = dirname($path);

        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        file_put_contents($path, $sourceCode);
    }

    /**
     * Формирует путь для класса модели по ее имени
     *
     * @param string $name Имя модели
     *
     * @return string
     */
    private function getModelPath($name)
    {
        $className = $this->manager->fullClassName($name);
        foreach ($this->bundlesClasses as $bundlesNamespace => $bundlesClass) {
            if (strpos($className, $bundlesNamespace) === 0) {
                $bundleDir             = dirname((new ReflectionClass($bundlesClass))->getFileName());
                $specificClassNamePart = substr($className, strlen($bundlesNamespace));
                $specificClassNamePath = FileSystem::normalizeSeparators($specificClassNamePart);
                $path                  = FileSystem::joinPaths([$bundleDir, $specificClassNamePath]) . '.php';

                return $path;
            }
        }

        throw new \RuntimeException(sprintf('Can\'t determine path for the model "%s"', $name));
    }
}